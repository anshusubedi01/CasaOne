<?php
$pageTitle = 'Fees';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$feePaySuccess = '';
$feePayError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_cash' && isLoggedInAsUser()) {
    $amount = (float)($_POST['amount'] ?? 0);
    if ($amount > 0) {
        try {
            $userId = getCurrentUserId();
            $stmt = $pdo->prepare("INSERT INTO payment (u_id, a_id, amount, pay_date, pay_type) VALUES (?, NULL, ?, CURDATE(), 'cash payment')");
            $stmt->execute([$userId, $amount]);
            $feePaySuccess = 'Cash payment recorded. It will appear in admin panel.';
        } catch (PDOException $e) {
            $feePayError = 'Payment could not be recorded.';
        }
    } else {
        $feePayError = 'Please enter a valid amount.';
    }
}


$fees = [];

try {
    // Try to get fee data from rooms
    $stmt = $pdo->query("
        SELECT DISTINCT 
            h.h_name,
            rt.type,
            rt.capacity,
            r.price
        FROM room r
        LEFT JOIN roomtype rt ON rt.room_id = r.room_id
        LEFT JOIN hostel h ON h.h_id = r.h_id
        WHERE r.price IS NOT NULL
        ORDER BY rt.capacity, r.price
    ");
    $fees = $stmt->fetchAll();
} catch (Exception $e) {
    // If query fails, show empty fees
    $fees = [];
}

// Default fee structure if no database data
$defaultFees = [
    ['type' => 'Single Room', 'capacity' => 1, 'monthly' => 15000, 'annual' => 150000],
    ['type' => 'Double Sharing', 'capacity' => 2, 'monthly' => 12000, 'annual' => 120000],
    ['type' => 'Triple Sharing', 'capacity' => 3, 'monthly' => 11000, 'annual' => 110000],
    ['type' => 'Four Sharing', 'capacity' => 4, 'monthly' => 10000, 'annual' => 100000],
];
?>
<section class="section">
    <div class="container">
        <h1 class="section-title">Fee Structure</h1>
        <p style="text-align:center; color: var(--color-text-muted); margin-bottom:2rem;">Transparent pricing based on room type - Monthly & Annual plans available</p>
        
        <div style="max-width: 900px; margin: 0 auto;">
            <table class="fee-table">
                <thead>
                    <tr>
                        <th>Room Type</th>
                        <th>Capacity</th>
                        <th>Monthly (NPR)</th>
                        <th>Annual (NPR)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($fees)): ?>
                        <?php foreach ($fees as $f): 
                            $monthlyPrice = $f['price'] ?? 0;
                            $annualPrice = $monthlyPrice * 10;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($f['type'] ?? '—') ?></td>
                            <td><?= (int)($f['capacity'] ?? 0) ?> Seater</td>
                            <td>Rs. <?= number_format($monthlyPrice) ?></td>
                            <td>Rs. <?= number_format($annualPrice) ?> <small style="color: var(--color-success);">(Save 2 months)</small></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php foreach ($defaultFees as $f): ?>
                        <tr>
                            <td><?= htmlspecialchars($f['type']) ?></td>
                            <td><?= $f['capacity'] ?> Seater</td>
                            <td>Rs. <?= number_format($f['monthly']) ?></td>
                            <td>Rs. <?= number_format($f['annual']) ?> <small style="color: var(--color-success);">(Save 2 months)</small></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div style="max-width: 700px; margin: 2rem auto 0; text-align: center;">
            <div class="card">
                <h3>Payment Information</h3>
                <p>Fees include accommodation, meals, electricity, WiFi, and housekeeping services. Security deposit of one month's rent required at admission.</p>
            </div>
        </div>

        <?php if (isLoggedInAsUser()): ?>
        <div class="form-card" style="max-width: 400px; margin: 2rem auto 0;">
            <h3>Pay (Cash only)</h3>
            <?php if ($feePaySuccess): ?><div class="alert alert-success"><?= htmlspecialchars($feePaySuccess) ?></div><?php endif; ?>
            <?php if ($feePayError): ?><div class="alert alert-error"><?= htmlspecialchars($feePayError) ?></div><?php endif; ?>
            <form method="post">
                <input type="hidden" name="action" value="pay_cash">
                <div class="form-group">
                    <label>Amount (Rs.) *</label>
                    <select name="amount" required>
                        <option value="">Select amount</option>
                        <?php if (!empty($fees)): ?>
                            <?php foreach ($fees as $f): $mp = $f['price'] ?? 0; if ($mp > 0): ?>
                            <option value="<?= (int)$mp ?>"><?= htmlspecialchars($f['type'] ?? '') ?> — Rs. <?= number_format($mp) ?>/month</option>
                            <?php endif; endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($defaultFees as $f): ?>
                            <option value="<?= (int)$f['monthly'] ?>"><?= htmlspecialchars($f['type']) ?> — Rs. <?= number_format($f['monthly']) ?>/month</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Pay</button>
            </form>
        </div>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
