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
        'enrollment_date',
        'status'
    ];

    protected $useTimestamps = false;

    /**
     * Insert a new enrollment record
     */
    public function enrollUser($data)
    {
        // Use enrollment_date if not set
        if (!isset($data['enrollment_date'])) {
            $data['enrollment_date'] = date('Y-m-d H:i:s');
        }
        
        // Check if enrolled_at column exists in the database
        $db = \Config\Database::connect();
        $fields = $db->getFieldData('enrollments');
        $fieldNames = array_column($fields, 'name');
        
        // Only include enrolled_at if the column exists
        if (!in_array('enrolled_at', $fieldNames)) {
            unset($data['enrolled_at']);
        } elseif (!isset($data['enrolled_at'])) {
            // Only set enrolled_at if column exists and value not provided
            $data['enrolled_at'] = $data['enrollment_date'];
        }
        
        return $this->insert($data);
    }

    /**
     * Fetch all courses a user is enrolled in
     */
    public function getUserEnrollments($user_id)
    {
        $results = $this->select('enrollments.*, courses.title, courses.description, users.name as instructor_name')
            ->join('courses', 'courses.id = enrollments.course_id')
            ->join('users', 'users.id = courses.instructor_id')
            ->where('enrollments.user_id', $user_id)
            ->findAll();
        
        // Sort by enrollment_date or enrolled_at in PHP
        usort($results, function($a, $b) {
            $dateA = $a['enrollment_date'] ?? $a['enrolled_at'] ?? '';
            $dateB = $b['enrollment_date'] ?? $b['enrolled_at'] ?? '';
            return strtotime($dateB) - strtotime($dateA);
        });
        
        return $results;
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

    /**
     * Get pending enrollments for a teacher's courses
     */
    public function getPendingEnrollmentsForTeacher($teacher_id)
    {
        return $this->select('enrollments.*, courses.title as course_title, courses.course_number, users.name as student_name, users.email as student_email, users.id as student_id')
            ->join('courses', 'courses.id = enrollments.course_id')
            ->join('users', 'users.id = enrollments.user_id')
            ->where('courses.instructor_id', $teacher_id)
            ->where('enrollments.status', 'pending')
            ->orderBy('enrollments.enrollment_date', 'DESC')
            ->findAll();
    }

    /**
     * Get approved enrollments for a user
     */
    public function getApprovedEnrollments($user_id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('enrollments');
        
        $results = $builder->select('enrollments.*, courses.title, courses.description, users.name as instructor_name')
            ->join('courses', 'courses.id = enrollments.course_id')
            ->join('users', 'users.id = courses.instructor_id', 'left')
            ->where('enrollments.user_id', $user_id)
            ->where('enrollments.status', 'approved')
            ->get()
            ->getResultArray();
        
        // Sort by enrollment_date or enrolled_at in PHP
        usort($results, function($a, $b) {
            $dateA = $a['enrollment_date'] ?? $a['enrolled_at'] ?? '';
            $dateB = $b['enrollment_date'] ?? $b['enrolled_at'] ?? '';
            return strtotime($dateB) - strtotime($dateA);
        });
        
        return $results;
    }

    /**
     * Get pending enrollments for a user
     */
    public function getPendingEnrollments($user_id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('enrollments');
        
        $results = $builder->select('enrollments.*, courses.title, courses.description, users.name as instructor_name')
            ->join('courses', 'courses.id = enrollments.course_id')
            ->join('users', 'users.id = courses.instructor_id', 'left')
            ->where('enrollments.user_id', $user_id)
            ->where('enrollments.status', 'pending')
            ->get()
            ->getResultArray();
        
        // Sort by enrollment_date or enrolled_at in PHP
        usort($results, function($a, $b) {
            $dateA = $a['enrollment_date'] ?? $a['enrolled_at'] ?? '';
            $dateB = $b['enrollment_date'] ?? $b['enrolled_at'] ?? '';
            return strtotime($dateB) - strtotime($dateA);
        });
        
        return $results;
    }

    /**
     * Update enrollment status
     */
    public function updateStatus($enrollment_id, $status)
    {
        $allowedStatuses = ['pending', 'approved', 'rejected'];
        if (!in_array($status, $allowedStatuses)) {
            return false;
        }
        return $this->update($enrollment_id, ['status' => $status]);
    }
}
