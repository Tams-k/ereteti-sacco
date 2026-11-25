
<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: deliveries.php');
    exit;
}

$member_no = trim($_POST['member_no'] ?? '');
$date = $_POST['delivery_date'] ?? '';
$session = $_POST['session'] ?? '';
$litres = $_POST['litres'] ?? '';

$errors = [];

// Validate member number
if (!preg_match('/^ER-\d{4}$/', $member_no)) {
    $errors[] = 'Enter a valid member number, e.g. ER-0001.';
}

// Find the farmer using a prepared statement
$stmt = mysqli_prepare($conn,
    "SELECT farmer_id FROM farmer WHERE member_no = ?"
);
mysqli_stmt_bind_param($stmt, 's', $member_no);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$farmer = mysqli_fetch_assoc($result);

if (!$farmer) {
    $errors[] = 'This member number was not found.';
}

// Validate date
$date_object = DateTime::createFromFormat('Y-m-d', $date);
if (!$date_object || $date_object->format('Y-m-d') !== $date) {
    $errors[] = 'Enter a valid delivery date.';
}

// Validate session
if (!in_array($session, ['MORNING', 'EVENING'], true)) {
    $errors[] = 'Choose Morning or Evening.';
}

// Validate litres
if (!is_numeric($litres) || $litres <= 0 || $litres > 60) {
    $errors[] = 'Litres must be greater than 0 and no more than 60.';
}

if (!empty($errors)) {
    echo '<h2>Delivery not recorded</h2>';
    foreach ($errors as $error) {
        echo '<p>' . htmlspecialchars($error) . '</p>';
    }
    echo '<p><a href="deliveries.php">Go back</a></p>';
    exit;
}

// Insert delivery using a prepared statement
$stmt = mysqli_prepare($conn,
    "INSERT INTO milk_delivery
    (farmer_id, delivery_date, session, litres)
    VALUES (?, ?, ?, ?)"
);

$farmer_id = $farmer['farmer_id'];
$litres = (float) $litres;

mysqli_stmt_bind_param(
    $stmt,
    'issd',
    $farmer_id,
    $date,
    $session,
    $litres
);

try {
    mysqli_stmt_execute($stmt);
    header('Location: deliveries.php?date=' . urlencode($date) . '&saved=1');
    exit;
} catch (mysqli_sql_exception $e) {
    echo '<h2>Delivery not recorded</h2>';
    echo '<p>This delivery may already exist.</p>';
    echo '<p><a href="deliveries.php?date=' . htmlspecialchars($date) . '">Go back</a></p>';
}
?>
