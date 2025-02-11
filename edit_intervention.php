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

// Récupérer l'ID de l'intervention depuis l'URL
if (!isset($_GET['id'])) {
    echo "<script>alert('ID d\'intervention manquant');</script>";
    header("Location: Liste_Intervention.php");
    exit();
}

$idIntervention = htmlspecialchars($_GET['id']);

// Récupérer les détails de l'intervention à partir de la base de données
$stmt = $pdo->prepare("
    SELECT 
        i.Id,
        i.Date,
        i.Type,
        i.Motive,
        i.Etat,
        i.IdIntervenant,
        i.IdClient,
        iv.Nom AS IntervenantNom,
        iv.Prenom AS IntervenantPrenom,
        c.Nom AS ClientNom,
        c.Prenom AS ClientPrenom
    FROM Intervention i
    JOIN Intervenant iv ON i.IdIntervenant = iv.IdIntervenant
    JOIN Client c ON i.IdClient = c.IdClient
    WHERE i.Id = ?
");
$stmt->execute([$idIntervention]);
$intervention = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si l'intervention existe
if (!$intervention) {
    echo "<script>alert('Intervention introuvable');</script>";
    header("Location: Liste_Intervention.php");
    exit();
}

// Mettre à jour les données de l'intervention si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les nouvelles données du formulaire
    $date = htmlspecialchars($_POST['date']);
    $type = htmlspecialchars($_POST['type']);
    $motive = htmlspecialchars($_POST['motive']);
    $etat = htmlspecialchars($_POST['etat']);
    $idIntervenant = htmlspecialchars($_POST['id_intervenant']);
    $idClient = htmlspecialchars($_POST['id_client']);

    // Préparer et exécuter la requête SQL pour mettre à jour l'intervention
    $updateStmt = $pdo->prepare("
        UPDATE Intervention 
        SET Date = ?, Type = ?, Motive = ?, Etat = ?, IdIntervenant = ?, IdClient = ?
        WHERE Id = ?
    ");
    try {
        $updateStmt->execute([$date, $type, $motive, $etat, $idIntervenant, $idClient, $idIntervention]);
        echo "<script>alert('Intervention mise à jour avec succès !');</script>";
        header("Location: Liste_Intervention.php");
        exit();
    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de la mise à jour de l\'intervention : " . $e->getMessage() . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier Intervention</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1 class="text-center mb-4">Modifier Intervention</h1>

        <!-- Formulaire de modification -->
        <form action="edit_intervention.php?id=<?php echo $idIntervention; ?>" method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label for="date" class="form-label">Date :</label>
                <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($intervention['Date']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="type" class="form-label">Type :</label>
                <select id="type" name="type" class="form-select" required>
                    <option value="Soft" <?php if ($intervention['Type'] === 'Soft') echo 'selected'; ?>>Soft</option>
                    <option value="Hard" <?php if ($intervention['Type'] === 'Hard') echo 'selected'; ?>>Hard</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="motive" class="form-label">Motif :</label>
                <textarea id="motive" name="motive" class="form-control" rows="3" required><?php echo htmlspecialchars($intervention['Motive']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="etat" class="form-label">État :</label>
                <select id="etat" name="etat" class="form-select" required>
                    <option value="En Attente" <?php if ($intervention['Etat'] === 'En Attente') echo 'selected'; ?>>En Attente</option>
                    <option value="Realisee" <?php if ($intervention['Etat'] === 'Realisee') echo 'selected'; ?>>Réalisée</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_intervenant" class="form-label">Intervenant :</label>
                <select id="id_intervenant" name="id_intervenant" class="form-select" required>
                    <?php
                    $stmtIntervenants = $pdo->query("SELECT IdIntervenant, Nom, Prenom FROM Intervenant");
                    while ($row = $stmtIntervenants->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($row['IdIntervenant'] == $intervention['IdIntervenant']) ? 'selected' : '';
                        echo '<option value="' . $row['IdIntervenant'] . '" ' . $selected . '>' . htmlspecialchars($row['Nom'] . ' ' . $row['Prenom']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="id_client" class="form-label">Client :</label>
                <select id="id_client" name="id_client" class="form-select" required>
                    <?php
                    $stmtClients = $pdo->query("SELECT IdClient, Nom, Prenom FROM Client");
                    while ($row = $stmtClients->fetch(PDO::FETCH_ASSOC)) {
                        $selected = ($row['IdClient'] == $intervention['IdClient']) ? 'selected' : '';
                        echo '<option value="' . $row['IdClient'] . '" ' . $selected . '>' . htmlspecialchars($row['Nom'] . ' ' . $row['Prenom']) . '</option>';
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Mettre à Jour</button>
            <a href="Liste_Intervention.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>

    <!-- Inclure Bootstrap JS et ses dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>