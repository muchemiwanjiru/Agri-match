<?php
session_start();

// Ensure the user is logged in and has the 'rentee' role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'rentee') {
    header("Location: login.php");
    exit();
}

// Database connection
$conn = new mysqli("localhost", "root", "", "agrimatch");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tractor_id = $_POST['tractor_id'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    $comment = $_POST['comment'] ?? '';
    $user_id = $_SESSION['user_id'];

    // Validate inputs
    if (!is_numeric($tractor_id) || !is_numeric($rating) || $rating < 1 || $rating > 5 || empty($comment)) {
        $_SESSION['error'] = "Invalid input. Rating must be 1-5, and comment is required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO reviews (tractor_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
        if ($stmt === false) {
            $_SESSION['error'] = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("iiis", $tractor_id, $user_id, $rating, $comment);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Review submitted successfully!";
            } else {
                $_SESSION['error'] = "Error submitting review: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    header("Location: book_tractor.php");
    exit();
}

// Fetch tractor details
$tractor_id = $_GET['tractor_id'] ?? 0;
if (!is_numeric($tractor_id) || $tractor_id <= 0) {
    $tractor = null;
} else {
    $stmt = $conn->prepare("SELECT name, model FROM tractors WHERE id = ?");
    $stmt->bind_param("i", $tractor_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $tractor = $result->fetch_assoc();
    } else {
        $tractor = null; // No tractor found
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Review - Agri-Match</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h2 {
            color: #28a745;
            margin-bottom: 20px;
        }
        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if ($tractor): ?>
            <h2>Add Review for <?php echo htmlspecialchars($tractor['name'] . ' ' . $tractor['model']); ?></h2>
            <form method="POST">
                <input type="hidden" name="tractor_id" value="<?php echo htmlspecialchars($tractor_id); ?>">
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating (1 to 5):</label>
                    <select name="rating" id="rating" class="form-control" required>
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="comment" class="form-label">Comment:</label>
                    <textarea name="comment" id="comment" class="form-control" rows="4" required></textarea>
                </div>
                <button type="submit" class="btn btn-success">Submit Review</button>
                <a href="book_tractor.php" class="btn btn-secondary ms-2">Back to Booking</a>
            </form>
        <?php else: ?>
            <div class="alert alert-warning">Tractor not found.</div>
            <a href="book_tractor.php" class="btn btn-secondary">Back to Booking</a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>