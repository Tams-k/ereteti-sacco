
<?php
require_once 'includes/db.php';

$id = $_POST['id'] ?? $_GET['id'] ?? '';
$date = $_POST['date'] ?? '';

if (!ctype_digit((string)$id) || $id === '') {
    header('Location: deliveries.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $litres = $_POST['litres'] ?? '';
    $accepted = isset($_POST['accepted']) ? 1 : 0;
    $date = $_POST['date'] ?? '';

    if (!is_numeric($litres) || $litres <= 0 || $litres > 60) {
        exit('Litres must be greater than 0 and no more than 60.');
    }

    $stmt = mysqli_prepare($conn,
        "UPDATE milk_delivery SET litres = ?, accepted = ? WHERE delivery_id = ?"
    );

    mysqli_stmt_bind_param($stmt, 'dii', $litres, $accepted, $id);
    mysqli_stmt_execute($stmt);

    header('Location: deliveries.php?date=' . urlencode($date) . '&updated=1');
    exit;
}

// Find the delivery to edit
$stmt = mysqli_prepare($conn,
    "SELECT delivery_id, delivery_date, litres, accepted
     FROM milk_delivery WHERE delivery_id = ?"
);

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$delivery = mysqli_fetch_assoc($result);

if (!$delivery) {
    exit('Delivery not found.');
}

$date = $delivery['delivery_date'];
$page_title = 'Edit delivery';
include 'includes/header.php';
?>

<main>
    <h1>Edit delivery</h1>

    <form action="edit-delivery.php" method="post">

        <input type="hidden" name="id"
               value="<?php echo $delivery['delivery_id']; ?>">

        <input type="hidden" name="date"
               value="<?php echo htmlspecialchars($date); ?>">

        <label for="litres">Litres</label>
        <input type="number" id="litres" name="litres"
               step="0.01" min="0.01" max="60"
               value="<?php echo htmlspecialchars($delivery['litres']); ?>"
               required>

        <label>
            <input type="checkbox" name="accepted" value="1"
                <?php echo $delivery['accepted'] ? 'checked' : ''; ?>>
            Accepted
        </label>

        <button type="submit">Save changes</button>
    </form>

    <p><a href="deliveries.php?date=<?php echo htmlspecialchars($date); ?>">Cancel</a></p>
</main>

<?php include 'includes/footer.php'; ?>
