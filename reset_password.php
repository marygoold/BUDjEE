<?php
@include 'config.php';
session_start();

$code_error = false;
$pass_error = false;

if (isset($_POST['submit'])) {
    $code = $_POST['code'];
    $new_pass = md5($_POST['new_pass']);
    $cpass = md5($_POST['confirm_pass']);

    if ($code == $_SESSION['reset_code']) {
        if ($new_pass == $cpass) {
            $email = $_SESSION['reset_email'];
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$new_pass, $email]);

            unset($_SESSION['reset_code']);
            unset($_SESSION['reset_email']);

            header('Location: login.php');
            exit();
        } else {
            $pass_error = true;
        }
    } else {
        $code_error = true;
    }
}
?>

<!DOCTYPE html>
<html>

<head>

    <title>Reset Password | BUDjEE</title>
    <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/components.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

    <style>
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
        }

        .input-wrapper {
            position: relative;
        }
    </style>
</head>

<body class="login_bg">

    <section class="form-container">
        <form action="" method="POST">
            <h3>Reset Password</h3>

            <div class="input-wrapper">
                <input type="number" name="code" class="box <?php if ($code_error) echo 'error'; ?>" placeholder="Enter reset code" required>
                <?php if ($code_error): ?>
                    <span class="placeholder-icon">!</span>
                <?php endif; ?>
            </div>

            <div class="input-wrapper">
                <input type="password" id="new_pass" name="new_pass" class="box <?php if ($pass_error) echo 'error'; ?>" placeholder="Enter new password" required>
                <i class="fas fa-eye toggle-password" toggle="#new_pass"></i>
                <?php if ($pass_error): ?>
                    <span class="placeholder-icon">!</span>
                <?php endif; ?>
            </div>

            <div class="input-wrapper">
                <input type="password" id="confirm_pass" name="confirm_pass" class="box <?php if ($pass_error) echo 'error'; ?>" placeholder="Confirm new password" required>
                <i class="fas fa-eye toggle-password" toggle="#confirm_pass"></i>
                <?php if ($pass_error): ?>
                    <span class="placeholder-icon">!</span>
                <?php endif; ?>
            </div>

            <input type="submit" name="submit" class="btn" value="Reset Password">
        </form>
    </section>

    <script>
        document.querySelectorAll('.toggle-password').forEach(icon => {
            icon.addEventListener('click', function() {
                const input = document.querySelector(this.getAttribute('toggle'));
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        });
    </script>

</body>

</html>