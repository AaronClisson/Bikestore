<?php
/**
 * IT Dashboard - View All Employees Across Stores
 *
 * Only accessible to employees with the 'it' role.
 */

if (!isset($_COOKIE['employee_id']) || trim($_COOKIE['employee_role']) !== 'it') {
    header("Location: login.php");
    exit;
}

include_once("../www/header.php");
require_once("../bootstrap.php");

use Entity\Employee;
use Entity\Store;

// Get all employees sorted by store name then employee name
$employeeRepository = $entityManager->getRepository(Employee::class);
$employees = $employeeRepository->createQueryBuilder('e')
    ->leftJoin('e.store', 's')
    ->addOrderBy('s.store_name', 'ASC')
    ->addOrderBy('e.employee_name', 'ASC')
    ->getQuery()
    ->getResult();

// Get all stores (for future use or dropdowns)
$storeRepository = $entityManager->getRepository(Store::class);
$stores = $storeRepository->findAll();
?>

<link rel="stylesheet" href="../css/products.css">

<h1>Employee Management - IT View</h1>

<div class="d-flex justify-content-between mb-4">
    <div>
        <button onclick="window.location.href='employeeItAdd.php'" class="btn-add">Add Employee</button>
    </div>
    <div>
        <span class="badge bg-info">
            IT Access - All Stores
        </span>
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
                <th>Store</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $employee): ?>
                <?php $store = $employee->getStore(); ?>
                <tr>
                    <td><?= htmlspecialchars($employee->getEmployeeId()) ?></td>
                    <td><?= htmlspecialchars($employee->getEmployeeName()) ?></td>
                    <td><?= htmlspecialchars($employee->getEmployeeEmail()) ?></td>
                    <td>
                        <span class="badge 
                            <?= $employee->getEmployeeRole() === 'chief' ? 'bg-warning' : 
                                ($employee->getEmployeeRole() === 'it' ? 'bg-info' : 'bg-secondary') ?>">
                            <?= ucfirst($employee->getEmployeeRole()) ?>
                        </span>
                    </td>
                    <td><?= $store ? htmlspecialchars($store->getStoreName()) : 'N/A' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include_once("../www/footer.php"); ?>
