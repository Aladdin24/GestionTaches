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

// Récupérer l'ID du client depuis l'URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID de client manquant');</script>";
    header("Location: Liste_clients.php");
    exit();
}

$idClient = htmlspecialchars($_GET['id']);

// Récupérer les détails du client à partir de la base de données
$stmt = $pdo->prepare("SELECT * FROM Client WHERE IdClient = ?");
$stmt->execute([$idClient]);
$client = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si le client existe
if (!$client) {
    echo "<script>alert('Client introuvable');</script>";
    header("Location: Liste_clients.php");
    exit();
}

// Mettre à jour les données du client si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les nouvelles données du formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $direction = htmlspecialchars($_POST['direction']);

    // Préparer et exécuter la requête SQL pour mettre à jour le client
    $updateStmt = $pdo->prepare("UPDATE Client SET Nom = ?, Prenom = ?, Direction = ? WHERE IdClient = ?");
    try {
        $updateStmt->execute([$nom, $prenom, $direction, $idClient]);
        echo "<script>alert('Client mis à jour avec succès !');</script>";
        header("Location: Liste_clients.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de la mise à jour du client : " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Client</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Modifier Client</h1>

        <!-- Formulaire de modification -->
        <form action="edit_client.php?id=<?php echo $idClient; ?>" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="nom" class="form-label">Nom :</label>
                <input type="text" id="nom" name="nom" class="form-control" value="<?php echo htmlspecialchars($client['Nom']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="prenom" class="form-label">Prénom :</label>
                <input type="text" id="prenom" name="prenom" class="form-control" value="<?php echo htmlspecialchars($client['Prenom']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="direction" class="form-label">Direction :</label>
                <input type="text" id="direction" name="direction" class="form-control" value="<?php echo htmlspecialchars($client['Direction']); ?>" required>
            </div>
            <button type="submit" class="btn btn-primary">Mettre à Jour</button>
            <a href="Liste_clients.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>

    <!-- Inclure Bootstrap JS et ses dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>