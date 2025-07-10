<?php
session_start();

// Ensure the user is logged in and has the 'rentee' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "agrimatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $make = $_POST['make'] ?? '';
    $model = $_POST['model'] ?? '';
    $description = $_POST['description'] ?? '';
    $price_per_day = $_POST['price_per_day'] ?? '';
    $available_from = $_POST['available_from'] ?? '';
    $available_to = $_POST['available_to'] ?? '';
    $location = $_POST['location'] ?? '';
    $condition = $_POST['condition'] ?? '';
    $image = '';

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $image = $upload_dir . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    // Validate inputs
    if (empty($make)) $errors[] = "Make is required";
    if (empty($model)) $errors[] = "Model is required";
    if (empty($price_per_day)) $errors[] = "Price per day is required";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO machinery_listings (make, model, description, price_per_day, available_from, available_to, location, `condition`, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddssss", $make, $model, $description, $price_per_day, $available_from, $available_to, $location, $condition, $image);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Machinery listing created successfully!";
            header("Location: machinery_listings.php");
            exit();
        } else {
            $errors[] = "Failed to create listing";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Machinery Listing - Agri-Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 800px; }
        .error { color: red; }
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
                    <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_wishlist.php">Wishlist</a></li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="createDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Create Listing
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="createDropdown">
                            <li><a class="dropdown-item" href="create_machinery_listing.php">Create Machinery Listing</a></li>
                            <li><a class="dropdown-item" href="create_operator_listing.php">Create Operator Listing</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="viewDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            View Listings
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="viewDropdown">
                            <li><a class="dropdown-item" href="machinery_listings.php">Machinery Listings</a></li>
                            <li><a class="dropdown-item" href="operator_listings.php">Operator Listings</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="book_tractor.php">Book Tractor</a></li>
                    <li class="nav-item"><a class="nav-link" href="book_package.php">Book Package Delivery</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_my_bookings.php">My Bookings</a></li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <h2 class="text-center my-4">Create a Machinery Listing</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg rounded-lg">
                    <div class="card-body">
                        <h4 class="mb-4 text-center text-primary">Fill in the details to create your listing</h4>
                        <?php if ($errors): ?>
                            <div class="error">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="make" class="form-label">Make</label>
                                <input type="text" class="form-control" id="make" name="make" value="<?php echo isset($_POST['make']) ? htmlspecialchars($_POST['make']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="model" class="form-label">Model</label>
                                <input type="text" class="form-control" id="model" name="model" value="<?php echo isset($_POST['model']) ? htmlspecialchars($_POST['model']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description"><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="price_per_day" class="form-label">Price per Day (KSh)</label>
                                <input type="number" step="0.01" class="form-control" id="price_per_day" name="price_per_day" value="<?php echo isset($_POST['price_per_day']) ? htmlspecialchars($_POST['price_per_day']) : ''; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="available_from" class="form-label">Available From</label>
                                <input type="date" class="form-control" id="available_from" name="available_from" value="<?php echo isset($_POST['available_from']) ? htmlspecialchars($_POST['available_from']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="available_to" class="form-label">Available To</label>
                                <input type="date" class="form-control" id="available_to" name="available_to" value="<?php echo isset($_POST['available_to']) ? htmlspecialchars($_POST['available_to']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="location" class="form-label">Location</label>
                                <input type="text" class="form-control" id="location" name="location" value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="condition" class="form-label">Condition</label>
                                <input type="text" class="form-control" id="condition" name="condition" value="<?php echo isset($_POST['condition']) ? htmlspecialchars($_POST['condition']) : ''; ?>">
                            </div>
                            <div class="mb-3">
                                <label for="image" class="form-label">Image</label>
                                <input type="file" class="form-control" id="image" name="image">
                            </div>
                            <button type="submit" class="btn btn-success btn-block mt-4">Create Listing</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>