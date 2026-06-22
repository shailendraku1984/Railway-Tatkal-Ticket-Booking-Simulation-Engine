<?php

namespace App\Models;

use App\Entities\Booking;
use CodeIgniter\Model;

class BookingModel extends Model
{
    protected $table = 'bookings';
    protected $returnType = Booking::class;
    protected $allowedFields = [
        'session_token', 'pnr', 'train_id', 'train_number', 'seat_id', 'rac_number',
        'waitlist_number', 'preferred_seat_type', 'status', 'booking_amount', 'booking_time', 'cancelled_at',
    ];
    protected $useTimestamps = true;
}
