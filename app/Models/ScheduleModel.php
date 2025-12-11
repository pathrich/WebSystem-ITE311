<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table = 'schedules';
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

    /**
     * Get schedules by course
     */
    public function getSchedulesByCourse($courseId)
    {
        return $this->where('course_id', $courseId)
            ->orderBy('day_of_week', 'ASC')
            ->orderBy('start_time', 'ASC')
            ->findAll();
    }

    /**
     * Check if there's a schedule conflict between two courses
     */
    public function hasScheduleConflict($courseId1, $courseId2)
    {
        $schedules1 = $this->getSchedulesByCourse($courseId1);
        $schedules2 = $this->getSchedulesByCourse($courseId2);

        foreach ($schedules1 as $schedule1) {
            foreach ($schedules2 as $schedule2) {
                // Check if same day
                if ($schedule1['day_of_week'] === $schedule2['day_of_week']) {
                    // Check if time overlaps
                    $start1 = strtotime($schedule1['start_time']);
                    $end1 = strtotime($schedule1['end_time']);
                    $start2 = strtotime($schedule2['start_time']);
                    $end2 = strtotime($schedule2['end_time']);

                    // Check for overlap: start1 < end2 && start2 < end1
                    if ($start1 < $end2 && $start2 < $end1) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Check if student has schedule conflict with any enrolled course
     */
    public function checkStudentScheduleConflict($userId, $newCourseId)
    {
        $enrollmentModel = new EnrollmentModel();
        // Only check approved enrollments (pending/rejected don't count as conflicts)
        $enrolledCourses = $enrollmentModel->select('course_id')
            ->where('user_id', $userId)
            ->where('status', 'approved')
            ->findAll();

        foreach ($enrolledCourses as $enrollment) {
            if ($this->hasScheduleConflict($enrollment['course_id'], $newCourseId)) {
                return [
                    'has_conflict' => true,
                    'conflicting_course_id' => $enrollment['course_id']
                ];
            }
        }

        return ['has_conflict' => false];
    }

    /**
     * Check if teacher has schedule conflict with any assigned course
     */
    public function checkTeacherScheduleConflict($teacherId, $newCourseId)
    {
        $courseModel = new CourseModel();
        $teacherCourses = $courseModel->where('instructor_id', $teacherId)->findAll();

        foreach ($teacherCourses as $course) {
            if ($this->hasScheduleConflict($course['id'], $newCourseId)) {
                return [
                    'has_conflict' => true,
                    'conflicting_course_id' => $course['id']
                ];
            }
        }

        return ['has_conflict' => false];
    }
}

