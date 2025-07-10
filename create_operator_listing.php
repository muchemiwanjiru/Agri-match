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
    $name = $_POST['name'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $certification = $_POST['certification'] ?? '';
    $hourly_rate = $_POST['hourly_rate'] ?? '';
    $available_from = $_POST['available_from'] ?? '';
    $available_to = $_POST['available_to'] ?? '';
    $profile_picture = '';

    // Handle file upload
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        $profile_picture = $upload_dir . basename($_FILES['profile_picture']['name']);
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $profile_picture);
    }

    // Validate inputs
    if (empty($name)) $errors[] = "Name is required";
    if (empty($hourly_rate)) $errors[] = "Hourly rate is required";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO operator_listings (name, bio, certification, hourly_rate, available_from, available_to, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssddss", $name, $bio, $certification, $hourly_rate, $available_from, $available_to, $profile_picture);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Operator listing created successfully!";
            header("Location: operator_listings.php"); // You'll need to create this page
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
    <title>Create Operator Listing - Agri-Match</title>
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
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container my-5">
        <h2 class="text-center my-4">List as an Operator</h2>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card shadow-lg rounded-lg">
                    <div class="card-body">
                        <h4 class="mb-4 text-cente