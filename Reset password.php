<?php
// Start the session
session_start();

// Database connection parameters
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

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the form data
    $email = $_POST['email'];
    $password = $_POST['password'];
    $token = $_POST['token'];

    if (!validateToken($email, $token)) {
        $_SESSION['error'] = "Invalid or expired token.";
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    // Hash the new password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    
    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $stmt->bind_param("ss", $hashedPassword, $email);

    if ($stmt->execute()) {
        // Password updated successfully
        $_SESSION['success'] = "Your password has been reset successfully!";
        header("Location: login.php"); // Redirect to login page or wherever you want
    } else {
        // Error updating password
        $_SESSION['error'] = "Error updating password. Please try again.";
        header("Location: reset_password.php?token=" . urlencode($token));
    }

    $stmt->close();
    $conn->close();
}


function validateToken($email, $token) {
    
    return true; 
}
?>