<?php
// Check if the 'employee_role' cookie is set and if its value is 'chief'
// If not, redirect to the login page
if (!isset($_COOKIE['employee_role']) || trim($_COOKIE['employee_role']) !== 'chief') {
    header("Location: login.php");
    exit;
}

// Include the header for the page layout
include_once("../www/header.php");

// API Configuration for making requests to external service
define('API_BASE_URL', 'https://dev-clisson231.users.info.unicaen.fr/2eannee/DevBack/S401/controller/api.php');
define('API_KEY', 'e8f1997c763');

/**
 * Function to make API requests.
 *
 * @param string $url The API URL to send the request to.
 * @param string $method The HTTP method (GET, POST, etc.). Default is 'GET'.
 * @param mixed $data Optional. Data to be sent with the request (for POST or PUT).
 * 
 * @return array|null Decoded response from the API in associative array format, or null if failed.
 */
function fetchApiData($url, $method = 'GET', $data = null)
{
    $options = [
        'http' => [
            'method' => $method,
            'header' => "Content-Type: application/json\r\n" .
                "X-Api-Key: " . API_KEY . "\r\n",
            'ignore_errors' => true
        ]
    ];

    // Add content to request if data is provided
    if ($data) {
        $options['http']['content'] = json_encode($data);
    }

    $context = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    // Return the response decoded into an array or null if no response
    return $response ? json_decode($response, true) : null;
}

// Define available stores with their names for selection
$allStores = [
    1 => ['store_id' => 1, 'store_name' => 'Santa Cruz Bikes'],
    2 => ['store_id' => 2, 'store_name' => 'Baldwin Bikes'],
    3 => ['store_id' => 3, 'store_name' => 'Rowlett Bikes']
];

// Retrieve current store information from the cookie
$currentStoreId = $_COOKIE['store_id']; // Using cookie to get store ID
$currentStore = $allStores[$currentStoreId] ?? null;

if (!$currentStore) {
    die("Invalid store configuration");
}

// Define roles for employee selection
$roles = [
    ['value' => 'employee', 'label' => 'Employee'],
    ['value' => 'chief', 'label' => 'Chief'],
    ['value' => 'it', 'label' => 'IT Staff']
];
?>

<link rel="stylesheet" href="../css/edit.css">
<div class="container mt-4">
    <h1 class="mb-4">Add a New Employee</h1>

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
                            <option value="<?= $currentStore['store_id'] ?>" selected>
                                <?= htmlspecialchars($currentStore['store_name']) ?>
                            </option>
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
            const originalBtnText = submitBtn.textContent;

            // Disable the submit button during the submission process
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

            // Gather employee data from the form
            const employeeData = {
                action: 'employee',
                employee_name: document.getElementById('employeeName').value,
                employee_email: document.getElementById('employeeEmail').value,
                employee_password: document.getElementById('employeePassword').value,
                employee_role: document.getElementById('employeeRole').value,
                store_id: document.getElementById('employeeStore').value
            };

            // Create a new XMLHttpRequest to send data to the API
            const xhr = new XMLHttpRequest();
            xhr.open('POST', '<?= API_BASE_URL ?>', true);
            xhr.setRequestHeader('Content-Type', 'application/json');
            xhr.setRequestHeader('X-Api-Key', '<?= API_KEY ?>');

            xhr.onload = function() {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;

                // Check if the response status is successful
                if (xhr.status >= 200 && xhr.status < 300) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        showAlert('success', 'Employee added successfully!');
                        // Redirect to the employee management page after 1.5 seconds
                        setTimeout(() => window.location.href = 'consultChief.php', 1500);
                    } else {
                        showAlert('danger', response.error || 'An error occurred');
                    }
                } else {
                    showAlert('danger', 'Request failed with status: ' + xhr.status);
                }
            };

            xhr.onerror = function() {
                submitBtn.disabled = false;
                submitBtn.textContent = originalBtnText;
                showAlert('danger', 'Request failed');
            };

            // Send the employee data to the server
            xhr.send(JSON.stringify(employeeData));

            /**
             * Displays an alert message on the page.
             * 
             * @param {string} type The type of the alert (e.g., 'success', 'danger').
             * @param {string} message The message to display in the alert.
             */
            function showAlert(type, message) {
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type} alert-dismissible fade show mt-3`;
                alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;

                const container = document.querySelector('.container');
                container.prepend(alertDiv);
            }
        });
    });
</script>

<?php include_once("../www/footer.php"); ?>