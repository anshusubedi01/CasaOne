<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
requireLogin();

$query = isset($_GET['room']) ? '?room=' . (int)$_GET['room'] : '';
header('Location: my-bookings.php' . $query);
exit;
