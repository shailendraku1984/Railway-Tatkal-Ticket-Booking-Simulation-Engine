<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Compartment extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'train_id' => 'integer',
        'seat_count' => 'integer',
    ];
}
