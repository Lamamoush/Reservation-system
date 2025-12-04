<?php
require_once 'includes/header.php';

// Check if logged in
if (!Auth::isLoggedIn()) {
    Functions::redirect('login.php', 'You must login first');
}

$db = new Database();
$conn = $db->getConnection();
$user = Auth::getUserInfo();

// Get user statistics
$stats = [];
try {
    // Total bookings count
    $sql = "SELECT COUNT(*) as total_bookings FROM bookings WHERE user_id = :user_id";
    $stmt = Security::safeQuery($conn, $sql, [':user_id' => $user['id']]);
    $stats['total_bookings'] = $stmt->fetch()['total_bookings'];
    
    // Confirmed bookings
    $sql = "SELECT COUNT(*) as confirmed_bookings FROM bookings WHERE user_id = :user_id AND status = 'confirmed'";
    $stmt = Security::safeQuery($conn, $sql, [':user_id' => $user['id']]);
    $stats['confirmed_bookings'] = $stmt->fetch()['confirmed_bookings'];
    
    // Cancelled bookings
    $sql = "SELECT COUNT(*) as cancelled_bookings FROM bookings WHERE user_id = :user_id AND status = 'cancelled'";
    $stmt = Security::safeQuery($conn, $sql, [':user_id' => $user['id']]);
    $stats['cancelled_bookings'] = $stmt->fetch()['cancelled_bookings'];
    
    // Total payments
    $sql = "SELECT SUM(total_price) as total_spent FROM bookings WHERE user_id = :user_id AND status = 'confirmed'";
    $stmt = Security::safeQuery($conn, $sql, [':user_id' => $user['id']]);
    $stats['total_spent'] = $stmt->fetch()['total_spent'] ?: 0;

} catch (Exception $e) {
    $error = "Error loading statistics: " . $e->getMessage();
}

// Get recent bookings
$recent_bookings = [];
try {
    $sql = "SELECT b.*, f.origin, f.destination, f.departure_date as flight_departure 
            FROM bookings b 
            JOIN flights f ON b.flight_number = f.flight_number 
            WHERE b.user_id = :user_id 
            ORDER BY b.booking_date DESC 
            LIMIT 5";
    $stmt = Security::safeQuery($conn, $sql, [':user_id' => $user['id']]);
    $recent_bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Error loading bookings: " . $e->getMessage();
}

// Handle booking cancellation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $booking_id = Security::cleanInput($_POST['booking_id']);
    $csrf_token = $_POST['csrf_token'];
    
    if (Security::verifyCSRFToken($csrf_token)) {
        try {
            $sql = "UPDATE bookings SET status = 'cancelled' WHERE id = :id AND user_id = :user_id";
            Security::safeQuery($conn, $sql, [
                ':id' => $booking_id,
                ':user_id' => $user['id']
            ]);
            Functions::redirect('dashboard.php', 'Booking cancelled successfully');
        } catch (Exception $e) {
            $error = "Error cancelling booking: " . $e->getMessage();
        }
    } else {
        $error = "Invalid verification token";
    }
}

$csrf_token = Security::generateCSRFToken();
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-tachometer-alt"></i> Dashboard</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- User welcome -->
    <div class="card mb-4">
        <div class="card-body bg-light">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="mb-1">Welcome, <?php echo Security::escapeOutput($user['name']); ?>!</h4>
                    <p class="text-muted mb-0">Email: <?php echo Security::escapeOutput($user['email']); ?></p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="booking.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> New Booking
                    </a>
                    <a href="flights.php" class="btn btn-outline-primary">
                        <i class="fas fa-plane"></i> Browse Flights
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick statistics -->
    <div class="row mb-5">
        <div class="col-md-3 mb-3">
            <div class="card stat-card primary text-center text-white">
                <div class="card-body">
                    <i class="fas fa-ticket-alt fa-2x mb-2"></i>
                    <h3 class="number"><?php echo $stats['total_bookings']; ?></h3>
                    <p class="mb-0">Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card success text-center text-white">
                <div class="card-body">
                    <i class="fas fa-check-circle fa-2x mb-2"></i>
                    <h3 class="number"><?php echo $stats['confirmed_bookings']; ?></h3>
                    <p class="mb-0">Confirmed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card warning text-center text-white">
                <div class="card-body">
                    <i class="fas fa-times-circle fa-2x mb-2"></i>
                    <h3 class="number"><?php echo $stats['cancelled_bookings']; ?></h3>
                    <p class="mb-0">Cancelled</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stat-card info text-center text-white">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                    <h3 class="number"><?php echo $stats['total_spent']; ?> SAR</h3>
                    <p class="mb-0">Total Spent</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent bookings -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Recent Bookings</h5>
                </div>
                <div class="card-body">
                    <?php if(empty($recent_bookings)): ?>
                        <div class="text-center py-4">
                            <i class="fas fa-ticket-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No previous bookings</h5>
                            <p class="text-muted">You can book your first ticket now</p>
                            <a href="flights.php" class="btn btn-primary">Browse Flights</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Booking No.</th>
                                        <th>Flight</th>
                                        <th>Passenger</th>
                                        <th>Travel Date</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($recent_bookings as $booking): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo Security::escapeOutput($booking['booking_reference']); ?></strong>
                                            <br>
                                            <small class="text-muted"><?php echo Functions::formatDate($booking['booking_date'], 'Y-m-d'); ?></small>
                                        </td>
                                        <td>
                                            <?php echo Security::escapeOutput($booking['flight_number']); ?>
                                            <br>
                                            <small class="text-muted">
                                                <?php echo Security::escapeOutput($booking['origin']); ?> → 
                                                <?php echo Security::escapeOutput($booking['destination']); ?>
                                            </small>
                                        </td>
                                        <td><?php echo Security::escapeOutput($booking['passenger_name']); ?></td>
                                        <td><?php echo Functions::formatDate($booking['flight_departure'], 'Y-m-d H:i'); ?></td>
                                        <td>
                                            <?php 
                                            $status_class = [
                                                'confirmed' => 'success',
                                                'pending' => 'warning',
                                                'cancelled' => 'danger'
                                            ];
                                            $status_text = [
                                                'confirmed' => 'Confirmed',
                                                'pending' => 'Pending',
                                                'cancelled' => 'Cancelled'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_class[$booking['status']]; ?>">
                                                <?php echo $status_text[$booking['status']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong class="text-success"><?php echo Security::escapeOutput($booking['total_price']); ?> SAR</strong>
                                        </td>
                                        <td>
                                            <?php if($booking['status'] == 'confirmed'): ?>
                                                <form method="POST" action="dashboard.php" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <button type="submit" name="cancel_booking" class="btn btn-sm btn-outline-danger" 
                                                            onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                        <i class="fas fa-times"></i> Cancel
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            <a href="booking_details.php?id=<?php echo $booking['id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="text-center mt-3">
                            <a href="my_bookings.php" class="btn btn-outline-primary">View All Bookings</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick actions -->
    <div class="row mt-4">
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-plane fa-2x text-primary mb-3"></i>
                    <h5 class="card-title">New Booking</h5>
                    <p class="card-text">Book a new flight ticket</p>
                    <a href="booking.php" class="btn btn-primary">Start Booking</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-search fa-2x text-success mb-3"></i>
                    <h5 class="card-title">Search Flights</h5>
                    <p class="card-text">Search for available flights</p>
                    <a href="search.php" class="btn btn-success">Search Now</a>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card text-center h-100">
                <div class="card-body">
                    <i class="fas fa-list fa-2x text-info mb-3"></i>
                    <h5 class="card-title">All Bookings</h5>
                    <p class="card-text">View complete booking history</p>
                    <a href="my_bookings.php" class="btn btn-info">View All</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>