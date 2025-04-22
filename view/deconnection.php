<?php
// Clear cookies by setting expiration time in the past
setcookie('employee_id', '', time() - 3600, '/');
setcookie('employee_role', '', time() - 3600, '/');
setcookie('store_id', '', time() - 3600, '/');

// Redirect to homepage
header("Location: index.php");
exit();
?>
