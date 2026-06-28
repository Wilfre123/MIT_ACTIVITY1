<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    echo"access denied pleas login first";
    exit();
}
?>

<h1>Welcome <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
<a href="logout.php">Logout</a>

