<?php
// Initialiser la session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_id'])) {
    header("Location: ../Authentification_management/login.php");
    exit();
}

// Inclure le fichier de connexion à la base de données
require_once '../includes/config.php';

// Obtenir les informations de l'utilisateur
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Traitement du formulaire
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Changer le nom
    if (!empty($_POST['name'])) {
        $name = htmlspecialchars($_POST['name']);
        $update_query = "UPDATE users SET name = ? WHERE id = ?";
        $stmt = $pdo->prepare($update_query);
        $stmt->execute([$name, $user_id]);
        $_SESSION['user_name'] = $name; // Mettre à jour la session
    }

    // Changer le mot de passe
    if (!empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if (password_verify($current_password, $user['password'])) {
            if ($new_password === $confirm_password) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $update_query = "UPDATE users SET password = ? WHERE id = ?";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute([$hashed_password, $user_id]);
            } else {
                $errors[] = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
            }
        } else {
            $errors[] = "Le mot de passe actuel est incorrect.";
        }
    }

    // Mettre à jour l'image de profil
    if (!empty($_FILES['profile_image']['name'])) {
        $image_name = $_FILES['profile_image']['name'];
        $image_tmp = $_FILES['profile_image']['tmp_name'];
        $image_ext = pathinfo($image_name, PATHINFO_EXTENSION);
        $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($image_ext, $allowed_ext)) {
            $new_image_name = "profile_" . $user_id . "." . $image_ext;
            $upload_path = "../uploads/";

            // Vérifiez si le répertoire existe, sinon créez-le
            if (!is_dir($upload_path)) {
                mkdir($upload_path, 0777, true);
            }

            $full_path = $upload_path . $new_image_name;

            if (move_uploaded_file($image_tmp, $full_path)) {
                $update_query = "UPDATE users SET profile_image = ? WHERE id = ?";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute([$new_image_name, $user_id]);
            } else {
                $errors[] = "Échec du téléchargement de l'image.";
            }
        } else {
            $errors[] = "Format d'image non pris en charge.";
        }
    }

    // Redirection après mise à jour
    if (empty($errors)) {
        header("Location: ../layouts/dashboard.php");
        exit();
    }
}

// Définir le titre de la page
$page_title = "Modifier le profil";

ob_start(); // Démarrer le tampon pour capturer le contenu
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Fonction pour rediriger vers le dashboard lorsque "Annuler" est cliqué
        function cancelChanges() {
            window.location.href = '../layouts/dashboard.php'; // Redirection vers le dashboard
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-6">
        <div class="bg-white shadow-md rounded p-6">
            <h1 class="text-2xl font-bold mb-4">Modifier le profil</h1>

            <!-- Afficher les erreurs -->
            <?php if (!empty($errors)): ?>
                <div class="bg-red-100 text-red-800 p-4 rounded mb-4">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Formulaire de mise à jour -->
            <form action="profile.php" method="POST" enctype="multipart/form-data">
                <!-- Nom -->
                <div class="mb-4">
                    <label for="name" class="block text-gray-700">Nom :</label>
                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="w-full p-2 border rounded">
                </div>

                <!-- Mot de passe -->
                <div class="mb-4">
                    <label for="current_password" class="block text-gray-700">Mot de passe actuel :</label>
                    <input type="password" name="current_password" id="current_password" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="new_password" class="block text-gray-700">Nouveau mot de passe :</label>
                    <input type="password" name="new_password" id="new_password" class="w-full p-2 border rounded">
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="block text-gray-700">Confirmer le mot de passe :</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="w-full p-2 border rounded">
                </div>

                <!-- Image de profil -->
                <div class="mb-4">
                    <label for="profile_image" class="block text-gray-700">Image de profil :</label>
                    <input type="file" name="profile_image" id="profile_image" class="w-full p-2 border rounded">
                </div>

                <!-- Boutons -->
                <div class="flex space-x-4">
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Mettre à jour</button>
                    <button type="button" onclick="cancelChanges()" class="bg-gray-400 text-white px-4 py-2 rounded">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>

<?php
// Capturer le contenu et le stocker dans la variable $content
$content = ob_get_clean();

// Inclure le layout avec le contenu
include '../layouts/layout.php';
?>
