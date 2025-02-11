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

// Récupérer l'ID de l'intervenant depuis la requête GET
if (!isset($_GET['id'])) {
    echo "<script>alert('ID d\'intervenant manquant');</script>";
    header("Location: dashboard.php");
    exit();
}

$idIntervenant = htmlspecialchars($_GET['id']);

// Préparer et exécuter la requête SQL pour rechercher l'intervenant
$stmt = $pdo->prepare("SELECT * FROM Intervenant WHERE IdIntervenant = ?");
$stmt->execute([$idIntervenant]);
$intervenant = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'intervenant existe
if (!$intervenant) {
    echo "<script>alert('Aucun intervenant trouvé avec cet ID');</script>";
    echo "<script>window.location.href='dashboard.php';</script>";
    exit(); // Assurez-vous que le script s'arrête après la redirection
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultat de la Recherche</title>
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

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }

        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="form-container">
            <h1>Détails de l'Intervenant</h1>
            <!-- Afficher les détails de l'intervenant dans un tableau -->
            <table class="table table-bordered table-striped">
                <tbody>
                    <tr>
                        <th scope="row">ID</th>
                        <td><?php echo htmlspecialchars($intervenant['IdIntervenant']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Nom</th>
                        <td><?php echo htmlspecialchars($intervenant['Nom']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Prénom</th>
                        <td><?php echo htmlspecialchars($intervenant['Prenom']); ?></td>
                    </tr>
                    <tr>
                        <th scope="row">Poste</th>
                        <td><?php echo htmlspecialchars($intervenant['Poste']); ?></td>
                    </tr>
                </tbody>
            </table>

            <!-- Boutons Modifier et Supprimer -->
            <div class="d-flex justify-content-between mt-4">
                <!-- Bouton Modifier -->
                <form action="edit_intervenant.php" method="GET" class="d-inline">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($intervenant['IdIntervenant']); ?>">
                    <button type="submit" class="btn btn-success">Modifier</button>
                </form>

                <!-- Bouton Supprimer -->
                <form action="delete_intervenant.php" method="GET" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet intervenant ?')" class="d-inline">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($intervenant['IdIntervenant']); ?>">
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>

            <!-- Lien de retour au tableau de bord -->
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn btn-primary">Retour au Tableau de Bord</a>
            </div>
        </div>
    </div>

    <!-- Inclure Bootstrap JS et ses dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>