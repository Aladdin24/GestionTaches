<?php
// Démarrer la session pour vérifier l'authentification
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: index.html");
    exit();
}

// Connexion à la base de données
$host = "localhost";
$dbname = "gst";
$username = "root";
$password = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Récupérer l'ID de l'intervenant depuis l'URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID d\'intervenant manquant');</script>";
    header("Location: dashboard.php");
    exit();
}

$idIntervenant = htmlspecialchars($_GET['id']);

// Récupérer les détails de l'intervenant à partir de la base de données
$stmt = $pdo->prepare("SELECT * FROM Intervenant WHERE IdIntervenant = ?");
$stmt->execute([$idIntervenant]);
$intervenant = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'intervenant existe
if (!$intervenant) {
    echo "<script>alert('Intervenant introuvable');</script>";
    header("Location: dashboard.php");
    exit();
}

// Mettre à jour les données de l'intervenant si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les nouvelles données du formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $poste = htmlspecialchars($_POST['poste']);

    // Préparer et exécuter la requête SQL pour mettre à jour les données de l'intervenant
    $updateStmt = $pdo->prepare("UPDATE Intervenant SET Nom = ?, Prenom = ?, Poste = ? WHERE IdIntervenant = ?");
    try {
        $updateStmt->execute([$nom, $prenom, $poste, $idIntervenant]);
        echo "<script>alert('Intervenant mis à jour avec succès !'); window.location.href='dashboard.php';</script>";
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de la mise à jour de l\'intervenant : " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Intervenant</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004990;
        }

        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #545b62;
            border-color: #495057;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h1>Modifier Intervenant</h1>
            <!-- Formulaire de modification -->
            <form action="edit_intervenant.php?id=<?= htmlspecialchars($idIntervenant) ?>" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom :</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($intervenant['Nom']) ?>" required>
                    <div class="invalid-feedback">Veuillez entrer un nom valide.</div>
                </div>
                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($intervenant['Prenom']) ?>" required>
                    <div class="invalid-feedback">Veuillez entrer un prénom valide.</div>
                </div>
                <div class="mb-3">
                    <label for="poste" class="form-label">Poste :</label>
                    <input type="text" id="poste" name="poste" class="form-control" value="<?= htmlspecialchars($intervenant['Poste']) ?>" required>
                    <div class="invalid-feedback">Veuillez entrer un poste valide.</div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Mettre à Jour</button>
                    <a href="dashboard.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Inclure Bootstrap JS et ses dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Validation du formulaire -->
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function () {
            'use strict';

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation');

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>
</body>

</html>