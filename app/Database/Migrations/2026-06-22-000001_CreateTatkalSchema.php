<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTatkalSchema extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'train_number' => ['type' => 'VARCHAR', 'constraint' => 10],
            'train_name' => ['type' => 'VARCHAR', 'constraint' => 120, 'default' => 'Tatkal Express Simulation'],
            'tatkal_opening_time' => ['type' => 'TIME'],
            'total_seats' => ['type' => 'INT', 'unsigned' => true],
            'rac_capacity' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('train_number');
        $this->forge->createTable('trains', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'train_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'code' => ['type' => 'VARCHAR', 'constraint' => 4],
            'class_type' => ['type' => 'ENUM', 'constraint' => ['SLEEPER', 'AC']],
            'seat_count' => ['type' => 'INT', 'unsigned' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('train_id');
        $this->forge->addUniqueKey(['train_id', 'code']);
        $this->forge->addForeignKey('train_id', 'trains', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('compartments', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'train_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'compartment_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'seat_number' => ['type' => 'INT', 'unsigned' => true],
            'seat_type' => ['type' => 'ENUM', 'constraint' => ['LB', 'MB', 'UB', 'SL', 'SU']],
            'status' => ['type' => 'ENUM', 'constraint' => ['AVAILABLE', 'LOCKED', 'BOOKED'], 'default' => 'AVAILABLE'],
            'booking_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'lock_version' => ['type' => 'INT', 'unsigned' => true, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['train_id', 'status', 'seat_type']);
        $this->forge->addUniqueKey(['compartment_id', 'seat_number']);
        $this->forge->addForeignKey('train_id', 'trains', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('compartment_id', 'compartments', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('seats', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'session_token' => ['type' => 'VARCHAR', 'constraint' => 64],
            'pnr' => ['type' => 'CHAR', 'constraint' => 10],
            'train_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'train_number' => ['type' => 'VARCHAR', 'constraint' => 10],
            'seat_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'rac_number' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'waitlist_number' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'preferred_seat_type' => ['type' => 'ENUM', 'constraint' => ['LB', 'MB', 'UB', 'SL', 'SU'], 'null' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['CONFIRMED', 'RAC', 'WAITING', 'CANCELLED']],
            'booking_time' => ['type' => 'DATETIME'],
            'cancelled_at' => ['type' => 'DATETIME', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('pnr');
        $this->forge->addUniqueKey('session_token');
        $this->forge->addKey(['train_id', 'status', 'booking_time']);
        $this->forge->addForeignKey('train_id', 'trains', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('bookings', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'passenger_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'age' => ['type' => 'INT', 'unsigned' => true],
            'gender' => ['type' => 'ENUM', 'constraint' => ['Male', 'Female']],
            'mobile_number' => ['type' => 'VARCHAR', 'constraint' => 15],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('mobile_number');
        $this->forge->addForeignKey('booking_id', 'bookings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('passengers', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'old_status' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'new_status' => ['type' => 'VARCHAR', 'constraint' => 20],
            'note' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('booking_id');
        $this->forge->addForeignKey('booking_id', 'bookings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('booking_status_history', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'rac_number' => ['type' => 'INT', 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['ACTIVE', 'PROMOTED', 'CANCELLED'], 'default' => 'ACTIVE'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'rac_number']);
        $this->forge->addKey('booking_id');
        $this->forge->addForeignKey('booking_id', 'bookings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rac_queue', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'waitlist_number' => ['type' => 'INT', 'unsigned' => true],
            'status' => ['type' => 'ENUM', 'constraint' => ['ACTIVE', 'PROMOTED', 'CANCELLED'], 'default' => 'ACTIVE'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['status', 'waitlist_number']);
        $this->forge->addKey('booking_id');
        $this->forge->addForeignKey('booking_id', 'bookings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('waiting_queue', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'cancelled_pnr' => ['type' => 'CHAR', 'constraint' => 10],
            'reason' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('booking_id', 'bookings', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('cancellations', true);

        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true],
            'booking_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'event_type' => ['type' => 'VARCHAR', 'constraint' => 80],
            'payload' => ['type' => 'JSON', 'null' => true],
            'duration_ms' => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey(['event_type', 'created_at']);
        $this->forge->createTable('booking_audit_logs', true);
    }

    public function down()
    {
        foreach ([
            'booking_audit_logs', 'cancellations', 'waiting_queue', 'rac_queue',
            'booking_status_history', 'passengers', 'bookings', 'seats',
            'compartments', 'trains',
        ] as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
