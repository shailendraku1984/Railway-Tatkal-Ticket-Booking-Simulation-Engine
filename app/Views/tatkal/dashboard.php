<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tatkal Simulation Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= site_url('tatkal') ?>">Tatkal Simulation</a>
        <div class="navbar-nav">
            <a class="nav-link" href="<?= site_url('tatkal/pnr') ?>">PNR Enquiry</a>
            <a class="nav-link" href="<?= site_url('tatkal/rejected') ?>">Rejected Requests</a>
        </div>
    </div>
</nav>

<main class="container-fluid py-4">
    <?php if (session('message')): ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
    <?php if ($dashboard['train'] === null): ?>
        <div class="alert alert-warning">Run migrations and seed <code>TatkalSeeder</code> before opening the dashboard.</div>
    <?php else: ?>
        <?php $metrics = $dashboard['metrics']; $reports = $dashboard['reports']; ?>
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">Train <?= esc($dashboard['train']['train_number']) ?></h1>
                <div class="text-muted">Opening time: <?= esc($dashboard['train']['tatkal_opening_time']) ?> | Sleeper INR 500 | AC INR 1200</div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form class="d-flex gap-2" method="get" action="<?= site_url('tatkal') ?>">
                    <input class="form-control" name="q" value="<?= esc($term) ?>" placeholder="PNR, mobile, passenger">
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
                <a class="btn btn-outline-secondary" href="<?= site_url('tatkal/rejected') ?>">Rejected History</a>
                <a class="btn btn-outline-danger" href="<?= site_url('tatkal/reset') ?>" onclick="return confirm('Reset all bookings and rejected request history?')">Reset History</a>
            </div>
        </div>

        <section class="row g-3 mb-4" id="metrics">
            <?php foreach ([
                'total_seats' => 'Total Seats',
                'available_seats' => 'Available Seats',
                'booked_seats' => 'Booked Seats',
                'rejected' => 'Rejected Requests',
                'revenue' => 'Revenue INR',
                'cancelled' => 'Cancelled',
            ] as $key => $label): ?>
                <div class="col-6 col-md-4 col-xl-2">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body">
                            <div class="text-muted small"><?= esc($label) ?></div>
                            <div class="fs-3 fw-semibold" data-metric="<?= esc($key) ?>"><?= esc((string) ($metrics[$key] ?? 0)) ?></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <?php if ($term !== ''): ?>
            <section class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Search Results</div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>PNR</th><th>Name</th><th>Mobile</th><th>Status</th><th>Seat</th><th>Amount</th><th>Action</th></tr></thead>
                        <tbody>
                        <?php foreach ($results as $row): ?>
                            <tr>
                                <td><?= esc($row['pnr']) ?></td>
                                <td><?= esc($row['passenger_name']) ?></td>
                                <td><?= esc($row['mobile_number']) ?></td>
                                <td><span class="badge text-bg-secondary"><?= esc($row['status']) ?></span></td>
                                <td><?= esc(trim(($row['compartment'] ?? '') . ' ' . ($row['seat_number'] ?? '') . ' ' . ($row['seat_type'] ?? ''))) ?></td>
                                <td>INR <?= esc(number_format((float) ($row['booking_amount'] ?? 0), 2)) ?></td>
                                <td><a class="btn btn-sm btn-outline-primary" href="<?= site_url('tatkal/pnr?pnr=' . urlencode($row['pnr'])) ?>">View</a></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($results === []): ?><tr><td colspan="7" class="text-muted">No matching bookings.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        <?php endif; ?>

        <section class="row g-3">
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Compartment Wise Occupancy</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Compartment</th><th>Class</th><th>Booked</th><th>Total</th><th>Occupancy</th></tr></thead>
                            <tbody>
                            <?php foreach ($reports['compartments'] as $row): ?>
                                <?php $percent = $row['total'] > 0 ? round(($row['booked'] / $row['total']) * 100) : 0; ?>
                                <tr>
                                    <td><?= esc($row['code']) ?></td>
                                    <td><?= esc($row['class_type']) ?></td>
                                    <td><?= esc($row['booked']) ?></td>
                                    <td><?= esc($row['total']) ?></td>
                                    <td>
                                        <div class="progress" style="height: 8px"><div class="progress-bar" style="width: <?= $percent ?>%"></div></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-semibold">Gender Distribution</div>
                    <div class="card-body">
                        <?php foreach ($reports['gender'] as $row): ?>
                            <div class="d-flex justify-content-between border-bottom py-2"><span><?= esc($row['gender']) ?></span><strong><?= esc($row['total']) ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Age Distribution</div>
                    <div class="card-body">
                        <?php foreach (($reports['age'] ?? []) as $label => $count): ?>
                            <div class="d-flex justify-content-between border-bottom py-2"><span><?= esc(str_replace('_', ' ', strtoupper($label))) ?></span><strong><?= esc((string) $count) ?></strong></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-semibold">Hourly Booking Statistics</div>
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead><tr><th>Hour</th><th>Total Bookings</th></tr></thead>
                            <tbody>
                            <?php foreach ($reports['hourly'] as $row): ?>
                                <tr><td><?= esc($row['hour_bucket']) ?></td><td><?= esc($row['total']) ?></td></tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    <?php endif; ?>
</main>
<script>
setInterval(async () => {
    const response = await fetch('<?= site_url('tatkal/live') ?>');
    const metrics = await response.json();
    Object.entries(metrics).forEach(([key, value]) => {
        const target = document.querySelector(`[data-metric="${key}"]`);
        if (target) target.textContent = value;
    });
}, 3000);
</script>
</body>
</html>
