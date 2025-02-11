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

// Récupérer toutes les interventions
$stmt = $pdo->query("
    SELECT 
        i.Id AS InterventionId,
        i.Date,
        i.Type,
        i.Motive,
        i.Etat,
        iv.Nom AS IntervenantNom,
        iv.Prenom AS IntervenantPrenom,
        c.Nom AS ClientNom,
        c.Prenom AS ClientPrenom
    FROM Intervention i
    JOIN Intervenant iv ON i.IdIntervenant = iv.IdIntervenant
    JOIN Client c ON i.IdClient = c.IdClient
");
$interventions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Interventions</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding: 20px;
        }
        table {
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="col-md-4">
                <h2><a href="dashboardAjIv.php" class="btn btn-success w-100">Ajouter une Intervention</a></h2>
            </div>
    <div class="container">
        <h1 class="text-center mb-4">Liste des Interventions</h1>

        <!-- Tableau des interventions -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date</th>
                    <th scope="col">Type</th>
                    <th scope="col">Motif</th>
                    <th scope="col">État</th>
                    <th scope="col">Intervenant</th>
                    <th scope="col">Client</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($interventions)): ?>
                    <tr>
                        <td colspan="8" class="text-center text-muted">Aucune intervention enregistrée.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($interventions as $index => $intervention): ?>
                        <tr>
                            <th scope="row"><?php echo $index + 1; ?></th>
                            <td><?php echo htmlspecialchars($intervention['Date']); ?></td>
                            <td><?php echo htmlspecialchars($intervention['Type']); ?></td>
                            <td><?php echo htmlspecialchars($intervention['Motive']); ?></td>
                            <td><?php echo htmlspecialchars($intervention['Etat']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($intervention['IntervenantNom'] . ' ' . $intervention['IntervenantPrenom']); ?>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($intervention['ClientNom'] . ' ' . $intervention['ClientPrenom']); ?>
                            </td>
                            <td>
                                <!-- Bouton Modifier -->
                                <form action="edit_intervention.php" method="GET" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($intervention['InterventionId']); ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Modifier</button>
                                </form>

                                <!-- Bouton Supprimer -->
                                <form action="delete_intervention.php" method="GET" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette intervention ?')" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($intervention['InterventionId']); ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Supprimer</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Lien de retour au tableau de bord -->
        <div class="text-center mt-4">
            <a href="dashboard.php" class="btn btn-secondary">Retour au Tableau de Bord</a>
        </div>
    </div>

    <!-- Inclure Bootstrap JS et ses dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>