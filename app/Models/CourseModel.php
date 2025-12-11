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
        'course_number',
        'description',
        'units',
        'instructor_id',
        'academic_year_id',
        'semester_id',
        'term_id',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = false;

    /**
     * Get all available courses
     */
    public function getAvailableCourses()
    {
        $courses = $this->select('courses.*, users.name as instructor_name')
            ->join('users', 'users.id = courses.instructor_id')
            ->orderBy('courses.created_at', 'DESC')
            ->findAll();
        
        // Add schedules to each course
        $scheduleModel = new \App\Models\ScheduleModel();
        foreach ($courses as &$course) {
            $course['schedules'] = $scheduleModel->getSchedulesByCourse($course['id']);
        }

        return $courses;
    }

    /**
     * Get courses not enrolled by a specific user (excluding pending enrollments)
     */
    public function getAvailableCoursesForUser($user_id)
    {
        // Get all enrollments (pending, approved, rejected) to exclude from available courses
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

        $courses = $query->findAll();
        
        // Add schedules to each course
        $scheduleModel = new \App\Models\ScheduleModel();
        foreach ($courses as &$course) {
            $course['schedules'] = $scheduleModel->getSchedulesByCourse($course['id']);
        }

        return $courses;
    }

    /**
     * Search for courses by keyword.
     * Searches both title and description fields using SQL LIKE.
     * Returns an array of matched courses with instructor name.
     *
     * @param string $keyword
     * @return array
     */
    public function searchCourses($keyword)
    {
        $keyword = trim((string) $keyword);

        $builder = $this->select('courses.*, users.name as instructor_name')
            ->join('users', 'users.id = courses.instructor_id');

        if ($keyword !== '') {
            $builder->groupStart();
            $builder->like('courses.title', $keyword);
            $builder->orLike('courses.description', $keyword);
            $builder->groupEnd();
        }

        $builder->orderBy('courses.created_at', 'DESC');

        return $builder->findAll();
    }

    /**
     * Search available courses for a specific user (not enrolled by the user).
     * Searches both title and description fields using SQL LIKE.
     *
     * @param string $keyword
     * @param int $user_id
     * @return array
     */
    public function searchAvailableCoursesForUser($keyword, $user_id)
    {
        $keyword = trim((string) $keyword);

        $enrolledCourses = $this->db->table('enrollments')
            ->select('course_id')
            ->where('user_id', $user_id)
            ->get()
            ->getResultArray();
        $enrolledIds = array_column($enrolledCourses, 'course_id');

        $builder = $this->select('courses.*, users.name as instructor_name')
            ->join('users', 'users.id = courses.instructor_id');

        if (!empty($enrolledIds)) {
            $builder->whereNotIn('courses.id', $enrolledIds);
        }

        if ($keyword !== '') {
            $builder->groupStart();
            $builder->like('courses.title', $keyword);
            $builder->orLike('courses.description', $keyword);
            $builder->groupEnd();
        }

        $builder->orderBy('courses.created_at', 'DESC');

        $courses = $builder->findAll();
        
        // Add schedules to each course
        $scheduleModel = new \App\Models\ScheduleModel();
        foreach ($courses as &$course) {
            $course['schedules'] = $scheduleModel->getSchedulesByCourse($course['id']);
        }

        return $courses;
    }
}
