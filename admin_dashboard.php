<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

if (!is_admin()) {
    header("Location: dashboard.php");
    exit();
}

// Breakdown by blood group
$by_group = $conn->query("SELECT blood_group, COUNT(*) as total FROM donors WHERE role='donor' GROUP BY blood_group ORDER BY total DESC");

// Breakdown by city
$by_city = $conn->query("SELECT city, COUNT(*) as total FROM donors WHERE role='donor' GROUP BY city ORDER BY total DESC");

// All donors table — paginated so large donor lists don't load as one giant page
$per_page = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $per_page;

$total_rows = $conn->query("SELECT COUNT(*) as total FROM donors WHERE role='donor'")->fetch_assoc()['total'];
$total_pages = max(1, ceil($total_rows / $per_page));

$stmt = $conn->prepare("SELECT * FROM donors WHERE role='donor' ORDER BY created_at DESC LIMIT ? OFFSET ?");
$stmt->bind_param("ii", $per_page, $offset);
$stmt->execute();
$all_donors = $stmt->get_result();

$total_donors = $conn->query("SELECT COUNT(*) as total FROM donors WHERE role='donor'")->fetch_assoc()['total'];
$available = $conn->query("SELECT COUNT(*) as total FROM donors WHERE role='donor' AND is_available='Yes'")->fetch_assoc()['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 16 16%22><text y=%2213%22 font-size=%2214%22>🩸</text></svg>">
<title>Reports - Blood Donor Finder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">
    <h3>Admin Reports</h3>
    <p class="text-muted">Overview of the entire donor database.</p>

    <div class="row mb-4">
        <div class="col-md-6 col-6">
            <div class="card text-center p-3 stat-card">
                <h4><?php echo $total_donors; ?></h4>
                <p class="mb-0">Total Registered Donors</p>
            </div>
        </div>
        <div class="col-md-6 col-6">
            <div class="card text-center p-3 stat-card bg-success-subtle">
                <h4><?php echo $available; ?></h4>
                <p class="mb-0">Currently Available</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card form-box p-4">
                <h5 class="mb-3">Donors by Blood Group</h5>
                <table class="table table-sm">
                    <thead><tr><th>Blood Group</th><th>Count</th></tr></thead>
                    <tbody>
                        <?php while ($row = $by_group->fetch_assoc()): ?>
                        <tr>
                            <td><span class="badge bg-danger"><?php echo $row['blood_group']; ?></span></td>
                            <td><?php echo $row['total']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card form-box p-4">
                <h5 class="mb-3">Donors by City</h5>
                <table class="table table-sm">
                    <thead><tr><th>City</th><th>Count</th></tr></thead>
                    <tbody>
                        <?php while ($row = $by_city->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['city']); ?></td>
                            <td><?php echo $row['total']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card form-box p-4">
        <h5 class="mb-3">All Donors</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>#</th><th>Photo</th><th>Name</th><th>Blood Group</th><th>City</th><th>Phone</th><th>Available</th><th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $all_donors->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td>
                            <?php if (!empty($row['photo'])): ?>
                                <img src="uploads/<?php echo htmlspecialchars($row['photo']); ?>" class="rounded-circle" width="32" height="32" style="object-fit:cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:32px;height:32px;font-size:0.8rem;">
                                    <?php echo strtoupper(substr($row['full_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><span class="badge bg-danger"><?php echo $row['blood_group']; ?></span></td>
                        <td><?php echo htmlspecialchars($row['city']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo $row['is_available'] == 'Yes' ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>'; ?></td>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
        <nav class="mt-3">
            <ul class="pagination justify-content-center mb-0">
                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page - 1; ?>">Previous</a>
                </li>
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?php echo $page >= $total_pages ? 'disabled' : ''; ?>">
                    <a class="page-link" href="?page=<?php echo $page + 1; ?>">Next</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

</body>
</html>