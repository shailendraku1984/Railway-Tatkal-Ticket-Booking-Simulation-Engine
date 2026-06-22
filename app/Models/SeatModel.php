<?php

namespace App\Models;

use App\Entities\Seat;
use CodeIgniter\Model;

class SeatModel extends Model
{
    protected $table = 'seats';
    protected $returnType = Seat::class;
    protected $allowedFields = ['train_id', 'compartment_id', 'seat_number', 'seat_type', 'status', 'booking_id', 'lock_version'];
    protected $useTimestamps = true;
}
