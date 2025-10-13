<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'user_id',
        'course_id',
        'enrolled_at'
    ];

    protected $useTimestamps = false;

    /**
     * Insert a new enrollment record
     */
    public function enrollUser($data)
    {
        $data['enrolled_at'] = date('Y-m-d H:i:s');
        return $this->insert($data);
    }

    /**
     * Fetch all courses a user is enrolled in
     */
    public function getUserEnrollments($user_id)
    {
        return $this->select('enrollments.*, courses.title, courses.description, users.name as instructor_name')
            ->join('courses', 'courses.id = enrollments.course_id')
            ->join('users', 'users.id = courses.instructor_id')
            ->where('enrollments.user_id', $user_id)
            ->orderBy('enrollments.enrolled_at', 'DESC')
            ->findAll();
    }

    /**
     * Check if a user is already enrolled in a specific course to prevent duplicates
     */
    public function isAlreadyEnrolled($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
            ->where('course_id', $course_id)
            ->first() !== null;
    }
}
