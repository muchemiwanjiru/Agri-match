<?php
session_start();

// Database connection
$conn = new mysqli("localhost", "root", "", "agrimatch");

// Check for database connection errors
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']); // Trim whitespace
    $password = trim($_POST['password']); // Trim whitespace
    $role = $_POST['role']; // Get the selected role from the form

    // Validate input
    if (empty($username) || empty($password) || empty($role)) {
        echo "<p style='color: red;'>Please fill in all fields.</p>";
    } else {
        // Check if the username already exists
        $sql = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                echo "<p style='color: red;'>Username already exists. Please choose a different username.</p>";
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert the new user into the database
                $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                if ($stmt) {
                    $stmt->bind_param("sss", $username, $hashed_password, $role);
                    if ($stmt->execute()) {
                        echo "<p style='color: green;'>Registration successful! <a href='login.php'>Login here</a>.</p>";
                    } else {
                        echo "<p style='color: red;'>Error: " . $stmt->error . "</p>";
                    }
                } else {
                    echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
                }
            }
            // Close the statement only once
            $stmt->close();
        } else {
            echo "<p style='color: red;'>Error: " . $conn->error . "</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        /* General Styles */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f0f8ff; /* Light blue background */
            color: #333; /* Dark text for contrast */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Container for the login form */
        .login-container {
            background-color: white; /* White background for the form */
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }

        /* Heading */
        h1 {
            color: #007bff; /* Blue heading */
            margin-bottom: 20px;
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            margin-bottom: 8px;
            color: #007bff; /* Blue labels */
            text-align: left;
        }

        input[type="text"],
        input[type="password"],
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #007bff; /* Blue border */
            border-radius: 5px;
            font-size: 16px;
            color: #333;
        }

        button[type="submit"] {
            background-color: #007bff; /* Blue button background */
            color: white; /* White button text */
            padding: 10px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0056b3; /* Darker blue on hover */
        }

        /* Link Styles */
        p {
            margin-top: 20px;
            font-size: 14px;
        }

        a {
            color: #007bff; /* Blue links */
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Error Messages */
        .error-message {
            color: red;
            margin-bottom: 20px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Register</h1>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" required><br>
            <label>Password:</label>
            <input type="password" name="password" required><br>
            <label>Role:</label>
            <select name="role" required>
                <option value="renter">Renter</option>
                <option value="rentee">Rentee</option>
            </select><br>
            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>