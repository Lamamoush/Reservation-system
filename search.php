<?php
require_once 'includes/header.php';

$db = new Database();
$conn = $db->getConnection();

$results = [];
$search_performed = false;

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['search'])) {
    $search_performed = true;
    
    $origin = Security::cleanInput($_GET['origin']);
    $destination = Security::cleanInput($_GET['destination']);
    $departure_date = Security::cleanInput($_GET['departure_date']);
    
    $sql = "SELECT * FROM flights 
            WHERE origin LIKE :origin 
            AND destination LIKE :destination 
            AND DATE(departure_date) = :departure_date 
            AND available_seats > 0 
            AND departure_date > NOW() 
            ORDER BY departure_date ASC";
    
    $params = [
        ':origin' => "%$origin%",
        ':destination' => "%$destination%",
        ':departure_date' => $departure_date
    ];
    
    try {
        $stmt = Security::safeQuery($conn, $sql, $params);
        $results = $stmt->fetchAll();
    } catch (Exception $e) {
        $error = "Search error: " . $e->getMessage();
    }
}
?>

<div class="container mt-4">
    <h2 class="text-center mb-4"><i class="fas fa-search"></i> Search for Flights</h2>
    
    <!-- Search form -->
    <div class="search-form mb-5">
        <form method="GET" action="search.php">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label for="origin" class="form-label">From</label>
                    <input type="text" class="form-control" id="origin" name="origin" 
                           value="<?php echo isset($_GET['origin']) ? Security::escapeOutput($_GET['origin']) : ''; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="destination" class="form-label">To</label>
                    <input type="text" class="form-control" id="destination" name="destination"
                           value="<?php echo isset($_GET['destination']) ? Security::escapeOutput($_GET['destination']) : ''; ?>" required>
                </div>
                <div class="col-md-3 mb-3">
                    <label for="departure_date" class="form-label">Travel Date</label>
                    <input type="date" class="form-control" id="departure_date" name="departure_date"
                           value="<?php echo isset($_GET['departure_date']) ? Security::escapeOutput($_GET['departure_date']) : ''; ?>" required>
                </div>
                <div class="col-md-3 mb-3 d-flex align-items-end">
                    <button type="submit" name="search" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Search
                    </button>
                </div>
            </div>
        </form>
    </div>
    
    <!-- Search results -->
    <?php if($search_performed): ?>
        <?php if(isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif(empty($results)): ?>
            <div class="alert alert-warning text-center">
                <i class="fas fa-exclamation-triangle"></i> No flights found matching search criteria
            </div>
        <?php else: ?>
            <h3 class="mb-3">Search Results:</h3>
            <div class="row">
                <?php foreach($results as $flight): ?>
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
                                    <strong>Date:</strong> <?php echo Functions::formatDate($flight['departure_date'], 'Y-m-d H:i'); ?>
                                </div>
                                <div class="col-6">
                                    <strong>Price:</strong> <span class="text-success"><?php echo Security::escapeOutput($flight['price']); ?> SAR</span>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-6">
                                    <strong>Seats:</strong> <?php echo Security::escapeOutput($flight['available_seats']); ?>
                                </div>
                                <div class="col-6">
                                    <strong>Arrival:</strong> <?php echo Functions::formatDate($flight['arrival_date'], 'Y-m-d H:i'); ?>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-12">
                                    <?php if(Auth::isLoggedIn()): ?>
                                        <a href="booking.php?flight=<?php echo $flight['flight_number']; ?>" 
                                           class="btn btn-success w-100">
                                           <i class="fas fa-ticket-alt"></i> Book Now
                                        </a>
                                    <?php else: ?>
                                        <a href="login.php" class="btn btn-warning w-100">
                                            <i class="fas fa-sign-in-alt"></i> Login to Book
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>