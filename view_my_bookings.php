<?php
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

// Update Delivery Status (Tractor)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && isset($_POST['type']) && $_POST['type'] == 'tractor') {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];

    $sql = "UPDATE bookings SET delivery_status=? WHERE id=? AND purchasedby=? AND delivery_status='pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_status, $booking_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Tractor delivery status updated successfully!";
        } else {
            $_SESSION['error'] = "Status update failed: Already completed or unauthorized.";
        }
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: view_my_bookings.php");
    exit();
}

// Update Delivery Status (Operator)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && isset($_POST['type']) && $_POST['type'] == 'operator') {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];

    $sql = "UPDATE operator_bookings SET delivery_status=? WHERE id=? AND purchasedby=? AND delivery_status='pending'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_status, $booking_id, $_SESSION['user_id']);
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $_SESSION['message'] = "Operator delivery status updated successfully!";
        } else {
            $_SESSION['error'] = "Status update failed: Already completed or unauthorized.";
        }
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: view_my_bookings.php");
    exit();
}

// Cancel Booking (Tractor)
if (isset($_GET['cancel_id']) && isset($_GET['type']) && $_GET['type'] == 'tractor') {
    $booking_id = $_GET['cancel_id'];
    $conn->begin_transaction();

    try {
        $sql = "SELECT tractor_id FROM bookings WHERE id=? AND purchasedby=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $tractor_id = $row['tractor_id'];

            $sql = "DELETE FROM bookings WHERE id=? AND purchasedby=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            if (!$stmt->execute()) {
                throw new Exception("Error canceling booking: " . $stmt->error);
            }

            $sql = "UPDATE tractors SET status='available' WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $tractor_id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating tractor status: " . $stmt->error);
            }

            $conn->commit();
            $_SESSION['message'] = "Tractor booking canceled successfully!";
        } else {
            throw new Exception("Booking not found or unauthorized.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    $stmt->close();
    header("Location: view_my_bookings.php");
    exit();
}

// Cancel Booking (Operator)
if (isset($_GET['cancel_id']) && isset($_GET['type']) && $_GET['type'] == 'operator') {
    $booking_id = $_GET['cancel_id'];
    $conn->begin_transaction();

    try {
        $sql = "SELECT operator_id FROM operator_bookings WHERE id=? AND purchasedby=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $operator_id = $row['operator_id'];

            $sql = "DELETE FROM operator_bookings WHERE id=? AND purchasedby=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            if (!$stmt->execute()) {
                throw new Exception("Error canceling operator booking: " . $stmt->error);
            }

            $sql = "UPDATE operators SET status='available' WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $operator_id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating operator status: " . $stmt->error);
            }

            $conn->commit();
            $_SESSION['message'] = "Operator booking canceled successfully!";
        } else {
            throw new Exception("Operator booking not found or unauthorized.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    $stmt->close();
    header("Location: view_my_bookings.php");
    exit();
}

// Return Booking (Tractor)
if (isset($_GET['return_id']) && isset($_GET['type']) && $_GET['type'] == 'tractor') {
    $booking_id = $_GET['return_id'];
    $conn->begin_transaction();

    try {
        $sql = "SELECT * FROM bookings WHERE id=? AND purchasedby=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $tractor_id = $row['tractor_id'];

            $sql = "INSERT INTO returned_bookings (user_id, tractor_id, start_date, end_date, amount, image, purchasedby) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissssi", $row['user_id'], $row['tractor_id'], $row['start_date'], $row['end_date'], 
                            $row['amount'], $row['image'], $row['purchasedby']);
            if (!$stmt->execute()) {
                throw new Exception("Error saving to returned_bookings: " . $stmt->error);
            }

            $sql = "DELETE FROM bookings WHERE id=? AND purchasedby=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            if (!$stmt->execute()) {
                throw new Exception("Error deleting booking: " . $stmt->error);
            }

            $sql = "UPDATE tractors SET status='available' WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $tractor_id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating tractor status: " . $stmt->error);
            }

            $conn->commit();
            $_SESSION['message'] = "Tractor returned successfully!";
        } else {
            throw new Exception("Booking not found or unauthorized.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    $stmt->close();
    header("Location: view_my_bookings.php");
    exit();
}

// Return Booking (Operator)
if (isset($_GET['return_id']) && isset($_GET['type']) && $_GET['type'] == 'operator') {
    $booking_id = $_GET['return_id'];
    $conn->begin_transaction();

    try {
        $sql = "SELECT * FROM operator_bookings WHERE id=? AND purchasedby=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $operator_id = $row['operator_id'];

            $sql = "INSERT INTO returned_operator_bookings (user_id, operator_id, start_date, end_date, amount, purchasedby) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iissii", $row['user_id'], $row['operator_id'], $row['start_date'], $row['end_date'], 
                            $row['amount'], $row['purchasedby']);
            if (!$stmt->execute()) {
                throw new Exception("Error saving to returned_operator_bookings: " . $stmt->error);
            }

            $sql = "DELETE FROM operator_bookings WHERE id=? AND purchasedby=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $booking_id, $_SESSION['user_id']);
            if (!$stmt->execute()) {
                throw new Exception("Error deleting operator booking: " . $stmt->error);
            }

            $sql = "UPDATE operators SET status='available' WHERE id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $operator_id);
            if (!$stmt->execute()) {
                throw new Exception("Error updating operator status: " . $stmt->error);
            }

            $conn->commit();
            $_SESSION['message'] = "Operator returned successfully!";
        } else {
            throw new Exception("Operator booking not found or unauthorized.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    $stmt->close();
    header("Location: view_my_bookings.php");
    exit();
}

$user_id = $_SESSION['user_id'];
// Fetch tractor bookings for the logged-in user
$sql = "SELECT bookings.*, tractors.name, tractors.model, tractors.capability, tractors.description 
        FROM bookings 
        JOIN tractors ON bookings.tractor_id = tractors.id 
        WHERE bookings.purchasedby=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_tractors = $stmt->get_result();

// Fetch operator bookings for the logged-in user
$sql = "SELECT ob.*, o.age, o.sex, o.strength, o.skills 
        FROM operator_bookings ob 
        JOIN operators o ON ob.operator_id = o.id 
        WHERE ob.purchasedby=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_operators = $stmt->get_result();

// Fetch package details for the logged-in user
$sql_packages = "SELECT * FROM packages WHERE user_id=?";
$stmt = $conn->prepare($sql_packages);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result_packages = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Agri-Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .container {
            max-width: 1200px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .table {
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table thead th {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 15px;
        }
        .table tbody td {
            padding: 15px;
            vertical-align: middle;
        }
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: #f8f9fa;
        }
        .action-buttons {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .action-buttons .btn {
            padding: 5px 15px;
        }
        h1, h2 {
            color: #28a745;
            margin-bottom: 20px;
        }
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
                    <li class="nav-item"><a class="nav-link" href="book_operator.php">Book Operator</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_package.php">Book Package Delivery</a></li>
                    <li class="nav-item"><a class="nav-link active" href="view_my_bookings.php">My Bookings</a></li>
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
        <h1>My Bookings</h1>

        <!-- Tractor Bookings -->
        <h2>Tractor Bookings</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Tractor</th>
                        <th>Model</th>
                        <th>Capability</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Delivery Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_tractors->num_rows > 0) {
                        while ($row = $result_tractors->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                    <td>" . htmlspecialchars($row['name']) . "</td>
                                    <td>" . htmlspecialchars($row['model']) . "</td>
                                    <td>" . htmlspecialchars($row['capability']) . "</td>
                                    <td>" . htmlspecialchars($row['description']) . "</td>
                                    <td>" . htmlspecialchars($row['amount']) . "</td>
                                    <td>" . htmlspecialchars($row['start_date']) . "</td>
                                    <td>" . htmlspecialchars($row['end_date']) . "</td>
                                    <td>" . htmlspecialchars($row['delivery_status']) . "</td>
                                    <td class='action-buttons'>";
                            
                            if ($row['delivery_status'] == 'pending') {
                                echo "<form method='POST'>
                                        <input type='hidden' name='booking_id' value='" . htmlspecialchars($row['id']) . "'>
                                        <input type='hidden' name='type' value='tractor'>
                                        <select name='status' class='form-select form-select-sm'>
                                            <option value='pending' selected>Pending</option>
                                            <option value='completed'>Completed</option>
                                        </select>
                                        <button type='submit' name='update_status' class='btn btn-primary btn-sm'>Update</button>
                                      </form>";
                            }

                            if ($row['delivery_status'] == 'pending') {
                                echo "<a href='view_my_bookings.php?cancel_id=" . htmlspecialchars($row['id']) . "&type=tractor' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to cancel this booking?\")'>Cancel</a>";
                            } elseif ($row['delivery_status'] == 'completed') {
                                echo "<a href='view_my_bookings.php?return_id=" . htmlspecialchars($row['id']) . "&type=tractor' class='btn btn-warning btn-sm' onclick='return confirm(\"Are you sure you want to return this tractor?\")'>Return</a>";
                            }

                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='10' class='text-center'>No tractor bookings found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Operator Bookings -->
        <h2>Operator Bookings</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>Operator ID</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Strength</th>
                        <th>Skills</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Delivery Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_operators->num_rows > 0) {
                        while ($row = $result_operators->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                    <td>" . htmlspecialchars($row['operator_id']) . "</td>
                                    <td>" . htmlspecialchars($row['age']) . "</td>
                                    <td>" . htmlspecialchars($row['sex']) . "</td>
                                    <td>" . htmlspecialchars($row['strength']) . "</td>
                                    <td>" . htmlspecialchars($row['skills']) . "</td>
                                    <td>" . htmlspecialchars($row['amount']) . "</td>
                                    <td>" . htmlspecialchars($row['start_date']) . "</td>
                                    <td>" . htmlspecialchars($row['end_date']) . "</td>
                                    <td>" . htmlspecialchars($row['delivery_status']) . "</td>
                                    <td class='action-buttons'>";
                            
                            if ($row['delivery_status'] == 'pending') {
                                echo "<form method='POST'>
                                        <input type='hidden' name='booking_id' value='" . htmlspecialchars($row['id']) . "'>
                                        <input type='hidden' name='type' value='operator'>
                                        <select name='status' class='form-select form-select-sm'>
                                            <option value='pending' selected>Pending</option>
                                            <option value='completed'>Completed</option>
                                        </select>
                                        <button type='submit' name='update_status' class='btn btn-primary btn-sm'>Update</button>
                                      </form>";
                            }

                            if ($row['delivery_status'] == 'pending') {
                                echo "<a href='view_my_bookings.php?cancel_id=" . htmlspecialchars($row['id']) . "&type=operator' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to cancel this booking?\")'>Cancel</a>";
                            } elseif ($row['delivery_status'] == 'completed') {
                                echo "<a href='view_my_bookings.php?return_id=" . htmlspecialchars($row['id']) . "&type=operator' class='btn btn-warning btn-sm' onclick='return confirm(\"Are you sure you want to return this operator?\")'>Return</a>";
                            }

                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' class='text-center'>No operator bookings found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Package Deliveries -->
        <h2>Package Deliveries</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Package ID</th>
                        <th>Package Count</th>
                        <th>Description</th>
                        <th>Weight</th>
                        <th>Transport Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_packages->num_rows > 0) {
                        while ($row = $result_packages->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                    <td>" . htmlspecialchars($row['package_count']) . "</td>
                                    <td>" . htmlspecialchars($row['description']) . "</td>
                                    <td>" . htmlspecialchars($row['weight']) . "</td>
                                    <td>" . htmlspecialchars($row['transport_date']) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5' class='text-center'>No package deliveries found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>