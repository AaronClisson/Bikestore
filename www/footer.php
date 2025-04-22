<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="author" content="Aaron Clisson">
    <link rel="stylesheet" type="text/css" href="../css/style.css">
</head>

<body>
    <footer>
        <div id="footermenu">
            <a href="../view/index.php">Home</a>
            <a href="../view/products.php">Products</a>
            <?php
            $role = $_COOKIE['employee_role'] ?? null;

            if (in_array($role, ['employee', 'chief', 'it'])) {
                echo '<a href="../view/account.php">Account</a>';
            }

            if ($role === "it") {
                echo '<a href="../view/consultIt.php">Consult Employees</a>';
            }

            if ($role === "chief") {
                echo '<a href="../view/consultChief.php">Consult Employees</a>';
            }

            if (!isset($_COOKIE['employee_id'])) {
                echo '<a href="../view/login.php">Connection</a>';
            } else {
                echo '<a href="../view/deconnection.php">Deconnection</a>';
            }
            ?>
        </div>
        <br>
        <address>
            <span>BUT MMI, 50000 Saint-Lô, France</span>
            <a href="tel:+33XXXXXXXXX">Tel</a>
            <a href="mailto:blb@bikestore.com">Email</a>
        </address>
        <br>
        <?php
        echo "<span>©<span class=\"copyright\"></span> - <time id=\"d\">" . date("Y") . "</time></span> ";
        ?>

        <script>
            function copyright() {
                const txt = document.querySelector('meta[name="author"]').getAttribute('content');
                const copyrightL = document.getElementsByClassName('copyright');
                for (let i = 0; i < copyrightL.length; i++)
                    copyrightL[i].textContent = txt;
            }
            copyright();
        </script>
    </footer>
</body>
</html>