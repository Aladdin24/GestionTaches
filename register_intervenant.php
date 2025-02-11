<?php
session_start();

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

// Gestion de la création du compte intervenant si le formulaire est soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $poste = htmlspecialchars(trim($_POST['poste']));

    // Valider les champs
    if (empty($username) || empty($password) || empty($nom) || empty($prenom) || empty($poste)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        // Vérifier si le nom d'utilisateur existe déjà
        $checkStmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE Username = ?");
        $checkStmt->execute([$username]);
        if ($checkStmt->fetch(PDO::FETCH_ASSOC)) {
            $error = "Ce nom d'utilisateur est déjà pris.";
        } else {
            // Hacher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insérer le nouvel utilisateur dans la table `utilisateurs`
            $insertUserStmt = $pdo->prepare("INSERT INTO utilisateurs (Username, Password, Role) VALUES (?, ?, 'intervenant')");
            $insertUserStmt->execute([$username, $hashedPassword]);

            // Récupérer l'ID de l'utilisateur nouvellement créé
            $idUtilisateur = $pdo->lastInsertId();

            // Insérer les détails de l'intervenant dans la table `intervenant`
            $insertIntervenantStmt = $pdo->prepare("INSERT INTO intervenant (Nom, Prenom, Poste, IdUtilisateur) VALUES (?, ?, ?, ?)");
            try {
                $insertIntervenantStmt->execute([$nom, $prenom, $poste, $idUtilisateur]);
                echo "<script>alert('Compte d'intervenant créé avec succès ! Vous pouvez maintenant vous connecter.');</script>";
                echo "<script>window.location.href='index.php';</script>";
                exit();
            } catch (PDOException $e) {
                $error = "Erreur lors de la création du compte : " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Intervenant</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        h1 {
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
        .error-message {
            color: red;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Inscription - Intervenant</h1>

            <!-- Afficher les messages d'erreur s'il y en a -->
            <?php if (isset($error)): ?>
                <p class="error-message"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <!-- Formulaire d'inscription -->
            <form action="registerIn.php" method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label for="username" class="form-label">Nom d'utilisateur :</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                    <div class="invalid-feedback">Veuillez entrer un nom d'utilisateur valide.</div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Mot de passe :</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                    <div class="invalid-feedback">Veuillez entrer un mot de passe valide.</div>
                </div>
                <div class="mb-3">
                    <label for="nom" class="form-label">Nom :</label>
                    <input type="text" id="nom" name="nom" class="form-control" required>
                    <div class="invalid-feedback">Veuillez entrer un nom valide.</div>
                </div>
                <div class="mb-3">
                    <label for="prenom" class="form-label">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" class="form-control" required>
                    <div class="invalid-feedback">Veuillez entrer un prénom valide.</div>
                </div>
                <div class="mb-3">
                    <label for="poste" class="form-label">Poste :</label>
                    <input type="text" id="poste" name="poste" class="form-control" required>
                    <div class="invalid-feedback">Veuillez entrer un poste valide.</div>
                </div>
                <div class="d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Créer le Compte</button>
                    <a href="choose_role.php" class="btn btn-secondary">Annuler</a>
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
            var forms = document.querySelectorAll('.needs-validation');
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
    </script>
</body>
</html>