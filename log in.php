<?php
session_start();

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "mash"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize error message variable
$error = "";

// Handle the login form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Prepare and bind
    $stmt = $conn->prepare("SELECT username, password FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    
    // Execute the statement
    $stmt->execute();
    $stmt->store_result();

    // Check if user exists
    if ($stmt->num_rows > 0) {
        $stmt->bind_result($username, $hashed_password);
        $stmt->fetch();

        // Verify the password
        if (password_verify($password, $hashed_password)) {
            // Password is correct, set session variables
            $_SESSION['username'] = $username;
            header("Location: Home.html"); // Redirect to a dashboard or home page
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No user found with that email.";
    }

    $stmt->close();
}

$conn->close();
?>