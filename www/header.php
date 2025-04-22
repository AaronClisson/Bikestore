<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Bike Like a Boss</title>
</head>

<header>
    <h1>Bike Like a Boss</h1>
    <nav>
        <li><a href="../view/index.php">Home</a></li>
        <li><a href="../view/products.php">Products</a></li>

        <?php
        $role = $_COOKIE['employee_role'] ?? null;

        if ($role === "employee" || $role === "chief" || $role === "it") {
            echo '<li><a href="../view/account.php">Account</a></li>';
        }

        if ($role === "it") {
            echo '<li><a href="../view/consultIt.php">Consult Employees</a></li>';
        }

        if ($role === "chief") {
            echo '<li><a href="../view/consultChief.php">Consult Employees</a></li>';
        }

        if (!isset($_COOKIE['employee_id'])) {
            echo '<li><a href="../view/login.php">Connection</a></li>';
        } else {
            echo '<li><a href="../view/deconnection.php">Deconnection</a></li>';
        }
        ?>
    </nav>
</header>