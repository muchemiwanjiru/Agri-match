<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Ensure the user is logged in and has the 'rentee' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'rentee') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "agrimatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Add to Wishlist, Bookings, and Booked Operator
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_wishlist'])) {
    $operator_id = $_POST['operator_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $user_id = $_SESSION['user_id'];

    if ($start_date > $end_date) {
        $_SESSION['error'] = "Error: End date must be after start date.";
    } else {
        $conn->begin_transaction();
        try {
            // Fetch operator details and ensure it's available
            $sql = "SELECT amount, status FROM operators WHERE id=? AND status='available'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $operator_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $amount = $row['amount'];

                // Insert into operator_wishlist
                $sql = "INSERT INTO operator_wishlist (user_id, operator_id, start_date, end_date, amount, purchasedby) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissii", $user_id, $operator_id, $start_date, $end_date, $amount, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding to wishlist: " . $stmt->error);
                }

                // Insert into operator_bookings
                $sql = "INSERT INTO operator_bookings (user_id, operator_id, start_date, end_date, amount, purchasedby, delivery_status) 
                        VALUES (?, ?, ?, ?, ?, ?, 'pending')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissii", $user_id, $operator_id, $start_date, $end_date, $amount, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding to bookings: " . $stmt->error);
                }

                // Insert into booked_operator
                $sql = "INSERT INTO booked_operator (user_id, operator_id, start_date, end_date, amount, purchasedby) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iissii", $user_id, $operator_id, $start_date, $end_date, $amount, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error adding to booked_operator: " . $stmt->error);
                }

                // Update operator status to 'booked'
                $sql = "UPDATE operators SET status='booked' WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $operator_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating operator status: " . $stmt->error);
                }

                $conn->commit();
                $_SESSION['message'] = "Operator added to wishlist, booked, and recorded successfully!";
            } else {
                throw new Exception("Error: Operator not available or not found.");
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = $e->getMessage();
        }
        $stmt->close();
    }
}

// Checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $user_id = $_SESSION['user_id'];
    $conn->begin_transaction();

    try {
        $sql = "SELECT * FROM operator_wishlist WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Since bookings and booked_operator are already created, just clear wishlist
            $sql = "DELETE FROM operator_wishlist WHERE user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $user_id);
            if (!$stmt->execute()) {
                throw new Exception("Error clearing wishlist: " . $stmt->error);
            }

            $conn->commit();
            $_SESSION['message'] = "Checkout completed! Wishlist cleared.";
        } else {
            $_SESSION['error'] = "Your wishlist is empty.";
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    $stmt->close();
}

// Fetch all operators
$sql = "SELECT * FROM operators";
$result = $conn->query($sql);
if (!$result) {
    $_SESSION['error'] = "Error fetching operators: " . $conn->error;
}

// Fetch wishlist items for the logged-in user
$user_id = $_SESSION['user_id'];
$sql_wishlist = "SELECT ow.*, o.age, o.sex, o.strength, o.skills 
                 FROM operator_wishlist ow 
                 JOIN operators o ON ow.operator_id = o.id 
                 WHERE ow.user_id=?";
$stmt = $conn->prepare($sql_wishlist);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_wishlist = $stmt->get_result();
if (!$result_wishlist) {
    $_SESSION['error'] = "Error fetching wishlist: " . $stmt->error;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Operator - Agri-Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .navbar { box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .container { max-width: 1200px; margin-top: 30px; margin-bottom: 30px; }
        .operator-container, .wishlist-container { display: flex; flex-wrap: wrap; gap: 20px; padding: 20px 0; }
        .operator-card, .wishlist-card { background-color: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); padding: 15px; width: 100%; max-width: 300px; text-align: center; transition: transform 0.2s; }
        .operator-card:hover, .wishlist-card:hover { transform: scale(1.05); }
        .operator-card h3, .wishlist-card h3 { color: #28a745; margin: 10px 0; font-size: 1.25rem; }
        .operator-card p, .wishlist-card p { margin: 5px 0; font-size: 0.9rem; }
        .form-label { font-weight: bold; color: #28a745; }
        .btn-primary { background-color: #28a745; border-color: #28a745; }
        .btn-primary:hover { background-color: #218838; border-color: #218838; }
        .btn-warning { background-color: #ffc107; border-color: #ffc107; color: #212529; }
        .btn-warning:hover { background-color: #e0a800; border-color: #e0a800; }
        h1, h2 { color: #28a745; margin-bottom: 20px; }
        .status-available { color: #28a745; font-weight: bold; }
        .status-booked { color: #dc3545; font-weight: bold; }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <img src="images/logo.png" alt="Agri-Match Logo" width="150">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_tractor.php">Book Tractor</a></li>
                    <li class="nav-item"><a class="nav-link active" href="book_operator.php">Book Operator</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_package.php">Book Package Delivery</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_my_bookings.php">My Bookings</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="container mt-3">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="container">
        <h1>Book Operator</h1>

        <h2>All Operators</h2>
        <div class="operator-container">
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status_class = $row['status'] === 'available' ? 'status-available' : 'status-booked';
                    echo "<div class='operator-card'>
                            <h3>Operator #" . htmlspecialchars($row['id']) . "</h3>
                            <p><strong>Age:</strong> " . htmlspecialchars($row['age']) . "</p>
                            <p><strong>Sex:</strong> " . htmlspecialchars($row['sex']) . "</p>
                            <p><strong>Strength:</strong> " . htmlspecialchars($row['strength']) . "</p>
                            <p><strong>Skills:</strong> " . htmlspecialchars($row['skills']) . "</p>
                            <p><strong>Amount:</strong> " . htmlspecialchars($row['amount']) . "</p>
                            <p><strong>Status:</strong> <span class='$status_class'>" . htmlspecialchars($row['status']) . "</span></p>";
                    
                    if ($row['status'] === 'available') {
                        echo "<form method='POST' class='mb-3'>
                                <input type='hidden' name='operator_id' value='" . htmlspecialchars($row['id']) . "'>
                                <div class='mb-3'>
                                    <label for='start_date_" . $row['id'] . "' class='form-label'>Start Date:</label>
                                    <input type='date' class='form-control' name='start_date' id='start_date_" . $row['id'] . "' required>
                                </div>
                                <div class='mb-3'>
                                    <label for='end_date_" . $row['id'] . "' class='form-label'>End Date:</label>
                                    <input type='date' class='form-control' name='end_date' id='end_date_" . $row['id'] . "' required>
                                </div>
                                <button type='submit' name='add_to_wishlist' class='btn btn-primary' onclick='return validateDates(" . $row['id'] . ")'>Add to Wishlist</button>
                              </form>";
                    } else {
                        echo "<p class='text-muted'>Not available for booking</p>";
                    }
                    echo "</div>";
                }
            } else {
                echo "<p class='text-muted text-center w-100'>No operators found.</p>";
            }
            ?>
        </div>

        <h2>Your Wishlist</h2>
        <div class="wishlist-container">
            <?php
            if ($result_wishlist && $result_wishlist->num_rows > 0) {
                while ($row = $result_wishlist->fetch_assoc()) {
                    echo "<div class='wishlist-card'>
                            <h3>Operator #" . htmlspecialchars($row['operator_id']) . "</h3>
                            <p><strong>Age:</strong> " . htmlspecialchars($row['age']) . "</p>
                            <p><strong>Sex:</strong> " . htmlspecialchars($row['sex']) . "</p>
                            <p><strong>Strength:</strong> " . htmlspecialchars($row['strength']) . "</p>
                            <p><strong>Skills:</strong> " . htmlspecialchars($row['skills']) . "</p>
                            <p><strong>Start Date:</strong> " . htmlspecialchars($row['start_date']) . "</p>
                            <p><strong>End Date:</strong> " . htmlspecialchars($row['end_date']) . "</p>
                            <p><strong>Amount:</strong> " . htmlspecialchars($row['amount']) . "</p>
                            <p><strong>Purchased By:</strong> " . htmlspecialchars($row['purchasedby']) . "</p>
                            <a href='remove_from_wishlist.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-danger btn-sm'>Remove</a>
                          </div>";
                }
            } else {
                echo "<p class='text-muted text-center w-100'>Your wishlist is empty.</p>";
            }
            ?>
        </div>

        <?php if ($result_wishlist && $result_wishlist->num_rows > 0): ?>
            <form method="POST" class="text-center">
                <button type="submit" name="checkout" class="btn btn-primary mt-3">Checkout</button>
            </form>
        <?php endif; ?>
    </div>

    <script>
        function validateDates(operatorId) {
            const startDate = document.getElementById('start_date_' + operatorId).value;
            const endDate = document.getElementById('end_date_' + operatorId).value;
            if (startDate > endDate) {
                alert("Error: End date must be after start date.");
                return false;
            }
            return true;
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>