<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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

    // Récupérer les données du formulaire
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Vérifier les identifiants dans la table utilisateurs
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE Username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['Password'])) {
        // Authentification réussie
        session_regenerate_id(true);
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role'] = $user['Role']; // Stocke le rôle de l'utilisateur

        // Redirection selon le rôle
        if ($user['Role'] === 'admin') {
            header('Location: dashboard.php');
        } elseif ($user['Role'] === 'client') {
            header('Location: client_dashboard.php');
        } elseif ($user['Role'] === 'intervenant') {
            header('Location: intervenant_dashboard.php');
        }
        exit();
    } else {
        // Authentification échouée
        echo "<script>alert('Identifiants incorrects');</script>";
        echo "<script>window.location.href='dashboard.php';</script>";
    }
}
?>