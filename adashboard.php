<?php
require_once 'includes/admin_header.php';

// التحقق من تسجيل الدخول
if (!Auth::isLoggedIn()) {
    Functions::redirect('login.php', 'يجب تسجيل الدخول أولاً');
}

// التحقق من أن المستخدم هو مدير
$user = Auth::getUserInfo();
if ($user['role'] !== 'admin') {
    Functions::redirect('dashboard.php', 'غير مصرح بالوصول. هذه الصفحة للمديرين فقط.');
}

$db = new Database();
$conn = $db->getConnection();

// جلب إحصائيات النظام الكاملة
$stats = [];
try {
    // إجمالي المستخدمين
    $sql = "SELECT COUNT(*) as total_users FROM users";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['total_users'] = $stmt->fetch()['total_users'];
    
    // المستخدمين النشطين
    $sql = "SELECT COUNT(*) as active_users FROM users WHERE status = 'active'";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['active_users'] = $stmt->fetch()['active_users'];
    
    // إجمالي الحجوزات
    $sql = "SELECT COUNT(*) as total_bookings FROM bookings";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['total_bookings'] = $stmt->fetch()['total_bookings'];
    
    // الحجوزات المؤكدة
    $sql = "SELECT COUNT(*) as confirmed_bookings FROM bookings WHERE status = 'confirmed'";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['confirmed_bookings'] = $stmt->fetch()['confirmed_bookings'];
    
    // إجمالي الإيرادات
    $sql = "SELECT SUM(total_price) as total_revenue FROM bookings WHERE status = 'confirmed'";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['total_revenue'] = $stmt->fetch()['total_revenue'] ?: 0;
    
    // إجمالي الرحلات
    $sql = "SELECT COUNT(*) as total_flights FROM flights";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['total_flights'] = $stmt->fetch()['total_flights'];
    
    // الرحلات النشطة
    $sql = "SELECT COUNT(*) as active_flights FROM flights WHERE departure_date > NOW()";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['active_flights'] = $stmt->fetch()['active_flights'];
    
    // الحجوزات اليومية
    $sql = "SELECT COUNT(*) as today_bookings FROM bookings WHERE DATE(booking_date) = CURDATE()";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['today_bookings'] = $stmt->fetch()['today_bookings'];
    
    // الإيرادات اليومية
    $sql = "SELECT SUM(total_price) as today_revenue FROM bookings WHERE DATE(booking_date) = CURDATE() AND status = 'confirmed'";
    $stmt = Security::safeQuery($conn, $sql);
    $stats['today_revenue'] = $stmt->fetch()['today_revenue'] ?: 0;

} catch (Exception $e) {
    $error = "خطأ في تحميل الإحصائيات: " . $e->getMessage();
}

// جلب آخر الحجوزات (جميع المستخدمين)
$recent_bookings = [];
try {
    $sql = "SELECT b.*, f.origin, f.destination, f.departure_date as flight_departure, 
                   u.name as user_name, u.email as user_email
            FROM bookings b 
            JOIN flights f ON b.flight_number = f.flight_number 
            JOIN users u ON b.user_id = u.id
            ORDER BY b.booking_date DESC 
            LIMIT 10";
    $stmt = Security::safeQuery($conn, $sql);
    $recent_bookings = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "خطأ في تحميل الحجوزات: " . $e->getMessage();
}

// جلب آخر المستخدمين المسجلين
$recent_users = [];
try {
    $sql = "SELECT * FROM users ORDER BY created_at DESC LIMIT 8";
    $stmt = Security::safeQuery($conn, $sql);
    $recent_users = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "خطأ في تحميل المستخدمين: " . $e->getMessage();
}

// جلب الرحلات القادمة
$upcoming_flights = [];
try {
    $sql = "SELECT * FROM flights WHERE departure_date > NOW() ORDER BY departure_date ASC LIMIT 6";
    $stmt = Security::safeQuery($conn, $sql);
    $upcoming_flights = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "خطأ في تحميل الرحلات: " . $e->getMessage();
}

$csrf_token = Security::generateCSRFToken();
?>

<div class="container-fluid mt-4">
    <h2 class="mb-4"><i class="fas fa-user-shield"></i> لوحة تحكم المدير</h2>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <!-- بطاقة ترحيب المدير -->
    <div class="card mb-4">
        <div class="card-body bg-gradient-primary text-white">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h4 class="mb-1"><i class="fas fa-crown"></i> مرحباً، <?php echo Security::escapeOutput($user['name']); ?>!</h4>
                    <p class="mb-0">أنت الآن في لوحة التحكم الإدارية مع صلاحيات كاملة</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="add_flight.php" class="btn btn-light">
                        <i class="fas fa-plus"></i> إضافة رحلة جديدة
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- صف الإحصائيات -->
    <div class="row mb-4">
        <!-- إجمالي المستخدمين -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                إجمالي المستخدمين</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                            <div class="mt-2">
                                <span class="badge bg-success"><?php echo $stats['active_users']; ?> نشطين</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إجمالي الحجوزات -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                إجمالي الحجوزات</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_bookings']; ?></div>
                            <div class="mt-2">
                                <span class="badge bg-success"><?php echo $stats['today_bookings']; ?> اليوم</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ticket-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إجمالي الإيرادات -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                إجمالي الإيرادات</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_revenue'], 2); ?> ريال</div>
                            <div class="mt-2">
                                <span class="badge bg-success"><?php echo number_format($stats['today_revenue'], 2); ?> اليوم</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- إجمالي الرحلات -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                إجمالي الرحلات</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_flights']; ?></div>
                            <div class="mt-2">
                                <span class="badge bg-success"><?php echo $stats['active_flights']; ?> نشطة</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-plane fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- صف المحتوى الرئيسي -->
    <div class="row">
        <!-- الحجوزات الحديثة -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history"></i> آخر الحجوزات</h6>
                    <a href="admin_bookings.php" class="btn btn-sm btn-primary">عرض الكل</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>رقم الحجز</th>
                                    <th>المستخدم</th>
                                    <th>الرحلة</th>
                                    <th>التاريخ</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($recent_bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong>#<?php echo Security::escapeOutput($booking['booking_reference']); ?></strong>
                                    </td>
                                    <td>
                                        <div><?php echo Security::escapeOutput($booking['user_name']); ?></div>
                                        <small class="text-muted"><?php echo Security::escapeOutput($booking['user_email']); ?></small>
                                    </td>
                                    <td>
                                        <div><?php echo Security::escapeOutput($booking['flight_number']); ?></div>
                                        <small class="text-muted">
                                            <?php echo Security::escapeOutput($booking['origin']); ?> → 
                                            <?php echo Security::escapeOutput($booking['destination']); ?>
                                        </small>
                                    </td>
                                    <td><?php echo Functions::formatDate($booking['flight_departure'], 'Y-m-d'); ?></td>
                                    <td>
                                        <?php 
                                        $status_class = [
                                            'confirmed' => 'success',
                                            'pending' => 'warning',
                                            'cancelled' => 'danger'
                                        ];
                                        $status_text = [
                                            'confirmed' => 'مؤكد',
                                            'pending' => 'قيد الانتظار',
                                            'cancelled' => 'ملغى'
                                        ];
                                        ?>
                                        <span class="badge bg-<?php echo $status_class[$booking['status']]; ?>">
                                            <?php echo $status_text[$booking['status']]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="booking_details.php?id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary" title="عرض التفاصيل">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_booking.php?id=<?php echo $booking['id']; ?>" 
                                           class="btn btn-sm btn-outline-warning" title="تعديل">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- الرحلات القادمة -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-success"><i class="fas fa-plane-departure"></i> الرحلات القادمة</h6>
                    <a href="manage_flights.php" class="btn btn-sm btn-success">إدارة الرحلات</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach($upcoming_flights as $flight): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border-left-info h-100">
                                <div class="card-body">
                                    <h6 class="card-title"><?php echo Security::escapeOutput($flight['flight_number']); ?></h6>
                                    <p class="card-text mb-1">
                                        <small><?php echo Security::escapeOutput($flight['origin']); ?> → 
                                        <?php echo Security::escapeOutput($flight['destination']); ?></small>
                                    </p>
                                    <p class="mb-1"><small>التاريخ: <?php echo Functions::formatDate($flight['departure_date'], 'Y-m-d H:i'); ?></small></p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="badge bg-<?php echo $flight['available_seats'] > 0 ? 'success' : 'danger'; ?>">
                                            <?php echo $flight['available_seats']; ?> مقاعد متاحة
                                        </span>
                                        <span class="text-success"><?php echo $flight['price']; ?> ريال</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- الشريط الجانبي -->
        <div class="col-xl-4 col-lg-5">
            <!-- المستخدمون الجدد -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-info"><i class="fas fa-user-plus"></i> المستخدمون الجدد</h6>
                    <a href="manage_users.php" class="btn btn-sm btn-info">إدارة المستخدمين</a>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach($recent_users as $user_item): ?>
                        <a href="user_details.php?id=<?php echo $user_item['id']; ?>" class="list-group-item list-group-item-action">
                            <div class="d-flex w-100 justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1"><?php echo Security::escapeOutput($user_item['name']); ?></h6>
                                    <small class="text-muted"><?php echo Security::escapeOutput($user_item['email']); ?></small>
                                </div>
                                <span class="badge bg-<?php echo $user_item['status'] == 'active' ? 'success' : 'danger'; ?>">
                                    <?php echo $user_item['status'] == 'active' ? 'نشط' : 'غير نشط'; ?>
                                </span>
                            </div>
                            <small>مسجل منذ: <?php echo Functions::formatDate($user_item['created_at'], 'Y-m-d'); ?></small>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- إجراءات سريعة -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning"><i class="fas fa-bolt"></i> إجراءات سريعة</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <a href="add_flight.php" class="btn btn-outline-primary w-100 mb-2">
                                <i class="fas fa-plus"></i> إضافة رحلة
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage_users.php" class="btn btn-outline-success w-100 mb-2">
                                <i class="fas fa-users"></i> إدارة المستخدمين
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="admin_bookings.php" class="btn btn-outline-info w-100 mb-2">
                                <i class="fas fa-ticket-alt"></i> جميع الحجوزات
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="manage_flights.php" class="btn btn-outline-warning w-100 mb-2">
                                <i class="fas fa-plane"></i> إدارة الرحلات
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="reports.php" class="btn btn-outline-danger w-100 mb-2">
                                <i class="fas fa-chart-bar"></i> التقارير
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="system_logs.php" class="btn btn-outline-secondary w-100 mb-2">
                                <i class="fas fa-clipboard-list"></i> السجلات
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>