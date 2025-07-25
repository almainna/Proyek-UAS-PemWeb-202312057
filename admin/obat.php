<?php
require_once '../config/init.php';

// Check if user is admin
if (!isLoggedIn() || !isAdmin()) {
    redirect('../login.php');
}

// Redirect to the main obat.php file
redirect('../obat.php');
?>
