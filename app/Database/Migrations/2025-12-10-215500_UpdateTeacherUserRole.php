<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateTeacherUserRole extends Migration
{
    public function up()
    {
        // Update teacher user role to 'teacher'
        // This updates the user with email 'teacher@example.com' to have role 'teacher'
        $this->db->table('users')
            ->where('email', 'teacher@example.com')
            ->orWhere('name', 'Teacher User')
            ->update(['role' => 'teacher']);
    }

    public function down()
    {
        // Revert teacher user role (optional - you can set it back to what it was before)
        // This is optional and can be left empty if you don't need to revert
    }
}
