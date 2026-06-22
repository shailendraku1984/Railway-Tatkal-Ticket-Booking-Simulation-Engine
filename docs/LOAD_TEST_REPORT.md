# Tatkal Load Test Report

## Environment

- Framework: CodeIgniter 4.7.3
- PHP: 8.2.12 through XAMPP
- Database: MySQL `railway_tatkal_booking`
- Train: `12345`
- Confirmed inventory: 1,440 seats
- RAC/Waitlist: disabled for this practical Tatkal simulation
- Overflow behavior: rejected request history with `No seat available`

## Validated Smoke Test

Command:

```bash
php spark tatkal:simulate 20
```

Result:

```text
Total Requests : 20
Confirmed      : 20
Rejected       : 0
Failed         : 0
Execution Time : 1.559 seconds
```

Report file:

```text
writable/tatkal_simulation/20260622155710_b54ce7/summary.json
```

## Full Load Command

```bash
php spark tatkal:simulate 10000
```

Expected final distribution on a fresh seed:

- Confirmed: 1,440
- Rejected: 8,560
- Failed: 0, assuming MySQL remains available and Tatkal opening time has passed

The simulator starts multiple PHP worker processes and each booking is protected by transaction boundaries, train-level advisory locks, and `SELECT ... FOR UPDATE` row locks.

## Overflow Verification

After 20 existing confirmed bookings, this command crossed the seat limit:

```bash
php spark tatkal:simulate 1425
```

Result:

```text
Total Requests : 1425
Confirmed      : 1420
Rejected       : 5
Failed         : 0
Execution Time : 29.229 seconds
```
