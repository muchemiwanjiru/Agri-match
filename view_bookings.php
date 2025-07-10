<?php
session_start();

// Ensure the user is logged in and has either 'admin' or 'renter' role
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'renter')) {
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

    if ($_SESSION['role'] == 'renter') {
        $sql = "UPDATE bookings 
                SET delivery_status=? 
                WHERE id=? 
                AND tractor_id IN (SELECT id FROM tractors WHERE user_id=?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $new_status, $booking_id, $_SESSION['user_id']);
    } else {
        // Admin can update any booking
        $sql = "UPDATE bookings 
                SET delivery_status=? 
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $booking_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Tractor delivery status updated successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: view_bookings.php");
    exit();
}

// Update Delivery Status (Operator)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && isset($_POST['type']) && $_POST['type'] == 'operator') {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['status'];

    if ($_SESSION['role'] == 'renter') {
        $sql = "UPDATE operator_bookings 
                SET delivery_status=? 
                WHERE id=? 
                AND operator_id IN (SELECT id FROM operators WHERE user_id=?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $new_status, $booking_id, $_SESSION['user_id']);
    } else {
        // Admin can update any operator booking
        $sql = "UPDATE operator_bookings 
                SET delivery_status=? 
                WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $new_status, $booking_id);
    }

    if ($stmt->execute()) {
        $_SESSION['message'] = "Operator delivery status updated successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $stmt->error;
    }
    $stmt->close();
    header("Location: view_bookings.php");
    exit();
}

// Fetch tractor bookings based on user role
if ($_SESSION['role'] == 'admin') {
    $sql_tractors = "SELECT bookings.id, bookings.user_id, bookings.tractor_id, bookings.start_date, bookings.end_date, bookings.amount, bookings.delivery_status, 
                            users.username, tractors.name, tractors.model, tractors.capability, tractors.description 
                     FROM bookings 
                     JOIN users ON bookings.user_id = users.id 
                     JOIN tractors ON bookings.tractor_id = tractors.id";
    $result_tractors = $conn->query($sql_tractors);
} else {
    $sql_tractors = "SELECT bookings.id, bookings.user_id, bookings.tractor_id, bookings.start_date, bookings.end_date, bookings.amount, bookings.delivery_status, 
                            users.username, tractors.name, tractors.model, tractors.capability, tractors.description 
                     FROM bookings 
                     JOIN users ON bookings.user_id = users.id 
                     JOIN tractors ON bookings.tractor_id = tractors.id 
                     WHERE tractors.user_id=?";
    $stmt = $conn->prepare($sql_tractors);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result_tractors = $stmt->get_result();
    $stmt->close();
}

// Fetch operator bookings based on user role
if ($_SESSION['role'] == 'admin') {
    $sql_operators = "SELECT ob.id, ob.user_id, ob.operator_id, ob.start_date, ob.end_date, ob.amount, ob.delivery_status, 
                             users.username, o.age, o.sex, o.strength, o.skills 
                      FROM operator_bookings ob 
                      JOIN users ON ob.user_id = users.id 
                      JOIN operators o ON ob.operator_id = o.id";
    $result_operators = $conn->query($sql_operators);
} else {
    $sql_operators = "SELECT ob.id, ob.user_id, ob.operator_id, ob.start_date, ob.end_date, ob.amount, ob.delivery_status, 
                             users.username, o.age, o.sex, o.strength, o.skills 
                      FROM operator_bookings ob 
                      JOIN users ON ob.user_id = users.id 
                      JOIN operators o ON ob.operator_id = o.id 
                      WHERE o.user_id=?";
    $stmt = $conn->prepare($sql_operators);
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result_operators = $stmt->get_result();
    $stmt->close();
}

// Fetch returned bookings (only for admin)
if ($_SESSION['role'] == 'admin') {
    $sql_returned_tractors = "SELECT returned_bookings.id, returned_bookings.user_id, returned_bookings.tractor_id, returned_bookings.start_date, 
                                     returned_bookings.end_date, returned_bookings.amount, returned_bookings.returned_datetime, 
                                     users.username, tractors.name, tractors.model, tractors.capability, tractors.description 
                              FROM returned_bookings 
                              JOIN users ON returned_bookings.user_id = users.id 
                              JOIN tractors ON returned_bookings.tractor_id = tractors.id";
    $result_returned_tractors = $conn->query($sql_returned_tractors);

    $sql_returned_operators = "SELECT rob.id, rob.user_id, rob.operator_id, rob.start_date, rob.end_date, rob.amount, rob.returned_datetime, 
                                      users.username, o.age, o.sex, o.strength, o.skills 
                               FROM returned_operator_bookings rob 
                               JOIN users ON rob.user_id = users.id 
                               JOIN operators o ON rob.operator_id = o.id";
    $result_returned_operators = $conn->query($sql_returned_operators);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Bookings - Agri-Match</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
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
        .action-buttons select {
            width: auto;
            display: inline-block;
        }
        .btn-update {
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
            <a class="navbar-brand" href="admin_dashboard.php">
                <img src="images/logo.png" alt="Agri-Match Logo" width="150">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="manage_tractors.php">Manage Tractors</a></li>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="manage_users.php">Manage Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="manage_operators.php">Manage Operators</a></li>
                        <li class="nav-item"><a class="nav-link active" href="view_bookings.php">View Bookings</a></li>
                    <?php endif; ?>
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
        <h1>View Bookings</h1>

        <!-- Tractor Bookings -->
        <h2>Tractor Bookings</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Booking ID</th>
                        <th>User</th>
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
                                    <td>" . htmlspecialchars($row['username']) . "</td>
                                    <td>" . htmlspecialchars($row['name']) . "</td>
                                    <td>" . htmlspecialchars($row['model']) . "</td>
                                    <td>" . htmlspecialchars($row['capability']) . "</td>
                                    <td>" . htmlspecialchars($row['description']) . "</td>
                                    <td>" . htmlspecialchars($row['amount']) . "</td>
                                    <td>" . htmlspecialchars($row['start_date']) . "</td>
                                    <td>" . htmlspecialchars($row['end_date']) . "</td>
                                    <td>" . htmlspecialchars($row['delivery_status']) . "</td>
                                    <td class='action-buttons'>
                                        <form method='POST'>
                                            <input type='hidden' name='booking_id' value='" . htmlspecialchars($row['id']) . "'>
                                            <input type='hidden' name='type' value='tractor'>
                                            <select name='status' class='form-select form-select-sm'>
                                                <option value='pending'" . ($row['delivery_status'] == 'pending' ? ' selected' : '') . ">Pending</option>
                                                <option value='completed'" . ($row['delivery_status'] == 'completed' ? ' selected' : '') . ">Completed</option>
                                            </select>
                                            <button type='submit' name='update_status' class='btn btn-primary btn-sm btn-update'>Update</button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' class='text-center'>No active tractor bookings found.</td></tr>";
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
                        <th>User</th>
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
                                    <td>" . htmlspecialchars($row['username']) . "</td>
                                    <td>" . htmlspecialchars($row['operator_id']) . "</td>
                                    <td>" . htmlspecialchars($row['age']) . "</td>
                                    <td>" . htmlspecialchars($row['sex']) . "</td>
                                    <td>" . htmlspecialchars($row['strength']) . "</td>
                                    <td>" . htmlspecialchars($row['skills']) . "</td>
                                    <td>" . htmlspecialchars($row['amount']) . "</td>
                                    <td>" . htmlspecialchars($row['start_date']) . "</td>
                                    <td>" . htmlspecialchars($row['end_date']) . "</td>
                                    <td>" . htmlspecialchars($row['delivery_status']) . "</td>
                                    <td class='action-buttons'>
                                        <form method='POST'>
                                            <input type='hidden' name='booking_id' value='" . htmlspecialchars($row['id']) . "'>
                                            <input type='hidden' name='type' value='operator'>
                                            <select name='status' class='form-select form-select-sm'>
                                                <option value='pending'" . ($row['delivery_status'] == 'pending' ? ' selected' : '') . ">Pending</option>
                                                <option value='completed'" . ($row['delivery_status'] == 'completed' ? ' selected' : '') . ">Completed</option>
                                            </select>
                                            <button type='submit' name='update_status' class='btn btn-primary btn-sm btn-update'>Update</button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='12' class='text-center'>No active operator bookings found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Returned Tractor Bookings (Admin Only) -->
        <?php if ($_SESSION['role'] == 'admin'): ?>
            <h2>Returned Tractor Bookings</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Return ID</th>
                            <th>User</th>
                            <th>Tractor</th>
                            <th>Model</th>
                            <th>Capability</th>
                            <th>Description</th>
                            <th>Amount</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Returned DateTime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_returned_tractors && $result_returned_tractors->num_rows > 0) {
                            while ($row = $result_returned_tractors->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['id']) . "</td>
                                        <td>" . htmlspecialchars($row['username']) . "</td>
                                        <td>" . htmlspecialchars($row['name']) . "</td>
                                        <td>" . htmlspecialchars($row['model']) . "</td>
                                        <td>" . htmlspecialchars($row['capability']) . "</td>
                                        <td>" . htmlspecialchars($row['description']) . "</td>
                                        <td>" . htmlspecialchars($row['amount']) . "</td>
                                        <td>" . htmlspecialchars($row['start_date']) . "</td>
                                        <td>" . htmlspecialchars($row['end_date']) . "</td>
                                        <td>" . htmlspecialchars($row['returned_datetime']) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='10' class='text-center'>No returned tractor bookings found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Returned Operator Bookings (Admin Only) -->
            <h2>Returned Operator Bookings</h2>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Return ID</th>
                            <th>User</th>
                            <th>Operator ID</th>
                            <th>Age</th>
                            <th>Sex</th>
                            <th>Strength</th>
                            <th>Skills</th>
                            <th>Amount</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Returned DateTime</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result_returned_operators && $result_returned_operators->num_rows > 0) {
                            while ($row = $result_returned_operators->fetch_assoc()) {
                                echo "<tr>
                                        <td>" . htmlspecialchars($row['id']) . "</td>
                                        <td>" . htmlspecialchars($row['username']) . "</td>
                                        <td>" . htmlspecialchars($row['operator_id']) . "</td>
                                        <td>" . htmlspecialchars($row['age']) . "</td>
                                        <td>" . htmlspecialchars($row['sex']) . "</td>
                                        <td>" . htmlspecialchars($row['strength']) . "</td>
                                        <td>" . htmlspecialchars($row['skills']) . "</td>
                                        <td>" . htmlspecialchars($row['amount']) . "</td>
                                        <td>" . htmlspecialchars($row['start_date']) . "</td>
                                        <td>" . htmlspecialchars($row['end_date']) . "</td>
                                        <td>" . htmlspecialchars($row['returned_datetime']) . "</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='11' class='text-center'>No returned operator bookings found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>