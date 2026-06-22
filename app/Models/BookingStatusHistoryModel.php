<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingStatusHistoryModel extends Model
{
    protected $table = 'booking_status_history';
    protected $returnType = 'array';
    protected $allowedFields = ['booking_id', 'old_status', 'new_status', 'note', 'created_at'];
}
