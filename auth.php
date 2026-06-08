<?php
session_start();
$conn = new mysqli("localhost", "root", "", "9ahwetna");

$message = '';
$message_type = '';

$action = $_POST['action'] ?? '';

if ($action === 'register') {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm  = $_POST['confirm_password'];

    if ($password !== $confirm) {
        $message      = "Les mots de passe ne correspondent pas !";
        $message_type = 'register_error';
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $check->store_result();

        if ($check->num_rows > 0) {
            $message      = "Le compte existe déjà, connectez-vous !";
            $message_type = 'login_error';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (email, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $hash);
            $stmt->execute();
            $_SESSION['user_id'] = $conn->insert_id;
            $message      = "Compte créé ! Bienvenue !";
            $message_type = 'register';
        }
    }

} elseif ($action === 'login') {
    $email = trim($_POST['email']);
    $stmt  = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $user  = $stmt->get_result()->fetch_assoc();

    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $message      = "Vous êtes connecté !";
        $message_type = 'login_success';
    } else {
        $message      = "Email ou mot de passe incorrect !";
        $message_type = 'login_error';
    }
}
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Login</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+8fhAXLRk2vvoC2f3B09zVXn8CA5QIVfZOJ3BCsw2P0p/We" crossorigin="anonymous">
    <link rel="stylesheet" href="logincss.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <script defer src="https://use.fontawesome.com/releases/v5.0.7/js/all.js"></script>

  </head>
  <body>

    <div class="login-reg-panel">

        <!-- REGISTER SIDE -->
        <div class="register-show">
            <h2>Don't have an account?</h2>
            <p>Register Yourself</p>

            <?php if ($message_type === 'register'): ?>
                <div class="alert-msg success">
                    <?= $message ?><br>
                    <a href="index.html">Revenir à l'accueil pour poursuivre l'achat</a>
                </div>
            <?php elseif ($message_type === 'register_error'): ?>
                <div class="alert-msg error">
                     <?= $message ?>
                    <a href="auth.php">Recommencer!</a>

                </div>
            <?php else: ?>
                <form action="auth.php" method="POST">
                    <input type="hidden"   name="action"           value="register">
                    <input type="text"     name="email"            placeholder="Email">
                    <input type="password" name="password"         placeholder="Password">
                    <input type="password" name="confirm_password" placeholder="Confirm Password">
                    <input type="submit" value="Register">
                </form>
            <?php endif; ?>
        </div>

        <!-- LOGIN SIDE -->
        <div class="white-panel">
            <div class="login-show">
                <h2>LOGIN</h2>

                <?php if ($message_type === 'login_success'): ?>
                    <div class="alert-msg success">
                        <?= $message ?><br>
                        <a href="homepage.html">Revenir à l'accueil pour poursuivre l'achat</a>
                    </div>
                <?php else: ?>
                    <?php if ($message_type === 'login_error'): ?>
                        <div class="alert-msg error">
                            <?= $message ?>
                        </div>
                    <?php endif; ?>
                    <form action="auth.php" method="POST">
                        <input type="hidden"   name="action"   value="login">
                        <input type="text"     name="email"    placeholder="Email">
                        <input type="password" name="password" placeholder="Password">
                        <input type="submit" value="Login">
                    </form>
                    <a href="">Forgot password?</a>
                <?php endif; ?>
            </div>
        </div>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/page.js" charset="utf-8"></script>
  </body>
</html>