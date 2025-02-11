<?php
session_start();

// Vérification de la session : Seul un utilisateur connecté peut accéder à cette page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
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

// Récupérer l'ID de l'intervention depuis l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID d\'intervention manquant ou invalide');</script>";
    header("Location: client_dashboard.php");
    exit();
}
$idIntervention = intval($_GET['id']);

// Récupérer les détails de l'intervention
$interventionStmt = $pdo->prepare("
    SELECT i.Id, i.Date, i.Type, i.Motive, i.Etat, iv.Nom AS IntervenantNom, iv.Prenom AS IntervenantPrenom, c.Nom AS ClientNom, c.Prenom AS ClientPrenom
    FROM intervention i
    LEFT JOIN intervenant iv ON i.IdIntervenant = iv.IdIntervenant
    LEFT JOIN client c ON i.IdClient = c.IdClient
    WHERE i.Id = ?
");
$interventionStmt->execute([$idIntervention]);
$intervention = $interventionStmt->fetch(PDO::FETCH_ASSOC);

// Si l'intervention n'existe pas, rediriger vers le tableau de bord
if (!$intervention) {
    echo "<script>alert('Intervention introuvable');</script>";
    header("Location: client_dashboard.php");
    exit();
}

// Vérifier si l'utilisateur est autorisé à voir cette intervention
if ($_SESSION['role'] === 'client') {
    // Un client ne peut voir que ses propres interventions
    if ($intervention['ClientNom'] . " " . $intervention['ClientPrenom'] !== $_SESSION['username']) {
        echo "<script>alert('Vous n'êtes pas autorisé à voir cette intervention');</script>";
        header("Location: client_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de l'Intervention</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .details-container {
            max-width: 800px;
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
        <div class="details-container">
            <h1>Détails de l'Intervention</h1>

            <!-- Afficher les détails de l'intervention -->
            <div class="mb-3">
                <strong>ID :</strong> <?= htmlspecialchars($intervention['Id']) ?>
            </div>
            <div class="mb-3">
                <strong>Date :</strong> <?= htmlspecialchars($intervention['Date']) ?>
            </div>
            <div class="mb-3">
                <strong>Type :</strong> <?= htmlspecialchars($intervention['Type']) ?>
            </div>
            <div class="mb-3">
                <strong>Motif :</strong> <?= nl2br(htmlspecialchars($intervention['Motive'])) ?>
            </div>
            <div class="mb-3">
                <strong>État :</strong> 
                <span style="font-weight: bold; color: <?= $intervention['Etat'] === 'Realisee' ? 'green' : 'orange' ?>;">
                    <?= htmlspecialchars($intervention['Etat']) ?>
                </span>
            </div>
            <div class="mb-3">
                <strong>Client :</strong> 
                <?= htmlspecialchars($intervention['ClientNom'] ?? '-') ?> 
                <?= htmlspecialchars($intervention['ClientPrenom'] ?? '') ?>
            </div>
            <div class="mb-3">
                <strong>Intervenant :</strong> 
                <?= htmlspecialchars($intervention['IntervenantNom'] ?? '-') ?> 
                <?= htmlspecialchars($intervention['IntervenantPrenom'] ?? '') ?>
            </div>

            <!-- Bouton Retour -->
            <div class="text-center">
                <a href="intervenant_dashboard.php" class="btn btn-primary">Retour au Tableau de Bord</a>
            </div>
        </div>
    </div>

    <!-- Inclure Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>