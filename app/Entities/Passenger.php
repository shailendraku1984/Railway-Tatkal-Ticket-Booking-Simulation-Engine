<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Passenger extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'booking_id' => 'integer',
        'age' => 'integer',
    ];
}
