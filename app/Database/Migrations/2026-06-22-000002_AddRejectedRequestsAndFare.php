<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRejectedRequestsAndFare extends Migration
{
    public function up()
    {
        $this->forge->addColumn('bookings', [
            'booking_amount' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0,
                'after' => 'status',
            ],
        ]);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'session_id' => ['type' => 'VARCHAR', 'constraint' => 64],
            'first_name' => ['type' => 'VARCHAR', 'constraint' => 80],
            'last_name' => ['type' => 'VARCHAR', 'constraint' => 80],
            'contact_no' => ['type' => 'VARCHAR', 'constraint' => 15],
            'request_time' => ['type' => 'DATETIME'],
            'message' => ['type' => 'VARCHAR', 'constraint' => 255],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['request_time', 'contact_no']);
        $this->forge->createTable('rejected_booking_requests', true);
    }

    public function down()
    {
        $this->forge->dropTable('rejected_booking_requests', true);
        $this->forge->dropColumn('bookings', 'booking_amount');
    }
}
