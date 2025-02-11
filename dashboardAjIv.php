<?php
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
    die("Erreur de connexion : " . $e->getMessage());
}

// Gestion des messages flash
function flash($key, $message = null) {
    if ($message) {
        $_SESSION[$key] = $message;
    } elseif (isset($_SESSION[$key])) {
        $msg = $_SESSION[$key];
        unset($_SESSION[$key]);
        return $msg;
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- SweetAlert2 pour les alertes personnalisées -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004990;
        }

        .alert-flash {
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Messages Flash -->
        <?php if ($msg = flash('success')) : ?>
            <div class="alert alert-success alert-flash" role="alert">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($msg = flash('error')) : ?>
            <div class="alert alert-danger alert-flash" role="alert">
                <?= htmlspecialchars($msg) ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <h2>Ajouter une Intervention</h2>
            <!-- Formulaire d'ajout d'intervention -->
            <form action="add_intervention.php" method="POST" class="needs-validation" novalidate id="interventionForm">
                <div class="mb-3">
                    <label for="date" class="form-label">Date :</label>
                    <input type="date" id="date" name="date" class="form-control" required>
                    <div class="invalid-feedback">Veuillez sélectionner une date valide.</div>
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Type :</label>
                    <select id="type" name="type" class="form-select" required>
                        <option value="" disabled selected>Sélectionnez un type</option>
                        <option value="Soft">Soft</option>
                        <option value="Hard">Hard</option>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un type valide.</div>
                </div>
                <div class="mb-3">
                    <label for="motive" class="form-label">Motif :</label>
                    <textarea id="motive" name="motive" class="form-control" rows="3" placeholder="Entrez le motif" required></textarea>
                    <div class="invalid-feedback">Veuillez entrer un motif valide.</div>
                </div>
                <div class="mb-3">
                    <label for="etat" class="form-label">État :</label>
                    <select id="etat" name="etat" class="form-select" required>
                        <option value="" disabled selected>Sélectionnez un état</option>
                        <option value="En Attente">En Attente</option>
                        <option value="Realisee">Réalisée</option>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un état valide.</div>
                </div>
                <div class="mb-3">
                    <label for="id_intervenant" class="form-label">Intervenant :</label>
                    <select id="id_intervenant" name="id_intervenant" class="form-select" required>
                        <option value="" disabled selected>Sélectionnez un intervenant</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM Intervenant");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($row['IdIntervenant']) . "'>" . htmlspecialchars($row['Nom'] . " " . $row['Prenom']) . "</option>";
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un intervenant valide.</div>
                </div>
                <div class="mb-3">
                    <label for="id_client" class="form-label">Client :</label>
                    <select id="id_client" name="id_client" class="form-select" required>
                        <option value="" disabled selected>Sélectionnez un client</option>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM Client");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            echo "<option value='" . htmlspecialchars($row['IdClient']) . "'>" . htmlspecialchars($row['Nom'] . " " . $row['Prenom']) . "</option>";
                        }
                        ?>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un client valide.</div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Ajouter</button>
                    <a href="Liste_Intervention.php" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Inclure Bootstrap JS et ses dépendances -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Validation du formulaire -->
    <script>
        // Example starter JavaScript for disabling form submissions if there are invalid fields
        (function () {
            'use strict';

            // Fetch all the forms we want to apply custom Bootstrap validation styles to
            var forms = document.querySelectorAll('.needs-validation');

            // Loop over them and prevent submission
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();

        // Gérer les messages flash avec SweetAlert2
        document.addEventListener('DOMContentLoaded', function () {
            const successMessage = "<?php echo htmlspecialchars(flash('success')); ?>";
            const errorMessage = "<?php echo htmlspecialchars(flash('error')); ?>";

            if (successMessage) {
                Swal.fire({
                    icon: 'success',
                    title: 'Succès',
                    text: successMessage,
                    timer: 3000,
                    showConfirmButton: false
                });
            }

            if (errorMessage) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erreur',
                    text: errorMessage,
                    timer: 5000,
                    showConfirmButton: false
                });
            }
        });

        // Recherche rapide dans les listes déroulantes
        $(document).ready(function () {
            $('#id_intervenant, #id_client').select2({
                placeholder: 'Rechercher...',
                allowClear: true
            });
        });
    </script>

    <!-- Ajouter Select2 pour la recherche rapide -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</body>

</html>