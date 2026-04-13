<?php
session_start();
require_once 'db_config.php';
require_once 'functions.php';

$idw = (int)($_GET['id'] ?? 0);

// Pobierz główny post (wątek)
$stmt_m = $conn->prepare("
    SELECT w.*, u.login, u.poziom_uprawnien, t.nazwa_tematu 
    FROM watki w
    JOIN uzytkownicy u ON w.idu = u.idu
    JOIN tematy t ON w.idt = t.idt
    WHERE w.idw = ? AND w.stan = 1
");
$stmt_m->execute([$idw]);
$main_post = $stmt_m->fetch();

if (!$main_post) die("Wątek nie istnieje lub został zablokowany.");

// Sprawdzenie bana
$ban_info = isset($_SESSION['lab17_user_id']) ? isUserBanned($_SESSION['lab17_user_id'], $conn) : false;

// Obsługa dodawania odpowiedzi
$error = "";
$warning = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_reply'])) {
    if (!isset($_SESSION['lab17_user_id'])) {
        $error = "Musisz być zalogowany, aby odpowiedzieć.";
    } elseif ($ban_info) {
        $error = "Jesteś zablokowany do: " . $ban_info['ban_do'] . ". Powód: " . $ban_info['powod_blokady'];
    } else {
        $tresc = trim($_POST['tresc']);
        if (empty($tresc)) {
            $error = "Treść odpowiedzi nie może być pusta.";
        } else {
            $f_tresc = filterContent($tresc, $conn);
            
            if ($f_tresc['profanity_count'] > 0) {
                $punishment = handleProfanityOffense($_SESSION['lab17_user_id'], $f_tresc['profanity_count'], $conn);
                $warning = "⚠️ Wykryto wulgaryzmy! Konto zablokowane do: " . $punishment['ban_until'];
                $ban_info = isUserBanned($_SESSION['lab17_user_id'], $conn);
            }

            $stmt_ins = $conn->prepare("INSERT INTO watki (idt, idu, id_rodzic, tresc) VALUES (?, ?, ?, ?)");
            $stmt_ins->execute([$main_post['idt'], $_SESSION['lab17_user_id'], $idw, $f_tresc['text']]);
            
            if (!$warning) {
                header("Location: thread.php?id=$idw&msg=replied");
                exit();
            }
        }
    }
}

// Pobierz odpowiedzi
$stmt_r = $conn->prepare("
    SELECT w.*, u.login, u.poziom_uprawnien 
    FROM watki w
    JOIN uzytkownicy u ON w.idu = u.idu
    WHERE w.id_rodzic = ? AND w.stan = 1
    ORDER BY w.datagodzina ASC
");
$stmt_r->execute([$idw]);
$replies = $stmt_r->fetchAll();

// --- LOGIKA MODERACJI (TYLKO DLA MOD/ADMIN) ---
if (isset($_SESSION['lab17_role']) && $_SESSION['lab17_role'] >= 2) {
    // Usuwanie posta
    if (isset($_GET['delete_post'])) {
        $del_id = (int)$_GET['delete_post'];
        $stmt_del = $conn->prepare("DELETE FROM watki WHERE idw = ? OR id_rodzic = ?");
        $stmt_del->execute([$del_id, $del_id]);
        
        // Jeśli usunięto główny wątek, wróć do listy tematów
        if ($del_id == $idw) {
            header("Location: topic.php?id=" . $main_post['idt']);
        } else {
            header("Location: thread.php?id=$idw&msg=deleted");
        }
        exit();
    }

    // Banowanie autora
    if (isset($_POST['ban_author'])) {
        $target_uid = (int)$_POST['user_id'];
        $duration = $_POST['duration'];
        $ban_until = ($duration == '1h') ? date('Y-m-d H:i:s', strtotime('+1 hour')) : '2099-12-31 23:59:59';
        
        $stmt_ban = $conn->prepare("UPDATE uzytkownicy SET ban_do = ?, powod_blokady = 'Naruszenie regulaminu' WHERE idu = ?");
        $stmt_ban->execute([$ban_until, $target_uid]);
        header("Location: thread.php?id=$idw&msg=banned");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($main_post['tytul']); ?> - Forum Lab 17</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .post-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px; margin-bottom: 25px; overflow: hidden; }
        .post-header { background: rgba(255,255,255,0.03); padding: 15px 20px; border-bottom: 1px solid var(--border-color); }
        .post-body { padding: 25px; min-height: 150px; }
        .author-box { border-right: 1px solid var(--border-color); padding: 20px; text-align: center; background: rgba(0,0,0,0.1); }
        .avatar-circle { width: 60px; height: 60px; background: var(--accent-color); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 10px; color: black; font-weight: bold; font-size: 1.5rem; }
        .text-accent { color: var(--accent-color) !important; }
        .breadcrumb-item + .breadcrumb-item::before { color: #666; }
    </style>
</head>
<body class="bg-dark text-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-black border-bottom border-secondary">
        <div class="container">
            <a class="navbar-brand fw-bold text-accent" href="index.php">💬 FORUM LAB 17</a>
            <div class="d-flex align-items-center">
                <a href="topic.php?id=<?php echo $main_post['idt']; ?>" class="btn btn-outline-light btn-sm me-2">Wróć do listy</a>
                <?php if (isset($_SESSION['lab17_user_id'])): ?>
                    <a href="logout.php" class="btn btn-outline-danger btn-sm">Wyloguj</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4 pb-5">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-secondary">Forum</a></li>
                <li class="breadcrumb-item"><a href="topic.php?id=<?php echo $main_post['idt']; ?>" class="text-secondary"><?php echo htmlspecialchars($main_post['nazwa_tematu']); ?></a></li>
                <li class="breadcrumb-item active text-accent" aria-current="page"><?php echo htmlspecialchars($main_post['tytul']); ?></li>
            </ol>
        </nav>

        <h2 class="mb-4 mt-2"><?php echo htmlspecialchars($main_post['tytul']); ?></h2>

        <!-- Główny Post -->
        <div class="post-card">
            <div class="row g-0">
                <div class="col-md-2 author-box">
                    <div class="avatar-circle"><?php echo strtoupper(substr($main_post['login'], 0, 1)); ?></div>
                    <div class="fw-bold text-light"><?php echo htmlspecialchars($main_post['login']); ?></div>
                    <div class="mt-1"><?php echo getRoleLabel($main_post['poziom_uprawnien']); ?></div>
                </div>
                <div class="col-md-10 d-flex flex-column">
                    <div class="post-header d-flex justify-content-between">
                        <small class="text-secondary">Opublikowano: <?php echo $main_post['datagodzina']; ?></small>
                        <div class="d-flex align-items-center gap-2">
                            <?php if (isset($_SESSION['lab17_role']) && $_SESSION['lab17_role'] >= 2): ?>
                                <a href="thread.php?id=<?php echo $idw; ?>&delete_post=<?php echo $main_post['idw']; ?>" class="btn btn-xxs btn-outline-danger py-0 px-2" style="font-size:0.7rem;" onclick="return confirm('Usunąć cały wątek?')">Usuń Wątek</a>
                                <form method="POST" class="d-flex gap-1 align-items-center" style="margin:0;">
                                    <input type="hidden" name="user_id" value="<?php echo $main_post['idu']; ?>">
                                    <select name="duration" class="form-select form-select-sm bg-dark text-white border-secondary py-0" style="font-size:0.6rem; height:20px;">
                                        <option value="1h">1h</option>
                                        <option value="perm">Perm</option>
                                    </select>
                                    <button type="submit" name="ban_author" class="btn btn-xxs btn-danger py-0 px-1" style="font-size:0.6rem; height:20px;">BAN</button>
                                </form>
                            <?php endif; ?>
                            <small class="text-secondary">#1</small>
                        </div>
                    </div>
                    <div class="post-body">
                        <?php echo nl2br(htmlspecialchars($main_post['tresc'])); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Odpowiedzi -->
        <?php foreach ($replies as $index => $r): ?>
            <div class="post-card">
                <div class="row g-0">
                    <div class="col-md-2 author-box">
                        <div class="avatar-circle" style="background: #444; color: #fff;"><?php echo strtoupper(substr($r['login'], 0, 1)); ?></div>
                        <div class="fw-bold text-light"><?php echo htmlspecialchars($r['login']); ?></div>
                        <div class="mt-1"><?php echo getRoleLabel($r['poziom_uprawnien']); ?></div>
                    </div>
                    <div class="col-md-10 d-flex flex-column">
                        <div class="post-header d-flex justify-content-between">
                            <small class="text-secondary">Odpowiedź: <?php echo $r['datagodzina']; ?></small>
                            <div class="d-flex align-items-center gap-2">
                                <?php if (isset($_SESSION['lab17_role']) && $_SESSION['lab17_role'] >= 2): ?>
                                    <a href="thread.php?id=<?php echo $idw; ?>&delete_post=<?php echo $r['idw']; ?>" class="btn btn-xxs btn-outline-danger py-0 px-2" style="font-size:0.7rem;" onclick="return confirm('Usunąć tę odpowiedź?')">Usuń</a>
                                    <form method="POST" class="d-flex gap-1 align-items-center" style="margin:0;">
                                        <input type="hidden" name="user_id" value="<?php echo $r['idu']; ?>">
                                        <select name="duration" class="form-select form-select-sm bg-dark text-white border-secondary py-0" style="font-size:0.6rem; height:20px;">
                                            <option value="1h">1h</option>
                                            <option value="perm">Perm</option>
                                        </select>
                                        <button type="submit" name="ban_author" class="btn btn-xxs btn-danger py-0 px-1" style="font-size:0.6rem; height:20px;">BAN</button>
                                    </form>
                                <?php endif; ?>
                                <small class="text-secondary">#<?php echo $index + 2; ?></small>
                            </div>
                        </div>
                        <div class="post-body">
                            <?php echo nl2br(htmlspecialchars($r['tresc'])); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <!-- Formularz Odpowiedzi -->
        <div class="mt-5">
            <h3>Twoja odpowiedź</h3>
            <hr class="border-secondary mb-4">
            <?php if (isset($_SESSION['lab17_user_id'])): ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if ($warning): ?>
                    <div class="alert alert-warning"><?php echo $warning; ?></div>
                <?php endif; ?>                <form method="POST">
                    <input type="hidden" name="add_reply" value="1">
                    <div class="mb-3">
                        <textarea name="tresc" class="form-control bg-dark text-light border-secondary" rows="6" placeholder="Napisz co myślisz..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-accent px-5 py-2 fw-bold">WYŚLIJ ODPOWIEDŹ</button>
                </form>
            <?php else: ?>
                <div class="alert alert-info bg-dark border-info text-info p-4">
                    <h5>Chcesz wziąć udział w dyskusji?</h5>
                    <p class="mb-0">Tylko zalogowani użytkownicy mogą pisać na forum. <a href="login.php" class="text-accent fw-bold">Zaloguj się</a> lub <a href="register.php" class="text-accent fw-bold">zarejestruj</a>, aby dołączyć.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
