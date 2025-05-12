 
<?php
require_once '../includes/functions.php';
if (isLoggedIn() && $_SESSION['role'] == 'client') {
    redirect('dashboard.php');
} else {
    redirect('../auth/login.php');
}
?>