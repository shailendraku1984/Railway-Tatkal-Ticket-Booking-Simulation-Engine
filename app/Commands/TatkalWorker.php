<?php

namespace App\Commands;

use App\Services\TatkalBookingService;
use CodeIgniter\CLI\BaseCommand;
use Config\Tatkal;

class TatkalWorker extends BaseCommand
{
    protected $group = 'Tatkal';
    protected $name = 'tatkal:worker';
    protected $description = 'Internal worker for Tatkal load simulation.';
    protected $usage = 'tatkal:worker [run_id] [worker_index] [start] [count]';

    public function run(array $params)
    {
        $runId = (string) ($params[0] ?? date('YmdHis'));
        $workerIndex = (int) ($params[1] ?? 1);
        $start = (int) ($params[2] ?? 1);
        $count = (int) ($params[3] ?? 1);
        $config = config(Tatkal::class);
        $service = new TatkalBookingService();
        $results = [
            'confirmed' => 0,
            'rac' => 0,
            'waiting' => 0,
            'rejected' => 0,
            'failed' => 0,
            'items' => [],
        ];

        for ($i = $start; $i < $start + $count; $i++) {
            $response = $service->book($this->passenger($i, $config->trainNumber));
            $status = strtolower((string) ($response['status'] ?? 'FAILED'));

            if (isset($results[$status])) {
                $results[$status]++;
            } else {
                $results['failed']++;
            }

            $results['items'][] = [
                'request' => $i,
                'status' => $response['status'] ?? 'FAILED',
                'pnr' => $response['pnr'] ?? null,
                'code' => $response['code'] ?? null,
            ];
        }

        $runDir = WRITEPATH . 'tatkal_simulation' . DIRECTORY_SEPARATOR . $runId;
        if (! is_dir($runDir)) {
            mkdir($runDir, 0775, true);
        }

        file_put_contents(
            $runDir . DIRECTORY_SEPARATOR . 'worker_' . $workerIndex . '.json',
            json_encode($results, JSON_PRETTY_PRINT)
        );
    }

    private function passenger(int $number, string $trainNumber): array
    {
        $seatTypes = ['Lower', 'Middle', 'Upper', 'Side Lower', 'Side Upper'];
        $firstNames = [
            'Amit', 'Ramesh', 'Ganesh', 'Umesh', 'Suresh', 'Mahesh', 'Rajesh', 'Dinesh',
            'Naresh', 'Mukesh', 'Rohit', 'Mohit', 'Anil', 'Sunil', 'Vikas', 'Nitin',
            'Kiran', 'Pooja', 'Neha', 'Anita', 'Sunita', 'Rekha', 'Geeta', 'Seema',
        ];
        $gender = random_int(0, 1) === 1 ? 'Male' : 'Female';
        $firstName = $firstNames[array_rand($firstNames)];

        return [
            'train_number' => $trainNumber,
            'first_name' => $firstName,
            'last_name' => $gender === 'Female' ? 'Kumari' : 'Kumar',
            'passenger_name' => $firstName . ' ' . ($gender === 'Female' ? 'Kumari' : 'Kumar'),
            'age' => random_int(18, 65),
            'gender' => $gender,
            'mobile_number' => '9' . str_pad((string) $number, 9, '0', STR_PAD_LEFT),
            'seat_preference' => $seatTypes[array_rand($seatTypes)],
        ];
    }
}
