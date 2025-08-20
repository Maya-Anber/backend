<?php
session_start();
require_once __DIR__ . '/../_inc/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION["user_id"])) {
    $user_id = $_SESSION["user_id"];
    $title = $_POST["title"];
    $author = $_POST["author"];
    $genre = $_POST["genre"];
    $publication_year = $_POST["publication_year"];
    $description = $_POST["description"];
    $condition_rating = $_POST["condition_rating"];
    $availability_status = $_POST["availability_status"];

    // Insert into books table
    $stmt_books = $conn->prepare("INSERT INTO books (title, author, genre, publication_year, description) VALUES (?, ?, ?, ?, ?)");
    $stmt_books->bind_param("sssis", $title, $author, $genre, $publication_year, $description);

    if ($stmt_books->execute()) {
        $book_id = $conn->insert_id; // Get the newly inserted book's ID

        // Insert into book_listings table
        $stmt_listings = $conn->prepare("INSERT INTO book_listings (book_id, user_id, condition_rating, availability_status) VALUES (?, ?, ?, ?)");
        $stmt_listings->bind_param("iiss", $book_id, $user_id, $condition_rating, $availability_status);

        if ($stmt_listings->execute()) {
            // Redirect to home page or display success message
            header("Location: /backend/list/index.php");
            exit();
        } else {
            echo "Error adding to book_listings: " . $stmt_listings->error;
        }
    } else {
        echo "Error adding to books: " . $stmt_books->error;
    }

    $stmt_books->close();
    $stmt_listings->close();
} else {
    // Redirect if not logged in or accessed directly
    header("Location: ../security/login.html");
    exit();
}

$conn->close();
?>