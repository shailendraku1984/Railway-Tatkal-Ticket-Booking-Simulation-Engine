<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rejected Booking Requests</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= site_url('tatkal') ?>">Tatkal Simulation</a>
        <div class="navbar-nav">
            <a class="nav-link" href="<?= site_url('tatkal/pnr') ?>">PNR Enquiry</a>
        </div>
    </div>
</nav>
<main class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0">Rejected Requests</h1>
        <a class="btn btn-outline-primary" href="<?= site_url('tatkal') ?>">Dashboard</a>
    </div>
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                <tr>
                    <th>Session ID</th>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Contact No</th>
                    <th>Request Time</th>
                    <th>Message</th>
                    <th>Created At</th>
                    <th>Updated At</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($requests as $request): ?>
                    <tr>
                        <td><code><?= esc($request['session_id']) ?></code></td>
                        <td><?= esc($request['first_name']) ?></td>
                        <td><?= esc($request['last_name']) ?></td>
                        <td><?= esc($request['contact_no']) ?></td>
                        <td><?= esc($request['request_time']) ?></td>
                        <td><?= esc($request['message']) ?></td>
                        <td><?= esc($request['created_at']) ?></td>
                        <td><?= esc($request['updated_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($requests === []): ?>
                    <tr><td colspan="8" class="text-muted">No rejected booking requests yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
