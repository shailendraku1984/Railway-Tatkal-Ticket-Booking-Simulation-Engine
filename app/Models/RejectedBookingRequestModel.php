<?php

namespace App\Models;

use CodeIgniter\Model;

class RejectedBookingRequestModel extends Model
{
    protected $table = 'rejected_booking_requests';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id', 'first_name', 'last_name', 'contact_no',
        'request_time', 'message', 'created_at', 'updated_at',
    ];
}
