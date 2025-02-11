<?php
session_start();

// Vérification de la session : Seul un utilisateur connecté avec le rôle "intervenant" peut accéder à cette page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'intervenant') {
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

// Récupérer les détails de l'intervenant actuel
$intervenantStmt = $pdo->prepare("SELECT * FROM intervenant i JOIN utilisateurs u ON i.IdUtilisateur = u.IdUtilisateur WHERE u.Username = ?");
$intervenantStmt->execute([$_SESSION['username']]);
$intervenant = $intervenantStmt->fetch(PDO::FETCH_ASSOC);

// Si l'intervenant n'existe pas, rediriger vers la page d'accueil
if (!$intervenant) {
    echo "<script>alert('Intervenant introuvable');</script>";
    header("Location: index.php");
    exit();
}

// Récupérer les interventions attribuées à cet intervenant
$interventionStmt = $pdo->prepare("
    SELECT i.Id, i.Date, i.Type, i.Motive, i.Etat, c.Nom AS ClientNom, c.Prenom AS ClientPrenom
    FROM intervention i
    LEFT JOIN client c ON i.IdClient = c.IdClient
    WHERE i.IdIntervenant = ?
");
$interventionStmt->execute([$intervenant['IdIntervenant']]);
$interventions = $interventionStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de Bord - Intervenant</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }
        .dashboard-container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #343a40;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004990;
        }
        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #dc3545; /* Rouge */
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .logout-button:hover {
            background-color: #a71d2a; /* Rouge foncé au survol */
        }
        .table th, .table td {
            vertical-align: middle;
        }
        .table thead th {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f8f9fa;
        }
        .action-btns a {
            margin-right: 5px;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <!-- Bouton de Déconnexion -->
    <button class="logout-button" onclick="location.href='logout.php'">Se Déconnecter</button>

    <div class="container">
        <div class="dashboard-container">
            <!-- Titre de Bienvenue -->
            <h1>Bienvenue, <?= htmlspecialchars($intervenant['Nom']) ?> <?= htmlspecialchars($intervenant['Prenom']) ?> !</h1>

            <!-- Informations de l'Intervenant -->
            <div class="mb-4">
                <h2 class="text-center mb-3">Mes Informations</h2>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <ul class="list-group">
                            <li class="list-group-item"><strong>Nom :</strong> <?= htmlspecialchars($intervenant['Nom']) ?></li>
                            <li class="list-group-item"><strong>Prénom :</strong> <?= htmlspecialchars($intervenant['Prenom']) ?></li>
                            <li class="list-group-item"><strong>Poste :</strong> <?= htmlspecialchars($intervenant['Poste']) ?></li>
                        </ul>
                        <div class="text-center mt-3">
                            <a href="edit_intervenantI.php?id=<?= htmlspecialchars($intervenant['IdIntervenant']) ?>" class="btn btn-primary w-100">Modifier mes informations</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Liste des Interventions -->
            <div>
                <h2 class="text-center mb-3">Mes Interventions</h2>
                <?php if (empty($interventions)): ?>
                    <p class="no-data">Aucune intervention attribuée pour le moment.</p>
                <?php else: ?>
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Motif</th>
                                <th>Client</th>
                                <th>État</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($interventions as $intervention): ?>
                                <tr>
                                    <td><?= htmlspecialchars($intervention['Date']) ?></td>
                                    <td><?= htmlspecialchars($intervention['Type']) ?></td>
                                    <td><?= htmlspecialchars($intervention['Motive']) ?></td>
                                    <td>
                                        <?= htmlspecialchars($intervention['ClientNom'] ?? '-') ?> 
                                        <?= htmlspecialchars($intervention['ClientPrenom'] ?? '') ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= ($intervention['Etat'] === 'En Attente') ? 'bg-warning' : 'bg-success' ?>">
                                            <?= htmlspecialchars($intervention['Etat']) ?>
                                        </span>
                                    </td>
                                    <td class="action-btns">
                                        <a href="view_in.php?id=<?= htmlspecialchars($intervention['Id']) ?>" class="btn btn-sm btn-info">Voir</a>
                                        <?php if ($intervention['Etat'] === 'En Attente'): ?>
                                            <a href="mark_as_realisee.php?id=<?= htmlspecialchars($intervention['Id']) ?>" class="btn btn-sm btn-success" onclick="return confirm('Êtes-vous sûr de vouloir marquer cette intervention comme réalisée ?')">Réalisée</a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Inclure Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>