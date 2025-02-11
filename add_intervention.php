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

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $date = htmlspecialchars($_POST['date']);
    $type = htmlspecialchars($_POST['type']);
    $motive = htmlspecialchars($_POST['motive']);
    $etat = htmlspecialchars($_POST['etat']);
    $idIntervenant = htmlspecialchars($_POST['id_intervenant']);
    $idClient = htmlspecialchars($_POST['id_client']);

    // Préparer et exécuter la requête SQL pour insérer une nouvelle intervention
    $stmt = $pdo->prepare("INSERT INTO Intervention (Date, Type, Motive, Etat, IdIntervenant, IdClient) VALUES (?, ?, ?, ?, ?, ?)");
    try {
        $stmt->execute([$date, $type, $motive, $etat, $idIntervenant, $idClient]);
        echo "<script>alert('Intervention ajoutée avec succès !');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de l\'ajout de l\'intervention : " . $e->getMessage() . "');</script>";
    }
}

// Rediriger vers la page du tableau de bord après l'ajout
header("Location: dashboard.php");
exit();
?>