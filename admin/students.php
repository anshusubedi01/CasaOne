<?php
$pageTitle = 'Students';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/includes/header.php';

$pdo = getDB();
$error = '';
$success = '';
$aId = getCurrentAdminId();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $s_name = trim($_POST['s_name'] ?? '');
        $s_email = trim($_POST['s_email'] ?? '');
        $s_phone = trim($_POST['s_phone'] ?? '');
        $room_id = (int)($_POST['room_id'] ?? 0);
        if ($s_name) {
            $pdo->prepare("INSERT INTO student (a_id, room_id, s_name, s_email, s_phone) VALUES (?, ?, ?, ?, ?)")->execute([$aId, $room_id ?: null, $s_name, $s_email ?: null, $s_phone ?: null]);
            $success = 'Student added.';
        } else {
            $error = 'Name is required.';
        }
    } elseif ($_POST['action'] === 'delete' && isset($_POST['s_id'])) {
        $pdo->prepare("DELETE FROM student WHERE s_id = ?")->execute([(int)$_POST['s_id']]);
        $success = 'Student deleted.';
    }
}

$students = $pdo->query("
    SELECT s.*, r.room_no, h.h_name
    FROM student s
    LEFT JOIN room r ON r.room_id = s.room_id
    LEFT JOIN hostel h ON h.h_id = r.h_id
    ORDER BY s.s_id
")->fetchAll();
$rooms = $pdo->query("SELECT room_id, room_no, h_id FROM room ORDER BY room_no")->fetchAll();
?>
<section class="section">
    <div class="container">
        <h1 class="section-title">Students</h1>
        <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

        <div class="form-card" style="max-width: 500px; margin-bottom: 2rem;">
            <h2>Add Student</h2>
            <form method="post">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>Name *</label>
                    <input type="text" name="s_name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="s_email">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="s_phone">
                </div>
                <div class="form-group">
                    <label>Room</label>
                    <select name="room_id">
                        <option value="">—</option>
                        <?php foreach ($rooms as $r): ?>
                        <option value="<?= (int)$r['room_id'] ?>"><?= htmlspecialchars($r['room_no']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Add</button>
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Room</th>
                        <th>Hostel</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $s): ?>
                    <tr>
                        <td><?= (int)$s['s_id'] ?></td>
                        <td><?= htmlspecialchars($s['s_name'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['s_email'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['s_phone'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['room_no'] ?? '—') ?></td>
                        <td><?= htmlspecialchars($s['h_name'] ?? '—') ?></td>
                        <td>
                            <form method="post" style="display:inline;" onsubmit="return confirm('Delete?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="s_id" value="<?= (int)$s['s_id'] ?>">
                                <button type="submit" class="btn btn-secondary" style="padding:0.3rem 0.6rem; font-size:0.85rem;">Delete</button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (empty($students)): ?>
        <p style="color: var(--color-text-muted);">No students yet.</p>
        <?php endif; ?>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
