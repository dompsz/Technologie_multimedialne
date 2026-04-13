<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

// Pobierz tematy
$stmt = $conn->query("SELECT * FROM tematy");
$tematy = $stmt->fetchAll();

$user_role = $_SESSION['lab17_role'] ?? 0;
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Forum Dyskusyjne - Lab 17</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .topic-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; transition: transform 0.2s; margin-bottom: 20px; }
        .topic-card:hover { transform: translateY(-3px); border-color: var(--accent-color); }
        .text-accent { color: var(--accent-color) !important; }
        .forum-header { background: rgba(0,0,0,0.5); padding: 40px 0; border-bottom: 2px solid var(--accent-color); margin-bottom: 40px; }
    </style>
</head>
<body class="bg-dark text-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand fw-bold text-accent" href="index.php">💬 FORUM LAB 17</a>
            <div class="d-flex align-items-center">
                <?php if (isset($_SESSION['lab17_user_id'])): ?>
                    <span class="text-secondary me-3">Witaj, <strong><?php echo htmlspecialchars($_SESSION['lab17_login']); ?></strong> (<?php echo getRoleLabel($user_role); ?>)</span>
                    <?php if ($user_role >= 2): ?>
                        <a href="admin.php" class="btn btn-outline-warning btn-sm me-2">🛡️ Moderacja</a>
                    <?php endif; ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline-success btn-sm me-2">Zaloguj się</a>
                    <a href="register.php" class="btn btn-accent btn-sm">Rejestracja</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="forum-header text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Forum Dyskusyjne</h1>
            <p class="lead text-secondary">Miejsce do wymiany wiedzy i doświadczeń.</p>
        </div>
    </div>

    <div class="container pb-5">
        <div class="row">
            <div class="col-md-9">
                <h3 class="mb-4">Kategorie Forum</h3>
                <?php foreach ($tematy as $t): 
                    // Statystyki dla tematu
                    $stmt_stats = $conn->prepare("SELECT COUNT(*) as cnt FROM watki WHERE idt = ? AND id_rodzic IS NULL");
                    $stmt_stats->execute([$t['idt']]);
                    $threads_count = $stmt_stats->fetch()['cnt'];
                ?>
                    <div class="topic-card p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h4 class="mb-1"><a href="topic.php?id=<?php echo $t['idt']; ?>" class="text-accent text-decoration-none"><?php echo htmlspecialchars($t['nazwa_tematu']); ?></a></h4>
                                <p class="text-secondary mb-0"><?php echo htmlspecialchars($t['opis']); ?></p>
                            </div>
                            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                <span class="badge bg-secondary"><?php echo $threads_count; ?> wątków</span>
                                <a href="topic.php?id=<?php echo $t['idt']; ?>" class="btn btn-sm btn-outline-light ms-2">Przeglądaj</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="col-md-3">
                <div class="card bg-dark text-light border-secondary p-3">
                    <h5>Statystyki Forum</h5>
                    <hr class="border-secondary">
                    <?php
                        $total_users = $conn->query("SELECT COUNT(*) FROM uzytkownicy")->fetchColumn();
                        $total_posts = $conn->query("SELECT COUNT(*) FROM watki")->fetchColumn();
                    ?>
                    <div class="d-flex justify-content-between mb-2">
                        <small>Użytkownicy:</small>
                        <span class="fw-bold"><?php echo $total_users; ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small>Posty:</small>
                        <span class="fw-bold"><?php echo $total_posts; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center py-4 mt-5 border-top border-secondary text-secondary">
        <div class="container">
            <small>&copy; 2026 Laboratorium 17 - System Forum Dyskusyjnego</small>
            <br><a href="../index.php" class="text-secondary">Powrót do Strony Głównej</a>
        </div>
    </footer>
</body>
</html>
