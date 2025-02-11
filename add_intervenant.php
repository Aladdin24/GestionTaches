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
    $poste = htmlspecialchars($_POST['poste']);

    // Préparer et exécuter la requête SQL pour insérer un nouvel intervenant
    $stmt = $pdo->prepare("INSERT INTO Intervenant (Nom, Prenom, Poste) VALUES (?, ?, ?)");
    try {
        $stmt->execute([$nom, $prenom, $poste]);
        echo "<script>alert('Intervenant ajouté avec succès !');</script>";
    } catch (PDOException $e) {
        echo "<script>alert('Erreur lors de l'ajout de l'intervenant : " . $e->getMessage() . "');</script>";
    }
}

// Rediriger vers la page du tableau de bord après l'ajout
header("Location: dashboard.php");
exit();
?>