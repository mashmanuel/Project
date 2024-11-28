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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Hash the password for secure storage
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Prepare and bind SQL statement
    $stmt = $conn->prepare("INSERT INTO `USERS` (username, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $user, $email, $hashedPassword);

    if ($stmt->execute()) {
        echo "New record created successfully";

        // Redirect to home page after successful registration
        header("Location: Home.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
?>
