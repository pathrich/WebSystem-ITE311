<!doctype html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>Announcements</title>
		<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
	</head>
	<body class="bg-light">
		<div class="container py-4">
			<div class="d-flex justify-content-between align-items-center mb-3">
				<h1 class="h3">Announcements</h1>
				<a href="<?= base_url('/') ?>" class="btn btn-sm btn-secondary">Home</a>
			</div>

			<?php if (! empty(session()->getFlashdata('error'))): ?>
				<div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
			<?php endif; ?>

			<?php if (! empty($announcements) && is_array($announcements)): ?>
				<div class="list-group">
					<?php foreach ($announcements as $a): ?>
						<div class="list-group-item">
							<div class="d-flex w-100 justify-content-between">
								<h5 class="mb-1"><?= esc($a['title']) ?></h5>
								<small class="text-muted"><?= date('M d, Y H:i', strtotime($a['created_at'])) ?></small>
							</div>
							<p class="mb-1 mt-2"><?= nl2br(esc($a['content'])) ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			<?php else: ?>
				<div class="card">
					<div class="card-body">
						<p class="mb-0">No announcements yet.</p>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</body>
</html>
