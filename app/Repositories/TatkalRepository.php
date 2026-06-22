<?php

namespace App\Repositories;

use CodeIgniter\Database\BaseConnection;
use Config\Database;

class TatkalRepository
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    public function db(): BaseConnection
    {
        return $this->db;
    }

    public function trainByNumber(string $trainNumber): ?array
    {
        return $this->db->table('trains')->where('train_number', $trainNumber)->get()->getRowArray();
    }

    public function lockTrain(int $trainId): ?array
    {
        return $this->db->query('SELECT * FROM trains WHERE id = ? FOR UPDATE', [$trainId])->getRowArray();
    }

    public function lockAvailableSeat(int $trainId, ?string $preferredType = null): ?array
    {
        $types = $this->seatPreferenceOrder($preferredType);
        $placeholders = implode(',', array_fill(0, count($types), '?'));
        $params = array_merge([$trainId], $types);

        return $this->db->query(
            "SELECT s.*, c.code AS compartment_code
             FROM seats s
             INNER JOIN compartments c ON c.id = s.compartment_id
             WHERE s.train_id = ? AND s.status = 'AVAILABLE'
             ORDER BY FIELD(s.seat_type, {$placeholders}), c.id, s.seat_number
             LIMIT 1 FOR UPDATE",
            $params
        )->getRowArray();
    }

    public function markSeatBooked(int $seatId, int $bookingId): void
    {
        $this->db->query(
            "UPDATE seats
             SET status = 'BOOKED', booking_id = ?, lock_version = lock_version + 1, updated_at = ?
             WHERE id = ? AND status = 'AVAILABLE'",
            [$bookingId, date('Y-m-d H:i:s'), $seatId]
        );
    }

    public function releaseSeat(int $seatId): void
    {
        $this->db->query(
            "UPDATE seats
             SET status = 'AVAILABLE', booking_id = NULL, lock_version = lock_version + 1, updated_at = ?
             WHERE id = ?",
            [date('Y-m-d H:i:s'), $seatId]
        );
    }

    public function createBooking(array $data): int
    {
        $this->db->table('bookings')->insert($data);
        return (int) $this->db->insertID();
    }

    public function createPassenger(array $data): int
    {
        $this->db->table('passengers')->insert($data);
        return (int) $this->db->insertID();
    }

    public function addStatusHistory(int $bookingId, ?string $oldStatus, string $newStatus, string $note): void
    {
        $this->db->table('booking_status_history')->insert([
            'booking_id' => $bookingId,
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'note' => $note,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function nextRacNumber(int $capacity): ?int
    {
        $active = $this->db->query(
            "SELECT rac_number FROM rac_queue WHERE status = 'ACTIVE' ORDER BY rac_number FOR UPDATE"
        )->getResultArray();

        $used = array_flip(array_map(static fn ($row) => (int) $row['rac_number'], $active));
        for ($i = 1; $i <= $capacity; $i++) {
            if (! isset($used[$i])) {
                return $i;
            }
        }

        return null;
    }

    public function nextWaitlistNumber(): int
    {
        $row = $this->db->query(
            "SELECT waitlist_number
             FROM waiting_queue
             WHERE status = 'ACTIVE'
             ORDER BY waitlist_number DESC
             LIMIT 1 FOR UPDATE"
        )->getRowArray();

        return (int) ($row['waitlist_number'] ?? 0) + 1;
    }

    public function addRac(int $bookingId, int $racNumber): void
    {
        $this->db->table('rac_queue')->insert([
            'booking_id' => $bookingId,
            'rac_number' => $racNumber,
            'status' => 'ACTIVE',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function addWaiting(int $bookingId, int $waitlistNumber): void
    {
        $this->db->table('waiting_queue')->insert([
            'booking_id' => $bookingId,
            'waitlist_number' => $waitlistNumber,
            'status' => 'ACTIVE',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function activeRacForUpdate(): ?array
    {
        return $this->db->query(
            "SELECT * FROM rac_queue WHERE status = 'ACTIVE' ORDER BY rac_number LIMIT 1 FOR UPDATE"
        )->getRowArray();
    }

    public function activeWaitingForUpdate(): ?array
    {
        return $this->db->query(
            "SELECT * FROM waiting_queue WHERE status = 'ACTIVE' ORDER BY waitlist_number LIMIT 1 FOR UPDATE"
        )->getRowArray();
    }

    public function lockBookingByPnr(string $pnr): ?array
    {
        return $this->db->query(
            "SELECT b.*, p.passenger_name, p.mobile_number
             FROM bookings b
             INNER JOIN passengers p ON p.booking_id = b.id
             WHERE b.pnr = ?
             LIMIT 1 FOR UPDATE",
            [$pnr]
        )->getRowArray();
    }

    public function updateBooking(int $bookingId, array $data): void
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $this->db->table('bookings')->where('id', $bookingId)->update($data);
    }

    public function audit(?int $bookingId, string $eventType, array $payload = [], ?int $durationMs = null): void
    {
        $this->db->table('booking_audit_logs')->insert([
            'booking_id' => $bookingId,
            'event_type' => $eventType,
            'payload' => json_encode($payload),
            'duration_ms' => $durationMs,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function addRejectedRequest(array $data): int
    {
        $this->db->table('rejected_booking_requests')->insert($data);
        return (int) $this->db->insertID();
    }

    public function rejectedRequests(int $limit = 200): array
    {
        return $this->db->table('rejected_booking_requests')
            ->orderBy('request_time', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }

    public function resetBookingHistory(): void
    {
        $now = date('Y-m-d H:i:s');
        $this->db->disableForeignKeyChecks();

        foreach ([
            'booking_audit_logs',
            'cancellations',
            'waiting_queue',
            'rac_queue',
            'booking_status_history',
            'passengers',
            'bookings',
            'rejected_booking_requests',
        ] as $table) {
            $this->db->table($table)->truncate();
        }

        $this->db->table('seats')->update([
            'status' => 'AVAILABLE',
            'booking_id' => null,
            'lock_version' => 0,
            'updated_at' => $now,
        ]);

        $this->db->enableForeignKeyChecks();
    }

    public function activeCounts(int $trainId): array
    {
        $row = $this->db->query(
            "SELECT
                SUM(CASE WHEN s.status = 'AVAILABLE' THEN 1 ELSE 0 END) AS available_seats,
                SUM(CASE WHEN s.status = 'BOOKED' THEN 1 ELSE 0 END) AS booked_seats,
                COUNT(*) AS total_seats
             FROM seats s
             WHERE s.train_id = ?",
            [$trainId]
        )->getRowArray();

        $status = $this->db->query(
            "SELECT status, COUNT(*) AS total FROM bookings WHERE train_id = ? GROUP BY status",
            [$trainId]
        )->getResultArray();

        foreach ($status as $item) {
            $row[strtolower($item['status'])] = (int) $item['total'];
        }

        foreach (['confirmed', 'rac', 'waiting', 'cancelled'] as $key) {
            $row[$key] = (int) ($row[$key] ?? 0);
        }

        $row['rejected'] = (int) $this->db->table('rejected_booking_requests')->countAllResults();
        $row['revenue'] = (int) ($this->db->query(
            "SELECT COALESCE(SUM(booking_amount), 0) AS revenue
             FROM bookings
             WHERE train_id = ? AND status = 'CONFIRMED'",
            [$trainId]
        )->getRowArray()['revenue'] ?? 0);

        return array_map('intval', $row);
    }

    public function searchBookings(string $term): array
    {
        return $this->db->query(
            "SELECT b.*, p.passenger_name, p.age, p.gender, p.mobile_number,
                    c.code AS compartment, s.seat_number, s.seat_type
             FROM bookings b
             INNER JOIN passengers p ON p.booking_id = b.id
             LEFT JOIN seats s ON s.id = b.seat_id
             LEFT JOIN compartments c ON c.id = s.compartment_id
             WHERE b.pnr = ? OR p.mobile_number = ? OR p.passenger_name LIKE ?
             ORDER BY b.booking_time DESC
             LIMIT 50",
            [$term, $term, '%' . $term . '%']
        )->getResultArray();
    }

    public function pnrDetails(string $pnr): ?array
    {
        return $this->db->query(
            "SELECT b.pnr, b.train_number, b.status, b.rac_number, b.waitlist_number, b.booking_amount,
                    p.passenger_name, p.age, p.gender, p.mobile_number,
                    c.code AS compartment, s.seat_number, s.seat_type
             FROM bookings b
             INNER JOIN passengers p ON p.booking_id = b.id
             LEFT JOIN seats s ON s.id = b.seat_id
             LEFT JOIN compartments c ON c.id = s.compartment_id
             WHERE b.pnr = ?
             LIMIT 1",
            [$pnr]
        )->getRowArray();
    }

    public function reports(int $trainId): array
    {
        return [
            'summary' => $this->activeCounts($trainId),
            'compartments' => $this->db->query(
                "SELECT c.code, c.class_type,
                        SUM(CASE WHEN s.status = 'BOOKED' THEN 1 ELSE 0 END) AS booked,
                        COUNT(*) AS total
                 FROM compartments c
                 INNER JOIN seats s ON s.compartment_id = c.id
                 WHERE c.train_id = ?
                 GROUP BY c.id, c.code, c.class_type
                 ORDER BY c.id",
                [$trainId]
            )->getResultArray(),
            'gender' => $this->db->query(
                "SELECT p.gender, COUNT(*) AS total
                 FROM passengers p INNER JOIN bookings b ON b.id = p.booking_id
                 WHERE b.train_id = ? GROUP BY p.gender",
                [$trainId]
            )->getResultArray(),
            'age' => $this->db->query(
                "SELECT
                    SUM(CASE WHEN p.age BETWEEN 18 AND 25 THEN 1 ELSE 0 END) AS age_18_25,
                    SUM(CASE WHEN p.age BETWEEN 26 AND 40 THEN 1 ELSE 0 END) AS age_26_40,
                    SUM(CASE WHEN p.age BETWEEN 41 AND 55 THEN 1 ELSE 0 END) AS age_41_55,
                    SUM(CASE WHEN p.age >= 56 THEN 1 ELSE 0 END) AS age_56_plus
                 FROM passengers p INNER JOIN bookings b ON b.id = p.booking_id
                 WHERE b.train_id = ?",
                [$trainId]
            )->getRowArray(),
            'hourly' => $this->db->query(
                "SELECT DATE_FORMAT(booking_time, '%Y-%m-%d %H:00:00') AS hour_bucket, COUNT(*) AS total
                 FROM bookings
                 WHERE train_id = ?
                 GROUP BY hour_bucket
                 ORDER BY hour_bucket DESC
                 LIMIT 24",
                [$trainId]
            )->getResultArray(),
        ];
    }

    public function renumberActiveWaitlist(): void
    {
        $rows = $this->db->query(
            "SELECT id, booking_id FROM waiting_queue WHERE status = 'ACTIVE' ORDER BY waitlist_number FOR UPDATE"
        )->getResultArray();

        $number = 1;
        foreach ($rows as $row) {
            $this->db->table('waiting_queue')->where('id', $row['id'])->update([
                'waitlist_number' => $number,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $this->db->table('bookings')->where('id', $row['booking_id'])->update([
                'waitlist_number' => $number,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $number++;
        }
    }

    private function seatPreferenceOrder(?string $preferredType): array
    {
        $all = ['LB', 'MB', 'UB', 'SL', 'SU'];
        if ($preferredType === null || ! in_array($preferredType, $all, true)) {
            return $all;
        }

        return array_values(array_unique(array_merge([$preferredType], $all)));
    }
}
