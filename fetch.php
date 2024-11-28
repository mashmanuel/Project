<?php
// Set content type to JSON
header('Content-Type: application/json');

try {
    // Retrieve JSON input from the client
    $data = json_decode(file_get_contents('php://input'), true);

    // Check if the required data is present
    if (!isset($data['orderItems']) || !isset($data['total'])) {
        throw new Exception("Invalid input data.");
    }

    // Extract order items and total amount
    $orderItems = $data['orderItems'];
    $total = $data['total'];

    // Perform basic validation
    if (empty($orderItems) || $total <= 0) {
        throw new Exception("Order is empty or total is invalid.");
    }

    // Database connection (replace with your credentials)
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "restaurant_db"; // Your database name

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Insert the order into the orders table
    $stmt = $conn->prepare("INSERT INTO orders (total_amount, created_at) VALUES (?, NOW())");
    $stmt->bind_param("d", $total);

    if (!$stmt->execute()) {
        throw new Exception("Failed to save order: " . $stmt->error);
    }

    // Get the last inserted order ID
    $orderId = $stmt->insert_id;

    // Insert each item into the order_items table
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, item_name, item_price) VALUES (?, ?, ?)");

    foreach ($orderItems as $item) {
        $stmt->bind_param("isd", $orderId, $item['name'], $item['price']);
        if (!$stmt->execute()) {
            throw new Exception("Failed to save order item: " . $stmt->error);
        }
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();

    // Respond with success and the order ID
    echo json_encode([
        'success' => true,
        'orderId' => $orderId,
    ]);

} catch (Exception $e) {
    // Respond with an error message
    echo json_encode([
        'error' => $e->getMessage(),
    ]);
}
