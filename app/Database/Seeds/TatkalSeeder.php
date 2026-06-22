<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class TatkalSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');
        $trainNumber = '12345';

        $existing = $this->db->table('trains')->where('train_number', $trainNumber)->get()->getRowArray();
        if ($existing !== null) {
            return;
        }

        $this->db->table('trains')->insert([
            'train_number' => $trainNumber,
            'train_name' => 'Tatkal Express Simulation',
            'tatkal_opening_time' => '00:00:00',
            'total_seats' => 1440,
            'rac_capacity' => 50,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $trainId = (int) $this->db->insertID();
        $codes = array_merge(
            array_map(static fn ($i) => 'S' . $i, range(1, 10)),
            array_map(static fn ($i) => 'B' . $i, range(1, 10))
        );

        foreach ($codes as $code) {
            $this->db->table('compartments')->insert([
                'train_id' => $trainId,
                'code' => $code,
                'class_type' => str_starts_with($code, 'S') ? 'SLEEPER' : 'AC',
                'seat_count' => 72,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $compartmentId = (int) $this->db->insertID();
            $seats = [];
            for ($seat = 1; $seat <= 72; $seat++) {
                $seats[] = [
                    'train_id' => $trainId,
                    'compartment_id' => $compartmentId,
                    'seat_number' => $seat,
                    'seat_type' => $this->seatType($seat),
                    'status' => 'AVAILABLE',
                    'lock_version' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $this->db->table('seats')->insertBatch($seats);
        }
    }

    private function seatType(int $seatNumber): string
    {
        return match ($seatNumber % 8) {
            1, 4 => 'LB',
            2, 5 => 'MB',
            3, 6 => 'UB',
            7 => 'SL',
            0 => 'SU',
        };
    }
}
