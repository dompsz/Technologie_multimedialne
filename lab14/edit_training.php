<?php
session_start();
require_once 'db_config.php';

// Zabezpieczenie
if (!isset($_SESSION['lab14_user_id']) || !in_array(($_SESSION['lab14_role'] ?? ''), ['admin', 'coach'])) {
    die("Brak uprawnień.");
}

$id = (int)($_GET['id'] ?? 0);
$test = ['nazwa_testu' => '', 'opis' => '', 'tresc_szkolenia' => '', 'czas_trwania' => 600];
$pytania = [];

if ($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM testy WHERE id_testu = ?");
    $stmt->execute([$id]);
    $test = $stmt->fetch();
    
    if (!$test) die("Brak szkolenia o tym ID.");
    
    // Pobierz pytania
    $stmt_p = $conn->prepare("SELECT * FROM pytania WHERE id_testu = ?");
    $stmt_p->execute([$id]);
    $pytania = $stmt_p->fetchAll();
}

// Obsługa zapisu danych podstawowych
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_main'])) {
    $nazwa = $_POST['nazwa_testu'] ?? '';
    $opis = $_POST['opis'] ?? '';
    $tresc = $_POST['tresc_szkolenia'] ?? '';
    $czas = (int)($_POST['czas_trwania'] ?? 600);

    if ($id > 0) {
        $stmt = $conn->prepare("UPDATE testy SET nazwa_testu = ?, opis = ?, tresc_szkolenia = ?, czas_trwania = ? WHERE id_testu = ?");
        $stmt->execute([$nazwa, $opis, $tresc, $czas, $id]);
    } else {
        $stmt = $conn->prepare("INSERT INTO testy (nazwa_testu, opis, tresc_szkolenia, czas_trwania) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nazwa, $opis, $tresc, $czas]);
        $id = $conn->lastInsertId();
    }
    
    header("Location: edit_training.php?id=$id&msg=saved");
    exit();
}

// Obsługa dodawania pytania
if (isset($_POST['add_question'])) {
    $tresc_q = $_POST['new_question_text'] ?? '';
    if (!empty($tresc_q)) {
        $stmt = $conn->prepare("INSERT INTO pytania (tresc_pytania, id_testu) VALUES (?, ?)");
        $stmt->execute([$tresc_q, $id]);
    }
    header("Location: edit_training.php?id=$id#questions");
    exit();
}

// Obsługa usuwania pytania
if (isset($_GET['delete_q'])) {
    $qid = (int)$_GET['delete_q'];
    $conn->prepare("DELETE FROM pytania WHERE id_pytania = ? AND id_testu = ?")->execute([$qid, $id]);
    header("Location: edit_training.php?id=$id#questions");
    exit();
}

// Obsługa dodawania odpowiedzi
if (isset($_POST['add_answer'])) {
    $qid = (int)$_POST['question_id'];
    $tresc_a = $_POST['new_answer_text'] ?? '';
    $poprawna = isset($_POST['is_correct']) ? 1 : 0;
    if (!empty($tresc_a)) {
        $stmt = $conn->prepare("INSERT INTO odpowiedzi (id_pytania, tresc_odpowiedzi, czy_poprawna) VALUES (?, ?, ?)");
        $stmt->execute([$qid, $tresc_a, $poprawna]);
    }
    header("Location: edit_training.php?id=$id#q_$qid");
    exit();
}

// Obsługa usuwania odpowiedzi
if (isset($_GET['delete_a'])) {
    $aid = (int)$_GET['delete_a'];
    $qid = (int)$_GET['qid'];
    $conn->prepare("DELETE FROM odpowiedzi WHERE id_odpowiedzi = ?")->execute([$aid]);
    header("Location: edit_training.php?id=$id#q_$qid");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo $id > 0 ? 'Edytuj' : 'Dodaj'; ?> Szkolenie - Lab 14</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .form-card { background: var(--card-bg); border: 1px solid var(--border-color); padding: 20px; border-radius: 12px; margin-bottom: 20px; color: #fff; }
        .q-card { background: #2a2a2a; border-left: 4px solid var(--accent-color); padding: 15px; margin-bottom: 15px; border-radius: 8px; }
        textarea { background: #1a1a1a !important; color: #fff !important; border-color: #444 !important; }
        input:not([type="checkbox"]) { background: #1a1a1a !important; color: #fff !important; border-color: #444 !important; }
        .form-check-input { background-color: #1a1a1a; border-color: #444; cursor: pointer; }
        .form-check-input:checked { background-color: var(--accent-color); border-color: var(--accent-color); }
        label { color: #bbb; }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2><?php echo $id > 0 ? '📝 Edycja' : '➕ Nowe'; ?> Szkolenie</h2>
            <a href="manage_trainings.php" class="btn btn-secondary">Powrót do listy</a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'saved'): ?>
            <div class="alert alert-success">Zmiany zapisane pomyślnie.</div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-card">
                <h4>Główne Informacje</h4>
                <div class="mb-3">
                    <label class="form-label">Nazwa Testu</label>
                    <input type="text" name="nazwa_testu" class="form-control" value="<?php echo htmlspecialchars($test['nazwa_testu']); ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Opis</label>
                    <textarea name="opis" class="form-control" rows="2"><?php echo htmlspecialchars($test['opis']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Czas trwania (sekundy)</label>
                    <input type="number" name="czas_trwania" class="form-control" value="<?php echo $test['czas_trwania']; ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Treść Szkolenia (HTML)</label>
                    <textarea name="tresc_szkolenia" class="form-control" rows="10"><?php echo htmlspecialchars($test['tresc_szkolenia']); ?></textarea>
                    <small class="text-muted">Możesz używać tagów HTML (h4, p, img, strong itp.)</small>
                </div>
                <button type="submit" name="save_main" class="btn btn-success">Zapisz Dane Podstawowe</button>
            </div>
        </form>

        <?php if ($id > 0): ?>
            <div id="questions" class="mt-5">
                <h3>Pytania i Odpowiedzi</h3>
                <hr>
                
                <?php foreach ($pytania as $p): ?>
                    <div class="q-card" id="q_<?php echo $p['id_pytania']; ?>">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5>Pytanie: <?php echo htmlspecialchars($p['tresc_pytania']); ?></h5>
                            <a href="edit_training.php?id=<?php echo $id; ?>&delete_q=<?php echo $p['id_pytania']; ?>" 
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Usunąć to pytanie wraz z odpowiedziami?')">Usuń Pytanie</a>
                        </div>
                        
                        <div class="ms-4 mt-3">
                            <h6>Odpowiedzi:</h6>
                            <ul class="list-group list-group-flush mb-3">
                                <?php
                                $stmt_a = $conn->prepare("SELECT * FROM odpowiedzi WHERE id_pytania = ?");
                                $stmt_a->execute([$p['id_pytania']]);
                                $odpowiedzi = $stmt_a->fetchAll();
                                foreach ($odpowiedzi as $a):
                                ?>
                                    <li class="list-group-item bg-transparent text-light border-secondary d-flex justify-content-between">
                                        <span>
                                            <?php if($a['czy_poprawna']): ?>✅<?php else: ?>❌<?php endif; ?>
                                            <?php echo htmlspecialchars($a['tresc_odpowiedzi']); ?>
                                        </span>
                                        <a href="edit_training.php?id=<?php echo $id; ?>&delete_a=<?php echo $a['id_odpowiedzi']; ?>&qid=<?php echo $p['id_pytania']; ?>" class="text-danger">usuń</a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                            
                            <form method="POST" class="row g-2">
                                <input type="hidden" name="question_id" value="<?php echo $p['id_pytania']; ?>">
                                <div class="col-md-8">
                                    <input type="text" name="new_answer_text" class="form-control form-control-sm" placeholder="Nowa odpowiedź..." required>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check pt-1">
                                        <input class="form-check-input" type="checkbox" name="is_correct" id="check_<?php echo $p['id_pytania']; ?>">
                                        <label class="form-check-label small" for="check_<?php echo $p['id_pytania']; ?>">Poprawna</label>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" name="add_answer" class="btn btn-sm btn-outline-success w-100">Dodaj</button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="form-card mt-4 border-info">
                    <h5>Dodaj Nowe Pytanie</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <textarea name="new_question_text" class="form-control" placeholder="Treść pytania..." required></textarea>
                        </div>
                        <button type="submit" name="add_question" class="btn btn-info">Dodaj Pytanie</button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info">Najpierw zapisz dane podstawowe, aby móc dodać pytania.</div>
        <?php endif; ?>
    </div>
    <div class="mb-5"></div>
</body>
</html>
