<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCourseFieldsToCoursesTable extends Migration
{
    public function up()
    {
        // Check if columns already exist
        $fields = $this->db->getFieldData('courses');
        $existingFields = [];
        foreach ($fields as $field) {
            $existingFields[] = $field->name;
        }

        $columnsToAdd = [];

        if (!in_array('course_number', $existingFields)) {
            $columnsToAdd['course_number'] = [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'after' => 'title',
            ];
        }

        if (!in_array('academic_year_id', $existingFields)) {
            $columnsToAdd['academic_year_id'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'course_number',
            ];
        }

        if (!in_array('semester_id', $existingFields)) {
            $columnsToAdd['semester_id'] = [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'academic_year_id',
            ];
        }

        if (!empty($columnsToAdd)) {
            $this->forge->addColumn('courses', $columnsToAdd);
        }

        // Add foreign keys if they don't exist
        if (!in_array('academic_year_id', $existingFields)) {
            $this->forge->addForeignKey('academic_year_id', 'academic_years', 'id', 'SET NULL', 'CASCADE');
        }
        if (!in_array('semester_id', $existingFields)) {
            $this->forge->addForeignKey('semester_id', 'semesters', 'id', 'SET NULL', 'CASCADE');
        }
    }

    public function down()
    {
        $this->forge->dropForeignKey('courses', 'courses_academic_year_id_foreign');
        $this->forge->dropForeignKey('courses', 'courses_semester_id_foreign');
        $this->forge->dropColumn('courses', ['course_number', 'academic_year_id', 'semester_id']);
    }
}

