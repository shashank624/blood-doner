<?php
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_login();

$donor_id = $_SESSION['donor_id'];

$stmt = $conn->prepare("DELETE FROM donors WHERE id = ?");
$stmt->bind_param("i", $donor_id);
$stmt->execute();
$stmt->close();

session_unset();
session_destroy();

header("Location: login.php");
exit();
?>