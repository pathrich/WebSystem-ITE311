<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseScheduleModel extends Model
{
    protected $table = 'course_schedules';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'course_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getSchedulesByCourse($courseId)
    {
        return $this->where('course_id', $courseId)
            ->orderBy('day_of_week', 'ASC')
            ->orderBy('start_time', 'ASC')
            ->findAll();
    }

    public function getSchedulesForCourses(array $courseIds)
    {
        $courseIds = array_values(array_filter(array_unique(array_map('intval', $courseIds))));
        if (empty($courseIds)) {
            return [];
        }

        $rows = $this->whereIn('course_id', $courseIds)
            ->orderBy('day_of_week', 'ASC')
            ->orderBy('start_time', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $cid = (int) ($row['course_id'] ?? 0);
            if (!isset($grouped[$cid])) {
                $grouped[$cid] = [];
            }
            $grouped[$cid][] = $row;
        }

        return $grouped;
    }

    public function getStudentTimetable($studentId)
    {
        $studentId = (int) $studentId;

        $db = \Config\Database::connect();

        return $db->table('course_schedules cs')
            ->select('cs.day_of_week, cs.start_time, cs.end_time, cs.room, courses.id as course_id, courses.title as course_title, users.name as instructor_name')
            ->join('enrollments', 'enrollments.course_id = cs.course_id AND enrollments.status = \'approved\'', 'inner')
            ->join('courses', 'courses.id = cs.course_id', 'inner')
            ->join('users', 'users.id = courses.instructor_id', 'left')
            ->where('enrollments.user_id', $studentId)
            ->orderBy('cs.day_of_week', 'ASC')
            ->orderBy('cs.start_time', 'ASC')
            ->get()
            ->getResultArray();
    }
}
