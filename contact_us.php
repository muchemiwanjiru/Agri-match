<?php
session_start();



$conn = new mysqli("localhost", "root", "", "agrimatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $message = $_POST['message'] ?? '';
    $phone = $_POST['phone'] ?? '';

    // Validate inputs
    if (empty($name)) $errors[] = "Name is required";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required";
    if (empty($message)) $errors[] = "Message is required";

    if (empty($errors)) {
        // Here you would typically store the message in a database or send an email
        // For this example, we'll just simulate success
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, phone, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $phone, $message);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Your message has been sent successfully!";
            $success = true;
        } else {
            $errors[] = "Failed to send message";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Agri-Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; }
        .container { max-width: 800px; }
        .error { color: red; }
        .success { color: green; }
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
    <div class="container py-5">
        <h2 class="text-center text-primary mb-4">Get in Touch</h2>
        <p class="text-center text-muted mb-5">Have any questions or feedback? We'd love to hear from you!</p>

        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="shadow-lg p-4 rounded bg-white">
                    <?php if ($success): ?>
                        <div class="success mb-3"><?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?></div>
                    <?php endif; ?>
                    <?php if ($errors): ?>
                        <div class="error mb-3">
                            <ul>
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo htmlspecialchars($error); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    <form method="POST" class="<?php echo $success ? 'd-none' : ''; ?>">
                        <div class="form-group mb-4">
                            <label for="name" class="text-dark font-weight-bold">Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-lg rounded-pill" 
                                   placeholder="Enter your full name" value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                        </div>
                        <div class="form-group mb-4">
                            <label for="email" class="text-dark font-weight-bold">Email</label>
                            <input type="email" name="email" id="email" class="form-control form-control-lg rounded-pill" 
                                   placeholder="Enter your email address" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                        </div>
                        <div class="form-group mb-4">
                            <label for="message" class="text-dark font-weight-bold">Message</label>
                            <textarea name="message" id="message" class="form-control form-control-lg rounded" rows="5" 
                                      placeholder="Write your message here..." required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        <div class="form-group mb-4">
                            <label for="phone" class="text-dark font-weight-bold">Phone <span class="text-muted">(optional)</span></label>
                            <input type="tel" name="phone" id="phone" class="form-control form-control-lg rounded-pill" 
                                   placeholder="Enter your phone number" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block rounded-pill">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>