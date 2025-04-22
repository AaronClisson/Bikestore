<?php
// Check if the 'employee_role' cookie is set and if its value is 'chief' or 'it'
// If the role doesn't match, redirect to the login page
if (!isset($_COOKIE['employee_role']) || !in_array($_COOKIE['employee_role'], ['chief', 'it'])) {
    header("Location: login.php");
    exit;
}

// Include the header for the page layout
include_once("../www/header.php");

// API Configuration for making requests to external service
define('API_BASE_URL', 'https://dev-clisson231.users.info.unicaen.fr/2eannee/DevBack/S401/controller/api.php');
define('API_KEY', 'e8f1997c763');

// List of available stores for employee assignment
$allStores = [
    1 => ['store_id' => 1, 'store_name' => 'Santa Cruz Bikes'],
    2 => ['store_id' => 2, 'store_name' => 'Baldwin Bikes'],
    3 => ['store_id' => 3, 'store_name' => 'Rowlett Bikes']
];

// Define available roles for employee assignment
$roles = [
    ['value' => 'employee', 'label' => 'Employee'],
    ['value' => 'chief', 'label' => 'Chief']
];
?>


<link rel="stylesheet" href="../css/edit.css">
<div class="container mt-4">
    <h1 class="mb-4">Add New Employee</h1>

    <div class="card">
        <div class="card-body">
            <form id="employeeForm">
                <div class="mb-3">
                    <label for="employeeName" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="employeeName" name="employeeName" required>
                </div>

                <div class="mb-3">
                    <label for="employeeEmail" class="form-label">Email</label>
                    <input type="email" class="form-control" id="employeeEmail" name="employeeEmail" required>
                </div>

                <div class="mb-3">
                    <label for="employeePassword" class="form-label">Password</label>
                    <input type="password" class="form-control" id="employeePassword" name="employeePassword" required>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="employeeStore" class="form-label">Store</label>
                        <select class="form-select" id="employeeStore" name="employeeStore" required>
                            <?php foreach ($allStores as $store) : ?>
                                <option value="<?= $store['store_id'] ?>">
                                    <?= htmlspecialchars($store['store_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="employeeRole" class="form-label">Role</label>
                        <select class="form-select" id="employeeRole" name="employeeRole" required>
                            <?php foreach ($roles as $role) : ?>
                                <option value="<?= $role['value'] ?>">
                                    <?= htmlspecialchars($role['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="consultChief.php" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Employee</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    /**
     * Handles the form submission for adding a new employee.
     */
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('employeeForm');

        form.addEventListener('submit', function(e) {
            // Prevent default form submission
            e.preventDefault();

            const submitBtn = form.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Processing...';

            // Gather employee data from the form
            const employeeData = {
                action: 'employee',
                employee_name: document.getElementById('employeeName').value,
                employee_email: document.getElementById('employeeEmail').value,
                employee_password: document.getElementById('employeePassword').value,
                employee_role: document.getElementById('employeeRole').value,
                store_id: document.getElementById('employeeStore').value
            };

            /**
             * Sends the employee data to the server using the Fetch API.
             * Handles the response and updates the submit button state accordingly.
             */
            fetch('<?= API_BASE_URL ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Api-Key': '<?= API_KEY ?>'
                    },
                    body: JSON.stringify(employeeData)
                })
                .then(response => response.json()) // Parse the JSON response
                .then(data => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Employee';

                    // If the response indicates success, show a success message and redirect
                    if (data.success) {
                        alert('Employee added successfully!');
                        window.location.href = 'consultChief.php';
                    } else {
                        alert('Error: ' + (data.error || 'An error occurred'));
                    }
                })
                .catch(() => {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Add Employee';
                    alert('Request failed');
                });
        });
    });
</script>

<?php include_once("../www/footer.php"); ?>