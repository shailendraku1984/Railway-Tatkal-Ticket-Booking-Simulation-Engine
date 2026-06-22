<?php

namespace App\Models;

use App\Entities\Passenger;
use CodeIgniter\Model;

class PassengerModel extends Model
{
    protected $table = 'passengers';
    protected $returnType = Passenger::class;
    protected $allowedFields = ['booking_id', 'passenger_name', 'age', 'gender', 'mobile_number'];
    protected $useTimestamps = true;
}
