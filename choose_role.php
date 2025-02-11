<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Choisir un Rôle</title>
    <!-- Inclure Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .role-selection {
            max-width: 400px;
            padding: 30px;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        h2 {
            margin-bottom: 20px;
        }
        .btn-role {
            margin: 10px 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="role-selection">
        <h2>Choisissez un rôle</h2>
        <!-- Bouton pour créer un compte client -->
        <a href="register_client.php" class="btn btn-success btn-role">Créer un compte Client</a>
        <!-- Bouton pour créer un compte intervenant -->
        <a href="register_intervenant.php" class="btn btn-info btn-role">Créer un compte Intervenant</a>
        <!-- Retour à la page de connexion -->
        <a href="dashboard.php" class="text-center text-decoration-none">Retour à la connexion</a>
    </div>
</body>
</html>