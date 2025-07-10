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

// Get listing ID from URL
$listing_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch listing details
$stmt = $conn->prepare("SELECT name, bio, certification, hourly_rate, available_from, available_to, profile_picture FROM operator_listings WHERE id = ?");
$stmt->bind_param("i", $listing_id);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

// Fetch average rating
$rating_stmt = $conn->prepare("SELECT AVG(rating) as avg_rating FROM operator_reviews WHERE listing_id = ?");
$rating_stmt->bind_param("i", $listing_id);
$rating_stmt->execute();
$avg_rating = $rating_stmt->get_result()->fetch_assoc()['avg_rating'];

// Fetch reviews
$reviews_stmt = $conn->prepare("SELECT r.rating, r.comment, u.username FROM operator_reviews r JOIN users u ON r.user_id = u.id WHERE r.listing_id = ?");
$reviews_stmt->bind_param("i", $listing_id);
$reviews_stmt->execute();
$reviews = $reviews_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Check for existing review
$existing_review_stmt = $conn->prepare("SELECT COUNT(*) as count FROM operator_reviews WHERE listing_id = ? AND user_id = ?");
$existing_review_stmt->bind_param("ii", $listing_id, $_SESSION['user_id']);
$existing_review_stmt->execute();
$existing_review = $existing_review_stmt->get_result()->fetch_assoc()['count'] > 0;

// Handle review submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rating = $_POST['rating'] ?? '';
    $comment = $_POST['comment'] ?? '';

    if (empty($rating)) $errors[] = "Rating is required";
    if (empty($comment)) $errors[] = "Comment is required";

    if (empty($errors) && !$existing_review) {
        $stmt = $conn->prepare("INSERT INTO operator_reviews (listing_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $listing_id, $_SESSION['user_id'], $rating, $comment);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Review submitted successfully!";
            header("Location: operator_listing_details.php?id=$listing_id");
            exit();
        } else {
            $errors[] = "Failed to submit review";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Operator Details - Agri-Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 1200px; }
        .img-fluid { max-height: 400px; object-fit: cover; }
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
        <h2><?php echo htmlspecialchars($listing['name']); ?> - Operator Details</h2>

        <div class="row mt-4">
            <div class="col-md-6">
                <h4>About the Operator</h4>
                <p><strong>Bio:</strong> <?php echo htmlspecialchars($listing['bio']); ?></p>
                <p><strong>Certification:</strong> <?php echo htmlspecialchars($listing['certification'] ?? 'Not specified'); ?></p>
                <p><strong>Hourly Rate:</strong> KSh <?php echo htmlspecialchars($listing['hourly_rate']); ?></p>
                <p><strong>Availability:</strong> From <?php echo htmlspecialchars($listing['available_from']); ?> to <?php echo htmlspecialchars($listing['available_to']); ?></p>
                <?php if ($listing['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($listing['profile_picture']); ?>" alt="<?php echo htmlspecialchars($listing['name']); ?>" class="img-fluid">
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <!-- Average Rating -->
                <h4>Average Rating: <?php echo $avg_rating ? number_format($avg_rating, 1) . ' stars' : 'No ratings yet'; ?></h4>

                <!-- Reviews Section -->
                <h4>Reviews</h4>
                <?php if ($reviews): ?>
                    <ul class="list-group">
                        <?php foreach ($reviews as $review): ?>
                            <li class="list-group-item">
                                <p><strong><?php echo htmlspecialchars($review['username']); ?></strong> - <?php echo htmlspecialchars($review['rating']); ?> Stars</p>
                                <p><?php echo htmlspecialchars($review['comment']); ?></p>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No reviews yet. Be the first to leave a review!</p>
                <?php endif; ?>

                <div class="mt-4">
                    <h5>Leave a Review</h5>
                    <?php if ($existing_review): ?>
                        <p>You have already submitted a review for this operator.</p>
                    <?php else: ?>
                        <?php if ($errors): ?>
                            <div class="error">
                                <ul>
                                    <?php foreach ($errors as $error): ?>
                                        <li><?php echo htmlspecialchars($error); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="form-group mb-3">
                                <label for="rating" class="form-label">Rating</label>
                                <select name="rating" id="rating" class="form-control" required>
                                    <option value="">Select Rating</option>
                                    <option value="1">1 Star</option>
                                    <option value="2">2 Stars</option>
                                    <option value="3">3 Stars</option>
                                    <option value="4">4 Stars</option>
                                    <option value="5">5 Stars</option>
                                </select>
                            </div>
                            <div class="form-group mb-3">
                                <label for="comment" class="form-label">Your Review</label>
                                <textarea name="comment" id="comment" rows="4" class="form-control" placeholder="Write your review here..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Submit Review</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>