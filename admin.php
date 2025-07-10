<?php
session_start();



$conn = new mysqli("localhost", "root", "", "agrimatch");

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $role = 'admin'; // Set role to admin

    // Validate input
    if (empty($username) || empty($password)) {
        echo "<p style='color: red;'>Please fill in all fields.</p>";
    } else {
        // Check if the username already exists
        $sql = "SELECT * FROM users WHERE username='$username'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            echo "<p style='color: red;'>Username already exists. Please choose a different username.</p>";
        } else {
            // Hash the password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert the new admin user into the database
            $sql = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
            if ($conn->query($sql)) {
                echo "<p style='color: green;'>Admin registration successful!</p>";
            } else {
                echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register Admin</title>
    <link rel="stylesheet" type="text/css" href="style.css">
</head>
<body>
    <h1>Register Admin</h1>
    <form method="POST">
        <label>Username:</label>
        <input type="text" name="username" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Register Admin</button>
    </form>
    <p><a href="admin_dashboard.php">Back to Dashboard</a></p>
</body>
</html>