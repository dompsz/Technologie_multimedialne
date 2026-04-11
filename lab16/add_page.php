<?php
session_start();
require_once 'db_config.php';

if(!isset($_SESSION['lab16_user_id'])) {
    header("Location: login.php");
    exit();
}

$error = '';
$success = '';

// Pobranie kategorii do selecta
$stmt_cat = $conn->query("SELECT * FROM kategorie ORDER BY nazwa ASC");
$categories = $stmt_cat->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tytul = trim($_POST['tytul']);
    $slug = trim($_POST['slug']);
    $tresc = $_POST['tresc'];
    $idk = !empty($_POST['idk']) ? $_POST['idk'] : null;
    $status = $_POST['status'];
    $idu = $_SESSION['lab16_user_id'];

    if (empty($tytul) || empty($slug) || empty($tresc)) {
        $error = "Tytuł, slug i treść są wymagane.";
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO podstrony (tytul, slug, tresc, idk, idu, status) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$tytul, $slug, $tresc, $idk, $idu, $status]);
            header("Location: dashboard.php?msg=added");
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
    <title>Dodaj stronę - CMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.ckeditor.com/ckeditor5/40.0.0/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable {
            min-height: 300px;
            color: #333;
        }
    </style>
</head>
<body class="bg-dark text-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card bg-secondary text-white">
                    <div class="card-header"><h4>Dodaj nową stronę</h4></div>
                    <div class="card-body">
                        <?php if($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tytuł strony</label>
                                    <input type="text" name="tytul" class="form-control" required onkeyup="generateSlug(this.value)">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Slug (URL)</label>
                                    <input type="text" name="slug" id="slug" class="form-control" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kategoria</label>
                                    <select name="idk" class="form-select">
                                        <option value="">-- Wybierz kategorię --</option>
                                        <?php foreach($categories as $cat): ?>
                                            <option value="<?php echo $cat['idk']; ?>"><?php echo htmlspecialchars($cat['nazwa']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="szkic">Szkic</option>
                                        <option value="opublikowany">Opublikowany</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Treść</label>
                                <textarea name="tresc" id="editor" class="form-control" rows="10"></textarea>
                            </div>
                            <div class="d-flex justify-content-between mt-4">
                                <a href="dashboard.php" class="btn btn-outline-light">Anuluj</a>
                                <button type="submit" class="btn btn-success">Zapisz stronę</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        ClassicEditor
            .create( document.querySelector( '#editor' ) )
            .catch( error => {
                console.error( error );
            } );

        function generateSlug(text) {
            const slug = text.toLowerCase()
                .replace(/[^\w\s-]/g, '')
                .replace(/[\s_-]+/g, '-')
                .replace(/^-+|-+$/g, '');
            document.getElementById('slug').value = slug;
        }
    </script>
</body>
</html>
