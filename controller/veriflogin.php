<?php
/**
 * Employee Authentication Handler
 * 
 * Processes login requests, validates credentials, and initiates employee sessions.
 * 
 * @package    Authentication
 * @author     Aaron CLISSON
 * @license    MIT
 * @version    1.0.0
 * @link       /view/login.php
 */
require_once "../bootstrap.php"; // Load Doctrine configuration
use Entity\Employee;

try {
    // Validate required POST parameters
    if (!isset($_POST['login'], $_POST['password']) || empty(trim($_POST['login']))) {
        displayLoginError('Données non saisies ou incomplètes');
        exit();
    }

    // Sanitize and validate input
    $login = filter_var(trim($_POST['login']), FILTER_SANITIZE_EMAIL);
    $password = trim($_POST['password']);

    // Validate email format
    if (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
        displayLoginError('Format d\'email invalide');
        exit();
    }

    // Check if employee exists
    $employeeRepo = $entityManager->getRepository(Employee::class);
    $employee = $employeeRepo->findOneBy(['employee_email' => $login]);

    if (!$employee) {
        displayLoginError('Login incorrect');
        exit();
    }

    // Verify password - NOTE: In production, use password_verify() with hashed passwords
    if ($password !== $employee->getEmployeePassword()) {
        displayLoginError('Password incorrect');
        exit();
    }

    // Successful authentication - set secure cookies
    setAuthCookies($employee);
    
    // Redirect to dashboard
    header('Location: ../view/index.php');
    exit();

} catch (Exception $e) {
    // Log error securely in production instead of displaying raw message
    error_log("Authentication Error: " . $e->getMessage());
    displayLoginError('Une erreur technique est survenue');
}

/**
 * Display login page with error message
 * 
 * @param string $message Error message to display
 */
function displayLoginError(string $message): void
{
    include_once("../view/login.php");
    echo "<script> 
            document.addEventListener('DOMContentLoaded', function() {
                let err = document.querySelector('#erreur'); 
                if (err) err.textContent = " . json_encode($message) . ";
            });
          </script>";
}

/**
 * Set authentication cookies with secure parameters
 * 
 * @param Employee $employee Authenticated employee entity
 */
function setAuthCookies(Employee $employee): void
{
    $expiry = time() + (86400 * 7); // 7 days
    $secure = isset($_SERVER['HTTPS']); // Secure in HTTPS only
    $httponly = true; // Prevent JavaScript access
    
    setcookie("employee_id", $employee->getEmployeeId(), [
        'expires' => $expiry,
        'path' => '/',
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Strict'
    ]);
    
    setcookie("employee_role", $employee->getEmployeeRole(), [
        'expires' => $expiry,
        'path' => '/',
        'secure' => $secure,
        'httponly' => $httponly,
        'samesite' => 'Strict'
    ]);
    
    if ($employee->getStore()) {
        setcookie("store_id", $employee->getStore()->getStoreId(), [
            'expires' => $expiry,
            'path' => '/',
            'secure' => $secure,
            'httponly' => $httponly,
            'samesite' => 'Strict'
        ]);
    }
}
?>