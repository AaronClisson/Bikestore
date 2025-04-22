<?php
include_once("../www/header.php");
?>

<link rel="stylesheet" href="../css/login.css">

<main class="login-container">
    <form action="../controller/veriflogin.php" method="POST">
        <h1>Connection</h1>
        <p>Please authenticate yourself to access BLB features.</p>
        
        <!-- Login Input -->
        <label for="login">Login</label>
        <input type="text" id="login" name="login" placeholder="Login" required aria-label="Enter your login">
        
        <!-- Password Input with visibility toggle -->
        <label for="password">Password</label>
        <div class="password-container">
            <input type="password" id="password" name="password" placeholder="Password" required aria-label="Enter your password">
            <button type="button" id="toggle-password" aria-label="Toggle password visibility">👁️</button>
        </div>

        <!-- Submit and Reset Buttons -->
        <input type="submit" id="submit" value="Submit">
        <input type="reset" id="reset" value="Reset">
        
        <!-- Display login errors if any -->
        <?php if (isset($_GET['error']) && $_GET['error'] === 'true') : ?>
            <p id="erreur" style="color: red;">Invalid login or password, please try again.</p>
        <?php endif; ?>

    </form>
</main>

<!-- Include JavaScript for password visibility toggle -->
<script>
    document.getElementById('toggle-password').addEventListener('click', function() {
        const passwordField = document.getElementById('password');
        const type = passwordField.type === 'password' ? 'text' : 'password';
        passwordField.type = type;
        this.textContent = type === 'password' ? '👁️' : '🙈';
    });
</script>

<?php
include_once("../www/footer.php");
?>
