<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Materials - <?= esc($course['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 offset-md-2">
                <div class="card">
                    <div class="card-header">
                        <h3>Upload Materials for: <?= esc($course['title']) ?></h3>
                        <a href="<?= base_url('dashboard') ?>" class="btn btn-secondary btn-sm">Back to Dashboard</a>
                    </div>
                    <div class="card-body">
                        <!-- Display flash messages -->
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success">
                                <?= session()->getFlashdata('success') ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger">
                                <?= session()->getFlashdata('error') ?>
                            </div>
                        <?php endif; ?>

                        <!-- Upload Form -->
                        <form action="<?= base_url('materials/upload/' . $course['id']) ?>" method="post" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="mb-3">
                                <label for="material_file" class="form-label">Select Material File</label>
                                <input type="file" class="form-control" id="material_file" name="material_file" required
                                       accept=".pdf,.ppt,.pptx">
                                <div class="form-text">
                                    Allowed file types: PDF and PPT only. Maximum size: 10MB.
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Upload Material</button>
                        </form>

                        <hr>

                        <!-- Existing Materials -->
                        <h4 class="mt-4">Existing Materials</h4>
                        <?php if (empty($materials)): ?>
                            <p class="text-muted">No materials uploaded yet.</p>
                        <?php else: ?>
                            <div class="list-group">
                                <?php foreach ($materials as $material): ?>
                                    <div class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= esc($material['file_name']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                Uploaded on: <?= date('M d, Y H:i', strtotime($material['created_at'])) ?>
                                            </small>
                                        </div>
                                        <div>
                                            <a href="<?= base_url('materials/download/' . $material['id']) ?>" class="btn btn-sm btn-outline-primary me-2" target="_blank">
                                                Download
                                            </a>
                                            <a href="<?= base_url('materials/delete/' . $material['id']) ?>" class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure you want to delete this material?')">
                                                Delete
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
