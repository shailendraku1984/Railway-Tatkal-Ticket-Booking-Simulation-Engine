<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Booking extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'train_id' => 'integer',
        'seat_id' => '?integer',
        'rac_number' => '?integer',
        'waitlist_number' => '?integer',
    ];
}
