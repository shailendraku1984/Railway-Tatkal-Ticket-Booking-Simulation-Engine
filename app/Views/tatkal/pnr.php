<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PNR Enquiry</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg bg-dark navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= site_url('tatkal') ?>">Tatkal Simulation</a>
    </div>
</nav>
<main class="container py-4">
    <h1 class="h3 mb-3">PNR Enquiry</h1>
    <?php if (session('message')): ?><div class="alert alert-success"><?= esc(session('message')) ?></div><?php endif; ?>
    <?php if (session('error')): ?><div class="alert alert-danger"><?= esc(session('error')) ?></div><?php endif; ?>
    <form class="row g-2 mb-4" method="get">
        <div class="col-md-8"><input class="form-control" name="pnr" value="<?= esc($pnr) ?>" maxlength="10" placeholder="Enter 10-digit PNR"></div>
        <div class="col-md-4"><button class="btn btn-primary w-100" type="submit">Search</button></div>
    </form>

    <?php if ($pnr !== '' && $booking === null): ?>
        <div class="alert alert-warning">No booking found for this PNR.</div>
    <?php elseif ($booking !== null): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4"><div class="text-muted small">Passenger</div><div class="fw-semibold"><?= esc($booking['passenger_name']) ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Train Number</div><div class="fw-semibold"><?= esc($booking['train_number']) ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Status</div><span class="badge text-bg-primary"><?= esc($booking['status']) ?></span></div>
                    <div class="col-md-4"><div class="text-muted small">Compartment</div><div><?= esc($booking['compartment'] ?? '-') ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Seat Number</div><div><?= esc($booking['seat_number'] ?? '-') ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Seat Type</div><div><?= esc($booking['seat_type'] ?? '-') ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Booking Amount</div><div>INR <?= esc(number_format((float) ($booking['booking_amount'] ?? 0), 2)) ?></div></div>
                    <div class="col-md-4"><div class="text-muted small">Mobile</div><div><?= esc($booking['mobile_number']) ?></div></div>
                </div>
                <?php if ($booking['status'] !== 'CANCELLED'): ?>
                    <form method="post" action="<?= site_url('tatkal/cancel') ?>" class="mt-4 d-flex gap-2">
                        <?= csrf_field() ?>
                        <input type="hidden" name="pnr" value="<?= esc($booking['pnr']) ?>">
                        <input class="form-control" name="reason" placeholder="Cancellation reason">
                        <button class="btn btn-outline-danger" type="submit">Cancel Ticket</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</main>
</body>
</html>
