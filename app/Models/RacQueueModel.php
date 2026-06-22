<?php

namespace App\Models;

use CodeIgniter\Model;

class RacQueueModel extends Model
{
    protected $table = 'rac_queue';
    protected $returnType = 'array';
    protected $allowedFields = ['booking_id', 'rac_number', 'status'];
    protected $useTimestamps = true;
}
