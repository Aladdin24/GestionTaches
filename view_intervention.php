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
    SELECT i.Id, i.Date, i.Type, i.Motive, i.Etat, iv.Nom AS IntervenantNom, iv.Prenom AS IntervenantPrenom, c.Nom AS ClientNom, c.Prenom AS ClientPrenom, i.IdIntervenant
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
    if ($intervention['ClientNom'] . " " . $intervention['ClientPrenom'] == $_SESSION['username']) {
        echo "<script>alert('Vous n'êtes pas autorisé à voir cette intervention');</script>";
        header("Location: client_dashboard.php");
        exit();
    }
}

// Récupérer la liste des intervenants disponibles
$intervenantsStmt = $pdo->query("SELECT IdIntervenant, Nom, Prenom, Poste FROM intervenant");
$intervenants = $intervenantsStmt->fetchAll(PDO::FETCH_ASSOC);

// Gestion de la modification de l'intervention
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if ($intervention['Etat'] !== 'En Attente') {
        echo "<script>alert('Cette intervention ne peut pas être modifiée car elle a déjà été réalisée ou annulée.');</script>";
        header("Location: client_dashboard.php");
        exit();
    }

    $date = htmlspecialchars(trim($_POST['date']));
    $type = htmlspecialchars(trim($_POST['type']));
    $motive = htmlspecialchars(trim($_POST['motive']));
    $idIntervenant = isset($_POST['intervenant']) ? intval($_POST['intervenant']) : null;

    if (empty($date) || empty($type) || empty($motive) || !$idIntervenant) {
        echo "<script>alert('Veuillez remplir tous les champs');</script>";
    } else {
        $updateStmt = $pdo->prepare("UPDATE intervention SET Date = ?, Type = ?, Motive = ?, IdIntervenant = ? WHERE Id = ?");
        try {
            $updateStmt->execute([$date, $type, $motive, $idIntervenant, $idIntervention]);
            echo "<script>alert('Intervention mise à jour avec succès !'); window.location.href='client_dashboard.php';</script>";
            exit();
        } catch (PDOException $e) {
            echo "<script>alert('Erreur lors de la mise à jour de l\'intervention : " . $e->getMessage() . "');</script>";
        }
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
            <?php if ($_SERVER["REQUEST_METHOD"] !== "POST" || isset($_GET['edit'])) : ?>
                <div>
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
                    <!-- Boutons Actions -->
                    <div class="text-center">
                        <?php if ($intervention['Etat'] === 'En Attente'): ?>
                            <a href="?id=<?= htmlspecialchars($intervention['Id']) ?>&edit=true" class="btn btn-warning me-2">Modifier</a>
                            <a href="cancel_intervention.php?id=<?= htmlspecialchars($intervention['Id']) ?>" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette intervention ?')">Annuler</a>
                        <?php endif; ?>
                        <a href="client_dashboard.php" class="btn btn-primary mt-3">Retour au Tableau de Bord</a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulaire de Modification -->
            <?php if ($_SERVER["REQUEST_METHOD"] !== "POST" && isset($_GET['edit'])) : ?>
                <div class="form-container">
                    <h2 class="text-center mb-3">Modifier l'Intervention</h2>
                    <form action="" method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="date" class="form-label">Date :</label>
                            <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($intervention['Date']) ?>" required>
                            <div class="invalid-feedback">Veuillez sélectionner une date valide.</div>
                        </div>
                        <div class="mb-3">
                            <label for="type" class="form-label">Type :</label>
                            <select id="type" name="type" class="form-select" required>
                                <option value="Soft" <?= $intervention['Type'] === 'Soft' ? 'selected' : '' ?>>Soft</option>
                                <option value="Hard" <?= $intervention['Type'] === 'Hard' ? 'selected' : '' ?>>Hard</option>
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un type d'intervention.</div>
                        </div>
                        <div class="mb-3">
                            <label for="motive" class="form-label">Motif :</label>
                            <textarea id="motive" name="motive" class="form-control" rows="4" required><?= htmlspecialchars($intervention['Motive']) ?></textarea>
                            <div class="invalid-feedback">Veuillez entrer un motif valide.</div>
                        </div>
                        <div class="mb-3">
                            <label for="intervenant" class="form-label">Intervenant :</label>
                            <select id="intervenant" name="intervenant" class="form-select" required>
                                <option value="" disabled selected>Sélectionnez un intervenant</option>
                                <?php foreach ($intervenants as $intervenant): ?>
                                    <option value="<?= htmlspecialchars($intervenant['IdIntervenant']) ?>" 
                                        <?= $intervenant['IdIntervenant'] == $intervention['IdIntervenant'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($intervenant['Nom']) ?> <?= htmlspecialchars($intervenant['Prenom']) ?> (<?= htmlspecialchars($intervenant['Poste']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un intervenant.</div>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="btn btn-success me-2">Enregistrer</button>
                            <a href="?id=<?= htmlspecialchars($intervention['Id']) ?>" class="btn btn-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Inclure Bootstrap JS -->
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