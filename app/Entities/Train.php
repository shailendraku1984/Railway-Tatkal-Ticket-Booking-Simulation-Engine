<?php

namespace App\Entities;

use CodeIgniter\Entity\Entity;

class Train extends Entity
{
    protected $casts = [
        'id' => 'integer',
        'total_seats' => 'integer',
        'rac_capacity' => 'integer',
    ];
}
