<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>jp test - dashboard</title>
</head>
<body>
    <h2>dashboard</h2>
    
    <p>You've Signin. User ID is <?php echo $_SESSION['user_id']?></p>
    
    <p><a href="logout.php">Logout</a></p>
</body>
</html>