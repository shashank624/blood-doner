<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$donor_id = $_SESSION['donor_id'];
$errors = [];

$stmt = $conn->prepare("SELECT * FROM donors WHERE id = ?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();

$phone = $me['phone'];
$blood_group = $me['blood_group'];
$city = $me['city'];
$last_donation_date = $me['last_donation_date'];
$is_available = $me['is_available'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $phone = clean_input($_POST['phone']);
    $blood_group = clean_input($_POST['blood_group']);
    $city = clean_input($_POST['city']);
    $last_donation_date = clean_input($_POST['last_donation_date']);
    $is_available = clean_input($_POST['is_available']);

    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = "Session expired. Please refresh the page and try again.";
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

    // Date field is optional
    $date_value = !empty($last_donation_date) ? $last_donation_date : null;

    // Handle optional new photo upload — replaces the old one if provided
    $photo_filename = $me['photo'];
    if (empty($errors) && isset($_FILES['photo']) && $_FILES['photo']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $file_type = mime_content_type($_FILES['photo']['tmp_name']);

        if (!in_array($file_type, $allowed_types)) {
            $errors[] = "Photo must be a JPG, PNG, or WEBP image.";
        } elseif ($_FILES['photo']['size'] > 2 * 1024 * 1024) {
            $errors[] = "Photo size must be under 2MB.";
        } else {
            if (!empty($me['photo']) && file_exists('uploads/' . $me['photo'])) {
                unlink('uploads/' . $me['photo']);
            }
            $extension = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $photo_filename = 'donor_' . uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['photo']['tmp_name'], 'uploads/' . $photo_filename);
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE donors SET phone = ?, blood_group = ?, city = ?, last_donation_date = ?, is_available = ?, photo = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $phone, $blood_group, $city, $date_value, $is_available, $photo_filename, $donor_id);

        if ($stmt->execute()) {
            $_SESSION['success'] = "Profile updated successfully.";
            header("Location: dashboard.php");
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
<title>Edit Profile - Blood Donor Finder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6 form-box">
            <h4 class="mb-4">Edit My Donor Profile</h4>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?><li><?php echo $error; ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" action="edit_profile.php" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">

                <div class="mb-3 text-center">
                    <?php if (!empty($me['photo'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($me['photo']); ?>" class="rounded-circle mb-2" width="90" height="90" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-2" style="width:90px;height:90px;font-size:2rem;">
                            <?php echo strtoupper(substr($me['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Profile Photo</label>
                    <input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
                    <div class="form-text">Leave empty to keep your current photo. Max 2MB.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="<?php echo htmlspecialchars($phone); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Blood Group</label>
                    <select name="blood_group" class="form-select">
                        <?php
                        $groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                        foreach ($groups as $g) {
                            $selected = ($blood_group == $g) ? "selected" : "";
                            echo "<option value=\"$g\" $selected>$g</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($city); ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Last Donation Date (optional)</label>
                    <input type="date" name="last_donation_date" class="form-control" value="<?php echo $last_donation_date; ?>">
                </div>

                <div class="mb-3">
                    <label class="form-label">Are you currently available to donate?</label>
                    <select name="is_available" class="form-select">
                        <option value="Yes" <?php echo $is_available=='Yes'?'selected':''; ?>>Yes</option>
                        <option value="No" <?php echo $is_available=='No'?'selected':''; ?>>No</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-danger">Save Changes</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </form>

            <hr class="my-4">
            <a href="delete_account.php" class="text-danger small" onclick="return confirm('Are you sure you want to delete your donor account? This cannot be undone.');">Delete my account</a>
        </div>
    </div>
</div>

</body>
</html>