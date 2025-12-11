<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUnitsToCoursesTable extends Migration
{
    public function up()
    {
        // Check if units column already exists
        $fields = $this->db->getFieldData('courses');
        $hasUnits = false;
        foreach ($fields as $field) {
            if ($field->name === 'units') {
                $hasUnits = true;
                break;
            }
        }

        if (!$hasUnits) {
            $this->forge->addColumn('courses', [
                'units' => [
                    'type' => 'DECIMAL',
                    'constraint' => '3,1',
                    'default' => 0,
                    'null' => true,
                    'after' => 'description',
                ],
            ]);
        }
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['units']);
    }
}
