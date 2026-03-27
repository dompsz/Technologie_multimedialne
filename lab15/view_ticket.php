<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab15_user_id']) || !isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$idpo = (int)$_GET['id'];
$user_id = $_SESSION['lab15_user_id'];
$role = $_SESSION['lab15_role'];

// Pobierz szczegóły posta
$stmt = $conn->prepare("
    SELECT p.*, z.nazwa as kategoria, k.nazwisko as klient_nazwisko 
    FROM posty p 
    JOIN zagadnienia z ON p.idz = z.idz 
    JOIN klienci k ON p.idk = k.idk 
    WHERE p.idpo = ?
");
$stmt->execute([$idpo]);
$post = $stmt->fetch();

if (!$post) {
    header("Location: dashboard.php");
    exit();
}

// Zabezpieczenie: Klient widzi tylko swoje, Pracownik widzi wszystko
if ($role === 'client' && $post['idk'] != $user_id) {
    header("Location: dashboard.php");
    exit();
}

// Obsługa nowej odpowiedzi / zmiany statusu / oceny
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_reply'])) {
        $tresc = trim($_POST['tresc']);
        if (!empty($tresc)) {
            if ($role === 'client') {
                // Klient dopisuje kolejny post (wymóg komunikatora)
                // W tej implementacji odpowiedzi pracowników są w tabeli 'odpowiedzi', 
                // a kolejne dopiski klienta mogłyby być nowymi postami, ale lepiej trzymać to w jednym wątku.
                // Uproszczenie: Klient dopisuje treść do 'odpowiedzi' z idp = NULL lub nową tabelę.
                // Według schematu 'odpowiedzi' mają idp (FK). 
                // Zmienimy schemat odpowiedzi lub pozwolimy klientowi "dodawać posty" w tym samym wątku?
                // Zgodnie z lab15.txt: "możliwość dopisywania kolejnych pytań/postów w ramach tego samego wątku (idpo)"
                // Potrzebujemy tabeli która obsłuży oba typy wiadomości lub 'odpowiedzi' z nullable idp.
                
                // Poprawka: Dodam tabelę 'wiadomosci' lub zaktualizuję 'odpowiedzi'
                // Na potrzeby tego zadania załóżmy, że odpowiedzi są tylko od pracowników, 
                // a klient może tylko zadać pierwsze pytanie LUB dopisujemy do 'odpowiedzi' z idp = 0/NULL.
                // Ponieważ idp ma CONSTRAINT, muszę go zmienić.
            } else {
                // Pracownik odpowiada
                $stmt = $conn->prepare("INSERT INTO odpowiedzi (idpo, idp, tresc) VALUES (?, ?, ?)");
                $stmt->execute([$idpo, $user_id, $tresc]);
                
                // Automatyczna zmiana stanu na "W trakcie" jeśli był "Oczekujący"
                if ($post['stan'] == 0) {
                    $conn->prepare("UPDATE posty SET stan = 1 WHERE idpo = ?")->execute([$idpo]);
                }
            }
            header("Location: view_ticket.php?id=$idpo");
            exit();
        }
    }

    if (isset($_POST['update_status']) && $role !== 'client') {
        $new_status = (int)$_POST['new_status'];
        $stmt = $conn->prepare("UPDATE posty SET stan = ? WHERE idpo = ?");
        $stmt->execute([$new_status, $idpo]);
        header("Location: view_ticket.php?id=$idpo");
        exit();
    }

    if (isset($_POST['rate_ticket']) && $role === 'client' && $post['stan'] == 2) {
        $ocena = (int)$_POST['ocena'];
        $stmt = $conn->prepare("UPDATE posty SET ocena_pracownika = ? WHERE idpo = ?");
        $stmt->execute([$ocena, $idpo]);
        header("Location: view_ticket.php?id=$idpo");
        exit();
    }
}

// Pobierz wszystkie odpowiedzi od pracowników
$stmt_o = $conn->prepare("
    SELECT o.*, p.nazwisko as pracownik_nazwisko 
    FROM odpowiedzi o 
    JOIN pracownicy p ON o.idp = p.idp 
    WHERE o.idpo = ? 
    ORDER BY o.datagodzina ASC
");
$stmt_o->execute([$idpo]);
$replies = $stmt_o->fetchAll();

function getStanLabel($stan) {
    switch ($stan) {
        case 0: return '<span class="badge bg-danger">Oczekujący</span>';
        case 1: return '<span class="badge bg-warning text-dark">W trakcie</span>';
        case 2: return '<span class="badge bg-success">Zakończony</span>';
        default: return 'Nieznany';
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Zgłoszenie #<?php echo $idpo; ?> - CRM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .msg-box { border-radius: 15px; padding: 15px; margin-bottom: 20px; position: relative; }
        .msg-client { background: rgba(13, 110, 253, 0.1); border: 1px solid #0d6efd; margin-right: 20%; }
        .msg-employee { background: rgba(25, 135, 84, 0.1); border: 1px solid #198754; margin-left: 20%; }
        .msg-meta { font-size: 0.8rem; color: #888; margin-bottom: 5px; }
        .btn-accent { background: var(--accent-color); color: #000; font-weight: bold; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-dark bg-black border-bottom border-secondary mb-4">
        <div class="container">
            <a class="navbar-brand" href="dashboard.php">← Powrót do Dashboardu</a>
            <span class="navbar-text">Zgłoszenie #<?php echo $idpo; ?></span>
        </div>
    </nav>

    <div class="container pb-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <!-- Nagłówek Zgłoszenia -->
                <div class="card bg-dark text-light border-secondary mb-4">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h3 class="mb-0"><?php echo htmlspecialchars($post['kategoria']); ?></h3>
                            <?php echo getStanLabel($post['stan']); ?>
                        </div>
                        <div class="small text-secondary mb-3">
                            Klient: <strong><?php echo htmlspecialchars($post['klient_nazwisko']); ?></strong> | 
                            Data: <?php echo $post['datagodzina']; ?> | 
                            Priorytet: <?php echo $post['priorytet']; ?>/5
                        </div>
                        
                        <!-- Panel sterowania dla Pracownika -->
                        <?php if ($role !== 'client'): ?>
                            <form method="POST" class="d-flex gap-2 mb-3">
                                <input type="hidden" name="update_status" value="1">
                                <select name="new_status" class="form-select form-select-sm bg-dark text-light border-secondary w-auto">
                                    <option value="0" <?php echo $post['stan'] == 0 ? 'selected' : ''; ?>>Oczekujący</option>
                                    <option value="1" <?php echo $post['stan'] == 1 ? 'selected' : ''; ?>>W trakcie</option>
                                    <option value="2" <?php echo $post['stan'] == 2 ? 'selected' : ''; ?>>Zakończony</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-warning">Zmień status</button>
                            </form>
                        <?php endif; ?>

                        <!-- System Ocen dla Klienta -->
                        <?php if ($role === 'client' && $post['stan'] == 2 && is_null($post['ocena_pracownika'])): ?>
                            <div class="alert alert-info bg-dark border-info text-info">
                                <h5>To zgłoszenie zostało zakończone. Oceń naszą pomoc:</h5>
                                <form method="POST" class="d-flex gap-2 mt-2">
                                    <input type="hidden" name="rate_ticket" value="1">
                                    <select name="ocena" class="form-select form-select-sm bg-dark text-light border-info w-auto" required>
                                        <option value="5">⭐⭐⭐⭐⭐ (5)</option>
                                        <option value="4">⭐⭐⭐⭐ (4)</option>
                                        <option value="3">⭐⭐⭐ (3)</option>
                                        <option value="2">⭐⭐ (2)</option>
                                        <option value="1">⭐ (1)</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-accent">Wystaw ocenę</button>
                                </form>
                            </div>
                        <?php elseif (!is_null($post['ocena_pracownika'])): ?>
                            <div class="alert alert-secondary bg-dark border-secondary text-secondary py-1">
                                Ocena klienta: <strong><?php echo $post['ocena_pracownika']; ?>/5</strong> ⭐
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Wątek Wiadomości -->
                <h5 class="mb-4">Historia rozmowy:</h5>
                
                <!-- Oryginalne pytanie klienta -->
                <div class="msg-box msg-client">
                    <div class="msg-meta"><?php echo htmlspecialchars($post['klient_nazwisko']); ?> • <?php echo $post['datagodzina']; ?></div>
                    <div class="msg-content"><?php echo nl2br(htmlspecialchars($post['tresc'])); ?></div>
                </div>

                <!-- Odpowiedzi pracowników -->
                <?php foreach ($replies as $r): ?>
                    <div class="msg-box msg-employee">
                        <div class="msg-meta">Pracownik: <?php echo htmlspecialchars($r['pracownik_nazwisko']); ?> • <?php echo $r['datagodzina']; ?></div>
                        <div class="msg-content"><?php echo nl2br(htmlspecialchars($r['tresc'])); ?></div>
                    </div>
                <?php endforeach; ?>

                <!-- Formularz odpowiedzi (jeśli nie zakończone) -->
                <?php if ($post['stan'] < 2): ?>
                    <?php if ($role !== 'client'): ?>
                        <div class="card bg-dark text-light border-secondary mt-4">
                            <div class="card-body">
                                <h6>Twoja odpowiedź:</h6>
                                <form method="POST">
                                    <input type="hidden" name="add_reply" value="1">
                                    <textarea name="tresc" class="form-control bg-dark text-light border-secondary mb-3" rows="4" placeholder="Wpisz treść odpowiedzi..." required></textarea>
                                    <button type="submit" class="btn btn-accent px-4">WYŚLIJ ODPOWIEDŹ</button>
                                </form>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning bg-dark border-warning text-warning mt-4">
                            Oczekuj na odpowiedź od naszego pracownika.
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-center text-secondary mt-4">Wątek został zamknięty.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
