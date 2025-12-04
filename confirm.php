<?php
include 'includes/header.php';
include 'includes/database.php';

$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : 0;

$db = new Database();
$conn = $db->getConnection();

$sql = "SELECT b.*, f.origin, f.destination, f.departure_date 
        FROM bookings b 
        JOIN flights f ON b.flight_number = f.flight_number 
        WHERE b.id = :id";
$stmt = $conn->prepare($sql);
$stmt->execute([':id' => $booking_id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-success">
                <div class="card-header bg-success text-white text-center">
                    <h4 class="mb-0"><i class="fas fa-check-circle"></i> Booking Successful!</h4>
                </div>
                <div class="card-body">
                    <?php if($booking): ?>
                    <div class="alert alert-info">
                        <h5>Booking Details:</h5>
                        <p><strong>Booking Number:</strong> <?php echo $booking['id']; ?></p>
                        <p><strong>Passenger Name:</strong> <?php echo $booking['passenger_name']; ?></p>
                        <p><strong>Flight:</strong> <?php echo $booking['origin']; ?> to <?php echo $booking['destination']; ?></p>
                        <p><strong>Flight Number:</strong> <?php echo $booking['flight_number']; ?></p>
                        <p><strong>Travel Date:</strong> <?php echo $booking['departure_date']; ?></p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary me-2">Back to Home</a>
                        <a href="dashboard.php" class="btn btn-outline-primary">Dashboard</a>
                    </div>
                    <?php else: ?>
                    <div class="alert alert-danger">Booking details not found</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>