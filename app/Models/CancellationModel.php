<?php

namespace App\Models;

use CodeIgniter\Model;

class CancellationModel extends Model
{
    protected $table = 'cancellations';
    protected $returnType = 'array';
    protected $allowedFields = ['booking_id', 'cancelled_pnr', 'reason', 'created_at'];
}
