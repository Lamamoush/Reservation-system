<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="adashboard.php">
            <i class="fas fa-plane"></i> نظام إدارة الحجوزات
        </a>
        
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="adashboard.php"><i class="fas fa-tachometer-alt"></i> لوحة التحكم</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-plane"></i> الرحلات
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="manage_flights.php">إدارة الرحلات</a></li>
                        <li><a class="dropdown-item" href="add_flight.php">إضافة رحلة جديدة</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="flight_schedule.php">جدول الرحلات</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-users"></i> المستخدمين
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="manage_users.php">إدارة المستخدمين</a></li>
                        <li><a class="dropdown-item" href="add_user.php">إضافة مستخدم جديد</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="admin_bookings.php"><i class="fas fa-ticket-alt"></i> جميع الحجوزات</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> التقارير</a>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-shield"></i> <?php echo $_SESSION['user_name']; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user"></i> الملف الشخصي</a></li>
                        <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-user"></i> لوحة التحكم العادية</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt"></i> تسجيل الخروج</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>