<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;

class TatkalLockManager
{
    public function acquire(BaseConnection $db, string $name, int $timeoutSeconds = 10): bool
    {
        $row = $db->query('SELECT GET_LOCK(?, ?) AS acquired', [$name, $timeoutSeconds])->getRowArray();
        return (int) ($row['acquired'] ?? 0) === 1;
    }

    public function release(BaseConnection $db, string $name): void
    {
        $db->query('SELECT RELEASE_LOCK(?)', [$name]);
    }
}
