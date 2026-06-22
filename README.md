# Railway Tatkal Ticket Booking Simulation Engine

A simulation-based Railway Tatkal Reservation System developed using CodeIgniter 4, MySQL and Bootstrap 5.

The project demonstrates how a railway reservation engine manages seat allocation, booking requests, PNR generation, ticket cancellation and booking analytics.

## Features

### Booking Engine

* Real-time seat allocation
* Sleeper and AC coach support
* Automatic seat assignment
* Unique PNR generation
* Passenger information management
* Booking rejection when seats are unavailable

### Coach Structure

Sleeper Coaches:

* S1 to S10
* 72 seats per coach

AC Coaches:

* B1 to B10
* 72 seats per coach

Total Capacity:

* 20 Coaches
* 1440 Seats

### PNR Enquiry

* Search booking by PNR
* Passenger details
* Coach details
* Seat details
* Booking status

### Ticket Cancellation

* Ticket cancellation support
* Cancellation reason tracking
* Seat inventory restoration

### Dashboard & Analytics

* Total seats
* Available seats
* Booked seats
* Cancelled tickets
* Coach-wise occupancy
* Gender distribution
* Age distribution
* Booking statistics

### Rejected Booking Tracking

* First Name
* Last Name
* Mobile Number
* Request Time
* Rejection Message

### CLI Load Simulation

Generate thousands of booking requests.

Example:

```bash
php spark tatkal:simulate 300
```

Simulation generates:

* Random passengers
* Random booking requests
* Seat allocation
* Booking reports

### Technology Stack

Backend:

* PHP 8+
* CodeIgniter 4

Database:

* MySQL

Frontend:

* Bootstrap 5

Tools:

* Spark CLI Commands

## Project Goals

* Understand railway reservation workflow
* Learn database transaction concepts
* Simulate high-volume booking requests
* Demonstrate backend system design skills
* Explore concurrency handling patterns

## Future Enhancements

* Redis Integration
* Distributed Locking
* Docker Containerization
* Kubernetes Deployment
* Booking Queue Processing
* WebSocket Live Updates
* Microservices Architecture
* CI/CD Pipeline
* Performance Benchmarking

## Screenshots

(Add dashboard and PNR screenshots here)

## Author

Shailendra Kumar
Senior PHP Technical Lead