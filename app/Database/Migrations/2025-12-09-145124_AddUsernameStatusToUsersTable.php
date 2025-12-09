<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUsernameStatusToUsersTable extends Migration
{
    public function up()
    {
        // Add username and status columns to users table
        $this->forge->addColumn('users', [
            'username' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'unique' => true,
                'after' => 'name',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'inactive'],
                'default' => 'active',
                'after' => 'role',
            ],
        ]);

        // Create user_activity_logs table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
            ],
            'action' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'description' => [
                'type' => 'TEXT',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_activity_logs');
    }

    public function down()
    {
        // Drop user_activity_logs table
        $this->forge->dropTable('user_activity_logs');

        // Remove added columns from users table
        $this->forge->dropColumn('users', ['username', 'status']);
    }
}
