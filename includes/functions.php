<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Cleans user input to prevent XSS and extra spaces
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['donor_id']);
}

function is_admin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

function require_login() {
    if (!is_logged_in()) {
        header("Location: login.php");
        exit();
    }
}

// Generates a one-time token to protect forms from CSRF attacks
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Checks the token submitted by a form against the one stored in session
function verify_csrf_token($token) {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}
// Calculates whether a donor is medically eligible to donate again.
// Blood banks require a 90-day gap between donations.
function get_eligibility_info($last_donation_date) {
    if (empty($last_donation_date)) {
        return [
            'eligible' => true,
            'label' => 'Eligible to Donate',
            'next_date' => null
        ];
    }

    $last_date = new DateTime($last_donation_date);
    $today = new DateTime();
    $days_passed = $today->diff($last_date)->days;
    $wait_days = 90;

    if ($days_passed >= $wait_days) {
        return [
            'eligible' => true,
            'label' => 'Eligible to Donate',
            'next_date' => null
        ];
    } else {
        $next_eligible = clone $last_date;
        $next_eligible->modify("+{$wait_days} days");
        return [
            'eligible' => false,
            'label' => 'Eligible from ' . $next_eligible->format('d M Y'),
            'next_date' => $next_eligible->format('Y-m-d')
        ];
    }
}
?>

