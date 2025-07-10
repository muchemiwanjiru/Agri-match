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

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['edit_tractor'])) {
        // Edit Tractor
        $id = $_POST['id'];
        $name = $_POST['name'];
        $model = $_POST['model'];
        $capability = $_POST['capability'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];

        // Handle image upload
        $image = $_FILES['image']['name'];
        if ($image) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($_FILES['image']['name']);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
            $image_path = $target_file;
        } else {
            // Keep the existing image if no new image is uploaded
            $sql = "SELECT image FROM tractors WHERE id='$id'";
            $result = $conn->query($sql);
            $row = $result->fetch_assoc();
            $image_path = $row['image'];
        }

        $sql = "UPDATE tractors 
                SET name='$name', model='$model', capability='$capability', description='$description', amount='$amount', image='$image_path' 
                WHERE id='$id'";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Tractor updated successfully!";
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
        }
    } elseif (isset($_POST['add_tractor'])) {
        // Add Tractor
        $name = $_POST['name'];
        $model = $_POST['model'];
        $capability = $_POST['capability'];
        $description = $_POST['description'];
        $amount = $_POST['amount'];
        $user_id = $_SESSION['user_id'];

        // Handle image upload
        $image = $_FILES['image']['name'];
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        $image_path = $target_file;

        $sql = "INSERT INTO tractors (name, model, capability, description, amount, image, user_id) 
                VALUES ('$name', '$model', '$capability', '$description', '$amount', '$image_path', '$user_id')";
        if ($conn->query($sql)) {
            $_SESSION['message'] = "Tractor added successfully!";
        } else {
            $_SESSION['error'] = "Error: " . $conn->error;
        }
    }
    header("Location: manage_tractors.php"); // Refresh page to show message
    exit();
}

// Delete Tractor
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $sql = "DELETE FROM tractors WHERE id='$id'";
    if ($conn->query($sql)) {
        $_SESSION['message'] = "Tractor deleted successfully!";
    } else {
        $_SESSION['error'] = "Error: " . $conn->error;
    }
    header("Location: manage_tractors.php");
    exit();
}

// Fetch all tractors based on role
if ($_SESSION['role'] == 'admin') {
    $sql = "SELECT * FROM tractors";
} else {
    $sql = "SELECT * FROM tractors WHERE user_id='{$_SESSION['user_id']}'";
}
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tractors - Agri-Match</title>
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
        .card {
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
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
        .img-thumbnail {
            max-width: 100px;
            height: auto;
        }
        h1, h2 {
            color: #28a745;
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: bold;
        }
        #editForm {
            display: none;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
                    <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Home</a></li>

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
        <h1>Manage Tractors</h1>

        <!-- Add New Tractor -->
        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Add New Tractor</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name:</label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="model" class="form-label">Model:</label>
                        <input type="text" class="form-control" name="model" id="model" required>
                    </div>
                    <div class="mb-3">
                        <label for="capability" class="form-label">Capability:</label>
                        <input type="text" class="form-control" name="capability" id="capability" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea class="form-control" name="description" id="description" rows="4" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount:</label>
                        <input type="number" class="form-control" name="amount" id="amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="image" class="form-label">Image:</label>
                        <input type="file" class="form-control" name="image" id="image" accept="image/*" required>
                    </div>
                    <button type="submit" name="add_tractor" class="btn btn-success">Add Tractor</button>
                </form>
            </div>
        </div>

        <!-- Tractor List -->
        <h2>Tractor List</h2>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Model</th>
                        <th>Capability</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                    <td>" . htmlspecialchars($row['id']) . "</td>
                                    <td>" . htmlspecialchars($row['name']) . "</td>
                                    <td>" . htmlspecialchars($row['model']) . "</td>
                                    <td>" . htmlspecialchars($row['capability']) . "</td>
                                    <td>" . htmlspecialchars($row['description']) . "</td>
                                    <td>" . htmlspecialchars($row['amount']) . "</td>
                                    <td><img src='" . htmlspecialchars($row['image']) . "' alt='Tractor Image' class='img-thumbnail'></td>
                                    <td>" . htmlspecialchars($row['status']) . "</td>
                                    <td>
                                        <button class='btn btn-primary btn-sm' onclick='openEditForm(" . htmlspecialchars(json_encode($row)) . ")'>Edit</button>
                                        <a href='manage_tractors.php?delete_id=" . htmlspecialchars($row['id']) . "' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure you want to delete this tractor?\")'>Delete</a>
                                    </td>
                                  </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='9' class='text-center'>No tractors found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Edit Tractor Form -->
        <div id="editForm">
            <h2>Edit Tractor</h2>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" id="editId">
                <div class="mb-3">
                    <label for="editName" class="form-label">Name:</label>
                    <input type="text" class="form-control" name="name" id="editName" required>
                </div>
                <div class="mb-3">
                    <label for="editModel" class="form-label">Model:</label>
                    <input type="text" class="form-control" name="model" id="editModel" required>
                </div>
                <div class="mb-3">
                    <label for="editCapability" class="form-label">Capability:</label>
                    <input type="text" class="form-control" name="capability" id="editCapability" required>
                </div>
                <div class="mb-3">
                    <label for="editDescription" class="form-label">Description:</label>
                    <textarea class="form-control" name="description" id="editDescription" rows="4" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="editAmount" class="form-label">Amount:</label>
                    <input type="number" class="form-control" name="amount" id="editAmount" required>
                </div>
                <div class="mb-3">
                    <label for="editImage" class="form-label">Image:</label>
                    <input type="file" class="form-control" name="image" id="editImage" accept="image/*">
                </div>
                <button type="submit" name="edit_tractor" class="btn btn-success">Update Tractor</button>
                <button type="button" class="btn btn-secondary" onclick="closeEditForm()">Cancel</button>
            </form>
        </div>
    </div>

    <script>
        function openEditForm(tractor) {
            document.getElementById('editId').value = tractor.id;
            document.getElementById('editName').value = tractor.name;
            document.getElementById('editModel').value = tractor.model;
            document.getElementById('editCapability').value = tractor.capability;
            document.getElementById('editDescription').value = tractor.description;
            document.getElementById('editAmount').value = tractor.amount;
            document.getElementById('editForm').style.display = 'block';
        }

        function closeEditForm() {
            document.getElementById('editForm').style.display = 'none';
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>