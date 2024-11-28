<?php
// Set headers for JSON response
header('Content-Type: application/json');

try {
    // Database connection (replace with your database credentials)
    $host = 'localhost';
    $db = 'mash';
    $user = 'root';
    $password = '';
    $dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Retrieve POST data
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if data is received properly
    if (!isset($data['orderItems']) || !isset($data['total'])) {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid input data',
        ]);
        exit;
    }

    $orderItems = $data['orderItems'];
    $total = $data['total'];

    // Insert order into the database
    $pdo->beginTransaction();

    // Insert into orders table
    $stmt = $pdo->prepare("INSERT INTO orders (total_amount, created_at) VALUES (:total, NOW())");
    $stmt->execute(['total' => $total]);
    $orderId = $pdo->lastInsertId(); // Get the generated Order ID

    // Insert order items into order_items table
    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, item_name, item_price) VALUES (:order_id, :item_name, :item_price)");
    foreach ($orderItems as $item) {
        $stmt->execute([
            'order_id' => $orderId,
            'item_name' => $item['name'],
            'item_price' => $item['price'],
        ]);
    }

    $pdo->commit();

    // Return success response
    echo json_encode([
        'success' => true,
        'orderId' => $orderId,
    ]);
} catch (Exception $e) {
    // Handle errors and rollback transaction
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
?>
