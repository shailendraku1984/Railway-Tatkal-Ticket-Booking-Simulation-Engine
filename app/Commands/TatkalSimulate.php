<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Tatkal;

class TatkalSimulate extends BaseCommand
{
    protected $group = 'Tatkal';
    protected $name = 'tatkal:simulate';
    protected $description = 'Generate high-concurrency Tatkal booking requests.';
    protected $usage = 'tatkal:simulate [request_count]';
    protected $arguments = [
        'request_count' => 'Total number of simulated passengers. Example: 10000',
    ];

    public function run(array $params)
    {
        $total = max(1, (int) ($params[0] ?? 10000));
        $config = config(Tatkal::class);
        $batchSize = max(1, (int) $config->workerBatchSize);
        $maxWorkers = max(1, (int) $config->maxWorkers);
        $runId = date('YmdHis') . '_' . bin2hex(random_bytes(3));
        $runDir = WRITEPATH . 'tatkal_simulation' . DIRECTORY_SEPARATOR . $runId;

        if (! is_dir($runDir)) {
            mkdir($runDir, 0775, true);
        }

        CLI::write("Tatkal simulation started: {$total} requests, {$maxWorkers} parallel workers", 'green');
        $startedAt = microtime(true);
        $pending = [];
        $start = 1;
        $workerIndex = 1;

        while ($start <= $total) {
            $count = min($batchSize, $total - $start + 1);
            $pending[] = [
                'index' => $workerIndex++,
                'start' => $start,
                'count' => $count,
            ];
            $start += $count;
        }

        $running = [];
        while ($pending !== [] || $running !== []) {
            while ($pending !== [] && count($running) < $maxWorkers) {
                $job = array_shift($pending);
                $command = $this->workerCommand($runId, $job['index'], $job['start'], $job['count']);
                $process = proc_open($command, [
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ], $pipes, ROOTPATH);

                if (is_resource($process)) {
                    $running[] = ['process' => $process, 'pipes' => $pipes, 'job' => $job];
                }
            }

            foreach ($running as $key => $item) {
                $status = proc_get_status($item['process']);
                if (! $status['running']) {
                    foreach ($item['pipes'] as $pipe) {
                        fclose($pipe);
                    }
                    proc_close($item['process']);
                    unset($running[$key]);
                    CLI::write('Worker ' . $item['job']['index'] . ' completed');
                }
            }

            $running = array_values($running);
            usleep(100000);
        }

        $summary = [
            'total_requests' => $total,
            'confirmed' => 0,
            'rac' => 0,
            'waiting' => 0,
            'rejected' => 0,
            'failed' => 0,
            'execution_time_seconds' => round(microtime(true) - $startedAt, 3),
            'run_id' => $runId,
        ];

        foreach (glob($runDir . DIRECTORY_SEPARATOR . 'worker_*.json') ?: [] as $file) {
            $worker = json_decode((string) file_get_contents($file), true) ?: [];
            $summary['confirmed'] += (int) ($worker['confirmed'] ?? 0);
            $summary['rac'] += (int) ($worker['rac'] ?? 0);
            $summary['waiting'] += (int) ($worker['waiting'] ?? 0);
            $summary['rejected'] += (int) ($worker['rejected'] ?? 0);
            $summary['failed'] += (int) ($worker['failed'] ?? 0);
        }

        file_put_contents($runDir . DIRECTORY_SEPARATOR . 'summary.json', json_encode($summary, JSON_PRETTY_PRINT));

        CLI::newLine();
        CLI::write('Simulation Summary', 'yellow');
        CLI::write('Total Requests : ' . $summary['total_requests']);
        CLI::write('Confirmed      : ' . $summary['confirmed']);
        CLI::write('Rejected       : ' . $summary['rejected']);
        CLI::write('Failed         : ' . $summary['failed']);
        CLI::write('Execution Time : ' . $summary['execution_time_seconds'] . ' seconds');
        CLI::write('Report         : ' . $runDir . DIRECTORY_SEPARATOR . 'summary.json', 'green');
    }

    private function workerCommand(string $runId, int $workerIndex, int $start, int $count): string
    {
        $php = escapeshellarg(PHP_BINARY);
        $spark = escapeshellarg(ROOTPATH . 'spark');

        return implode(' ', [
            $php,
            $spark,
            'tatkal:worker',
            escapeshellarg($runId),
            (string) $workerIndex,
            (string) $start,
            (string) $count,
        ]);
    }
}
