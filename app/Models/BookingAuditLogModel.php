<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingAuditLogModel extends Model
{
    protected $table = 'booking_audit_logs';
    protected $returnType = 'array';
    protected $allowedFields = ['booking_id', 'event_type', 'payload', 'duration_ms', 'created_at'];
}
