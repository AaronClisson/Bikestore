<?php
/**
 * Chief Dashboard - View Employees in Store
 *
 * Allows store chiefs to view all employees belonging to their store.
 * Access is restricted to users with the 'chief' role.
 */

if (
    !isset($_COOKIE['employee_id']) ||
    !isset($_COOKIE['employee_role']) ||
    trim($_COOKIE['employee_role']) !== 'chief'
) {
    header("Location: login.php");
    exit;
}

include_once("../www/header.php");
require_once("../bootstrap.php");

use Entity\Employee;
use Entity\Store;

// Get the current store based on cookie
$currentStoreId = $_COOKIE['store_id'];
$storeRepository = $entityManager->getRepository(Store::class);
$currentStore = $storeRepository->find($currentStoreId);

if (!$currentStore) {
    die("Store not found.");
}

// Fetch employees from the current store, sorted by name
$employeeRepository = $entityManager->getRepository(Employee::class);
$employees = $employeeRepository->findBy(
    ['store' => $currentStore],
    ['employee_name' => 'ASC']
);
?>

<link rel="stylesheet" href="../css/products.css">

<h1>Employee Management - <?= htmlspecialchars($currentStore->getStoreName()) ?></h1>

<div class="d-flex justify-content-between mb-4">
    <div>
        <button onclick="window.location.href='employeeAdd.php'" class="btn-add">Add Employee</button>
    </div>
</div>

<div class="table-responsive">
    <table class="table table-striped table-hover">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?= htmlspecialchars($employee->getEmployeeId()) ?></td>
                    <td><?= htmlspecialchars($employee->getEmployeeName()) ?></td>
                    <td><?= htmlspecialchars($employee->getEmployeeEmail()) ?></td>
                    <td>
                        <span class="badge <?= $employee->getEmployeeRole() === 'chief' ? 'bg-warning' : 'bg-info' ?>">
                            <?= ucfirst($employee->getEmployeeRole()) ?>
                        </span>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include_once("../www/footer.php"); ?>
