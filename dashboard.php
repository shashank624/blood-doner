<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$donor_id = $_SESSION['donor_id'];

// Fetch this donor's own details
$stmt = $conn->prepare("SELECT * FROM donors WHERE id = ?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$eligibility = get_eligibility_info($me['last_donation_date']);

// Overall stats for the small report cards
$total_donors = $conn->query("SELECT COUNT(*) as total FROM donors WHERE role='donor'")->fetch_assoc()['total'];
$available_donors = $conn->query("SELECT COUNT(*) as total FROM donors WHERE role='donor' AND is_available='Yes'")->fetch_assoc()['total'];
$total_cities = $conn->query("SELECT COUNT(DISTINCT city) as total FROM donors WHERE role='donor'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 16 16%22><text y=%2213%22 font-size=%2214%22>🩸</text></svg>">
<title>My Profile - Blood Donor Finder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">

    <h3>Welcome, <?php echo htmlspecialchars($me['full_name']); ?></h3>
    <p class="text-muted">Thank you for being part of our donor community.</p>

    <div class="row mb-4">
        <div class="col-md-4 col-6">
            <div class="card text-center p-3 stat-card">
                <h4><?php echo $total_donors; ?></h4>
                <p class="mb-0">Total Donors</p>
            </div>
        </div>
        <div class="col-md-4 col-6">
            <div class="card text-center p-3 stat-card bg-success-subtle">
                <h4><?php echo $available_donors; ?></h4>
                <p class="mb-0">Available Now</p>
            </div>
        </div>
        <div class="col-md-4 col-12">
            <div class="card text-center p-3 stat-card bg-info-subtle">
                <h4><?php echo $total_cities; ?></h4>
                <p class="mb-0">Cities Covered</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-7">
            <div class="card p-4 form-box">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">My Donor Profile</h5>
                    <a href="edit_profile.php" class="btn btn-sm btn-outline-primary">Edit Profile</a>
                </div>

                <div class="text-center mb-3">
                    <?php if (!empty($me['photo'])): ?>
                        <img src="uploads/<?php echo htmlspecialchars($me['photo']); ?>" class="rounded-circle" width="90" height="90" style="object-fit:cover;">
                    <?php else: ?>
                        <div class="rounded-circle bg-danger text-white d-inline-flex align-items-center justify-content-center" style="width:90px;height:90px;font-size:2rem;">
                            <?php echo strtoupper(substr($me['full_name'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <table class="table table-borderless mb-0">
                    <tr><th width="40%">Blood Group</th><td><span class="badge bg-danger fs-6"><?php echo $me['blood_group']; ?></span></td></tr>
                    <tr><th>Phone</th><td><?php echo htmlspecialchars($me['phone']); ?></td></tr>
                    <tr><th>Email</th><td><?php echo htmlspecialchars($me['email']); ?></td></tr>
                    <tr><th>City</th><td><?php echo htmlspecialchars($me['city']); ?></td></tr>
                    <tr><th>Last Donated</th><td><?php echo $me['last_donation_date'] ? date('d M Y', strtotime($me['last_donation_date'])) : 'Not recorded'; ?></td></tr>
                    <tr><th>Availability</th>
                        <td>
                            <?php if ($me['is_available'] == 'Yes'): ?>
                                <span class="badge bg-success">Available to Donate</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Not Available</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                            <tr><th>Medical Eligibility</th>
                        <td>
                            <?php if ($eligibility['eligible']): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> <?php echo $eligibility['label']; ?></span>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark"><i class="bi bi-hourglass-split"></i> <?php echo $eligibility['label']; ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="col-md-5">
            <div class="card p-4 form-box text-center">
                <h5>Need Blood Urgently?</h5>
                <p class="text-muted">Search our donor database by blood group and city.</p>
                <a href="search.php" class="btn btn-danger">🔍 Search Donors</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>