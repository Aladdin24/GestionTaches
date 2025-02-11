<?php
session_start();

// Vérification de la session : Seul un utilisateur connecté avec le rôle "intervenant" peut accéder à cette page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'intervenant') {
    header("Location: index.php");
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
    header("Location: intervenant_dashboard.php");
    exit();
}
$idIntervention = intval($_GET['id']);

// Récupérer les détails de l'intervention
$interventionStmt = $pdo->prepare("
    SELECT i.Id, i.Etat, i.IdIntervenant
    FROM intervention i
    WHERE i.Id = ?
");
$interventionStmt->execute([$idIntervention]);
$intervention = $interventionStmt->fetch(PDO::FETCH_ASSOC);

// Si l'intervention n'existe pas, rediriger vers le tableau de bord
if (!$intervention) {
    echo "<script>alert('Intervention introuvable');</script>";
    header("Location: intervenant_dashboard.php");
    exit();
}

// Vérifier si l'intervenant actuel est autorisé à marquer cette intervention comme réalisée
$intervenantStmt = $pdo->prepare("SELECT IdIntervenant FROM intervenant i JOIN utilisateurs u ON i.IdUtilisateur = u.IdUtilisateur WHERE u.Username = ?");
$intervenantStmt->execute([$_SESSION['username']]);
$intervenant = $intervenantStmt->fetch(PDO::FETCH_ASSOC);

if (!$intervenant || $intervention['IdIntervenant'] !== $intervenant['IdIntervenant']) {
    echo "<script>alert('Vous n\'êtes pas autorisé à marquer cette intervention comme réalisée');</script>";
    header("Location: intervenant_dashboard.php");
    exit();
}

// Vérifier si l'intervention est encore en attente
if ($intervention['Etat'] !== 'En Attente') {
    echo "<script>alert('Cette intervention ne peut pas être marquée comme réalisée car elle a déjà été traitée ou annulée.');</script>";
    header("Location: intervenant_dashboard.php");
    exit();
}

// Mettre à jour l'état de l'intervention
$updateStmt = $pdo->prepare("UPDATE intervention SET Etat = 'Realisee' WHERE Id = ?");
try {
    $updateStmt->execute([$idIntervention]);
    echo "<script>alert('Intervention marquée comme réalisée avec succès !'); window.location.href='intervenant_dashboard.php';</script>";
    exit();
} catch (PDOException $e) {
    echo "<script>alert('Erreur lors de la mise à jour de l\'intervention : " . $e->getMessage() . "');</script>";
    header("Location: intervenant_dashboard.php");
    exit();
}
?>