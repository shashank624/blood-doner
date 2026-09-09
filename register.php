<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';

$errors = [];
$full_name = $email = $phone = $blood_group = $city = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $full_name = clean_input($_POST['full_name']);
    $email = clean_input($_POST['email']);
    $phone = clean_input($_POST['phone']);
    $blood_group = clean_input($_POST['blood_group']);
    $city = clean_input($_POST['city']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // ---- Validation ----
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expired. Please refresh the page and try again.";
    }

    if (empty($full_name)) {
        $errors[] = "Full name is required.";
    }

    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (empty($phone)) {
        $errors[] = "Phone number is required.";
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "Phone number must be exactly 10 digits.";
    }

    if (empty($blood_group)) {
        $errors[] = "Please select your blood group.";
    }

    if (empty($city)) {
        $errors[] = "City is required.";
    }

    if (empty($password)) {
        $errors[] = "Password is required.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match.";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $conn->prepare("SELECT id FROM donors WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $errors[] = "This email is already registered.";
        }
        $stmt->close();
    }

    // Handle optional profile photo upload
    $photo_filename = null;
    if (empty($errors) && isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($_FILES['photo']['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Photo must be a JPG, PNG, or WEBP image.";
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Photo size must be under 2MB.";
        } else {
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo_filename = 'donor_' . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo_filename);
        }
    }

    // If no errors, insert the new donor
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO donors (full_name, email, password, phone, blood_group, city, photo) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $full_name, $email, $hashed_password, $phone, $blood_group, $city, $photo_filename);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Registration successful! You can now login.";
            header("Location: login.php");
            exit();
        } else {
            $errors[] = "Something went wrong. Please try again.";
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
<title>Register as Donor - Blood Donor Finder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 form-box">
            <h3 class="text-center mb-1">🩸 Become a Donor</h3>
            <p class="text-center text-muted mb-4">Register to help save lives</p>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php" enctype="multipart/form-data" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?php echo $full_name; ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="<?php echo $phone; ?>" placeholder="10 digit number">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="<?php echo $email; ?>">
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Blood Group</label>
                        <select name="blood_group" class="form-select">
                            <option value="">-- Select --</option>
                            <?php
                            $groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                            foreach ($groups as $g) {
                                $selected = ($blood_group == $g) ? "selected" : "";
                                echo "<option value=\"$g\" $selected>$g</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">City</label>
                        <input type="text" name="city" class="form-control" value="<?php echo $city; ?>" placeholder="e.g. Ahmedabad">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Profile Photo (optional)</label>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">JPG, PNG or WEBP, max 2MB.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control">
                </div>

                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control">
                </div>

                <button type="submit" class="btn btn-danger w-100">Register as Donor</button>
            </form>

            <p class="text-center mt-3">Already registered? <a href="login.php">Login here</a></p>
        </div>
    </div>
</div>

</body>
</html>