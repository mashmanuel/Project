<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "mash";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process the form data
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $orderName = $_POST['order_name'];
    $orderItem = $_POST['order_item'];
    $orderQuantity = $_POST['order_quantity'];

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO `orders` (customer_name, item, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("ssi", $orderName, $orderItem, $orderQuantity);

    if ($stmt->execute()) {
        echo "Order placed successfully!";
        // Optionally redirect to a confirmation page
        header("Location: order_success.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
