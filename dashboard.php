<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['role'] !== 'admin') {
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
    die("Erreur de connexion : " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            padding: 20px;
            background-color: #f8f9fa;
        }
        h1, h2 {
            color: #343a40;
        }
        .table {
            margin-top: 20px;
        }
        .chart-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .chart-box {
            width: 48%;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        @media (max-width: 768px) {
            .chart-container {
                flex-direction: column;
            }
            .chart-box {
                width: 100%;
            }
        }
        /* Style pour le bouton de déconnexion */
        .logout-button {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #dc3545; /* Rouge */
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            border-radius: 4px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .logout-button:hover {
            background-color: #a71d2a; /* Rouge foncé au survol */
        }
    </style>
</head>
<body>
    <button class="logout-button" onclick="location.href='logout.php'">Se Déconnecter</button>
    <div class="container">
        <h1 class="text-center mb-4">Gestion des Tâches</h1>

        <!-- Actions principales -->
        <div class="row mb-4">
        <div class="col-md-4">
                <h2><a href="dashboard.php" class="btn btn-info w-100">Liste des Intervenants</a></h2>
            </div>
            <div class="col-md-4">
                <h2><a href="Liste_Intervention.php" class="btn btn-success w-100">Liste des Intervention</a></h2>
            </div>
            <div class="col-md-4">
                <h2><a href="Liste_clients.php" class="btn btn-primary w-100">Liste des clients</a></h2>
            </div>
            
           
        </div>

        <!-- Tableau des intervenants -->
        <h2 class="mb-3">Rechercher un Intervenant</h2>
<form action="search_intervenant.php" method="GET" class="row g-3 align-items-center">
    <div class="col-auto">
        <!-- Champ de recherche -->
        <input 
            type="text" 
            name="id" 
            class="form-control" 
            placeholder="ID de l'intervenant" 
            aria-label="ID de l'intervenant" 
            required
        >
    </div>
    <div class="col-auto">
        <!-- Bouton de soumission -->
        <button type="submit" class="btn btn-info w-100">Rechercher</button>
    </div>
    <div class="col-md-4">
                <h2><a href="dashboardAj.php" class="btn btn-info w-100">Ajouter un Intervenant</a></h2>
            </div>
</form>
        <h2 class="mb-3">Liste des Intervenants</h2>
        <table class="table table-bordered table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Poste</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $stmt = $pdo->query("SELECT * FROM Intervenant");
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['IdIntervenant']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Nom']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Prenom']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['Poste']) . "</td>";
                    echo "<td>
                            <a href='edit_intervenant.php?id=" . htmlspecialchars($row['IdIntervenant']) . "' class='btn btn-sm btn-warning me-2'>Modifier</a>
                            <a href='delete_intervenant.php?id=" . htmlspecialchars($row['IdIntervenant']) . "' class='btn btn-sm btn-danger' onclick=\"return confirm('Êtes-vous sûr ?')\">Supprimer</a>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Statistiques -->
        <h2 class="mt-5 mb-3">Statistiques des Tâches</h2>
        <div class="chart-container">
            <div class="chart-box">
                <h3 class="text-center">Tâches Réalisées par Intervenant</h3>
                <canvas id="intervenantChart"></canvas>
            </div>
            <div class="chart-box">
                <h3 class="text-center">État des Tâches</h3>
                <canvas id="etatChart"></canvas>
            </div>
        </div>

        <!-- Boutons d'exportation -->
        <div class="text-center mt-4">
            <button class="btn btn-primary" onclick="exportPDF()">Exporter en PDF</button>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        const ctx1 = document.getElementById('intervenantChart').getContext('2d');
        const ctx2 = document.getElementById('etatChart').getContext('2d');

        // Fetch data for intervenant chart
        fetch('stats_intervenants.php')
            .then(response => response.json())
            .then(data => {
                new Chart(ctx1, {
                    type: 'pie',
                    data: {
                        labels: data.labels,
                        datasets: [{
                            label: 'Tâches Réalisées',
                            data: data.data,
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        }
                    }
                });
            });

        // Fetch data for état chart
        fetch('stats_etats.php')
            .then(response => response.json())
            .then(data => {
                new Chart(ctx2, {
                    type: 'pie',
                    data: {
                        labels: ['En Attente', 'Réalisée'],
                        datasets: [{
                            label: 'État des Tâches',
                            data: data,
                            backgroundColor: ['#FF6384', '#36A2EB']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top'
                            }
                        }
                    }
                });
            });

        // Export to PDF function
        function exportPDF() {
            window.location.href = 'export_stats.php';
        }
    </script>

    <!-- Bootstrap JS (Optional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>