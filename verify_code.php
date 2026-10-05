<?php
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'config.php';
session_start();

if (!isset($_SESSION['verify_email'])) {
    header("Location: register.php");
    exit;
}

$email = $_SESSION['verify_email'];
$code_error = false;

$stmt = $conn->prepare("SELECT name, verify_code FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "User not found.";
    exit;
}

$name = htmlspecialchars($user['name']);
$verify_code = $user['verify_code'];

if (!isset($_SESSION['email_sent'])) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'budjee.website@gmail.com';
        $mail->Password   = 'jdrn jyid ulki ymgm';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('budjee.website@gmail.com', 'BudjEE');
        $mail->addAddress($email, $name);

        $mail->isHTML(false);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Verify your BUDjEE Email';

        $mail->Body = "Hi $name,\n\nWe received a request to verify your email for BUDjEE.\n\nYour verification code is:\n\n$verify_code\n\nIf you didn’t request this, you can ignore this email.\n\n– BUDjEE Team";

        $mail->send();
        $_SESSION['email_sent'] = true;
    } catch (Exception $e) {
        echo "Could not send email. Mailer Error: {$mail->ErrorInfo}";
        exit;
    }
}

if (isset($_POST['verify_code'])) {
    $input_code = $_POST['code'];
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND verify_code = ?");
    $stmt->execute([$email, $input_code]);

    if ($stmt->rowCount() > 0) {
        $update = $conn->prepare("UPDATE users SET verified = 1 WHERE email = ?");
        $update->execute([$email]);
        unset($_SESSION['verify_email']);
        unset($_SESSION['email_sent']);
        header('Location: login.php');
        exit;
    } else {
        $code_error = true;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Verify Email | BUDjEE</title>
    <link rel="icon" href="images/logo_budjee.png" type="image/x-icon" />
    <link rel="stylesheet" href="css/components.css">
</head>

<body class="login_bg">
    <section class="form-container">
        <form action="" method="POST">
            <h3>Verify Your Email</h3>
            <p>Email sent to: <strong><?= htmlspecialchars($email) ?></strong></p>

            <div class="input-wrapper">
                <input
                    type="number"
                    name="code"
                    class="box <?= $code_error ? 'error' : '' ?>"
                    placeholder="Enter the 6-digit code"
                    required
                    maxlength="6">
                <?php if ($code_error): ?>
                    <span class="placeholder-icon">!</span>
                <?php endif; ?>
            </div>

            <input type="submit" name="verify_code" class="btn" value="Verify">
        </form>
    </section>
</body>

</html>