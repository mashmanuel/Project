<?php
header('Content-Type: application/json');
$servername = "localhost";
$username = "root"; 
$password = ""; 
$dbname = "mash";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Handle incoming requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'getMenu':
        getMenu($conn);
        break;
    case 'placeOrder':
        placeOrder($conn);
        break;
    case 'getOrderDetails':
        getOrderDetails($conn);
        break;
    default:
        echo json_encode(["error" => "Invalid action"]);
}

// Fetch menu items
function getMenu($conn) {
    $result = $conn->query("SELECT * FROM menu_items");
    $menu = [];
    while ($row = $result->fetch_assoc()) {
        $menu[] = $row;
    }
    echo json_encode($menu);
}

// Place an order
function placeOrder($conn) {
    $data = json_decode(file_get_contents('php://input'), true);
    $orderItems = $data['items'];
    $paymentMethod = $data['paymentMethod'];
    $total = $data['total'];

    $conn->query("INSERT INTO orders (total, payment_status, payment_method) VALUES ($total, 'pending', '$paymentMethod')");
    $orderId = $conn->insert_id;

    foreach ($orderItems as $item) {
        $menuItemId = $item['menu_item_id'];
        $quantity = $item['quantity'];
        $conn->query("INSERT INTO order_details (order_id, menu_item_id, quantity) VALUES ($orderId, $menuItemId, $quantity)");
    }

    echo json_encode(["success" => true, "order_id" => $orderId]);
}

// Get order details
function getOrderDetails($conn) {
    $orderId = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
    $order = $conn->query("SELECT * FROM orders WHERE id = $orderId")->fetch_assoc();
    $items = $conn->query("SELECT od.*, mi.name, mi.price FROM order_details od 
                           JOIN menu_items mi ON od.menu_item_id = mi.id 
                           WHERE od.order_id = $orderId");
    $order['items'] = [];
    while ($row = $items->fetch_assoc()) {
        $order['items'][] = $row;
    }
    echo json_encode($order);
}
?>
