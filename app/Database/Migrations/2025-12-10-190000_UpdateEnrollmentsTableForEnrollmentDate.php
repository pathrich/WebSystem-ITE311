<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpdateEnrollmentsTableForEnrollmentDate extends Migration
{
    public function up()
    {
        // Check if enrolled_at exists and rename it to enrollment_date
        $fields = $this->db->getFieldData('enrollments');
        $hasEnrolledAt = false;
        foreach ($fields as $field) {
            if ($field->name === 'enrolled_at') {
                $hasEnrolledAt = true;
                break;
            }
        }

        if ($hasEnrolledAt) {
            // Rename enrolled_at to enrollment_date
            $this->db->query("ALTER TABLE `enrollments` CHANGE `enrolled_at` `enrollment_date` DATETIME NULL");
        } else {
            // Check if enrollment_date doesn't exist, add it
            $hasEnrollmentDate = false;
            foreach ($fields as $field) {
                if ($field->name === 'enrollment_date') {
                    $hasEnrollmentDate = true;
                    break;
                }
            }
            
            if (!$hasEnrollmentDate) {
                $this->forge->addColumn('enrollments', [
                    'enrollment_date' => [
                        'type' => 'DATETIME',
                        'null' => true,
                        'after' => 'course_id',
                    ],
                ]);
            }
        }
    }

    public function down()
    {
        // Rename back to enrolled_at if needed
        $this->db->query("ALTER TABLE `enrollments` CHANGE `enrollment_date` `enrolled_at` DATETIME NULL");
    }
}

