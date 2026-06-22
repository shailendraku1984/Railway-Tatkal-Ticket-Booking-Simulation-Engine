<?php

namespace App\Models;

use CodeIgniter\Model;

class WaitingQueueModel extends Model
{
    protected $table = 'waiting_queue';
    protected $returnType = 'array';
    protected $allowedFields = ['booking_id', 'waitlist_number', 'status'];
    protected $useTimestamps = true;
}
