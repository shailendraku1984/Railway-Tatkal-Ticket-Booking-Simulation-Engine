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
php spark tatkal:simulate 10000
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

<img width="1344" height="906" alt="simulation" src="https://github.com/user-attachments/assets/46d1a5e3-1ee0-49b1-bd5f-df546d4293e1" />

<img width="1349" height="1166" alt="Dashboard" src="https://github.com/user-attachments/assets/2f71d91c-0109-45fd-a9b7-a19642c8155f" />

<img width="1349" height="1133" alt="Dashboard-2" src="https://github.com/user-attachments/assets/f17c7db3-aeb4-41e9-b07b-04eef089a1d0" />

<img width="1349" height="1166" alt="Tatkal Simulation Dashboard" src="https://github.com/user-attachments/assets/e65b8fdc-963d-4372-9fd2-9a352a2c5fa6" />

<img width="1349" height="1166" alt="Tatkal Simulation Dashboard" src="https://github.com/user-attachments/assets/18aa62bc-8245-4ad6-99b1-6d2dce65443f" />

<img width="1349" height="1166" alt="Tatkal Simulation Dashboard" src="https://github.com/user-attachments/assets/d8eb7c74-20a4-4b2c-9900-4fc76649ad6d" />

<img width="1366" height="643" alt="Rejected Booking Requests" src="https://github.com/user-attachments/assets/10dcea95-0487-4c32-b371-10efb999987b" />

<img width="1366" height="643" alt="PNR Enquiry" src="https://github.com/user-attachments/assets/95a9484a-c862-481c-a24f-d3ffabe34ce0" />

<img width="1366" height="643" alt="PNR Enquiry" src="https://github.com/user-attachments/assets/053eb6fd-0f7f-4f9f-9fdc-aec2e91b39b4" />

## Future roadmap

v1.0
✓ Booking Engine
✓ PNR Management
✓ Cancellation
✓ Analytics Dashboard

v2.0
□ Database Transactions
□ Seat Locking
□ Concurrency Testing

v3.0
□ Redis Cache
□ Distributed Locks
□ Queue Processing

v4.0
□ Docker
□ Docker Compose

v5.0
□ Kubernetes
□ HPA
□ Ingress
□ CI/CD

## Author

Shailendra Kumar
Senior PHP Technical Lead
