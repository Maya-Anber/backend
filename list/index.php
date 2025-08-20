<?php
session_start();
require_once __DIR__ . '/../_inc/db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Exchange - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.html">📚 Book Exchange</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav" data-dyn-nav></div>
        </div>
    </nav>

    <div class="container my-5">
        <!-- My Books Section -->
        <h2 class="mb-4">My Books</h2>
        <div class="row g-4">
            <?php
            if (isset($_SESSION["user_id"])) {
                $user_id = $_SESSION["user_id"];

                $stmt = $conn->prepare("SELECT b.book_id, b.title, b.author, b.genre, b.publication_year, b.description 
                                        FROM book_listings bl
                                        INNER JOIN books b ON bl.book_id = b.book_id
                                        WHERE bl.user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo '<div class="col-md-4">';
                        echo '<a href="book-details.php?book_id=' . $row["book_id"] . '" style="text-decoration: none; color: inherit;">';
                        echo '<div class="card h-100 shadow-sm">';
                        echo '<div class="card-body">';
                        echo '<h5 class="card-title">' . htmlspecialchars($row["title"]) . '</h5>';
                        echo '<h6 class="card-subtitle mb-2 text-muted">by ' . htmlspecialchars($row["author"]) . '</h6>';
                        echo '<p><strong>Genre:</strong> ' . htmlspecialchars($row["genre"]) . '</p>';
                        echo '<p><strong>Year:</strong> ' . htmlspecialchars($row["publication_year"]) . '</p>';
                        echo '<p class="card-text">' . htmlspecialchars($row["description"]) . '</p>';
                        echo '</div>';
                        echo '</div>';
                        echo '</a>';
                        echo '</div>';
                    }
                } else {
                    echo '<p class="text-muted">No books available for you yet.</p>';
                }
            } else {
                echo '<p class="text-muted">Please log in to see your books.</p>';
            }
            ?>
        </div>

        <!-- Available Books Section -->
        <h2 class="mb-4 mt-5">Available Books</h2>
        <div class="row g-4">
            <?php
            // Fetch all books
            $stmt_all = $conn->prepare("SELECT b.book_id, b.title, b.author, b.genre, b.publication_year, b.description
                                        FROM books b");
            $stmt_all->execute();
            $result_all = $stmt_all->get_result();

            if ($result_all->num_rows > 0) {
                while ($row = $result_all->fetch_assoc()) {
                    echo '<div class="col-md-4">';
                    echo '<a href="book-details.php?book_id=' . $row["book_id"] . '" style="text-decoration: none; color: inherit;">';
                    echo '<div class="card h-100 shadow-sm">';
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($row["title"]) . '</h5>';
                    echo '<h6 class="card-subtitle mb-2 text-muted">by ' . htmlspecialchars($row["author"]) . '</h6>';
                    echo '<p><strong>Genre:</strong> ' . htmlspecialchars($row["genre"]) . '</p>';
                    echo '<p><strong>Year:</strong> ' . htmlspecialchars($row["publication_year"]) . '</p>';
                    echo '<p class="card-text">' . htmlspecialchars($row["description"]) . '</p>';
                    echo '</div>';
                    echo '</div>';
                    echo '</a>';
                    echo '</div>';
                }
            } else {
                echo '<p class="text-muted">No books available.</p>';
            }
            ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-3 mt-5">
        © 2025 Book Exchange. All rights reserved.
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/nav.js"></script>
</body>
</html>