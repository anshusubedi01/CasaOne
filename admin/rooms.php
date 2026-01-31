<?php
$pageTitle = 'Rooms';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $h_id = (int)($_POST['h_id'] ?? 0);
        $room_no = trim($_POST['room_no'] ?? '');
        $price = (float)($_POST['price'] ?? 0);
        $availability = trim($_POST['availability'] ?? 'available') ?: 'available';
        if ($h_id && $room_no !== '') {
            $pdo->prepare("INSERT INTO room (h_id, room_no, price, availability) VALUES (?, ?, ?, ?)")->execute([$h_id, $room_no, $price, $availability]);
            $r_id = $pdo->lastInsertId();
            $type = trim($_POST['type'] ?? '');
            $capacity = (int)($_POST['capacity'] ?? 0);
            if ($type !== '' || $capacity > 0) {
                $pdo->prepare("INSERT INTO roomtype (room_id, type, capacity) VALUES (?, ?, ?)")->execute([$r_id, $type ?: null, $capacity ?: null]);
            }
            $success = 'Room added.';
        } else {
            $error = 'Hostel and room number required.';
        }
    } elseif ($_POST['action'] === 'update_avail' && isset($_POST['room_id'], $_POST['availability'])) {
        $pdo->prepare("UPDATE room SET availability = ? WHERE room_id = ?")->execute([$_POST['availability'], (int)$_POST['room_id']]);
        $success = 'Availability updated.';
    } elseif ($_POST['action'] === 'delete' && isset($_POST['room_id'])) {
        try {
            $pdo->prepare("DELETE FROM roomtype WHERE room_id = ?")->execute([(int)$_POST['room_id']]);
            $pdo->prepare("DELETE FROM room WHERE room_id = ?")->execute([(int)$_POST['room_id']]);
            $success = 'Room deleted.';
        } catch (PDOException $e) {
            $error = 'Cannot delete: room has bookings or students.';
        }
    }
}

$rooms = $pdo->query("
    SELECT r.*, h.h_name,
           (SELECT rt.type FROM roomtype rt WHERE rt.room_id = r.room_id LIMIT 1) as type,
           (SELECT rt.capacity FROM roomtype rt WHERE rt.room_id = r.room_id LIMIT 1) as capacity
    FROM room r
    LEFT JOIN hostel h ON h.h_id = r.h_id
    ORDER BY h.h_name, r.room_no
")->fetchAll();
$hostels = $pdo->query("SELECT h_id, h_name FROM hostel ORDER BY h_name")->fetchAll();
?>
<section class="section">
    <div class="container">
        <h1 class="section-title">Rooms</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <?php if (!empty($hostels)): ?>
        <div class="form-card" style="max-width: 500px; margin-bottom: 2rem;">
            <h2>Add Room</h2>
            <form method="post">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Hostel *</label>
                    <select name="h_id" required>
                        <?php foreach ($hostels as $h): ?>
                        <option value="<?= (int)$h['h_id'] ?>"><?= htmlspecialchars($h['h_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Room No *</label>
                    <input type="text" name="room_no" required>
                </div>
                <div class="form-group">
                    <label>Price</label>
                    <input type="number" name="price" step="0.01" value="0">
                </div>
                <div class="form-group">
                    <label>Availability</label>
                    <select name="availability">
                        <option value="available">Available</option>
                        <option value="occupied">Occupied</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Type (roomtype)</label>
                    <input type="text" name="type" placeholder="e.g. Single">
                </div>
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" min="0">
                </div>
                <button type="submit" class="btn btn-primary">Add</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Hostel</th>
                        <th>Room No</th>
                        <th>Type</th>
                        <th>Capacity</th>
                        <th>Price</th>
                        <th>Availability</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rooms as $r): ?>
                    <tr>
                        <td><?= (int)$r['room_id'] ?></td>
                        <td><?= htmlspecialchars($r['h_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['room_no'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($r['type'] ?? '—') ?></td>
                        <td><?= (int)($r['capacity'] ?? 0) ?></td>
                        <td>₹<?= number_format($r['price'] ?? 0) ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="update_avail">
                                <input type="hidden" name="room_id" value="<?= (int)$r['room_id'] ?>">
                                <select name="availability" onchange="this.form.submit()">
                                    <option value="available" <?= ($r['availability'] ?? '') === 'available' ? 'selected' : '' ?>>Available</option>
                                    <option value="occupied" <?= ($r['availability'] ?? '') === 'occupied' ? 'selected' : '' ?>>Occupied</option>
                                </select>
                            </form>
                        </td>
                        <td>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete this room?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="room_id" value="<?= (int)$r['room_id'] ?>">
                                <button type="submit" class="btn btn-secondary" style="padding:0.3rem 0.6rem; font-size:0.85rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($rooms)): ?>
        <p style="color: var(--color-text-muted);">No rooms yet. Add a hostel first.</p>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
