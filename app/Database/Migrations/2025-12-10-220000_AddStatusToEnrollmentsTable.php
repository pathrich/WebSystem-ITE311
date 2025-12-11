<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToEnrollmentsTable extends Migration
{
    public function up()
    {
        // Check if 'status' column already exists
        if (!$this->db->fieldExists('status', 'enrollments')) {
            $this->forge->addColumn('enrollments', [
                'status' => [
                    'type' => 'VARCHAR',
                    'constraint' => '20',
                    'default' => 'pending',
                    'after' => 'enrollment_date',
                ],
            ]);
            
            // Update existing enrollments to 'approved' status (for backward compatibility)
            $this->db->table('enrollments')
                ->where('status IS NULL', null, false)
                ->set('status', 'approved')
                ->update();
        }
    }

    public function down()
    {
        // Drop the 'status' column if it exists
        if ($this->db->fieldExists('status', 'enrollments')) {
            $this->forge->dropColumn('enrollments', 'status');
        }
    }
}

