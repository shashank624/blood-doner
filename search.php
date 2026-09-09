<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$blood_group = isset($_GET['blood_group']) ? clean_input($_GET['blood_group']) : '';
$city = isset($_GET['city']) ? clean_input($_GET['city']) : '';
$searched = isset($_GET['search']);

$donors = [];

if ($searched) {
    // Build query based on which filters were used
    $sql = "SELECT * FROM donors WHERE role = 'donor'";
    $params = [];
    $types = "";

    if (!empty($blood_group)) {
        $sql .= " AND blood_group = ?";
        $params[] = $blood_group;
        $types .= "s";
    }

    if (!empty($city)) {
        $sql .= " AND city LIKE ?";
        $params[] = "%" . $city . "%";
        $types .= "s";
    }

    $sql .= " ORDER BY is_available DESC, full_name ASC";

    $stmt = $conn->prepare($sql);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $donors[] = $row;
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 16 16%22><text y=%2213%22 font-size=%2214%22>🩸</text></svg>">
<title>Search Donors - Blood Donor Finder</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php include 'includes/navbar.php'; ?>

<div class="container mt-4">

    <div class="card form-box p-4 mb-4">
        <h5 class="mb-3">🔍 Find a Blood Donor</h5>
        <form method="GET" action="search.php" class="row g-3">
            <div class="col-md-5">
                <label class="form-label">Blood Group</label>
                <select name="blood_group" class="form-select">
                    <option value="">Any Blood Group</option>
                    <?php
                    $groups = ['A+','A-','B+','B-','AB+','AB-','O+','O-'];
                    foreach ($groups as $g) {
                        $selected = ($blood_group == $g) ? "selected" : "";
                        echo "<option value=\"$g\" $selected>$g</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label">City</label>
                <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($city); ?>" placeholder="e.g. Ahmedabad">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" name="search" value="1" class="btn btn-danger w-100">Search</button>
            </div>
        </form>
    </div>

    <?php if ($searched): ?>
        <h6 class="mb-3">
            <?php echo count($donors); ?> donor(s) found
            <?php if ($blood_group): ?> for <span class="badge bg-danger"><?php echo $blood_group; ?></span><?php endif; ?>
            <?php if ($city): ?> in "<?php echo htmlspecialchars($city); ?>"<?php endif; ?>
        </h6>

        <?php if (count($donors) == 0): ?>
            <div class="alert alert-warning">No matching donors found. Try widening your search.</div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($donors as $d): ?>
                <div class="col-md-6 mb-3">
                    <div class="card p-3 donor-card">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="d-flex align-items-center gap-2">
                                <?php if (!empty($d['photo'])): ?>
                                    <img src="uploads/<?php echo htmlspecialchars($d['photo']); ?>" class="rounded-circle" width="48" height="48" style="object-fit:cover;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width:48px;height:48px;">
                                        <?php echo strtoupper(substr($d['full_name'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                                <div>
                                    <h6 class="mb-1"><?php echo htmlspecialchars($d['full_name']); ?></h6>
                                    <p class="mb-0 text-muted small"><?php echo htmlspecialchars($d['city']); ?></p>
                                </div>
                            </div>
                            <span class="badge bg-danger fs-6"><?php echo $d['blood_group']; ?></span>
                        </div>
                        <hr class="my-2">
                        <p class="mb-1">📞 <?php echo htmlspecialchars($d['phone']); ?></p>
                        <p class="mb-0">
                            <?php if ($d['is_available'] == 'Yes'): ?>
                                <span class="badge bg-success">Available</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Currently Unavailable</span>
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <p class="text-muted text-center mt-5">Select a blood group and/or city above, then click Search.</p>
    <?php endif; ?>

</div>

</body>
</html>