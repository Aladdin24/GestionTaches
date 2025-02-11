<?php
// Inclure la bibliothèque TCPDF
require_once('tcpdf/tcpdf.php');

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

// Récupérer les statistiques des tâches réalisées par intervenant
$stmtIntervenants = $pdo->query("
    SELECT i.Nom, i.Prenom, COUNT(*) AS count
    FROM Intervention iv
    JOIN Intervenant i ON iv.IdIntervenant = i.IdIntervenant
    WHERE iv.Etat = 'Realisee'
    GROUP BY iv.IdIntervenant
");
$statsIntervenants = [];
while ($row = $stmtIntervenants->fetch(PDO::FETCH_ASSOC)) {
    $statsIntervenants[] = [
        'intervenant' => $row['Nom'] . " " . $row['Prenom'],
        'count' => $row['count']
    ];
}

// Récupérer les statistiques des états des tâches
$stmtEtats = $pdo->query("
    SELECT Etat, COUNT(*) AS count
    FROM Intervention
    GROUP BY Etat
");
$statsEtats = [];
while ($row = $stmtEtats->fetch(PDO::FETCH_ASSOC)) {
    $statsEtats[$row['Etat']] = $row['count'];
}

// Créer une instance de TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Définir les propriétés du document
$pdf->SetCreator('Gestion des Tâches');
$pdf->SetAuthor('Votre Nom');
$pdf->SetTitle('Statistiques des Tâches');
$pdf->SetSubject('Export des Statistiques');
$pdf->SetKeywords('Tâches, Statistiques, PDF');

// Ajouter une page
$pdf->AddPage();

// Définir la police et la taille
$pdf->SetFont('helvetica', '', 12);

// Titre du document
$pdf->Cell(0, 10, 'Statistiques des Tâches', 0, 1, 'C');
$pdf->Ln(10);

// Section 1 : Tâches Réalisées par Intervenant
$pdf->Cell(0, 10, 'Tâches Réalisées par Intervenant', 0, 1, 'L');
$pdf->Ln(5);

// Tableau pour les statistiques des intervenants
$pdf->SetFillColor(255, 255, 255); // Fond blanc
$pdf->SetTextColor(0); // Texte noir
$pdf->SetDrawColor(0, 0, 0); // Lignes noires

// En-tête du tableau
$pdf->Cell(100, 10, 'Intervenant', 1, 0, 'C', 1);
$pdf->Cell(40, 10, 'Nombre de Tâches', 1, 1, 'C', 1);

foreach ($statsIntervenants as $stat) {
    $pdf->Cell(100, 10, $stat['intervenant'], 1, 0, 'L', 1);
    $pdf->Cell(40, 10, $stat['count'], 1, 1, 'C', 1);
}
$pdf->Ln(10);

// Section 2 : États des Tâches
$pdf->Cell(0, 10, 'États des Tâches', 0, 1, 'L');
$pdf->Ln(5);

// Tableau pour les statistiques des états
$pdf->Cell(100, 10, 'État', 1, 0, 'C', 1);
$pdf->Cell(40, 10, 'Nombre de Tâches', 1, 1, 'C', 1);

foreach ($statsEtats as $etat => $count) {
    $pdf->Cell(100, 10, $etat, 1, 0, 'L', 1);
    $pdf->Cell(40, 10, $count, 1, 1, 'C', 1);
}

// Sortie du PDF
$pdf->Output('statistiques.pdf', 'I'); // 'I' pour afficher dans le navigateur