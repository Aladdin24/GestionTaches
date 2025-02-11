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

// Récupérer tous les clients
$stmt = $pdo->query("SELECT * FROM Client");
$clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Clients</title>
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
    <div class="container">
    <div class="col-md-4">
                <h2><a href="dashboardAjCl.php" class="btn btn-primary w-100">Ajouter client</a></h2>
            </div>
        <h1 class="text-center mb-4">Liste des Clients</h1>

        <!-- Tableau des clients -->
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Nom</th>
                    <th scope="col">Prénom</th>
                    <th scope="col">Direction</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($clients)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted">Aucun client enregistré.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($clients as $index => $client): ?>
                        <tr>
                            <th scope="row"><?php echo $index + 1; ?></th>
                            <td><?php echo htmlspecialchars($client['Nom']); ?></td>
                            <td><?php echo htmlspecialchars($client['Prenom']); ?></td>
                            <td><?php echo htmlspecialchars($client['Direction']); ?></td>
                            <td>
                                <!-- Bouton Modifier -->
                                <form action="edit_client.php" method="GET" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($client['IdClient']); ?>">
                                    <button type="submit" class="btn btn-sm btn-primary">Modifier</button>
                                </form>

                                <!-- Bouton Supprimer -->
                                <form action="delete_client.php" method="GET" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce client ?')" class="d-inline">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($client['IdClient']); ?>">
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