<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$success = "";
$email = $phone = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expired. Please refresh the page and try again.";
    }

    if (empty($email) || empty($phone)) {
        $errors[] = "Email and phone number are both required.";
    }

    if (empty($new_password)) {
        $errors[] = "Please enter a new password.";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Verify that email AND phone match the same account —
    // this is our simple identity check since there's no email server here
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM donors WHERE email = ? AND phone = ?");
        $stmt->bind_param("ss", $email, $phone);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $donor = $result->fetch_assoc();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            $update = $conn->prepare("UPDATE donors SET password = ? WHERE id = ?");
            $update->bind_param("si", $hashed_password, $donor['id']);
            $update->execute();
            $update->close();

            $success = "Password reset successful! You can now login with your new password.";
            $email = $phone = "";
        } else {
            $errors[] = "No account found matching that email and phone number.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 16 16%22><text y=%2213%22 font-size=%2214%22>🩸</text></svg>">
<title>Forgot Password - Blood Donor Finder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 form-box">
            <h3 class="text-center mb-1"><i class="bi bi-key-fill"></i> Reset Password</h3>
            <p class="text-center text-muted mb-4">Verify your identity to set a new password</p>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <p class="text-center"><a href="login.php" class="btn btn-danger w-100">Go to Login</a></p>
            <?php else: ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="forgot_password.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                    <div class="mb-3">
                        <label class="form-label">Registered Email Address</label>
                        <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Registered Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>" placeholder="10 digit number">
                        <div class="form-text">We use this to confirm it's really you.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control">
                    </div>

                    <button type="submit" class="btn btn-danger w-100">Reset Password</button>
                </form>

            <?php endif; ?>

            <p class="text-center mt-3"><a href="login.php">Back to Login</a></p>
        </div>
    </div>
</div>

</body>
</html>