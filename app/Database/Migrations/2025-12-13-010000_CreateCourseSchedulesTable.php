<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCourseSchedulesTable extends Migration
{
    public function up()
    {
        if ($this->db->tableExists('course_schedules')) {
            return;
        }

        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'course_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'day_of_week' => [
                'type' => 'ENUM',
                'constraint' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ],
            'start_time' => [
                'type' => 'TIME',
            ],
            'end_time' => [
                'type' => 'TIME',
            ],
            'room' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('course_id', 'courses', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('course_schedules');

        if ($this->db->tableExists('schedules')) {
            try {
                $this->db->query('INSERT INTO course_schedules (course_id, day_of_week, start_time, end_time, room, created_at, updated_at) SELECT course_id, day_of_week, start_time, end_time, room, created_at, updated_at FROM schedules');
            } catch (\Throwable $e) {
            }
        }
    }

    public function down()
    {
        if ($this->db->tableExists('course_schedules')) {
            $this->forge->dropTable('course_schedules');
        }
    }
}
