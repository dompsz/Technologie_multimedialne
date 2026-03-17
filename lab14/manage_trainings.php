<?php
session_start();
require_once 'db_config.php';

// Zabezpieczenie
if (!isset($_SESSION['lab14_user_id']) || !in_array(($_SESSION['lab14_role'] ?? ''), ['admin', 'coach'])) {
    die("Brak uprawnień.");
}

// Obsługa usuwania
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM testy WHERE id_testu = ?");
    $stmt->execute([$id]);
    header("Location: manage_trainings.php?msg=deleted");
    exit();
}

// Pobierz wszystkie szkolenia
$stmt = $conn->query("SELECT * FROM testy ORDER BY id_testu DESC");
$testy = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zarządzaj Szkoleniami - Lab 14</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark text-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>🛠️ Zarządzanie Szkoleniami</h2>
            <div>
                <a href="edit_training.php" class="btn btn-success">➕ Dodaj Nowe Szkolenie</a>
                <a href="coach.php" class="btn btn-secondary">Powrót do Panelu</a>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div class="alert alert-success">Szkolenie zostało usunięte.</div>
        <?php endif; ?>

        <div class="card bg-dark text-light border-secondary">
            <div class="table-responsive">
                <table class="table table-dark table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nazwa Szkolenia</th>
                            <th>Czas (sek)</th>
                            <th>Akcje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($testy as $t): ?>
                            <tr>
                                <td><?php echo $t['id_testu']; ?></td>
                                <td><?php echo htmlspecialchars($t['nazwa_testu']); ?></td>
                                <td><?php echo $t['czas_trwania']; ?></td>
                                <td>
                                    <a href="edit_training.php?id=<?php echo $t['id_testu']; ?>" class="btn btn-sm btn-primary">Edytuj</a>
                                    <a href="manage_trainings.php?delete=<?php echo $t['id_testu']; ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Czy na pewno chcesz usunąć to szkolenie?')">Usuń</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($testy)): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted">Brak szkoleń w systemie.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
