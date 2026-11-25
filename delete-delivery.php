
<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: deliveries.php');
    exit;
}

$id = $_POST['delivery_id'] ?? '';
$date = $_POST['date'] ?? '2026-10-12';

if (!ctype_digit((string)$id) || $id === '') {
    header('Location: deliveries.php');
    exit;
}

// Delete using a prepared statement
$stmt = mysqli_prepare($conn,
    "DELETE FROM milk_delivery WHERE delivery_id = ?"
);

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

// Return to the deliveries page
header('Location: deliveries.php?date=' . urlencode($date) . '&deleted=1');
exit;
?>
