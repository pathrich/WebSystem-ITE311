<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-0">Create New Course</h1>
        <p class="text-muted mb-0 small">Fill in the course details below</p>
    </div>
    <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Back to Dashboard
    </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="bi bi-book me-2"></i>Course Information</h5>
    </div>
    <div class="card-body">
        <form action="<?= base_url('course/store') ?>" method="post" id="courseForm">
            <?= csrf_field() ?>
            
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="course_number" class="form-label">Control Number (CN) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light">CN-</span>
                        <input type="text" class="form-control" id="course_number" name="course_number" 
                               value="<?= old('course_number') ?>" required placeholder="0001" 
                               pattern="[0-9]{4}" maxlength="4" minlength="4"
                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 4)">
                    </div>
                    <div class="form-text">Enter exactly 4 digits (e.g., 0001 for CN-0001). CN must be unique.</div>
                    <input type="hidden" id="course_number_full" name="course_number_full">
                </div>
                <div class="col-md-6">
                    <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="title" name="title" 
                           value="<?= old('title') ?>" required placeholder="e.g., Introduction to Programming">
                </div>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3" 
                          placeholder="Enter course description..."><?= old('description') ?></textarea>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="academic_year_id" class="form-label">Academic Year</label>
                    <select class="form-select" id="academic_year_id" name="academic_year_id">
                        <option value="">Select Academic Year</option>
                        <?php if (!empty($academicYears)): ?>
                            <?php foreach ($academicYears as $year): ?>
                                <option value="<?= $year['id'] ?>" <?= old('academic_year_id') == $year['id'] ? 'selected' : '' ?>>
                                    <?= esc($year['year_start']) ?> - <?= esc($year['year_end']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No academic years available. Please run migrations first.</option>
                        <?php endif; ?>
                    </select>
                    <?php if (empty($academicYears)): ?>
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Run migrations to add academic years: <code>php spark migrate</code>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="semester_id" class="form-label">Semester</label>
                    <select class="form-select" id="semester_id" name="semester_id">
                        <option value="">Select Semester</option>
                        <?php if (!empty($semesters)): ?>
                            <?php foreach ($semesters as $semester): ?>
                                <?php 
                                // Show 1st and 2nd Semester
                                $semesterName = strtolower($semester['name'] ?? '');
                                $isValidSemester = strpos($semesterName, '1st') !== false || 
                                                  strpos($semesterName, '2nd') !== false || 
                                                  strpos($semesterName, '1') !== false || 
                                                  strpos($semesterName, '2') !== false || 
                                                  strpos($semesterName, 'first') !== false ||
                                                  strpos($semesterName, 'second') !== false ||
                                                  $semesterName === 'sem 1' ||
                                                  $semesterName === 'sem 2' ||
                                                  $semesterName === 'semester 1' ||
                                                  $semesterName === 'semester 2';
                                if ($isValidSemester):
                                ?>
                                    <option value="<?= $semester['id'] ?>" 
                                            data-academic-year="<?= $semester['academic_year_id'] ?>"
                                            <?= old('semester_id') == $semester['id'] ? 'selected' : '' ?>>
                                        <?= esc($semester['name']) ?> (<?= esc($semester['year_start']) ?>-<?= esc($semester['year_end']) ?>)
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No semesters available. Please run migrations first.</option>
                        <?php endif; ?>
                    </select>
                    <div class="form-text">Semester options will filter based on selected academic year</div>
                    <?php if (empty($semesters)): ?>
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Run migrations to add semesters: <code>php spark migrate</code>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label for="term_id" class="form-label">Term</label>
                    <select class="form-select" id="term_id" name="term_id">
                        <option value="">Select Term</option>
                        <?php if (!empty($terms)): ?>
                            <?php foreach ($terms as $term): ?>
                                <option value="<?= $term['id'] ?>" 
                                        data-semester-id="<?= $term['semester_id'] ?>"
                                        <?= old('term_id') == $term['id'] ? 'selected' : '' ?>>
                                    <?= esc($term['name']) ?> (<?= esc($term['semester_name']) ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>No terms available. Please run migrations first.</option>
                        <?php endif; ?>
                    </select>
                    <div class="form-text">Term options will filter based on selected semester</div>
                    <?php if (empty($terms)): ?>
                        <div class="form-text text-warning">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Run migrations to add terms: <code>php spark migrate</code>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <hr class="my-4">
            <h5 class="mb-3"><i class="bi bi-clock me-2"></i>Course Schedule</h5>
            <p class="text-muted small">Add one or more schedule entries for this course</p>
            
            <div id="scheduleContainer">
                <div class="schedule-entry card mb-3">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Day of Week</label>
                                <select class="form-select" name="schedule_day[]" required>
                                    <option value="">Select Day</option>
                                    <option value="Monday">Monday</option>
                                    <option value="Tuesday">Tuesday</option>
                                    <option value="Wednesday">Wednesday</option>
                                    <option value="Thursday">Thursday</option>
                                    <option value="Friday">Friday</option>
                                    <option value="Saturday">Saturday</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Start Time</label>
                                <select class="form-select" name="schedule_start_time[]" required>
                                    <option value="">Select Start Time</option>
                                    <?php
                                    // Generate time options from 7:00 AM to 9:00 PM in 30-minute intervals
                                    for ($hour = 7; $hour <= 21; $hour++) {
                                        for ($minute = 0; $minute < 60; $minute += 30) {
                                            $time24 = sprintf('%02d:%02d:00', $hour, $minute);
                                            $hour12 = $hour > 12 ? $hour - 12 : ($hour == 0 ? 12 : $hour);
                                            $ampm = $hour >= 12 ? 'PM' : 'AM';
                                            $minuteStr = $minute == 0 ? '00' : $minute;
                                            $time12 = sprintf('%d:%s %s', $hour12, $minuteStr, $ampm);
                                            echo '<option value="' . $time24 . '">' . $time12 . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">End Time</label>
                                <select class="form-select" name="schedule_end_time[]" required>
                                    <option value="">Select End Time</option>
                                    <?php
                                    // Generate time options from 7:00 AM to 9:00 PM in 30-minute intervals
                                    for ($hour = 7; $hour <= 21; $hour++) {
                                        for ($minute = 0; $minute < 60; $minute += 30) {
                                            $time24 = sprintf('%02d:%02d:00', $hour, $minute);
                                            $hour12 = $hour > 12 ? $hour - 12 : ($hour == 0 ? 12 : $hour);
                                            $ampm = $hour >= 12 ? 'PM' : 'AM';
                                            $minuteStr = $minute == 0 ? '00' : $minute;
                                            $time12 = sprintf('%d:%s %s', $hour12, $minuteStr, $ampm);
                                            echo '<option value="' . $time24 . '">' . $time12 . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12 text-end">
                                <button type="button" class="btn btn-danger btn-sm remove-schedule" style="display: none;">
                                    <i class="bi bi-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-outline-primary btn-sm mb-3" id="addScheduleBtn">
                <i class="bi bi-plus me-2"></i>Add Another Schedule
            </button>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save me-2"></i>Create Course
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Wait for jQuery to be available
    function waitForJQuery(callback) {
        if (typeof jQuery !== 'undefined') {
            callback(jQuery);
        } else {
            setTimeout(function() { waitForJQuery(callback); }, 50);
        }
    }

    waitForJQuery(function($) {
        // Filter semesters based on academic year
        $('#academic_year_id').on('change', function() {
            const selectedYear = $(this).val();
            $('#semester_id option').each(function() {
                const option = $(this);
                if (option.val() === '') {
                    option.show();
                } else {
                    const academicYearId = option.data('academic-year');
                    if (selectedYear === '' || academicYearId == selectedYear) {
                        option.show();
                    } else {
                        option.hide();
                        if (option.prop('selected')) {
                            option.prop('selected', false);
                            // Also clear term selection when semester is cleared
                            $('#term_id').val('').trigger('change');
                        }
                    }
                }
            });
            // Trigger semester change to update terms
            $('#semester_id').trigger('change');
        });

        // Filter terms based on semester
        $('#semester_id').on('change', function() {
            const selectedSemester = $(this).val();
            $('#term_id option').each(function() {
                const option = $(this);
                if (option.val() === '') {
                    option.show();
                } else {
                    const semesterId = option.data('semester-id');
                    if (selectedSemester === '' || semesterId == selectedSemester) {
                        option.show();
                    } else {
                        option.hide();
                        if (option.prop('selected')) {
                            option.prop('selected', false);
                        }
                    }
                }
            });
        });

        // Add schedule entry - using both jQuery and vanilla JS for reliability
        const addScheduleBtn = document.getElementById('addScheduleBtn');
        if (addScheduleBtn) {
            addScheduleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const firstEntry = document.querySelector('.schedule-entry');
                if (!firstEntry) {
                    console.error('No schedule entry found to clone');
                    return;
                }
                
                const newEntry = firstEntry.cloneNode(true);
                
                // Clear all input values in the cloned entry
                const inputs = newEntry.querySelectorAll('input, select');
                inputs.forEach(function(input) {
                    if (input.type === 'checkbox') {
                        input.checked = false;
                    } else {
                        input.value = '';
                    }
                });
                
                // Show the remove button
                const removeBtn = newEntry.querySelector('.remove-schedule');
                if (removeBtn) {
                    removeBtn.style.display = 'block';
                }
                
                // Append to container
                const container = document.getElementById('scheduleContainer');
                if (container) {
                    container.appendChild(newEntry);
                }
            });
        }

        // Remove schedule entry
        $(document).on('click', '.remove-schedule', function(e) {
            e.preventDefault();
            if ($('.schedule-entry').length > 1) {
                $(this).closest('.schedule-entry').remove();
            } else {
                alert('At least one schedule entry is required.');
            }
        });

        // Show remove button if more than one entry
        if ($('.schedule-entry').length > 1) {
            $('.remove-schedule').show();
        }

        // Format CN input: ensure only numbers are entered, max 4 digits
        $('#course_number').on('input', function() {
            let value = $(this).val().replace(/[^0-9]/g, '').slice(0, 4);
            $(this).val(value);
            
            // Validate: must be exactly 4 digits
            if (value.length > 0 && value.length < 4) {
                $(this).addClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
                $(this).after('<div class="invalid-feedback">Please enter exactly 4 digits.</div>');
            } else if (value.length === 4) {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            }
        });

        // Format CN on form submit - ensure exactly 4 digits
        $('#courseForm').on('submit', function(e) {
            const cnValue = $('#course_number').val();
            if (cnValue) {
                if (cnValue.length !== 4) {
                    e.preventDefault();
                    alert('Control Number (CN) must be exactly 4 digits (e.g., 0001).');
                    $('#course_number').focus();
                    return false;
                }
                // Pad with zeros to ensure 4 digits
                const paddedValue = cnValue.padStart(4, '0');
                $('#course_number').val(paddedValue);
            }
        });
    });
});
</script>
<?= $this->endSection() ?>

