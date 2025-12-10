<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserActivityLogsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => '50'
            ],
            'description' => [
                'type' => 'TEXT'
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => '45'
            ],
            'user_agent' => [
                'type' => 'TEXT'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true
            ]
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey('user_id');
        $this->forge->addKey('action');
        // Create table only if it does not already exist
        $this->forge->createTable('user_activity_logs', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_activity_logs');
    }
}
