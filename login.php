<?php
session_start();

require_once __DIR__ . '/lib/db/client.php';
require_once __DIR__ . '/lib/utils/common.func.php';
require_once __DIR__ . '/service/User.php';
//var_dump($_SESSION); //debug
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //csrf verify
    if (!verifyCSRF($_POST['csrf'] ?? '')) {
        http_response_code(403);
        exit;
    }
    //xss filter
    $name = safetyInput($_POST['name'] ?? '');
    //Using PDO with MySQL automatically protects against SQL injection
    //So do noting~~~
    $password = $_POST['password'] ?? '';
    $result = User::login($name, $password);
    if (empty($result['errors'])) {
         $_SESSION['user_id'] = $result['data']['user']['id'];
        header('Location: dashboard.php');
        //exit;
    } else {
        $errors = $result['errors'];
    }
}
$csrf = getCSRF();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Jp test - Login</title>
</head>
<body>
    <h2>Sign in / Login</h2>
    <?php if (!empty($errors)): ?>
        <div style="color: red;">
            <?php foreach ($errors as $error): ?>
                <p><?php echo errorMessage($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    <form method="POST" action="">
        <input type="hidden" name="csrf" value="<?php echo $csrf; ?>">
        <div>
            <label for="name">name:</label>
            <input type="text" id="name" name="name" required maxlength="20">
        </div>  
        <div>
            <label for="password">password:</label>
            <input type="password" id="password" name="password" required maxlength="20">
        </div>
        
        <button type="submit">Sign in</button>
    </form>
    
    <p>switch to <a href="register.php">Sign up / Register</a></p>
</body>
</html>
