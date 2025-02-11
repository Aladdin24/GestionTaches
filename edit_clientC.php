<?php
session_start();

// Vérification de la session : Seul un utilisateur connecté avec le rôle "client" peut accéder à cette page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'client') {
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

// Récupérer l'ID du client depuis l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID de client manquant ou invalide');</script>";
    header("Location: client_dashboard.php");
    exit();
}
$idClient = intval($_GET['id']);

// Récupérer les détails actuels du client
$clientStmt = $pdo->prepare("SELECT * FROM client WHERE IdClient = ?");
$clientStmt->execute([$idClient]);
$client = $clientStmt->fetch(PDO::FETCH_ASSOC);

// Si le client n'existe pas, rediriger vers le tableau de bord
if (!$client) {
    echo "<script>alert('Client introuvable');</script>";
    header("Location: client_dashboard.php");
    exit();
}

// Mettre à jour les données du client si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les nouvelles données du formulaire
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $direction = htmlspecialchars(trim($_POST['direction']));

    // Valider les champs (ajoute des validations supplémentaires selon tes besoins)
    if (empty($nom) || empty($prenom) || empty($direction)) {
        echo "<script>alert('Veuillez remplir tous les champs');</script>";
    } else {
        // Préparer et exécuter la requête SQL pour mettre à jour les données du client
        $updateStmt = $pdo->prepare("UPDATE client SET Nom = ?, Prenom = ?, Direction = ? WHERE IdClient = ?");
        try {
            $updateStmt->execute([$nom, $prenom, $direction, $idClient]);
            echo "<script>alert('Informations mises à jour avec succès !'); window.location.href='client_dashboard.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Erreur lors de la mise à jour des informations : " . $e->getMessage() . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier mes Informations - Client</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Modifier mes Informations</h1>
            <!-- Formulaire de modification -->
            <form action="edit_clientC.php?id=<?= htmlspecialchars($idClient) ?>" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom :</label>
                    <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars($client['Nom']) ?>" required>
                    <div class="invalid-feedback">Veuillez entrer un nom valide.</div>
                </div>
                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" value="<?= htmlspecialchars($client['Prenom']) ?>" required>
                    <div class="invalid-feedback">Veuillez entrer un prénom valide.</div>
                </div>
                <div class="mb-3">
                    <label for="direction" class="form-label">Direction :</label>
                    <input type="text" id="direction" name="direction" class="form-control" value="<?= htmlspecialchars($client['Direction']) ?>" required>
                    <div class="invalid-feedback">Veuillez entrer une direction valide.</div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Mettre à Jour</button>
                    <a href="client_dashboard.php" class="btn btn-secondary">Annuler</a>
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
            var forms = document.querySelectorAll('.needs-validation');
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