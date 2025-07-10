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

if (isset($_GET['id']) && isset($_GET['type'])) {
    $wishlist_id = $_GET['id'];
    $type = $_GET['type']; // 'tractor' or 'operator'
    $user_id = $_SESSION['user_id'];

    $conn->begin_transaction();
    try {
        if ($type === 'tractor') {
            // Fetch tractor_id from wishlist
            $sql = "SELECT tractor_id FROM wishlist WHERE id=? AND user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $wishlist_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $item_id = $row['tractor_id'];

                // Delete from wishlist
                $sql = "DELETE FROM wishlist WHERE id=? AND user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $wishlist_id, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error removing from wishlist: " . $stmt->error);
                }

                // Delete from bookings
                $sql = "DELETE FROM bookings WHERE user_id=? AND tractor_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error removing from bookings: " . $stmt->error);
                }

                // Delete from booked_tractor (optional, if you want to keep history, skip this)
                $sql = "DELETE FROM booked_tractor WHERE user_id=? AND tractor_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error removing from booked_tractor: " . $stmt->error);
                }

                // Update tractor status to 'available'
                $sql = "UPDATE tractors SET status='available' WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating tractor status: " . $stmt->error);
                }

                $conn->commit();
                $_SESSION['message'] = "Tractor removed from wishlist and booking canceled!";
            } else {
                throw new Exception("Wishlist item not found or not owned by you.");
            }
        } elseif ($type === 'operator') {
            // Fetch operator_id from operator_wishlist
            $sql = "SELECT operator_id FROM operator_wishlist WHERE id=? AND user_id=?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $wishlist_id, $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $item_id = $row['operator_id'];

                // Delete from operator_wishlist
                $sql = "DELETE FROM operator_wishlist WHERE id=? AND user_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $wishlist_id, $user_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error removing from operator wishlist: " . $stmt->error);
                }

                // Delete from operator_bookings
                $sql = "DELETE FROM operator_bookings WHERE user_id=? AND operator_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error removing from operator bookings: " . $stmt->error);
                }

                // Delete from booked_operator (optional, if you want to keep history, skip this)
                $sql = "DELETE FROM booked_operator WHERE user_id=? AND operator_id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error removing from booked_operator: " . $stmt->error);
                }

                // Update operator status to 'available'
                $sql = "UPDATE operators SET status='available' WHERE id=?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $item_id);
                if (!$stmt->execute()) {
                    throw new Exception("Error updating operator status: " . $stmt->error);
                }

                $conn->commit();
                $_SESSION['message'] = "Operator removed from wishlist and booking canceled!";
            } else {
                throw new Exception("Wishlist item not found or not owned by you.");
            }
        } else {
            throw new Exception("Invalid type specified.");
        }
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error'] = $e->getMessage();
    }
    $stmt->close();
}

$conn->close();
// No redirect to stay on the calling page (e.g., book_tractor.php or book_operator.php)
?>