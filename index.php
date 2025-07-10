<?php
session_start();

$conn = new mysqli("localhost", "root", "", "agrimatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch featured listings (assuming a generic listings table or machinery_listings for this example)
$stmt = $conn->prepare("SELECT id, make AS title, description, price_per_day AS price, image FROM machinery_listings LIMIT 3");
$stmt->execute();
$listings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Agri-Match</title>
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
        .hero-section {
            background-color: #28a745;
        }
        .carousel-item img {
            height: 500px;
            object-fit: cover;
        }
        .carousel-caption {
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .dropdown-menu {
            background-color: #28a745;
        }
        .dropdown-item {
            color: white;
        }
        .dropdown-item:hover {
            background-color: #218838;
            color: white;
        }
        footer {
            background-color: #343a40;
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
                    <li class="nav-item"><a class="nav-link active" href="user_dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="add_review.php">Review</a></li>
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

    <!-- Messages (if any) -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="container mt-3">
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo htmlspecialchars($_SESSION['message']); 
                unset($_SESSION['message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <section class="hero bg-success text-white text-center py-5 hero-section">
        <div class="container">
            <img src="images/logo.png" alt="Agri-Match Logo" width="150" class="mb-3">
            <h1 class="display-4 font-weight-bold">Welcome to Agri-Match</h1>
            <p class="lead">Empowering farmers and operators with innovative solutions for agricultural success.</p>
            <a class="btn btn-light btn-lg mt-3 shadow" href="register.php">Get started</a>
        </div>
    </section>

    <!-- Carousel Section -->
    <section id="heroCarousel" class="carousel slide mt-5" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-label="Slide 1"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
        </div>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img src="images/carousel1.jpg" class="d-block w-100" alt="Revolutionizing Farming">
                <div class="carousel-caption">
                    <h5>Revolutionizing Farming</h5>
                    <p>Explore a variety of agricultural solutions to grow your farm.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/carousel2.jpg" class="d-block w-100" alt="Skilled Operators On Demand">
                <div class="carousel-caption">
                    <h5>Skilled Operators On Demand</h5>
                    <p>Hire experts to maximize your productivity.</p>
                </div>
            </div>
            <div class="carousel-item">
                <img src="images/carousel3.jpg" class="d-block w-100" alt="Collaborating for Success">
                <div class="carousel-caption">
                    <h5>Collaborating for Success</h5>
                    <p>Join a network of farmers and service providers working together.</p>
                </div>
            </div>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev" aria-label="Previous Slide">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next" aria-label="Next Slide">
            <span class="carousel-control-next-icon"></span>
        </button>
    </section>

    <!-- Featured Listings Section -->
    <section class="featured-listings py-5 bg-light">
        <div class="container text-center">
            <h2 class="mb-5">Featured Listings</h2>
            <div class="row">
                <?php if ($listings): ?>
                    <?php foreach ($listings as $listing): ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card shadow-sm border-0">
                                <img src="<?php echo $listing['image'] ? htmlspecialchars($listing['image']) : 'images/default_image.png'; ?>" 
                                     class="card-img-top" alt="<?php echo htmlspecialchars($listing['title']); ?>">
                                <div class="card-body">
                                    <h5 class="card-title"><?php echo htmlspecialchars($listing['title']); ?></h5>
                                    <p class="card-text text-truncate"><?php echo htmlspecialchars(substr($listing['description'], 0, 45)) . (strlen($listing['description']) > 45 ? '...' : ''); ?></p>
                                    <p class="card-text"><strong>KSh <?php echo htmlspecialchars($listing['price']); ?></strong></p>
                                    <a href="machinery_listing.php?id=<?php echo $listing['id']; ?>" class="btn btn-primary btn-sm">View Details</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted">No listings available right now. Check back later!</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- How it Works Section -->
    <section class="how-it-works py-5 text-center">
        <div class="container">
            <h2 class="mb-5">How It Works</h2>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5>Create Your Listing</h5>
                            <p>Post your machinery or operator services to reach a wider audience.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5>Browse Listings</h5>
                            <p>Find the tools or services you need with ease.</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h5>Rent or Hire</h5>
                            <p>Get the equipment or expertise to achieve your goals.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials py-5 bg-dark text-white text-center">
        <div class="container">
            <h2 class="mb-5">What Our Users Say</h2>
            <div class="row">
                <div class="col-lg-4">
                    <div class="card bg-light text-dark shadow-sm">
                        <div class="card-body">
                            <p>"Agri-Match helped me find the perfect tractor. Highly recommended!"</p>
                            <footer class="blockquote-footer">Samuel Karanja</footer>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card bg-light text-dark shadow-sm">
                        <div class="card-body">
                            <p>"The platform made it easy to hire skilled operators. My farm is thriving!"</p>
                            <footer class="blockquote-footer">Cynthia Murugi</footer>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card bg-light text-dark shadow-sm">
                        <div class="card-body">
                            <p>"A game changer for connecting farmers with vital services."</p>
                            <footer class="blockquote-footer">Johnson Kamau</footer>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase mb-3">About Agri-Match</h5>
                    <p>Connecting you with the best machinery and operators in agriculture, empowering sustainable farming solutions.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="dashboard.php" class="text-white text-decoration-none">Home</a></li>
                        <li><a href="about_us.php" class="text-white text-decoration-none">About Us</a></li>
                        <li><a href="contact_us.php" class="text-white text-decoration-none">Contact Us</a></li>
                        <li><a href="wishlist.php" class="text-white text-decoration-none">Wishlist</a></li>
                        <li><a href="logout.php" class="text-white text-decoration-none">Logout</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5 class="text-uppercase mb-3">Contact Us</h5>
                    <p>Phone: <a href="tel:+254713809495" class="text-white">+254 713809495</a></p>
                    <p>Email: <a href="mailto:jaelasap18@gmail.com" class="text-white">jaelasap18@gmail.com</a></p>
                </div>
            </div>
            <div class="row mt-4 text-center">
                <div class="col-12">
                    <img src="images/logo.png" alt="Agri-Match Logo" width="150">
                </div>
            </div>
            <div class="row mt-4">
                <div class="col-md-6">
                    <p class="small">© 2024 Agri-Match | All Rights Reserved</p>
                    <p class="small">Connecting Agricultural Services for a Sustainable Future</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <h5 class="text-uppercase mb-3">Follow Us</h5>
                    <div class="social-icons">
                        <a href="#" class="text-white mx-2"><i class="fab fa-facebook fa-2x"></i></a>
                        <a href="#" class="text-white mx-2"><i class="fab fa-twitter fa-2x"></i></a>
                        <a href="#" class="text-white mx-2"><i class="fab fa-instagram fa-2x"></i></a>
                        <a href="#" class="text-white mx-2"><i class="fab fa-linkedin fa-2x"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>