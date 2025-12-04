<?php include 'includes/header.php'; ?>

<div class="container mt-5">
    <div class="jumbotron bg-light p-5 rounded">
        <h1 class="display-4">WELCOME TO THE COAST AIRRlINES</h1>
        <p class="lead">Book your trip easily and securely</p>
        <hr class="my-4">
        <p>Choose from the best trips at the best prices</p>
        <a class="btn btn-primary btn-lg" href="flights.php" role="button">
            Browse Flights <i class="fas fa-arrow-right"></i>
        </a>
    </div>

    <div class="row mt-5">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-search fa-3x text-primary mb-3"></i>
                    <h5 class="card-title">Search for Flights</h5>
                    <p class="card-text">Find the best flights that suit you</p>
                    <a href="flights.php" class="btn btn-outline-primary">Search Now</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-ticket-alt fa-3x text-success mb-3"></i>
                    <h5 class="card-title">Book Your Ticket</h5>
                    <p class="card-text">Book your ticket in simple steps</p>
                    <a href="booking.php" class="btn btn-outline-success">Book Now</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <i class="fas fa-user fa-3x text-info mb-3"></i>
                    <h5 class="card-title">Access Booking</h5>
                    <p class="card-text">Access your bookings dashboard</p>
                    <a href="dashboard.php" class="btn btn-outline-info">Access Now</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>