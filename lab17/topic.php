<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

$idt = (int)($_GET['id'] ?? 1);

// Pobierz informacje o temacie
$stmt_t = $conn->prepare("SELECT * FROM tematy WHERE idt = ?");
$stmt_t->execute([$idt]);
$temat = $stmt_t->fetch();

if (!$temat) die("Temat nie istnieje.");

// Sprawdzenie bana
$ban_info = isset($_SESSION['lab17_user_id']) ? isUserBanned($_SESSION['lab17_user_id'], $conn) : false;

// Obsługa dodawania nowego wątku
$error = "";
$warning = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_thread'])) {
    if (!isset($_SESSION['lab17_user_id'])) {
        $error = "Musisz być zalogowany, aby dodać wątek.";
    } elseif ($ban_info) {
        $error = "Jesteś zablokowany do: " . $ban_info['ban_do'] . ". Powód: " . $ban_info['powod_blokady'];
    } else {
        $tytul = trim($_POST['tytul']);
        $tresc = trim($_POST['tresc']);
        
        if (empty($tytul) || empty($tresc)) {
            $error = "Wypełnij wszystkie pola.";
        } else {
            // Filtrowanie i liczenie wulgaryzmów
            $f_tytul = filterContent($tytul, $conn);
            $f_tresc = filterContent($tresc, $conn);
            
            $total_profanity = $f_tytul['profanity_count'] + $f_tresc['profanity_count'];
            $is_malicious = $f_tytul['malicious_found'] || $f_tresc['malicious_found'];
            
            if ($total_profanity > 0 || $is_malicious) {
                // Nakładamy karę
                $punishment = handleProfanityOffense($_SESSION['lab17_user_id'], $total_profanity, $conn, $is_malicious);
                $warning = "⚠️ Wykryto niedozwolone treści! Twoje konto zostało zablokowane do: " . $punishment['ban_until'] . " (Powód: " . $punishment['reason'] . ")";
                // Odświeżamy info o banie
                $ban_info = isUserBanned($_SESSION['lab17_user_id'], $conn);
            }

            // Zapisujemy mimo wszystko (już ocenzurowane) - lub blokujemy zapis (zależnie od polityki)
            // Tutaj: zapisujemy ocenzurowane, ale user dostaje bana na przyszłość
            $stmt_ins = $conn->prepare("INSERT INTO watki (idt, idu, tytul, tresc) VALUES (?, ?, ?, ?)");
            $stmt_ins->execute([$idt, $_SESSION['lab17_user_id'], $f_tytul['text'], $f_tresc['text']]);
            
            if (!$warning) {
                header("Location: topic.php?id=$idt&msg=created");
                exit();
            }
        }
    }
}

// Pobierz wątki (główne posty)
$stmt_w = $conn->prepare("
    SELECT w.*, u.login,
    (SELECT COUNT(*) FROM watki r WHERE r.id_rodzic = w.idw) as repl_count,
    (SELECT MAX(datagodzina) FROM watki r WHERE r.id_rodzic = w.idw OR r.idw = w.idw) as last_activity
    FROM watki w
    JOIN uzytkownicy u ON w.idu = u.idu
    WHERE w.idt = ? AND w.id_rodzic IS NULL AND w.stan = 1
    ORDER BY last_activity DESC
");
$stmt_w->execute([$idt]);
$watki = $stmt_w->fetchAll();

// Obsługa usuwania (Mod/Admin)
if (isset($_GET['delete_thread']) && isset($_SESSION['lab17_role']) && $_SESSION['lab17_role'] >= 2) {
    $del_id = (int)$_GET['delete_thread'];
    $stmt_del = $conn->prepare("DELETE FROM watki WHERE idw = ? OR id_rodzic = ?");
    $stmt_del->execute([$del_id, $del_id]);
    header("Location: topic.php?id=$idt&msg=deleted");
    exit();
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($temat['nazwa_tematu']); ?> - Forum Lab 17</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .thread-row { background: var(--card-bg); border-left: 3px solid var(--accent-color); transition: background 0.2s; }
        .thread-row:hover { background: rgba(255,255,255,0.05); }
        .text-accent { color: var(--accent-color) !important; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand fw-bold text-accent" href="index.php">💬 FORUM LAB 17</a>
            <a href="index.php" class="btn btn-outline-light btn-sm">← Powrót do Tematów</a>
        </div>
    </nav>

    <div class="container mt-5 pb-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <h2 class="mb-1"><?php echo htmlspecialchars($temat['nazwa_tematu']); ?></h2>
                <p class="text-secondary"><?php echo htmlspecialchars($temat['opis']); ?></p>
            </div>
            <?php if (isset($_SESSION['lab17_user_id']) && !$ban_info): ?>
                <button class="btn btn-accent" data-bs-toggle="collapse" data-bs-target="#newThreadForm">➕ Nowy Wątek</button>
            <?php endif; ?>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($warning): ?>
            <div class="alert alert-warning"><?php echo $warning; ?></div>
        <?php endif; ?>

        <?php if ($ban_info && !isset($_POST['add_thread'])): ?>
            <div class="alert alert-danger">
                <strong>Twoje konto jest obecnie zablokowane!</strong><br>
                Powód: <?php echo htmlspecialchars($ban_info['powod_blokady']); ?><br>
                Blokada wygasa: <?php echo $ban_info['ban_do']; ?>
            </div>
        <?php endif; ?>

        <!-- Formularz Nowego Wątku -->
        <?php if (isset($_SESSION['lab17_user_id']) && !$ban_info): ?>
        <div class="collapse mb-5" id="newThreadForm">
            <div class="card bg-dark text-light border-accent p-4">
                <h4>Rozpocznij nową dyskusję</h4>
                <form method="POST">
                    <input type="hidden" name="add_thread" value="1">
                    <div class="mb-3">
                        <label class="form-label text-secondary">Tytuł wątku</label>
                        <input type="text" name="tytul" class="form-control bg-dark text-light border-secondary" placeholder="O czym chcesz porozmawiać?" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary">Treść wiadomości</label>
                        <textarea name="tresc" class="form-control bg-dark text-light border-secondary" rows="5" required></textarea>
                        <small class="text-muted">Pamiętaj o kulturze wypowiedzi. Wulgaryzmy skutkują automatycznym banem!</small>
                    </div>
                    <button type="submit" class="btn btn-accent px-4">OPUBLIKUJ WĄTEK</button>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-dark table-hover align-middle">
                <thead>
                    <tr class="text-secondary small uppercase">
                        <th>Temat / Autor</th>
                        <th class="text-center">Odpowiedzi</th>
                        <th class="text-end">Ostatnia aktywność</th>
                        <?php if (isset($_SESSION['lab17_role']) && $_SESSION['lab17_role'] >= 2): ?>
                        <th class="text-end">Akcja</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($watki as $w): ?>
                        <tr class="thread-row">
                            <td class="py-3">
                                <h5 class="mb-0">
                                    <a href="thread.php?id=<?php echo $w['idw']; ?>" class="text-light text-decoration-none fw-bold">
                                        <?php echo htmlspecialchars($w['tytul']); ?>
                                    </a>
                                </h5>
                                <small class="text-secondary">Przez: <span class="text-accent"><?php echo htmlspecialchars($w['login']); ?></span></small>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary"><?php echo $w['repl_count']; ?></span>
                            </td>
                            <td class="text-end text-secondary small">
                                <?php echo $w['last_activity']; ?>
                            </td>
                            <?php if (isset($_SESSION['lab17_role']) && $_SESSION['lab17_role'] >= 2): ?>
                            <td class="text-end">
                                <a href="topic.php?id=<?php echo $idt; ?>&delete_thread=<?php echo $w['idw']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Trwale usunąć ten wątek wraz z odpowiedziami?')">Usuń</a>
                            </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($watki)): ?>
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">Brak aktywnych wątków w tym temacie. Bądź pierwszy!</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
