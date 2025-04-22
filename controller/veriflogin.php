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

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php'; // Load Doctrine configuration
use Entity\Employee;

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    header('Location: ../view/login.php?error=invalid_request_method');
    exit();
}

try {
    // Validate required fields
    if (empty($_POST['login'] ?? '') || empty($_POST['password'] ?? '')) {
        throw new InvalidArgumentException('Email and password are required');
    }

    // Sanitize and validate email
    $email = filter_var(trim($_POST['login']), FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('Please enter a valid email address');
    }

    $password = $_POST['password'];

    // Find employee by email
    $employeeRepo = $entityManager->getRepository(Employee::class);
    $employee = $employeeRepo->findOneBy(['employee_email' => $email]);

    if (!$employee) {
        throw new RuntimeException('Invalid credentials'); // Generic message for security
    }

    // Verify password - IMPORTANT: In production, use password_verify() with hashed passwords
    if (!hash_equals($employee->getEmployeePassword(), $password)) {
        throw new RuntimeException('Invalid credentials');
    }

    // Regenerate session ID to prevent fixation
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    session_regenerate_id(true);

    // Store user data in session instead of cookies for better security
    $_SESSION['employee'] = [
        'id' => $employee->getEmployeeId(),
        'role' => $employee->getEmployeeRole(),
        'store_id' => $employee->getStore() ? $employee->getStore()->getStoreId() : null,
        'last_login' => time()
    ];

    // Set secure cookie with session ID only
    setcookie(
        session_name(),
        session_id(),
        [
            'expires' => time() + (86400 * 7), // 7 days
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]
    );

    // Redirect to dashboard
    header('Location: ../view/index.php');
    exit();

} catch (InvalidArgumentException $e) {
    // Client-side errors (400 Bad Request)
    header('Location: ../view/login.php?error=' . urlencode($e->getMessage()));
    exit();
} catch (RuntimeException $e) {
    // Authentication failures (401 Unauthorized)
    header('Location: ../view/login.php?error=' . urlencode($e->getMessage()));
    exit();
} catch (Exception $e) {
    // Server errors (500 Internal Server Error)
    error_log('Login Error: ' . $e->getMessage());
    header('Location: ../view/login.php?error=login_failed');
    exit();
}