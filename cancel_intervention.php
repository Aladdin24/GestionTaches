<?php
session_start();

// Vérification de la session : Seul un utilisateur connecté peut accéder à cette page
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
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<script>alert('ID d\'intervention manquant ou invalide');</script>";
    header("Location: client_dashboard.php");
    exit();
}
$idIntervention = intval($_GET['id']);

// Récupérer les détails de l'intervention
$interventionStmt = $pdo->prepare("
    SELECT i.Id, i.Etat, c.Nom AS ClientNom, c.Prenom AS ClientPrenom
    FROM intervention i
    LEFT JOIN client c ON i.IdClient = c.IdClient
    WHERE i.Id = ?
");
$interventionStmt->execute([$idIntervention]);
$intervention = $interventionStmt->fetch(PDO::FETCH_ASSOC);

// Si l'intervention n'existe pas, rediriger vers le tableau de bord
if (!$intervention) {
    echo "<script>alert('Intervention introuvable');</script>";
    header("Location: client_dashboard.php");
    exit();
}

// Vérifier si l'utilisateur est autorisé à annuler cette intervention
if ($_SESSION['role'] === 'client') {
    // Un client ne peut annuler que ses propres interventions
    if ($intervention['ClientNom'] . " " . $intervention['ClientPrenom'] == $_SESSION['username']) {
       // echo "<script>alert('Vous n\'êtes pas autorisé à annuler cette intervention');</script>";
        echo "<script>window.location.href='client_dashboard.php';</script>";
       // header("Location: client_dashboard.php");
        exit();
    }
}

// Vérifier si l'intervention est encore en attente
if ($intervention['Etat'] !== 'En Attente') {
    echo "<script>alert('Cette intervention ne peut pas être annulée car elle a déjà été réalisée ou annulée précédemment.');</script>";
    header("Location: client_dashboard.php");
    exit();
}

// Mettre à jour l'état de l'intervention
$updateStmt = $pdo->prepare("UPDATE intervention SET Etat = 'Annulee' WHERE Id = ?");
try {
    $updateStmt->execute([$idIntervention]);
    echo "<script>alert('Intervention annulée avec succès !'); window.location.href='client_dashboard.php';</script>";
    exit();
} catch (PDOException $e) {
    echo "<script>alert('Erreur lors de l\'annulation de l\'intervention : " . $e->getMessage() . "');</script>";
    header("Location: client_dashboard.php");
    exit();
}
?>