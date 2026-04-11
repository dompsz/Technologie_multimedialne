<?php
require_once 'db_config.php';

$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: index.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM podstrony WHERE slug = ? AND status = 'opublikowany'");
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    die("Podstrona nie istnieje lub nie jest dostępna.");
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($page['tytul']); ?> - CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-dark bg-dark border-bottom border-secondary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">CMS Lab 16</a>
        </div>
    </nav>

    <div class="container">
        <div class="card bg-secondary text-white p-4">
            <h1><?php echo htmlspecialchars($page['tytul']); ?></h1>
            <hr>
            <div class="content mt-3">
                <?php echo $page['tresc']; ?>
            </div>
            <div class="mt-4">
                <a href="index.php" class="btn btn-outline-light">Powrót do strony głównej</a>
            </div>
        </div>
    </div>
</body>
</html>
