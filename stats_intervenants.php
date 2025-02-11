<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=gst", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT i.Nom, i.Prenom, COUNT(*) AS count
        FROM Intervention iv
        JOIN Intervenant i ON iv.IdIntervenant = i.IdIntervenant
        WHERE iv.Etat = 'Realisee'
        GROUP BY iv.IdIntervenant
    ");

    $data = [];
    $labels = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $labels[] = $row['Nom'] . " " . $row['Prenom'];
        $data[] = $row['count'];
    }

    echo json_encode(['labels' => $labels, 'data' => $data]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>