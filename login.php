<?php
session_start();
unset($_SESSION['username']);

$error = '';

if(isset($_POST['Login'])) {
    $un = $_POST["un"];
    $pa = $_POST['pa'];

    require 'db_config.php';
    $con = get_db_connection();
    $query = mysqli_query($con, "SELECT * FROM users WHERE username='" . $un . "'");
    $row = mysqli_fetch_array($query);

    if($row && $pa == $row['password']) {
        $_SESSION['username'] = $row['username'];
        if($row['role'] == "Admin") {
            header('Location: admin.php');
            exit;
        } else {
            header('Location: orders.php');
            exit;
        }
    } else {
        $error = "Invalid username or password.";
    }
    mysqli_close($con);
}
?>
<html>
<head>
    <title>Login – Ceylon Coconut Mill</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('images/coconut plantation.jpg') center/cover no-repeat;
            font-family: Arial, sans-serif;
        }
        .login-card {
            background: #fff;
            border-radius: 14px;
            padding: 40px 36px;
            width: 100%;
            max-width: 360px;
            box-shadow: 0 16px 48px rgba(0,0,0,0.28);
        }
        .login-card h1 { font-size: 22px; font-weight: 800; color: #1a3a1a; margin: 0 0 4px; }
        .login-card h1 span { color: #7ec820; }
        .login-card .sub { font-size: 13px; color: #777; margin-bottom: 26px; }
        .login-card label { display: block; font-size: 13px; font-weight: 600; color: #444; margin-bottom: 5px; }
        .login-card input[type=text],
        .login-card input[type=password] {
            width: 100%; border: 1px solid #d0d0d0; border-radius: 8px;
            padding: 10px 12px; font-size: 14px; color: #333;
            margin-bottom: 14px; outline: none; box-sizing: border-box;
        }
        .login-card input[type=text]:focus,
        .login-card input[type=password]:focus { border-color: #7ec820; }
        .error-msg {
            background: #fff0f0; border: 1px solid #f5c6c6; border-radius: 7px;
            color: #c00; font-size: 13px; padding: 9px 12px; margin-bottom: 14px;
        }
        .btn {
            width: 100%; background: #7ec820; color: #fff; border: none;
            border-radius: 8px; padding: 11px; font-size: 14px;
            font-weight: 700; cursor: pointer; margin-top: 4px;
        }
        .btn:hover { background: #6ab318; }
        .back { display: block; text-align: center; margin-top: 16px; font-size: 13px; color: #7ec820; text-decoration: none; }
        .back:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1>Ceylon <span>Coconut</span> Mill</h1>
        <p class="sub">Sign in to your account</p>
        <form method="POST" action="login.php">
            <label>Username</label>
            <input type="text" name="un" placeholder="Enter username" value="<?php echo isset($_POST['un']) ? htmlspecialchars($_POST['un']) : ''; ?>">
            <label>Password</label>
            <input type="password" name="pa" placeholder="Enter password">
            <?php if($error): ?>
                <div class="error-msg"><?php echo $error; ?></div>
            <?php endif; ?>
            <input class="btn" type="submit" name="Login" value="Sign In">
        </form>
        <a class="back" href="index.html">← Back to Home</a>
    </div>
</body>
</html>
