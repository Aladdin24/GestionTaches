<?php
// Démarrer la session
session_start();
// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    // Rediriger vers le tableau de bord si l'utilisateur est connecté
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentification</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Inclure Font Awesome pour les icônes -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: url('https://img.freepik.com/photos-gratuite/communication-globale_53876-89014.jpg?t=st=1739247805~exp=1739251405~hmac=c411ee188a8f2911de53c5d6e547538909c672ee133fd085adf9e3a9089d4344&w=900') no-repeat center center fixed; /* Image de fond */
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .login-form {
            max-width: 500px;
            padding: 40px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2rem;
        }
        .form-group {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6c757d;
        }
        .password-toggle:hover {
            color: #007bff;
        }
        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
            font-size: 1rem;
            padding: 12px 20px;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004990;
        }
        .text-center {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-form">
        <h2>Authentification</h2>
        <!-- Formulaire de connexion -->
        <form action="login.php" method="POST" class="needs-validation" novalidate>
            <div class="mb-4 form-group">
                <label for="username" class="form-label">Nom d'utilisateur :</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Entrez votre nom d'utilisateur" required>
                <div class="invalid-feedback">Veuillez entrer un nom d'utilisateur valide.</div>
            </div>
            <div class="mb-4 form-group">
                <label for="password" class="form-label">Mot de passe :</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-control" placeholder="Entrez votre mot de passe" required>
                    <span class="password-toggle input-group-text" onclick="togglePasswordVisibility()">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
                <div class="invalid-feedback">Veuillez entrer un mot de passe valide.</div>
            </div>
            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </form>
        <!-- Lien pour créer un compte -->
        <div class="text-center">
            <a href="choose_role.php" class="text-decoration-none">Créer un compte</a>
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
        // Fonction pour afficher/masquer le mot de passe
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>