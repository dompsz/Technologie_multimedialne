<?php
session_start();
require_once 'db_config.php';

if (!isset($_SESSION['lab13_user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['lab13_user_id'];
$is_admin = ($_SESSION['lab13_login'] === 'admin');

// Funkcja pomocnicza do koloru postępu
function getProgressColor($percent) {
    if ($percent == 0) return '#ff4444'; // Czerwony
    if ($percent == 100) return '#00ff00'; // Zielony
    return '#ffffff'; // Biały (zamiast czarnego na ciemnym tle) / Czarny na jasnym
}

// 1. Pobranie zadań, którymi zarządza użytkownik (Manager)
// Obliczamy średnią z podzadań bezpośrednio w SQL
$stmt_managed = $conn->prepare("
    SELECT z.*, 
           (SELECT AVG(stan) FROM podzadanie WHERE idz = z.idz) as srednia_postepu,
           (SELECT COUNT(*) FROM podzadanie WHERE idz = z.idz) as liczba_podzadan
    FROM zadanie z 
    WHERE z.idp = ?
");
$stmt_managed->execute([$user_id]);
$managed_tasks = $stmt_managed->fetchAll();

// 2. Pobranie podzadań przypisanych do użytkownika (Wykonawca)
$stmt_assigned = $conn->prepare("
    SELECT p.*, z.nazwa_zadania, pr.login as manager_login
    FROM podzadanie p
    JOIN zadanie z ON p.idz = z.idz
    JOIN pracownik pr ON z.idp = pr.idp
    WHERE p.idp = ?
");
$stmt_assigned->execute([$user_id]);
$assigned_subtasks = $stmt_assigned->fetchAll();

// 3. Pobranie listy wszystkich pracowników (do przypisywania w formularzu)
$stmt_workers = $conn->query("SELECT idp, login FROM pracownik ORDER BY login");
$all_workers = $stmt_workers->fetchAll();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - System Todo Lab 13</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        .task-card { background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; margin-bottom: 20px; }
        .progress-text { font-weight: bold; }
        .subtask-item { border-left: 3px solid #444; padding-left: 10px; margin-bottom: 10px; }
        .admin-btn { background: #ffc107; color: #000; }
        input[type=range] { width: 100%; cursor: pointer; }
    </style>
</head>
<body class="bg-dark text-light">

<nav class="navbar navbar-dark bg-black border-bottom border-secondary px-4">
    <div class="container-fluid">
        <span class="navbar-brand mb-0 h1">System Todo - Lab 13</span>
        <div class="d-flex align-items-center">
            <span class="me-3">Witaj, <strong><?php echo htmlspecialchars($_SESSION['lab13_login']); ?></strong></span>
            <?php if($is_admin): ?>
                <a href="admin.php" class="btn btn-sm admin-btn me-2">PANEL ADMINA</a>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-sm btn-outline-danger">Wyloguj</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <div class="row">
        <!-- LEWA KOLUMNA: TWOJE PODZADANIA (WYKONAWCA) -->
        <div class="col-md-6">
            <h3 class="mb-4 text-accent">Zadania do wykonania</h3>
            <?php if(empty($assigned_subtasks)): ?>
                <p class="text-muted">Nie masz przypisanych żadnych podzadań.</p>
            <?php else: ?>
                <?php foreach($assigned_subtasks as $sub): ?>
                    <div class="task-card">
                        <h5 style="color: <?php echo getProgressColor($sub['stan']); ?>">
                            <?php echo htmlspecialchars($sub['nazwa_podzadania']); ?>
                        </h5>
                        <p class="small text-muted mb-2">Projekt: <?php echo htmlspecialchars($sub['nazwa_zadania']); ?> (Manager: <?php echo htmlspecialchars($sub['manager_login']); ?>)</p>
                        
                        <form action="update_subtask.php" method="POST" class="mt-3">
                            <input type="hidden" name="idpz" value="<?php echo $sub['idpz']; ?>">
                            <label class="form-label d-flex justify-content-between">
                                Postęp: <span id="val-<?php echo $sub['idpz']; ?>"><?php echo $sub['stan']; ?>%</span>
                            </label>
                            <input type="range" class="form-range" name="stan" value="<?php echo $sub['stan']; ?>" 
                                   min="0" max="100" 
                                   oninput="document.getElementById('val-<?php echo $sub['idpz']; ?>').innerText = this.value + '%'">
                            <button type="submit" class="btn btn-sm btn-primary mt-2">Zaktualizuj stan</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- PRAWA KOLUMNA: ZARZĄDZANE ZADANIA (MANAGER) -->
        <div class="col-md-6">
            <h3 class="mb-4 text-accent">Twoje projekty (Manager)</h3>
            
            <!-- Dodawanie zadania -->
            <div class="task-card mb-4 border-info">
                <h6>Nowe zadanie główne</h6>
                <form action="add_task.php" method="POST" class="d-flex gap-2">
                    <input type="text" name="nazwa_zadania" class="form-control form-control-sm bg-dark text-white" placeholder="Nazwa projektu..." required>
                    <button type="submit" class="btn btn-sm btn-info">DODAJ</button>
                </form>
            </div>

            <?php if(empty($managed_tasks)): ?>
                <p class="text-muted">Nie zarządzasz jeszcze żadnymi zadaniami.</p>
            <?php else: ?>
                <?php foreach($managed_tasks as $task): 
                    $avg = $task['srednia_postepu'] !== null ? round($task['srednia_postepu']) : 0;
                    $color = getProgressColor($avg);
                ?>
                    <div class="task-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <h5 style="color: <?php echo $color; ?>">
                                <?php echo htmlspecialchars($task['nazwa_zadania']); ?>
                            </h5>
                            <span class="badge bg-secondary"><?php echo $avg; ?>%</span>
                        </div>
                        
                        <div class="mt-3 border-top pt-2">
                            <button class="btn btn-sm btn-outline-light mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#addSub-<?php echo $task['idz']; ?>">
                                + Dodaj podzadanie
                            </button>
                            
                            <div class="collapse mb-3" id="addSub-<?php echo $task['idz']; ?>">
                                <form action="add_subtask.php" method="POST" class="card card-body bg-dark border-secondary p-2">
                                    <input type="hidden" name="idz" value="<?php echo $task['idz']; ?>">
                                    <input type="text" name="nazwa_podzadania" class="form-control form-control-sm mb-2 bg-dark text-white" placeholder="Nazwa kroku..." required>
                                    <select name="idp_wykonawca" class="form-select form-select-sm mb-2 bg-dark text-white">
                                        <?php foreach($all_workers as $worker): ?>
                                            <option value="<?php echo $worker['idp']; ?>"><?php echo htmlspecialchars($worker['login']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success">PRZYPISZ</button>
                                </form>
                            </div>

                            <!-- Lista podzadań w tym projekcie -->
                            <?php
                            $stmt_sub_list = $conn->prepare("
                                SELECT p.*, pr.login as wykonawca 
                                FROM podzadanie p 
                                JOIN pracownik pr ON p.idp = pr.idp 
                                WHERE p.idz = ?
                            ");
                            $stmt_sub_list->execute([$task['idz']]);
                            $sub_list = $stmt_sub_list->fetchAll();
                            
                            foreach($sub_list as $sl): ?>
                                <div class="subtask-item d-flex justify-content-between align-items-center small">
                                    <span style="color: <?php echo getProgressColor($sl['stan']); ?>">
                                        <?php echo htmlspecialchars($sl['nazwa_podzadania']); ?> (<?php echo htmlspecialchars($sl['wykonawca']); ?>)
                                    </span>
                                    <span><?php echo $sl['stan']; ?>%</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
