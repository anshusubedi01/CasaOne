<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$countHostels = $pdo->query("SELECT COUNT(*) FROM hostel")->fetchColumn();
$countRooms = $pdo->query("SELECT COUNT(*) FROM room")->fetchColumn();
$countUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$countStudents = $pdo->query("SELECT COUNT(*) FROM student")->fetchColumn();
$countBookings = 0;
try {
    $countBookings = $pdo->query("SELECT COUNT(*) FROM booking")->fetchColumn();
} catch (Throwable $e) { }
$countComplaints = $pdo->query("SELECT COUNT(*) FROM complaint")->fetchColumn();
$countPayments = $pdo->query("SELECT COUNT(*) FROM payment")->fetchColumn();
?>
<section class="section">
    <div class="container">
        <h1 class="section-title">Admin Dashboard</h1>
        <p style="color: var(--color-text-muted); margin-bottom:2rem;">Welcome, <?= htmlspecialchars(getCurrentAdminName()) ?>.</p>
        <div class="card-grid">
            <a href="hostels.php" class="card" style="text-decoration:none; color:inherit;">
                <h3>Hostels</h3>
                <p><?= (int)$countHostels ?> hostels</p>
            </a>
            <a href="rooms.php" class="card" style="text-decoration:none; color:inherit;">
                <h3>Rooms</h3>
                <p><?= (int)$countRooms ?> rooms</p>
            </a>
            <a href="users.php" class="card" style="text-decoration:none; color:inherit;">
                <h3>Users</h3>
                <p><?= (int)$countUsers ?> registered users</p>
            </a>
            <a href="students.php" class="card" style="text-decoration:none; color:inherit;">
                <h3>Students</h3>
                <p><?= (int)$countStudents ?> students</p>
            </a>
            <a href="bookings.php" class="card" style="text-decoration:none; color:inherit;">
                <h3>Bookings</h3>
                <p><?= (int)$countBookings ?> bookings</p>
            </a>
            <a href="complaints.php" class="card" style="text-decoration:none; color:inherit;">
                <h3>Complaints</h3>
                <p><?= (int)$countComplaints ?> complaints</p>
            </a>
            <a href="payments.php" class="card" style="text-decoration:none; color:inherit;">
                <h3>Payments</h3>
                <p><?= (int)$countPayments ?> payments</p>
            </a>
        </div>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
