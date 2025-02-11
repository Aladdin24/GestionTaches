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

// Préparer et exécuter la requête SQL pour supprimer le client
$deleteStmt = $pdo->prepare("DELETE FROM Client WHERE IdClient = ?");
try {
    $deleteStmt->execute([$idClient]);
    echo "<script>alert('Client supprimé avec succès !');</script>";
} catch (PDOException $e) {
    echo "<script>alert('Erreur lors de la suppression du client : " . $e->getMessage() . "');</script>";
}

// Rediriger vers la liste des clients après la suppression
header("Location: Liste_clients.php");
exit();
?>