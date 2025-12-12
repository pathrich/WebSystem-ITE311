<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<div class="row">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Courses</h3>
    </div>

    <div class="row mb-4">
      <div class="col-md-8 col-lg-6">
        <form id="searchForm" method="get" action="<?= site_url('courses/search') ?>">
          <div class="input-group">
            <input type="text" id="searchInput" class="form-control" placeholder="Search courses by title or description..." name="search_term">
            <button class="btn btn-outline-secondary" type="submit">
              <i class="bi bi-search"></i>
            </button>
          </div>
          <div class="form-check mt-2">
            <input class="form-check-input" type="checkbox" id="useServerSearch">
            <label class="form-check-label" for="useServerSearch">
              Use server-side search (comprehensive results)
            </label>
          </div>
        </form>
      </div>
    </div>
    <div id="coursesContainer" class="row">
      <?php if (isset($courses) && is_array($courses) && count($courses) > 0): ?>
        <?php foreach ($courses as $course): ?>
          <div class="col-md-4 mb-4 course-item">
            <div class="card course-card h-100">
              <div class="card-body">
                <h5 class="card-title"><?= esc($course['title'] ?? $course['course_name'] ?? '') ?></h5>
                <p class="card-text"><?= esc($course['description'] ?? $course['course_description'] ?? '') ?></p>
                <?php if (!empty($course['id'])): ?>
                  <a href="<?= site_url('courses/view/' . $course['id']) ?>" class="btn btn-primary">View Course</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-info" role="alert">No courses available</div>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Client-side and AJAX search scripts (per instruction) -->
<script>
  $(document).ready(function() {
    // Client-side filtering (always active on keyup)
    $('#searchInput').on('keyup', function() {
      var value = $(this).val().toLowerCase();
      $('.course-item').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
      });
    });

    // Server-side search with AJAX (only when checkbox is checked)
    $('#searchForm').on('submit', function(e) {
      e.preventDefault();

      var searchTerm = $('#searchInput').val();
      var useServer = $('#useServerSearch').is(':checked');

      if (!useServer) {
        // If not using server-side search, rely on client-side filter only
        // Trigger keyup handler to ensure filter is applied
        $('#searchInput').trigger('keyup');
        return;
      }

      $.get('<?= site_url('courses/search') ?>', { search_term: searchTerm }, function(response) {
        // Expecting { courses: [...], searchTerm: '...' }
        var data = response && response.courses ? response.courses : [];

        $('#coursesContainer').empty();

        if (data.length > 0) {
          $.each(data, function(index, course) {
            var title = course.title || course.course_name || '';
            var description = course.description || course.course_description || '';
            var id = course.id || '';

            var courseHtml =
              '<div class="col-md-4 mb-4 course-item">' +
                '<div class="card course-card h-100">' +
                  '<div class="card-body">' +
                    '<h5 class="card-title">' + $('<div/>').text(title).html() + '</h5>' +
                    '<p class="card-text">' + $('<div/>').text(description).html() + '</p>' +
                    (id ? '<a href="<?= site_url('courses/view') ?>/' + id + '" class="btn btn-primary">View Course</a>' : '') +
                  '</div>' +
                '</div>' +
              '</div>';

            $('#coursesContainer').append(courseHtml);
          });
        } else {
          $('#coursesContainer').html('<div class="col-12"><div class="alert alert-info">No courses found matching your search.</div></div>');
        }
      }, 'json');
    });
  });
</script>

<?= $this->endSection() ?>
