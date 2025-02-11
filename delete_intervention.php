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

// Préparer et exécuter la requête SQL pour supprimer l'intervention
$deleteStmt = $pdo->prepare("DELETE FROM Intervention WHERE Id = ?");
try {
    $deleteStmt->execute([$idIntervention]);
    echo "<script>alert('Intervention supprimée avec succès !');</script>";
} catch (PDOException $e) {
    echo "<script>alert('Erreur lors de la suppression de l\'intervention : " . $e->getMessage() . "');</script>";
}

// Rediriger vers la liste des interventions après la suppression
header("Location: Liste_Intervention.php");
exit();
?>