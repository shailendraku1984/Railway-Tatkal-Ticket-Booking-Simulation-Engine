<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Seat extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'train_id' => 'integer',
        'compartment_id' => 'integer',
        'seat_number' => 'integer',
        'booking_id' => '?integer',
        'lock_version' => 'integer',
    ];
}
