<?= $this->extend('template') ?>

<?= $this->section('content') ?>

<div class="row">
  <div class="col-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Courses</h3>
      <div class="input-group w-50">
        <input type="text" id="searchInput" class="form-control" placeholder="Search courses..." aria-label="Search courses">
        <button class="btn btn-outline-secondary" id="serverSearchBtn" type="button">Search</button>
      </div>
    </div>
    <div id="courseResults" class="row g-3">
      <?php if (isset($courses) && is_array($courses) && count($courses) > 0): ?>
        <?php foreach ($courses as $course): ?>
          <div class="col-12 col-md-6 course-item">
            <div class="card h-100">
              <div class="card-body">
                <h5 class="card-title"><?= esc($course['title']) ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">By: <?= esc($course['instructor_name'] ?? '') ?></h6>
                <p class="card-text"><?= esc($course['description']) ?></p>
                <div class="small text-muted">Added: <?= esc($course['created_at'] ?? '') ?></div>
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

<!-- Client-side and Server-side search scripts -->
<script>
  function initCourseSearch() {
    $(function() {
    // Client-side filtering and server-side AJAX search (with debounce)

    // Server-side AJAX search with debounce
    var debounceTimer = null;
    function performServerSearch(keyword) {
      $.ajax({
        url: '<?= site_url('courses/search') ?>',
        method: 'GET',
        data: { keyword: keyword },
        dataType: 'json',
        success: function(data) {
          // Replace the courseResults content
          var html = '';
          if (!data || data.length === 0) {
            html = '<div class="col-12"><div class="alert alert-warning">No courses found</div></div>';
          } else {
            data.forEach(function(c) {
              html += '<div class="col-12 col-md-6 course-item">';
              html += '<div class="card h-100"><div class="card-body">';
              html += '<h5 class="card-title">' + $('<div/>').text(c.title).html() + '</h5>';
              html += '<h6 class="card-subtitle mb-2 text-muted">By: ' + $('<div/>').text(c.instructor_name || '').html() + '</h6>';
              html += '<p class="card-text">' + $('<div/>').text(c.description || '').html() + '</p>';
              html += '<div class="small text-muted">Added: ' + $('<div/>').text(c.created_at || '').html() + '</div>';
              html += '</div></div></div>';
            });
          }
          $('#courseResults').html(html);
        },
        error: function() {
          $('#courseResults').html('<div class="col-12"><div class="alert alert-danger">Error fetching courses.</div></div>');
        }
      });
    }

    $('#searchInput').on('keyup', function() {
      var keyword = $(this).val();

      // Instant client-side filtering
      var q = keyword.toLowerCase();
      $('.course-item').each(function() {
        var text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(q) > -1);
      });

      // Debounced server-side search
      if (debounceTimer) { clearTimeout(debounceTimer); }
      debounceTimer = setTimeout(function() { performServerSearch(keyword); }, 300);
    });

    $('#serverSearchBtn').on('click', function() {
      var keyword = $('#searchInput').val();
      performServerSearch(keyword);
    });
    });
  }

  if (window.jQuery) {
    initCourseSearch();
  } else {
    window.addEventListener('load', initCourseSearch);
  }
</script>

<?= $this->endSection() ?>
