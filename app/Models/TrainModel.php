<?php

namespace App\Models;

use App\Entities\Train;
use CodeIgniter\Model;

class TrainModel extends Model
{
    protected $table = 'trains';
    protected $returnType = Train::class;
    protected $allowedFields = ['train_number', 'train_name', 'tatkal_opening_time', 'total_seats', 'rac_capacity'];
    protected $useTimestamps = true;
}
