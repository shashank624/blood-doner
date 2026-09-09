<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$email = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = clean_input($_POST['email']);
    $password = $_POST['password'];

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expired. Please try again.";
    } elseif (empty($email) || empty($password)) {
        $errors[] = "Both email and password are required.";
    } else {
        $stmt = $conn->prepare("SELECT id, full_name, password, role FROM donors WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $donor = $result->fetch_assoc();

            if (password_verify($password, $donor['password'])) {
                $_SESSION['donor_id'] = $donor['id'];
                $_SESSION['full_name'] = $donor['full_name'];
                $_SESSION['role'] = $donor['role'];

                header("Location: dashboard.php");
                exit();
            } else {
                $errors[] = "Incorrect password.";
            }
        } else {
            $errors[] = "No account found with this email.";
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
<title>Login - Blood Donor Finder</title>
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
            <h3 class="text-center mb-4">🩸 Login</h3>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $email; ?>">
                </div>

                    <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="mb-3 text-end">
                    <a href="forgot_password.php" class="small">Forgot Password?</a>
                </div>

                <button type="submit" class="btn btn-danger w-100">Login</button>
            </form>

            <p class="text-center mt-3">New donor? <a href="register.php">Register here</a></p>
        </div>
    </div>
</div>

</body>
</html>