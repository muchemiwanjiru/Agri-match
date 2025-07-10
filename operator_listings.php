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

// Pagination
$items_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;

// Count total listings
$count_stmt = $conn->prepare("SELECT COUNT(*) as total FROM operator_listings");
$count_stmt->execute();
$total_items = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_items / $items_per_page);

// Fetch listings
$stmt = $conn->prepare("SELECT id, name, bio, hourly_rate, profile_picture, location FROM operator_listings LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $items_per_page, $offset);
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Listings - Agri-Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 1200px; }
        .card-img-top { height: 250px; object-fit: cover; }
        .card { transition: transform 0.2s; }
        .card:hover { transform: scale(1.05); }
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
                    <li class="nav-item"><a class="nav-link active" href="user_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_wishlist.php">Wishlist</a></li>
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
    <div class="container mt-5">
        <h1 class="text-center mb-4">Operator Listings</h1>
        
        <div class="row">
            <?php if ($listings): ?>
                <?php foreach ($listings as $listing): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-lg border-light">
                            <img src="<?php echo $listing['profile_picture'] ? htmlspecialchars($listing['profile_picture']) : 'images/default_image.png'; ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($listing['name']); ?> Profile Picture">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($listing['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($listing['bio'], 0, 45)) . (strlen($listing['bio']) > 45 ? '...' : ''); ?></p>
                                <p class="text-muted">Hourly Rate: KSh <?php echo htmlspecialchars($listing['hourly_rate']); ?></p>
                                <a href="operator_listing_details.php?id=<?php echo $listing['id']; ?>" class="btn btn-success btn-block">Hire This Operator</a>
                            </div>
                            <div class="card-footer text-muted">
                                <small>Located in: <?php echo htmlspecialchars($listing['location'] ?? 'Not specified'); ?></small>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-warning" role="alert">
                        No operator listings available at the moment.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Pagination Controls -->
        <nav aria-label="Page navigation example" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                    </li>
                <?php endif; ?>
                <li class="page-item disabled">
                    <a class="page-link"><?php echo $page; ?> of <?php echo $total_pages; ?></a>
                </li>
                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>