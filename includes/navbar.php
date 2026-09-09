<nav class="navbar navbar-expand-lg navbar-dark bg-danger">
  <div class="container">
    <a class="navbar-brand" href="dashboard.php"><i class="bi bi-droplet-fill"></i> Blood Donor Finder</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-person-circle me-1"></i>My Profile</a></li>
        <li class="nav-item"><a class="nav-link" href="search.php"><i class="bi bi-search me-1"></i>Search Donors</a></li>
        <?php if (is_admin()): ?>
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php"><i class="bi bi-bar-chart-fill me-1"></i>Reports</a></li>
        <?php endif; ?>
        <li class="nav-item"><span class="nav-link text-light">Hi, <?php echo htmlspecialchars($_SESSION['full_name']); ?></span></li>
        <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right me-1"></i>Logout</a></li>
      </ul>
    </div>
  </div>
</nav>