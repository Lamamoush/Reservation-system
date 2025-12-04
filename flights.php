<?php
require_once 'includes/header.php';

$db = new Database();
$conn = $db->getConnection();

// Modified query - removed date condition for testing
$sql = "SELECT * FROM flights WHERE available_seats > 0 ORDER BY departure_date ASC";
$stmt = $conn->prepare($sql);
$stmt->execute();
$flights = $stmt->fetchAll(PDO::FETCH_ASSOC);

// For debugging: display query and results
error_log("Flights query: " . $sql);
error_log("Flights count: " . count($flights));
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-plane-departure"></i> Available Flights</h2>
    
    <!-- For debugging: display debug information -->
    <div class="alert alert-info">
        <strong>Debug Information:</strong><br>
        - Number of flights in database: <?php echo count($flights); ?><br>
        - Current server time: <?php echo date('Y-m-d H:i:s'); ?><br>
        - SQL Query: <?php echo $sql; ?>
    </div>
    
    <?php if(empty($flights)): ?>
        <div class="alert alert-warning text-center">
            <i class="fas fa-exclamation-triangle"></i> No flights available currently
            <br>
            <small>This may be because there are no flights in the database or all flights are full</small>
        </div>
        
        <!-- Button to add sample flights -->
        <div class="text-center mt-4">
            <a href="add_sample_flights.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Sample Flights
            </a>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach($flights as $flight): ?>
            <div class="col-md-6 mb-4">
                <div class="card flight-card">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Flight <?php echo Security::escapeOutput($flight['flight_number']); ?></h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6">
                                <strong>From:</strong> <?php echo Security::escapeOutput($flight['origin']); ?>
                            </div>
                            <div class="col-6">
                                <strong>To:</strong> <?php echo Security::escapeOutput($flight['destination']); ?>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <strong>Departure Date:</strong> 
                                <?php echo Functions::formatDate($flight['departure_date'], 'Y-m-d H:i'); ?>
                            </div>
                            <div class="col-6">
                                <strong>Arrival Date:</strong> 
                                <?php echo Functions::formatDate($flight['arrival_date'], 'Y-m-d H:i'); ?>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <strong>Price:</strong> 
                                <span class="text-success fw-bold"><?php echo Security::escapeOutput($flight['price']); ?> SAR</span>
                            </div>
                            <div class="col-6">
                                <strong>Available Seats:</strong> 
                                <span class="badge bg-<?php echo $flight['available_seats'] > 0 ? 'success' : 'danger'; ?>">
                                    <?php echo Security::escapeOutput($flight['available_seats']); ?>
                                </span>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-12">
                                <?php if(Auth::isLoggedIn() && $flight['available_seats'] > 0): ?>
                                    <a href="booking.php?flight=<?php echo $flight['flight_number']; ?>" 
                                       class="btn btn-success w-100">
                                       <i class="fas fa-ticket-alt"></i> Book Now
                                    </a>
                                <?php elseif(!Auth::isLoggedIn() && $flight['available_seats'] > 0): ?>
                                    <a href="login.php" class="btn btn-warning w-100">
                                        <i class="fas fa-sign-in-alt"></i> Login to Book
                                    </a>
                                <?php else: ?>
                                    <button class="btn btn-danger w-100" disabled>
                                        <i class="fas fa-times"></i> No Seats Available
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>