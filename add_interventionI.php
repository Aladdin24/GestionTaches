<?php
session_start();

// Vérification de la session : Seul un utilisateur connecté avec le rôle "client" peut accéder à cette page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'client') {
    header("Location: index.php");
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

// Récupérer l'ID du client actuel
$clientStmt = $pdo->prepare("SELECT IdClient FROM client c JOIN utilisateurs u ON c.IdUtilisateur = u.IdUtilisateur WHERE u.Username = ?");
$clientStmt->execute([$_SESSION['username']]);
$client = $clientStmt->fetch(PDO::FETCH_ASSOC);

// Si le client n'existe pas, rediriger vers le tableau de bord
if (!$client) {
    echo "<script>alert('Client introuvable');</script>";
    header("Location: client_dashboard.php");
    exit();
}

// Récupérer la liste des intervenants disponibles
$intervenantsStmt = $pdo->query("SELECT IdIntervenant, Nom, Prenom, Poste FROM intervenant");
$intervenants = $intervenantsStmt->fetchAll(PDO::FETCH_ASSOC);

// Ajouter une nouvelle intervention si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $date = htmlspecialchars(trim($_POST['date']));
    $type = htmlspecialchars(trim($_POST['type']));
    $motive = htmlspecialchars(trim($_POST['motive']));
    $idClient = $client['IdClient'];
    $etat = "En Attente"; // Par défaut, l'état est "En Attente"
    $idIntervenant = isset($_POST['intervenant']) ? intval($_POST['intervenant']) : null;

    // Valider les champs
    if (empty($date) || empty($type) || empty($motive) || !$idIntervenant) {
        $error = "Veuillez remplir tous les champs.";
    } elseif (strtotime($date) < time()) {
        $error = "La date doit être future ou aujourd'hui.";
    } else {
        // Préparer et exécuter la requête SQL pour ajouter l'intervention
        $insertStmt = $pdo->prepare("
            INSERT INTO intervention (Date, Type, Motive, Etat, IdIntervenant, IdClient)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        try {
            $insertStmt->execute([$date, $type, $motive, $etat, $idIntervenant, $idClient]);
            echo "<script>alert('Intervention ajoutée avec succès !'); window.location.href='client_dashboard.php';</script>";
            exit();
        } catch (PDOException $e) {
            $error = "Erreur lors de l'ajout de l'intervention : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter une Intervention - Client</title>
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
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Ajouter une Intervention</h1>

            <!-- Afficher les messages d'erreur s'il y en a -->
            <?php if (isset($error)): ?>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- Formulaire d'ajout d'intervention -->
            <form action="add_interventionI.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="date" class="form-label">Date :</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                    <div class="invalid-feedback">Veuillez sélectionner une date valide.</div>
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Type :</label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="Soft">Soft</option>
                        <option value="Hard">Hard</option>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un type d'intervention.</div>
                </div>
                <div class="mb-3">
                    <label for="motive" class="form-label">Motif :</label>
                    <textarea id="motive" name="motive" class="form-control" rows="4" required></textarea>
                    <div class="invalid-feedback">Veuillez entrer un motif valide.</div>
                </div>
                <div class="mb-3">
                    <label for="intervenant" class="form-label">Intervenant :</label>
                    <select id="intervenant" name="intervenant" class="form-select" required>
                        <option value="" disabled selected>Sélectionnez un intervenant</option>
                        <?php foreach ($intervenants as $intervenant): ?>
                            <option value="<?= htmlspecialchars($intervenant['IdIntervenant']) ?>">
                                <?= htmlspecialchars($intervenant['Nom']) ?> <?= htmlspecialchars($intervenant['Prenom']) ?> (<?= htmlspecialchars($intervenant['Poste']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un intervenant.</div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
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