 
<?php
require_once '../includes/functions.php';
if (isAdmin()) {
    redirect('dashboard.php');
} else {
    redirect('../auth/login.php');
}
?>