<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/backend/assets/theme.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.html">📚 Book Exchange</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav" data-dyn-nav></div>
        </div>
    </nav>

    <!-- Book Details -->
    <div class="container my-5">
        <?php
        session_start();
        require_once __DIR__ . '/../_inc/db.php';

        // Check if book_id is set in the GET request
        if (isset($_GET['book_id'])) {
            $book_id = $_GET['book_id'];

            // Fetch book details from the database
            $stmt = $conn->prepare("SELECT b.title, b.author, b.genre, b.publication_year, b.description, bl.user_id AS owner_id
                                    FROM books b
                                    JOIN book_listings bl ON b.book_id = bl.book_id
                                    WHERE b.book_id = ?");
            $stmt->bind_param("i", $book_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $title = htmlspecialchars($row['title']);
                $author = htmlspecialchars($row['author']);
                $genre = htmlspecialchars($row['genre']);
                $publication_year = htmlspecialchars($row['publication_year']);
                $description = htmlspecialchars($row['description']);
                $owner_id = intval($row['owner_id']);

                echo '<div class="row">';
                echo '<div class="col-md-4">';
                echo '</div>';
                echo '<div class="col-md-8">';
                echo '<h2>' . $title . '</h2>';
                echo '<p class="text-muted">by ' . $author . '</p>';
                echo '<p>' . $description . '</p>';

                // Check if user is logged in and is not the owner
                if (isset($_SESSION['user_id']) && $_SESSION['user_id'] != $owner_id) {
                    echo '<a href="request_exchange.html?book_id=' . $book_id . '" class="btn btn-success">Exchange this Book</a>';
                }

                echo '</div>';
                echo '</div>';
            } else {
                echo '<p class="text-muted">Book not found.</p>';
            }
        } else {
            echo '<p class="text-muted">Book ID not provided.</p>';
        }
        ?>
    </div>

    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; 2025 Book Exchange. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/nav.js"></script>
</body>
</html>
