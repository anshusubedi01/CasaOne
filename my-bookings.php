<?php
$pageTitle = 'My Bookings';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pdo = getDB();
$userId = getCurrentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['booking_id'])) {
    $bid = (int)$_POST['booking_id'];
    $action = $_POST['action'];
    $stmt = $pdo->prepare("SELECT b_id, status, amount FROM booking WHERE b_id = ? AND u_id = ?");
    $stmt->execute([$bid, $userId]);
    $b = $stmt->fetch();
    if ($b) {
        if ($action === 'confirm' && $b['status'] === 'pending') {
            $pdo->prepare("UPDATE booking SET status = 'confirmed' WHERE b_id = ?")->execute([$bid]);
            try {
                $pdo->prepare("INSERT INTO payment (u_id, a_id, amount, pay_date, pay_type) VALUES (?, NULL, ?, CURDATE(), 'cash payment')")->execute([$userId, $b['amount']]);
            } catch (PDOException $e) {
                $pdo->prepare("INSERT INTO payment (u_id, a_id, amount, pay_date) VALUES (?, NULL, ?, CURDATE())")->execute([$userId, $b['amount']]);
            }
        } elseif ($action === 'check_in' && $b['status'] === 'confirmed') {
            $pdo->prepare("UPDATE booking SET status = 'checked_in' WHERE b_id = ?")->execute([$bid]);
        } elseif ($action === 'check_out' && $b['status'] === 'checked_in') {
            $pdo->prepare("UPDATE booking SET check_out = CURDATE(), status = 'checked_out' WHERE b_id = ?")->execute([$bid]);
        }
    }
    header('Location: my-bookings.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT b.*, r.room_no, r.h_id, h.h_name
    FROM booking b
    JOIN room r ON r.room_id = b.room_id
    LEFT JOIN hostel h ON h.h_id = b.h_id
    WHERE b.u_id = ?
    ORDER BY b.book_date DESC
");
$stmt->execute([$userId]);
$bookings = $stmt->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
    <div class="container">
        <h1 class="section-title">My Bookings</h1>
        <p style="text-align:center; color: var(--color-text-muted); margin-bottom:2rem;">Check-in, check-out, and payment.</p>
        <?php if (empty($bookings)): ?>
        <p style="text-align:center; color: var(--color-text-muted);">No bookings yet. <a href="booking.php">Book a room</a>.</p>
        <?php else: ?>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Room</th>
                        <th>Hostel</th>
                        <th>Amount</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['room_no']) ?></td>
                        <td><?= htmlspecialchars($b['h_name'] ?? '—') ?></td>
                        <td>₹<?= number_format($b['amount'] ?? 0) ?></td>
                        <td><?= htmlspecialchars($b['check_in'] ?? '—') ?></td>
                        <td><?= !empty($b['check_out']) ? htmlspecialchars($b['check_out']) : '—' ?></td>
                        <td><span class="badge badge-<?= $b['status'] ?? 'pending' ?>"><?= ucfirst(str_replace('_', ' ', $b['status'] ?? 'pending')) ?></span></td>
                        <td>
                            <?php if (($b['status'] ?? '') === 'pending'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?= (int)$b['b_id'] ?>">
                                <input type="hidden" name="action" value="confirm">
                                <button type="submit" class="btn btn-primary" style="padding:0.4rem 0.8rem; font-size:0.9rem;">Pay & Confirm</button>
                            </form>
                            <?php elseif (($b['status'] ?? '') === 'confirmed'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?= (int)$b['b_id'] ?>">
                                <input type="hidden" name="action" value="check_in">
                                <button type="submit" class="btn btn-primary" style="padding:0.4rem 0.8rem; font-size:0.9rem;">Check In</button>
                            </form>
                            <?php elseif (($b['status'] ?? '') === 'checked_in'): ?>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="booking_id" value="<?= (int)$b['b_id'] ?>">
                                <input type="hidden" name="action" value="check_out">
                                <button type="submit" class="btn btn-secondary" style="padding:0.4rem 0.8rem; font-size:0.9rem;">Check Out</button>
                            </form>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <p style="text-align:center; margin-top:1.5rem;"><a href="booking.php" class="btn btn-primary">New Booking</a></p>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
