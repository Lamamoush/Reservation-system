<?php
include 'includes/header.php';
include 'includes/database.php';
include 'includes/security.php';

$flight_number = isset($_GET['flight']) ? Security::cleanInput($_GET['flight']) : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Process booking form
    $db = new Database();
    $conn = $db->getConnection();
    
    $passenger_name = Security::cleanInput($_POST['passenger_name']);
    $passenger_email = Security::cleanInput($_POST['passenger_email']);
    $passenger_phone = Security::cleanInput($_POST['passenger_phone']);
    $flight_number = Security::cleanInput($_POST['flight_number']);
    
    $sql = "INSERT INTO bookings (passenger_name, passenger_email, passenger_phone, flight_number, departure_date) 
            VALUES (:name, :email, :phone, :flight, NOW())";
    
    $params = [
        ':name' => $passenger_name,
        ':email' => $passenger_email,
        ':phone' => $passenger_phone,
        ':flight' => $flight_number
    ];
    
    try {
        $stmt = Security::safeQuery($conn, $sql, $params);
        header("Location: confirm.php?booking_id=" . $conn->lastInsertId());
        exit();
    } catch(PDOException $e) {
        $error = "Booking error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-ticket-alt"></i> Flight Ticket Booking</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="booking.php">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="passenger_name" class="form-label">Full Passenger Name</label>
                                <input type="text" class="form-control" id="passenger_name" name="passenger_name" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="passenger_email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="passenger_email" name="passenger_email" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="passenger_phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="passenger_phone" name="passenger_phone" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="flight_number" class="form-label">Flight Number</label>
                            <input type="text" class="form-control" id="flight_number" name="flight_number" 
                                   value="<?php echo $flight_number; ?>" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-check"></i> Confirm Booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>