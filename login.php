<?php
session_start();
$conn = new mysqli("localhost", "root", "", "agrimatch");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Use prepared statements to prevent SQL injection
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] == 'admin') {
                    header("Location: admin_dashboard.php");
                } elseif ($user['role'] == 'renter') {
                    header("Location: manage_tractors.php");
                } elseif ($user['role'] == 'rentee') {
                    header("Location: user_dashboard.php");
                }
                exit(); // Ensure no further code is executed after redirection
            } else {
                echo "<p class='error-message'>Invalid password!</p>";
            }
        } else {
            echo "<p class='error-message'>User not found!</p>";
        }
        $stmt->close();
    } else {
        echo "<p class='error-message'>Database error. Please try again later.</p>";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
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
        input[type="password"] {
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
        <h1>Login</h1>
        <form method="POST">
            <label>Username:</label>
            <input type="text" name="username" required><br>
            <label>Password:</label>
            <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>