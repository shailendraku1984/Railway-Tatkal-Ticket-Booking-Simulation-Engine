<?php

namespace App\Models;

use App\Entities\Compartment;
use CodeIgniter\Model;

class CompartmentModel extends Model
{
    protected $table = 'compartments';
    protected $returnType = Compartment::class;
    protected $allowedFields = ['train_id', 'code', 'class_type', 'seat_count'];
    protected $useTimestamps = true;
}
