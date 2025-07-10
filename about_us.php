<?php
session_start();



$conn = new mysqli("localhost", "root", "", "agrimatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Agri-Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 1200px; }
        .card-img-top { height: 200px; object-fit: cover; }
        .fa-3x { margin-bottom: 15px; }
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
        <!-- Section: Introduction -->
        <div class="text-center">
            <h1 class="display-4">About Agri-Match</h1>
            <p class="lead">Connecting Agricultural Services and Products for a Sustainable Future</p>
        </div>

        <!-- Section: Mission and Vision -->
        <div class="row mt-5">
            <div class="col-md-6">
                <h2 class="h3">Our Mission</h2>
                <p>At Agri-Match, we aim to bridge the gap between agricultural machinery operators and those who need them, offering a seamless marketplace where farmers, businesses, and individuals can rent, hire, and collaborate. Our mission is driven by the passion to support agriculture and help create solutions for Zero Hunger (SDG 2).</p>
            </div>
            <div class="col-md-6">
                <h2 class="h3">Our Vision</h2>
                <p>We envision a world where agriculture thrives, where technology connects people and products, and where agricultural innovation is accessible to all. We strive to be a catalyst for positive change in the agricultural industry by providing easy-to-use platforms for renting machinery and hiring operators.</p>
            </div>
        </div>

        <!-- Section: Our Values -->
        <div class="mt-5">
            <h3 class="text-center">Our Core Values</h3>
            <div class="row">
                <div class="col-md-4 text-center">
                    <i class="fas fa-seedling fa-3x text-success"></i>
                    <h4>Sustainability</h4>
                    <p>We are committed to supporting sustainable farming practices and reducing waste by providing efficient agricultural resources.</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-users fa-3x text-primary"></i>
                    <h4>Community</h4>
                    <p>We believe in the power of collaboration and community. Our platform fosters partnerships between operators and clients.</p>
                </div>
                <div class="col-md-4 text-center">
                    <i class="fas fa-cogs fa-3x text-warning"></i>
                    <h4>Innovation</h4>
                    <p>We embrace technological innovation, providing farmers with the tools and machinery they need to increase productivity.</p>
                </div>
            </div>
        </div>

        <!-- Section: Our Team -->
        <div class="mt-5">
            <h3 class="text-center">Meet Our Team</h3>
            <div class="row">
                <div class="col-md-4">
                    <div class="card">
                        <img src="images/githubporfolioavatar.jpg" class="card-img-top" alt="Team Member 1">
                        <div class="card-body">
                            <h5 class="card-title">Annabel Amondi</h5>
                            <p class="card-text">Founder & CEO</p>
                            <p class="card-text">Annabel is passionate about empowering farmers and revolutionizing agricultural practices through technology. He leads Agri-Match with a vision to change the future of farming.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="images/tech.jpg" class="card-img-top" alt="Team Member 2">
                        <div class="card-body">
                            <h5 class="card-title">David Ngelechei</h5>
                            <p class="card-text">COO</p>
                            <p class="card-text">David works tirelessly to ensure that Agri-Match provides the best services to users and operators, and that our platform is user-friendly and efficient.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="images/image_zVvOaBT7_1729321786947_512.jpg" class="card-img-top" alt="Team Member 3">
                        <div class="card-body">
                            <h5 class="card-title">Jessica Jael</h5>
                            <p class="card-text">CTO</p>
                            <p class="card-text">Jael oversees all technical aspects of Agri-Match, ensuring the platform is innovative, reliable, and scalable to meet the needs of the agricultural industry.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Call to Action -->
        <div class="mt-5 text-center">
            <h3>Join Us in Transforming Agriculture</h3>
            <p>Become part of the Agri-Match community and help us achieve a world with better, more sustainable agricultural practices. Whether you're an operator or a farmer, there is a place for you with us!</p>
            <a href="signup.php" class="btn btn-primary btn-lg">Sign Up Now</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>