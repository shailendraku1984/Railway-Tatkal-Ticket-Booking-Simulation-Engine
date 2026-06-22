# Tatkal Ticket Booking Simulation Design

## Scope

This CodeIgniter 4 module simulates Indian Railway Tatkal booking for train `12345`.

- 20 compartments: `S1-S10`, `B1-B10`
- 72 seats per compartment
- 1,440 confirmed seats
- RAC capacity: 50
- Waitlist: unlimited
- CLI load command: `php spark tatkal:simulate 10000`

## Core Components

- Controllers: `TatkalController`
- Commands: `TatkalSimulate`, `TatkalWorker`
- Service layer: `TatkalBookingService`, `TatkalLockManager`
- Repository layer: `TatkalRepository`
- Tables: `trains`, `compartments`, `seats`, `bookings`, `passengers`, `booking_status_history`, `rac_queue`, `waiting_queue`, `cancellations`, `booking_audit_logs`

## Concurrency Strategy

The booking flow combines coarse and fine locks:

- MySQL advisory lock: `GET_LOCK('tatkal_train_12345', 10)` serializes train inventory decisions.
- Transaction boundary: every booking and cancellation runs inside one database transaction.
- Pessimistic row locks: `SELECT ... FOR UPDATE` locks train rows, available seats, RAC rows, waitlist rows, and cancellation targets.
- Optimistic version field: `seats.lock_version` increments on every allocation or release.
- Deadlock retry: retryable MySQL lock errors are retried with backoff.
- Audit log: important booking and cancellation events are stored with payload and duration.

Redis can be placed in front of `TatkalLockManager` later without changing the booking service contract. In the current XAMPP-friendly implementation, MySQL advisory locks are the reliable default.

## Booking Sequence

```mermaid
sequenceDiagram
    participant CLI as CLI Worker
    participant Service as TatkalBookingService
    participant Lock as TatkalLockManager
    participant DB as MySQL

    CLI->>Service: book(passenger request)
    Service->>DB: Load train 12345
    Service->>Service: Check Tatkal opening time
    Service->>Lock: GET_LOCK(train)
    Service->>DB: BEGIN
    Service->>DB: SELECT train FOR UPDATE
    Service->>DB: SELECT available seat FOR UPDATE
    alt Seat available
        Service->>DB: Insert booking and passenger
        Service->>DB: Mark seat BOOKED, increment lock_version
        Service->>DB: Insert CONFIRMED history and audit log
    else No confirmed seat
        Service->>DB: Lock active RAC queue
        alt RAC slot available
            Service->>DB: Insert RAC booking and queue row
        else RAC full
            Service->>DB: Lock waitlist tail
            Service->>DB: Insert WAITING booking and queue row
        end
    end
    Service->>DB: COMMIT
    Service->>Lock: RELEASE_LOCK(train)
    Service-->>CLI: PNR and status
```

## Cancellation Sequence

```mermaid
sequenceDiagram
    participant Admin as Dashboard
    participant Service as TatkalBookingService
    participant DB as MySQL

    Admin->>Service: cancel(PNR)
    Service->>DB: BEGIN
    Service->>DB: Lock booking by PNR FOR UPDATE
    Service->>DB: Mark booking CANCELLED
    alt Cancelled ticket was CONFIRMED
        Service->>DB: Lock first RAC FOR UPDATE
        alt RAC exists
            Service->>DB: Promote RAC to released seat
            Service->>DB: Lock first WL FOR UPDATE
            Service->>DB: Promote WL to RAC
            Service->>DB: Renumber active WL rows
        else No RAC exists
            Service->>DB: Release seat to AVAILABLE
        end
    else Cancelled ticket was RAC
        Service->>DB: Mark RAC row CANCELLED
        Service->>DB: Promote first WL to RAC
    else Cancelled ticket was WAITING
        Service->>DB: Mark WL row CANCELLED
        Service->>DB: Renumber active WL rows
    end
    Service->>DB: Insert cancellation, history, audit
    Service->>DB: COMMIT
```

## Running

```bash
php spark migrate
php spark db:seed TatkalSeeder
php spark tatkal:simulate 10000
```

Simulation output is stored under `writable/tatkal_simulation/<run_id>/summary.json`.

## Dashboard

- `/tatkal`: live metrics, search, reports
- `/tatkal/pnr`: PNR enquiry and cancellation
- `/tatkal/live`: JSON metrics endpoint polled by the dashboard
