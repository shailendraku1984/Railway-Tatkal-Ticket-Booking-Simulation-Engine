<?php

namespace App\Services;

use App\Repositories\TatkalRepository;
use CodeIgniter\Database\Exceptions\DatabaseException;
use Config\Services;
use Config\Tatkal;
use Throwable;

class TatkalBookingService
{
    private TatkalRepository $repo;
    private TatkalLockManager $locks;
    private Tatkal $config;

    public function __construct(?TatkalRepository $repo = null, ?TatkalLockManager $locks = null, ?Tatkal $config = null)
    {
        $this->repo = $repo ?? new TatkalRepository();
        $this->locks = $locks ?? new TatkalLockManager();
        $this->config = $config ?? config(Tatkal::class);
    }

    public function book(array $request): array
    {
        $startedAt = microtime(true);
        $train = $this->repo->trainByNumber($request['train_number'] ?? $this->config->trainNumber);

        if ($train === null) {
            return $this->failed('TRAIN_NOT_FOUND', 'Train configuration has not been seeded.');
        }

        if (! $this->isOpen($train['tatkal_opening_time'])) {
            return $this->failed('TATKAL_NOT_OPEN', 'Tatkal booking has not opened yet.');
        }

        return $this->withRetry(function () use ($request, $train, $startedAt) {
            $db = $this->repo->db();
            $lockName = 'tatkal_train_' . $train['train_number'];

            if (! $this->locks->acquire($db, $lockName, 10)) {
                return $this->failed('LOCK_TIMEOUT', 'Could not acquire train booking lock.');
            }

            try {
                $db->transBegin();
                $this->repo->lockTrain((int) $train['id']);

                $preferredType = $this->normalizeSeatType($request['seat_preference'] ?? null);
                $seat = $this->repo->lockAvailableSeat((int) $train['id'], $preferredType);
                $now = date('Y-m-d H:i:s');
                $sessionToken = bin2hex(random_bytes(16));

                if ($seat !== null) {
                    $pnr = $this->generatePnr();
                    $amount = $this->fareForCompartment((string) $seat['compartment_code']);
                    $bookingId = $this->repo->createBooking([
                        'session_token' => $sessionToken,
                        'pnr' => $pnr,
                        'train_id' => $train['id'],
                        'train_number' => $train['train_number'],
                        'seat_id' => $seat['id'],
                        'preferred_seat_type' => $preferredType,
                        'status' => 'CONFIRMED',
                        'booking_amount' => $amount,
                        'booking_time' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $this->repo->markSeatBooked((int) $seat['id'], $bookingId);
                    $this->createPassenger($bookingId, $request);
                    $this->repo->addStatusHistory($bookingId, null, 'CONFIRMED', 'Seat allocated with pessimistic row lock.');
                    $this->repo->audit($bookingId, 'BOOKING_CONFIRMED', ['seat_id' => $seat['id']], $this->elapsed($startedAt));
                    $db->transCommit();

                    return [
                        'ok' => true,
                        'status' => 'CONFIRMED',
                        'pnr' => $pnr,
                        'compartment' => $seat['compartment_code'],
                        'seat_number' => (int) $seat['seat_number'],
                        'seat_type' => $seat['seat_type'],
                        'booking_amount' => $amount,
                    ];
                }

                [$firstName, $lastName] = $this->requestNameParts($request);
                $this->repo->addRejectedRequest([
                    'session_id' => $sessionToken,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'contact_no' => $request['mobile_number'] ?? $request['mobile'] ?? '',
                    'request_time' => $now,
                    'message' => 'No seat available',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $this->repo->audit(null, 'BOOKING_REJECTED', ['session_id' => $sessionToken, 'message' => 'No seat available'], $this->elapsed($startedAt));
                $db->transCommit();

                return [
                    'ok' => false,
                    'status' => 'REJECTED',
                    'session_id' => $sessionToken,
                    'code' => 'NO_SEAT_AVAILABLE',
                    'message' => 'No seat available',
                ];
            } catch (Throwable $exception) {
                if ($db->transStatus() !== false) {
                    $db->transRollback();
                }
                throw $exception;
            } finally {
                $this->locks->release($db, $lockName);
            }
        });
    }

    public function cancel(string $pnr, string $reason = 'Passenger requested cancellation'): array
    {
        return $this->withRetry(function () use ($pnr, $reason) {
            $db = $this->repo->db();
            $lockName = 'tatkal_cancel_' . $this->config->trainNumber;

            if (! $this->locks->acquire($db, $lockName, 10)) {
                return $this->failed('LOCK_TIMEOUT', 'Could not acquire cancellation lock.');
            }

            try {
                $db->transBegin();
                $booking = $this->repo->lockBookingByPnr($pnr);
                if ($booking === null) {
                    $db->transRollback();
                    return $this->failed('PNR_NOT_FOUND', 'PNR was not found.');
                }
                if ($booking['status'] === 'CANCELLED') {
                    $db->transRollback();
                    return $this->failed('ALREADY_CANCELLED', 'Booking is already cancelled.');
                }

                $now = date('Y-m-d H:i:s');
                $this->repo->updateBooking((int) $booking['id'], ['status' => 'CANCELLED', 'cancelled_at' => $now]);
                $this->repo->addStatusHistory((int) $booking['id'], $booking['status'], 'CANCELLED', $reason);
                $db->table('cancellations')->insert([
                    'booking_id' => $booking['id'],
                    'cancelled_pnr' => $pnr,
                    'reason' => $reason,
                    'created_at' => $now,
                ]);

                if ($booking['status'] === 'CONFIRMED' && $booking['seat_id'] !== null) {
                    $this->repo->releaseSeat((int) $booking['seat_id']);
                }

                $this->repo->audit((int) $booking['id'], 'BOOKING_CANCELLED', ['pnr' => $pnr, 'previous_status' => $booking['status']]);
                $db->transCommit();

                return ['ok' => true, 'status' => 'CANCELLED', 'pnr' => $pnr];
            } catch (Throwable $exception) {
                if ($db->transStatus() !== false) {
                    $db->transRollback();
                }
                throw $exception;
            } finally {
                $this->locks->release($db, $lockName);
            }
        });
    }

    public function dashboard(): array
    {
        $train = $this->repo->trainByNumber($this->config->trainNumber);
        if ($train === null) {
            return ['train' => null, 'metrics' => [], 'reports' => []];
        }

        return [
            'train' => $train,
            'metrics' => $this->repo->activeCounts((int) $train['id']),
            'reports' => $this->repo->reports((int) $train['id']),
        ];
    }

    public function search(string $term): array
    {
        return $this->repo->searchBookings($term);
    }

    public function pnr(string $pnr): ?array
    {
        return $this->repo->pnrDetails($pnr);
    }

    public function rejectedRequests(): array
    {
        return $this->repo->rejectedRequests();
    }

    public function reset(): void
    {
        $this->repo->resetBookingHistory();
    }

    private function createPassenger(int $bookingId, array $request): void
    {
        [$firstName, $lastName] = $this->requestNameParts($request);

        $this->repo->createPassenger([
            'booking_id' => $bookingId,
            'passenger_name' => trim($firstName . ' ' . $lastName),
            'age' => (int) ($request['age'] ?? 18),
            'gender' => $request['gender'] ?? 'Male',
            'mobile_number' => $request['mobile_number'] ?? $request['mobile'] ?? '',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function requestNameParts(array $request): array
    {
        $gender = $request['gender'] ?? 'Male';
        $firstName = trim((string) ($request['first_name'] ?? ''));
        $lastName = trim((string) ($request['last_name'] ?? ''));

        if ($firstName === '' && isset($request['passenger_name'])) {
            $parts = preg_split('/\s+/', trim((string) $request['passenger_name'])) ?: [];
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        if ($firstName === '') {
            $firstName = 'Passenger';
        }

        if ($lastName === '') {
            $lastName = $gender === 'Female' ? 'Kumari' : 'Kumar';
        }

        return [$firstName, $lastName];
    }

    private function fareForCompartment(string $compartmentCode): float
    {
        return str_starts_with($compartmentCode, 'B') ? 1200.00 : 500.00;
    }

    private function generatePnr(): string
    {
        do {
            $pnr = (string) random_int(1000000000, 9999999999);
        } while ($this->repo->pnrDetails($pnr) !== null);

        return $pnr;
    }

    private function normalizeSeatType(?string $type): ?string
    {
        if ($type === null || $type === '') {
            return null;
        }

        $map = [
            'LOWER' => 'LB',
            'MIDDLE' => 'MB',
            'UPPER' => 'UB',
            'SIDE LOWER' => 'SL',
            'SIDE_LOWER' => 'SL',
            'SIDE UPPER' => 'SU',
            'SIDE_UPPER' => 'SU',
            'LB' => 'LB',
            'MB' => 'MB',
            'UB' => 'UB',
            'SL' => 'SL',
            'SU' => 'SU',
        ];

        return $map[strtoupper(trim($type))] ?? null;
    }

    private function isOpen(string $openingTime): bool
    {
        return date('H:i:s') >= $openingTime;
    }

    private function withRetry(callable $callback): array
    {
        $attempt = 0;
        beginning:
        try {
            return $callback();
        } catch (DatabaseException $exception) {
            $attempt++;
            if ($attempt <= $this->config->deadlockRetries && $this->isRetryable($exception)) {
                usleep(50000 * $attempt);
                goto beginning;
            }
            Services::logger()->error('Tatkal booking failed: ' . $exception->getMessage());
            return $this->failed('DATABASE_ERROR', $exception->getMessage());
        } catch (Throwable $exception) {
            Services::logger()->error('Tatkal booking failed: ' . $exception->getMessage());
            return $this->failed('BOOKING_FAILED', $exception->getMessage());
        }
    }

    private function isRetryable(DatabaseException $exception): bool
    {
        return str_contains($exception->getMessage(), 'Deadlock')
            || str_contains($exception->getMessage(), 'Lock wait timeout')
            || str_contains($exception->getMessage(), '1213')
            || str_contains($exception->getMessage(), '1205');
    }

    private function failed(string $code, string $message): array
    {
        return ['ok' => false, 'status' => 'FAILED', 'code' => $code, 'message' => $message];
    }

    private function elapsed(float $startedAt): int
    {
        return (int) round((microtime(true) - $startedAt) * 1000);
    }
}
