<?php

namespace App\Models;

use CodeIgniter\Model;

class AssignmentModel extends Model
{
    protected $table = 'assignments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'course_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'due_date',
        'max_score',
        'created_by',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get assignments by course
     */
    public function getAssignmentsByCourse($course_id)
    {
        return $this->select('assignments.*, users.name as created_by_name')
            ->join('users', 'users.id = assignments.created_by', 'left')
            ->where('assignments.course_id', $course_id)
            ->orderBy('assignments.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get assignments for a teacher's courses
     */
    public function getAssignmentsForTeacher($teacher_id)
    {
        return $this->select('assignments.*, courses.title as course_title, courses.course_number, users.name as created_by_name')
            ->join('courses', 'courses.id = assignments.course_id')
            ->join('users', 'users.id = assignments.created_by', 'left')
            ->where('courses.instructor_id', $teacher_id)
            ->orderBy('assignments.created_at', 'DESC')
            ->findAll();
    }

    public function getAssignmentsForCourses(array $courseIds)
    {
        $courseIds = array_values(array_filter(array_unique(array_map('intval', $courseIds))));
        if (empty($courseIds)) {
            return [];
        }

        return $this->select('assignments.*, courses.title as course_title, users.name as instructor_name')
            ->join('courses', 'courses.id = assignments.course_id', 'inner')
            ->join('users', 'users.id = courses.instructor_id', 'left')
            ->whereIn('assignments.course_id', $courseIds)
            ->orderBy('courses.title', 'ASC')
            ->orderBy('assignments.due_date', 'ASC')
            ->orderBy('assignments.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Insert a new assignment
     */
    public function insertAssignment($data)
    {
        return $this->insert($data);
    }
}

