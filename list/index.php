<?php
session_start();
require_once __DIR__ . '/../_inc/db.php';
// Read filters from query params
$genre = isset($_GET['genre']) ? trim($_GET['genre']) : '';
$author = isset($_GET['author']) ? trim($_GET['author']) : '';
$availability = isset($_GET['availability']) ? trim($_GET['availability']) : '';

// Normalize availability filter
$allowedAvailability = ['available', 'pending', 'exchanged'];
if ($availability !== '' && !in_array($availability, $allowedAvailability, true)) {
    $availability = '';
}

// Fetch genres for dropdown
$genres = [];
if ($conn) {
    if ($res = $conn->query("SELECT DISTINCT genre FROM books WHERE genre IS NOT NULL AND genre <> '' ORDER BY genre ASC")) {
        while ($row = $res->fetch_assoc()) {
            $genres[] = $row['genre'];
        }
        $res->free();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Exchange - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/backend/assets/theme.css">
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

    <!-- Filters -->
    <div class="container mt-4">
        <form method="get" class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label for="genre" class="form-label">Genre</label>
                <select name="genre" id="genre" class="form-select">
                    <option value="">All genres</option>
                    <?php foreach ($genres as $g): ?>
                        <option value="<?php echo htmlspecialchars($g); ?>" <?php echo ($genre === $g ? 'selected' : ''); ?>><?php echo htmlspecialchars($g); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-4">
                <label for="author" class="form-label">Author</label>
                <input type="text" name="author" id="author" value="<?php echo htmlspecialchars($author); ?>" class="form-control" placeholder="e.g. George Orwell">
            </div>
            <div class="col-12 col-md-3">
                <label for="availability" class="form-label">Availability</label>
                <select name="availability" id="availability" class="form-select">
                    <?php
                    $availOptions = ['' => 'Available only (default)', 'available' => 'Available', 'pending' => 'Pending', 'exchanged' => 'Exchanged'];
                    foreach ($availOptions as $val => $label) {
                        $sel = ($availability === $val) ? 'selected' : '';
                        // Default UI shows "Available only" when no explicit filter chosen
                        if ($val === '' && $availability === '') $sel = 'selected';
                        echo '<option value="' . htmlspecialchars($val) . '" ' . $sel . '>' . htmlspecialchars($label) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="col-12 col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>
        </form>
    </div>

    <div class="container my-5">
        <!-- My Books Section -->
        <h2 class="mb-4">My Books</h2>
        <div class="row g-4">
            <?php
            if (isset($_SESSION["user_id"])) {
                $user_id = $_SESSION["user_id"];

                // Build dynamic query based on filters
                $query = "SELECT b.book_id, b.title, b.author, b.genre, b.publication_year, b.description
                          FROM book_listings bl
                          INNER JOIN books b ON bl.book_id = b.book_id
                          WHERE bl.user_id = ?";
                $types = "i";
                $params = [$user_id];

                if ($genre !== '') {
                    $query .= " AND b.genre = ?";
                    $types .= "s";
                    $params[] = $genre;
                }
                if ($author !== '') {
                    $query .= " AND b.author LIKE ?";
                    $types .= "s";
                    $params[] = "%" . $author . "%";
                }
                if ($availability !== '') {
                    $query .= " AND bl.availability_status = ?";
                    $types .= "s";
                    $params[] = $availability;
                }

                $stmt = $conn->prepare($query);
                if ($stmt === false) {
                    echo '<p class="text-danger">Failed to prepare statement.</p>';
                } else {
                    $stmt->bind_param($types, ...$params);
                }
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
            // Browse listings (default to available unless a specific availability filter is selected)
            $queryAll = "SELECT b.book_id, b.title, b.author, b.genre, b.publication_year, b.description, bl.availability_status
                         FROM book_listings bl
                         INNER JOIN books b ON bl.book_id = b.book_id";

            $typesAll = "";
            $paramsAll = [];
            $conds = [];

            $availabilityForAll = ($availability !== '') ? $availability : 'available';
            $conds[] = "bl.availability_status = ?";
            $typesAll .= "s";
            $paramsAll[] = $availabilityForAll;

            if ($genre !== '') {
                $conds[] = "b.genre = ?";
                $typesAll .= "s";
                $paramsAll[] = $genre;
            }
            if ($author !== '') {
                $conds[] = "b.author LIKE ?";
                $typesAll .= "s";
                $paramsAll[] = "%" . $author . "%";
            }

            if (count($conds) > 0) {
                $queryAll .= " WHERE " . implode(" AND ", $conds);
            }

            $stmt_all = $conn->prepare($queryAll);
            if ($stmt_all === false) {
                echo '<p class="text-danger">Failed to prepare statement.</p>';
                $result_all = false;
            } else {
                $stmt_all->bind_param($typesAll, ...$paramsAll);
                $stmt_all->execute();
                $result_all = $stmt_all->get_result();
            }

            if ($result_all && $result_all->num_rows > 0) {
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
                    echo '<span class="badge bg-secondary">' . htmlspecialchars($row["availability_status"]) . '</span>';
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