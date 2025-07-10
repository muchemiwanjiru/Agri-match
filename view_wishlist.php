<?php
session_start();

// Ensure the user is logged in and has the appropriate role
if (!isset($_SESSION['user_id']) || (!in_array($_SESSION['role'], ['admin', 'renter', 'rentee']))) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "agrimatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch wishlist items for the logged-in user
$user_id = $_SESSION['user_id'];
$sql_wishlist = "SELECT wishlist.*, tractors.name, tractors.model, tractors.capability, tractors.description, tractors.image 
                 FROM wishlist 
                 JOIN tractors ON wishlist.tractor_id = tractors.id 
                 WHERE wishlist.user_id='$user_id'";
$result_wishlist = $conn->query($sql_wishlist);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Wishlist - Agri-Match</title>
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
        .wishlist-container {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px 0;
        }
        .wishlist-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px;
            width: 100%;
            max-width: 300px;
            text-align: center;
            transition: transform 0.2s;
        }
        .wishlist-card:hover {
            transform: scale(1.05);
        }
        .wishlist-card img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .wishlist-card h3 {
            color: #28a745;
            margin: 10px 0;
            font-size: 1.25rem;
        }
        .wishlist-card p {
            margin: 5px 0;
            font-size: 0.9rem;
        }
        .action-buttons .btn {
            margin-top: 10px;
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
            <a class="navbar-brand" href="dashboard.php">
                <img src="images/logo.png" alt="Agri-Match Logo" width="150">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="dashboard.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="about_us.php">About Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="contact_us.php">Contact Us</a></li>
                    <li class="nav-item"><a class="nav-link" href="view_wishlist.php">Wishlist</a></li>
   
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
        <h1>View Wishlist</h1>
        <h2>Your Wishlist</h2>
        <div class="wishlist-container">
            <?php
            if ($result_wishlist->num_rows > 0) {
                while ($row = $result_wishlist->fetch_assoc()) {
                    echo "<div class='wishlist-card'>
                            <img src='" . htmlspecialchars($row['image']) . "' alt='Tractor Image'>
                            <h3>" . htmlspecialchars($row['name']) . "</h3>
                            <p><strong>Model:</strong> " . htmlspecialchars($row['model']) . "</p>
                            <p><strong>Capability:</strong> " . htmlspecialchars($row['capability']) . "</p>
                            <p><strong>Description:</strong> " . htmlspecialchars($row['description']) . "</p>
                            <p><strong>Start Date:</strong> " . htmlspecialchars($row['start_date']) . "</p>
                            <p><strong>End Date:</strong> " . htmlspecialchars($row['end_date']) . "</p>
                            <p><strong>Amount:</strong> " . htmlspecialchars($row['amount']) . "</p>
                            <div class='action-buttons'>
                                <a href='remove_from_wishlist.php?id=" . htmlspecialchars($row['id']) . "' class='btn btn-danger btn-sm'>Remove</a>
                            </div>
                          </div>";
                }
            } else {
                echo "<p class='text-muted text-center w-100'>Your wishlist is empty.</p>";
            }
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>