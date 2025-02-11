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
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $direction = htmlspecialchars($_POST['direction']);

    // Préparer et exécuter la requête SQL pour insérer un nouveau client
    $stmt = $pdo->prepare("INSERT INTO Client (Nom, Prenom, Direction) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$nom, $prenom, $direction]);
        echo "<script>alert('Client ajouté avec succès !');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de l\'ajout du client : " . $e->getMessage() . "');</script>";
    }
}

// Rediriger vers la page du tableau de bord après l'ajout
header("Location: Liste_clients.php");
exit();
?>