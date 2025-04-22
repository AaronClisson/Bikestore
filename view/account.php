<?php
/**
 * Employee Account Management Page
 *
 * Displays and allows update of the currently logged-in employee's account details.
 * Requires authentication via cookie and interacts with the database via Doctrine.
 *
 * @package    AccountManagement
 * @author     Aaron CLISSON
 * @version    1.0
 */

if (!isset($_COOKIE['employee_id'])) {
    header("Location: login.php");
    exit;
}

include_once("../www/header.php");
require_once "../bootstrap.php";

use Entity\Employee;

// Retrieve authenticated employee
$employeeId = $_COOKIE['employee_id'];
$employeeRepo = $entityManager->getRepository(Employee::class);
$employee = $employeeRepo->find($employeeId);

if (!$employee) {
    echo "<div class='alert alert-danger'>Employee not found.</div>";
    include_once("../www/footer.php");
    exit;
}

// Handle profile update form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee->setEmployeeName(trim($_POST['employee_name']));
    $employee->setEmployeeEmail(trim($_POST['employee_email']));

    if (!empty($_POST['employee_password'])) {
        // NOTE: Consider using password_hash() for better security
        $employee->setEmployeePassword(trim($_POST['employee_password']));
    }

    $entityManager->persist($employee);
    $entityManager->flush();

    echo "<div class='alert alert-success'>Account information updated successfully.</div>";
}
?>
<link rel="stylesheet" href="../css/account.css">

<div class="account-container">
    <h1>My Account</h1>
    <form method="POST">
        <div class="mb-3">
            <label for="employee_name" class="form-label">Name</label>
            <input type="text" class="form-control" id="employee_name" name="employee_name" value="<?= htmlspecialchars($employee->getEmployeeName()) ?>" required>
        </div>
        <div class="mb-3">
            <label for="employee_email" class="form-label">Email</label>
            <input type="email" class="form-control" id="employee_email" name="employee_email" value="<?= htmlspecialchars($employee->getEmployeeEmail()) ?>" required>
        </div>
        <div class="mb-3">
            <label for="employee_password" class="form-label">New password (leave blank to keep current)</label>
            <input type="password" class="form-control" id="employee_password" name="employee_password">
        </div>
        <div class="btn-group">
            <button type="submit" class="btn-save">Update</button>
            <button type="reset" class="btn-cancel">Cancel</button>
        </div>
    </form>
</div>

<?php 
include_once("../www/footer.php");
?>
