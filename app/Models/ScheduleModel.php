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

    protected function normalizeDayOfWeek($day)
    {
        $day = strtolower(trim((string) $day));
        $map = [
            'mon' => 'monday',
            'tue' => 'tuesday',
            'tues' => 'tuesday',
            'wed' => 'wednesday',
            'thu' => 'thursday',
            'thur' => 'thursday',
            'thurs' => 'thursday',
            'fri' => 'friday',
            'sat' => 'saturday',
            'sun' => 'sunday',
        ];
        return $map[$day] ?? $day;
    }

    protected function timeToSeconds($time)
    {
        $time = trim((string) $time);
        if ($time === '') {
            return null;
        }

        $parts = explode(':', $time);
        if (count($parts) < 2) {
            return null;
        }

        $h = (int) $parts[0];
        $m = (int) $parts[1];
        $s = isset($parts[2]) ? (int) $parts[2] : 0;
        return ($h * 3600) + ($m * 60) + $s;
    }

    public function getScheduleOverlapDetails($courseId1, $courseId2)
    {
        if ((int) $courseId1 === (int) $courseId2) {
            return [];
        }

        $schedules1 = $this->getSchedulesByCourse($courseId1);
        $schedules2 = $this->getSchedulesByCourse($courseId2);

        $overlaps = [];

        foreach ($schedules1 as $schedule1) {
            foreach ($schedules2 as $schedule2) {
                $day1 = $this->normalizeDayOfWeek($schedule1['day_of_week'] ?? '');
                $day2 = $this->normalizeDayOfWeek($schedule2['day_of_week'] ?? '');
                if ($day1 === '' || $day2 === '' || $day1 !== $day2) {
                    continue;
                }

                $start1 = $this->timeToSeconds($schedule1['start_time'] ?? '');
                $end1 = $this->timeToSeconds($schedule1['end_time'] ?? '');
                $start2 = $this->timeToSeconds($schedule2['start_time'] ?? '');
                $end2 = $this->timeToSeconds($schedule2['end_time'] ?? '');

                if ($start1 === null || $end1 === null || $start2 === null || $end2 === null) {
                    continue;
                }

                if ($start1 < $end2 && $start2 < $end1) {
                    $overlaps[] = [
                        'day_of_week' => $schedule1['day_of_week'],
                        'course1_start_time' => $schedule1['start_time'],
                        'course1_end_time' => $schedule1['end_time'],
                        'course2_start_time' => $schedule2['start_time'],
                        'course2_end_time' => $schedule2['end_time'],
                    ];
                }
            }
        }

        return $overlaps;
    }

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
        return !empty($this->getScheduleOverlapDetails($courseId1, $courseId2));
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
            if ((int) $course['id'] === (int) $newCourseId) {
                continue;
            }
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

