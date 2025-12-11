<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTermIdToCoursesTable extends Migration
{
    public function up()
    {
        // Check if 'term_id' column already exists
        if (!$this->db->fieldExists('term_id', 'courses')) {
            $this->forge->addColumn('courses', [
                'term_id' => [
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => true,
                    'null' => true,
                    'after' => 'semester_id', // Position after semester_id
                ],
            ]);
            
            // Add foreign key constraint
            $this->forge->addForeignKey('term_id', 'terms', 'id', 'CASCADE', 'CASCADE', 'courses_term_fk');
        }
    }

    public function down()
    {
        // Drop the foreign key first
        if ($this->db->fieldExists('term_id', 'courses')) {
            $this->db->query('ALTER TABLE `courses` DROP FOREIGN KEY `courses_term_fk`');
            $this->forge->dropColumn('courses', 'term_id');
        }
    }
}
