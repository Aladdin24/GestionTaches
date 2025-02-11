<?php
header('Content-Type: application/json');

try {
    $pdo = new PDO("mysql:host=localhost;dbname=gst", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query("
        SELECT Etat, COUNT(*) AS count
        FROM Intervention
        GROUP BY Etat
    ");

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if ($row['Etat'] === 'En Attente') {
            $data[0] = $row['count'];
        } elseif ($row['Etat'] === 'Realisee') {
            $data[1] = $row['count'];
        }
    }

    echo json_encode($data);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>