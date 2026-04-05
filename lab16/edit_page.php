<?php
session_start();
require_once 'db_config.php';

if(!isset($_SESSION['lab16_user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if(!$id) { header("Location: dashboard.php"); exit(); }

$error = '';
$user_id = $_SESSION['lab16_user_id'];
$role = $_SESSION['lab16_role'];

// Pobranie danych strony
$stmt = $conn->prepare("SELECT * FROM podstrony WHERE idp = ?");
$stmt->execute([$id]);
$page = $stmt->fetch();

if(!$page) { header("Location: dashboard.php"); exit(); }

// Sprawdzenie uprawnień (tylko autor lub admin)
if($role !== 'admin' && $page['idu'] != $user_id) {
    header("Location: dashboard.php?error=unauthorized");
    exit();
}

// Pobranie kategorii
$stmt_cat = $conn->query("SELECT * FROM kategorie ORDER BY nazwa ASC");
$categories = $stmt_cat->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tytul = trim($_POST['tytul']);
    $slug = trim($_POST['slug']);
    $tresc = $_POST['tresc'];
    $idk = !empty($_POST['idk']) ? $_POST['idk'] : null;
    $status = $_POST['status'];

    if (empty($tytul) || empty($slug) || empty($tresc)) {
        $error = "Wszystkie pola są wymagane.";
    } else {
        try {
            $stmt_u = $conn->prepare("UPDATE podstrony SET tytul = ?, slug = ?, tresc = ?, idk = ?, status = ? WHERE idp = ?");
            $stmt_u->execute([$tytul, $slug, $tresc, $idk, $status, $id]);
            header("Location: dashboard.php?msg=updated");
            exit();
        } catch (PDOException $e) {
            $error = "Błąd: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Edytuj stronę - CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-secondary text-white">
                    <div class="card-header"><h4>Edytuj stronę: <?php echo htmlspecialchars($page['tytul']); ?></h4></div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Tytuł strony</label>
                                <input type="text" name="tytul" class="form-control" value="<?php echo htmlspecialchars($page['tytul']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Slug (URL)</label>
                                <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($page['slug']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kategoria</label>
                                <select name="idk" class="form-select">
                                    <option value="">-- Wybierz kategorię --</option>
                                    <?php foreach($categories as $cat): ?>
                                        <option value="<?php echo $cat['idk']; ?>" <?php echo ($cat['idk'] == $page['idk']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nazwa']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Treść</label>
                                <textarea name="tresc" class="form-control" rows="10" required><?php echo htmlspecialchars($page['tresc']); ?></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="szkic" <?php echo ($page['status'] === 'szkic') ? 'selected' : ''; ?>>Szkic</option>
                                    <option value="opublikowany" <?php echo ($page['status'] === 'opublikowany') ? 'selected' : ''; ?>>Opublikowany</option>
                                </select>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-outline-light">Powrót</a>
                                <button type="submit" class="btn btn-primary">Zapisz zmiany</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
