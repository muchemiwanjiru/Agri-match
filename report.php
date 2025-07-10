<?php
session_start();

// Ensure the user is logged in and has an admin role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "agrimatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Count total tractors
$sql_total_tractors = "SELECT COUNT(*) AS total FROM tractors";
$result_total_tractors = $conn->query($sql_total_tractors);
$row_total_tractors = $result_total_tractors->fetch_assoc();
$total_tractors = $row_total_tractors['total'];

// Count booked tractors
$sql_booked_tractors = "SELECT COUNT(*) AS booked FROM tractors WHERE status='booked'";
$result_booked_tractors = $conn->query($sql_booked_tractors);
$row_booked_tractors = $result_booked_tractors->fetch_assoc();
$booked_tractors = $row_booked_tractors['booked'];

// Calculate available tractors
$available_tractors = $total_tractors - $booked_tractors;

// Count total operators
$sql_total_operators = "SELECT COUNT(*) AS total FROM operators";
$result_total_operators = $conn->query($sql_total_operators);
$row_total_operators = $result_total_operators->fetch_assoc();
$total_operators = $row_total_operators['total'];

// Count booked operators
$sql_booked_operators = "SELECT COUNT(*) AS booked FROM operators WHERE status='booked'";
$result_booked_operators = $conn->query($sql_booked_operators);
$row_booked_operators = $result_booked_operators->fetch_assoc();
$booked_operators = $row_booked_operators['booked'];

// Calculate available operators
$available_operators = $total_operators - $booked_operators;

// Handle search filter (shared for tractors and operators)
$search_query = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $search_type = $_POST['search_type'];
    $search_value = $_POST['search_value'];

    if (!empty($search_value)) {
        switch ($search_type) {
            case 'date':
                $search_query = " WHERE DATE(booked_datetime) = ?";
                break;
            case 'month':
                $search_query = " WHERE MONTH(booked_datetime) = ? AND YEAR(booked_datetime) = YEAR(CURDATE())";
                break;
            case 'year':
                $search_query = " WHERE YEAR(booked_datetime) = ?";
                break;
        }
    }
}

// Fetch booked_tractor details
$sql_booked_tractor = "SELECT bt.id, bt.user_id, bt.tractor_id, bt.start_date, bt.end_date, bt.amount, bt.booked_datetime, 
                             u.username, t.name, t.model 
                       FROM booked_tractor bt 
                       JOIN users u ON bt.user_id = u.id 
                       JOIN tractors t ON bt.tractor_id = t.id" . $search_query;

$stmt_tractor = $conn->prepare($sql_booked_tractor);
if ($search_query && !empty($search_value)) {
    if ($search_type == 'month') {
        $month = (int)$search_value;
        $stmt_tractor->bind_param("i", $month);
    } else {
        $stmt_tractor->bind_param("s", $search_value);
    }
}
$stmt_tractor->execute();
$result_booked_tractor = $stmt_tractor->get_result();

// Fetch booked_operator details
$sql_booked_operator = "SELECT bo.id, bo.user_id, bo.operator_id, bo.start_date, bo.end_date, bo.amount, bo.booked_datetime, 
                               u.username, o.age, o.sex, o.strength, o.skills 
                        FROM booked_operator bo 
                        JOIN users u ON bo.user_id = u.id 
                        JOIN operators o ON bo.operator_id = o.id" . $search_query;

$stmt_operator = $conn->prepare($sql_booked_operator);
if ($search_query && !empty($search_value)) {
    if ($search_type == 'month') {
        $month = (int)$search_value;
        $stmt_operator->bind_param("i", $month);
    } else {
        $stmt_operator->bind_param("s", $search_value);
    }
}
$stmt_operator->execute();
$result_booked_operator = $stmt_operator->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Report - Agri-Match</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
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
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        h1, h2 {
            color: #28a745;
            margin-bottom: 20px;
        }
        .search-form {
            max-width: 500px;
            margin: 20px auto;
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
                    <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Home</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="manageDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Manage
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="manageDropdown">
                            <li><a class="dropdown-item" href="manage_tractors.php">Manage Tractors</a></li>
                            <li><a class="dropdown-item" href="manage_users.php">Manage Users</a></li>
                            <li><a class="dropdown-item" href="manage_operators.php">Manage Operators</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="view_bookings.php">View Bookings</a></li>
                    <li class="nav-item"><a class="nav-link active" href="report.php">View Report</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1 class="text-center text-success">Booking Report</h1>

        <!-- Tractor Summary Cards -->
        <h2 class="text-center">Tractor Summary</h2>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Tractors</h5>
                        <p class="card-text fs-3 fw-bold"><?php echo $total_tractors; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-danger">
                    <div class="card-body">
                        <h5 class="card-title">Booked Tractors</h5>
                        <p class="card-text fs-3 fw-bold"><?php echo $booked_tractors; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Available Tractors</h5>
                        <p class="card-text fs-3 fw-bold"><?php echo $available_tractors; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operator Summary Cards -->
        <h2 class="text-center mt-5">Operator Summary</h2>
        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card text-center border-success">
                    <div class="card-body">
                        <h5 class="card-title">Total Operators</h5>
                        <p class="card-text fs-3 fw-bold"><?php echo $total_operators; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-danger">
                    <div class="card-body">
                        <h5 class="card-title">Booked Operators</h5>
                        <p class="card-text fs-3 fw-bold"><?php echo $booked_operators; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card text-center border-primary">
                    <div class="card-body">
                        <h5 class="card-title">Available Operators</h5>
                        <p class="card-text fs-3 fw-bold"><?php echo $available_operators; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Form -->
        <form method="POST" class="search-form">
            <div class="input-group mb-3">
                <select name="search_type" class="form-select" required>
                    <option value="date">Date</option>
                    <option value="month">Month</option>
                    <option value="year">Year</option>
                </select>
                <input type="text" name="search_value" class="form-control" placeholder="e.g., 2025-03-06, 3, 2025" required>
                <button type="submit" name="search" class="btn btn-primary">Search</button>
            </div>
        </form>

        <!-- Booked Tractor Details -->
        <h2 class="text-center">Booked Tractor Details</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Tractor</th>
                        <th>Model</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Amount</th>
                        <th>Booked DateTime</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_booked_tractor->num_rows > 0) {
                        while ($row = $result_booked_tractor->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                    <td>" . htmlspecialchars($row['username']) . "</td>
                                    <td>" . htmlspecialchars($row['name']) . "</td>
                                    <td>" . htmlspecialchars($row['model']) . "</td>
                                    <td>" . htmlspecialchars($row['start_date']) . "</td>
                                    <td>" . htmlspecialchars($row['end_date']) . "</td>
                                    <td>" . htmlspecialchars($row['amount']) . "</td>
                                    <td>" . htmlspecialchars($row['booked_datetime']) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' class='text-center'>No booked tractors found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Booked Operator Details -->
        <h2 class="text-center mt-5">Booked Operator Details</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Operator ID</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Strength</th>
                        <th>Skills</th>
                        <th>Amount</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Booked DateTime</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result_booked_operator->num_rows > 0) {
                        while ($row = $result_booked_operator->fetch_assoc()) {
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
                                    <td>" . htmlspecialchars($row['booked_datetime']) . "</td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='11' class='text-center'>No booked operators found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$stmt_tractor->close();
$stmt_operator->close();
$conn->close();
?>