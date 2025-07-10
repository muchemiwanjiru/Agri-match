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

// Add Operator
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_operator'])) {
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $strength = $_POST['strength'];
    $skills = $_POST['skills'];
    $amount = $_POST['amount'];

    $sql = "INSERT INTO operators (age, sex, strength, skills, amount) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssi", $age, $sex, $strength, $skills, $amount);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Operator added successfully!";
    } else {
        $_SESSION['error'] = "Error adding operator: " . $stmt->error;
    }
    $stmt->close();
    header("Location: manage_operators.php");
    exit();
}

// Edit Operator
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_operator'])) {
    $id = $_POST['id'];
    $age = $_POST['age'];
    $sex = $_POST['sex'];
    $strength = $_POST['strength'];
    $skills = $_POST['skills'];
    $amount = $_POST['amount'];

    $sql = "UPDATE operators SET age=?, sex=?, strength=?, skills=?, amount=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssii", $age, $sex, $strength, $skills, $amount, $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Operator updated successfully!";
    } else {
        $_SESSION['error'] = "Error updating operator: " . $stmt->error;
    }
    $stmt->close();
    header("Location: manage_operators.php");
    exit();
}

// Delete Operator
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $sql = "DELETE FROM operators WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        $_SESSION['message'] = "Operator deleted successfully!";
    } else {
        $_SESSION['error'] = "Error deleting operator: " . $stmt->error;
    }
    $stmt->close();
    header("Location: manage_operators.php");
    exit();
}

// Fetch all operators
$sql = "SELECT * FROM operators";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Operators - Agri-Match</title>
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
        h1, h2 {
            color: #28a745;
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #218838;
        }
        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        #editForm {
            display: none;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
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
                            <li><a class="dropdown-item active" href="manage_operators.php">Manage Operators</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="view_bookings.php">View Bookings</a></li>
                    <li class="nav-item"><a class="nav-link" href="report.php">View Report</a></li>
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
        <h1>Manage Operators</h1>

        <!-- Add Operator Form -->
        <div class="form-container">
            <h2>Add New Operator</h2>
            <form method="POST">
                <div class="mb-3">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" class="form-control" id="age" name="age" min="18" required>
                </div>
                <div class="mb-3">
                    <label for="sex" class="form-label">Sex</label>
                    <select class="form-select" id="sex" name="sex" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="strength" class="form-label">Strength</label>
                    <input type="text" class="form-control" id="strength" name="strength" required>
                </div>
                <div class="mb-3">
                    <label for="skills" class="form-label">Skills</label>
                    <textarea class="form-control" id="skills" name="skills" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Amount (Payment)</label>
                    <input type="number" class="form-control" id="amount" name="amount" min="0" required>
                </div>
                <button type="submit" name="add_operator" class="btn btn-primary">Add Operator</button>
            </form>
        </div>

        <!-- Operators Table -->
        <h2>All Operators</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Age</th>
                        <th>Sex</th>
                        <th>Strength</th>
                        <th>Skills</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                    <td>" . htmlspecialchars($row['age']) . "</td>
                                    <td>" . htmlspecialchars($row['sex']) . "</td>
                                    <td>" . htmlspecialchars($row['strength']) . "</td>
                                    <td>" . htmlspecialchars($row['skills']) . "</td>
                                    <td>" . htmlspecialchars($row['amount']) . "</td>
                                    <td>" . htmlspecialchars($row['status']) . "</td>
                                    <td>" . htmlspecialchars($row['created_at']) . "</td>
                                    <td>
                                        <button class='btn btn-primary btn-sm' onclick='openEditForm(" . htmlspecialchars(json_encode($row)) . ")'>Edit</button>
                                        <a href='manage_operators.php?delete_id=" . htmlspecialchars($row['id']) . "' 
                                           class='btn btn-danger btn-sm' 
                                           onclick='return confirm(\"Are you sure you want to delete this operator?\")'>Delete</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No operators found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Operator Form -->
        <div id="editForm">
            <h2>Edit Operator</h2>
            <form method="POST">
                <input type="hidden" name="id" id="editId">
                <div class="mb-3">
                    <label for="editAge" class="form-label">Age</label>
                    <input type="number" class="form-control" id="editAge" name="age" min="18" required>
                </div>
                <div class="mb-3">
                    <label for="editSex" class="form-label">Sex</label>
                    <select class="form-select" id="editSex" name="sex" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="editStrength" class="form-label">Strength</label>
                    <input type="text" class="form-control" id="editStrength" name="strength" required>
                </div>
                <div class="mb-3">
                    <label for="editSkills" class="form-label">Skills</label>
                    <textarea class="form-control" id="editSkills" name="skills" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="editAmount" class="form-label">Amount (Payment)</label>
                    <input type="number" class="form-control" id="editAmount" name="amount" min="0" required>
                </div>
                <button type="submit" name="edit_operator" class="btn btn-primary">Update Operator</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditForm()">Cancel</button>
            </form>
        </div>
    </div>

    <!-- JavaScript for Edit Form -->
    <script>
        function openEditForm(operator) {
            document.getElementById('editId').value = operator.id;
            document.getElementById('editAge').value = operator.age;
            document.getElementById('editSex').value = operator.sex;
            document.getElementById('editStrength').value = operator.strength;
            document.getElementById('editSkills').value = operator.skills;
            document.getElementById('editAmount').value = operator.amount;
            document.getElementById('editForm').style.display = 'block';
        }

        function closeEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }
    </script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
$conn->close();
?>