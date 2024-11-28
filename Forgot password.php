<?php
// Database credentials
$servername = "localhost";
$username = "your_db_username";
$password = "your_db_password";
$dbname = "mash";

// Create a connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check if the email exists
    $stmt = $conn->prepare("SELECT id FROM `sign up` WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // Email exists, generate a reset token
        $token = bin2hex(random_bytes(32));
        $expires = date("U") + 1800; // Token expiration (30 minutes from now)

        // Insert token into the database
        $stmt = $conn->prepare("INSERT INTO password_resets (email, token, expires) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token=?, expires=?");
        $stmt->bind_param("ssiss", $email, $token, $expires, $token, $expires);
        $stmt->execute();

        // Send the password reset link via email
        $reset_link = "http://yourwebsite.com/reset_password.php?token=" . $token;
        $subject = "Password Reset Request";
        $message = "Click the following link to reset your password: $reset_link";
        $headers = "From: no-reply@yourwebsite.com";

        if (mail($email, $subject, $message, $headers)) {
            echo "A password reset link has been sent to your email.";
        } else {
            echo "Failed to send the email.";
        }
    } else {
        echo "No account found with that email address.";
    }

    $stmt->close();
}

$conn->close();
?>
