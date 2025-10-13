<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'title',
        'description',
        'instructor_id',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;

    /**
     * Get all available courses
     */
    public function getAvailableCourses()
    {
        return $this->select('courses.*, users.name as instructor_name')
            ->join('users', 'users.id = courses.instructor_id')
            ->orderBy('courses.created_at', 'DESC')
            ->findAll();
    }

    /**
     * Get courses not enrolled by a specific user
     */
    public function getAvailableCoursesForUser($user_id)
    {
        $enrolledCourses = $this->db->table('enrollments')
            ->select('course_id')
            ->where('user_id', $user_id)
            ->get()
            ->getResultArray();

        $enrolledIds = array_column($enrolledCourses, 'course_id');

        $query = $this->select('courses.*, users.name as instructor_name')
            ->join('users', 'users.id = courses.instructor_id')
            ->orderBy('courses.created_at', 'DESC');

        if (!empty($enrolledIds)) {
            $query->whereNotIn('courses.id', $enrolledIds);
        }

        return $query->findAll();
    }
}
