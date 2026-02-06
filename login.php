<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT id, name, role, password FROM users WHERE email = '$email'";
    $result = mysqli_query($conn, $sql);
    $user = mysqli_fetch_assoc($result);

    if ($user && $password == $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['name'] = $user['name'];
        header("Location:admin_menu.php");
    } else {
        echo "<script>alert('Invalid credentials');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - ระบบจัดการการเรียนการสอน</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #b8e6d5 0%, #a8dbc8 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 1000px;
            height: 550px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        /* Left Side - Login */
        .login-section {
            flex: 0 0 400px;
            padding: 50px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .logo {
            font-size: 13px;
            font-weight: 500;
            color: #666;
            margin-bottom: 40px;
        }

        .user-icon {
            width: 70px;
            height: 70px;
            background: #b8e6d5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            font-size: 32px;
        }

        .input-group {
            margin-bottom: 16px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s;
        }

        .input-group input:focus {
            outline: none;
            border-color: #a8dbc8;
        }

        .remember-forgot {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #666;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .forgot-password {
            color: #2d5f4f;
            text-decoration: none;
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 15px;
            font-weight: 500;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 15px;
        }

        .login-btn:hover {
            background: linear-gradient(135deg, #1a4033 0%, #2d5f4f 100%);
        }

        .signup-link {
            text-align: center;
            color: #666;
            font-size: 13px;
        }

        .signup-link a {
            color: #2d5f4f;
            text-decoration: none;
        }

        /* Right Side - Welcome */
        .welcome-section {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
            align-items: flex-end;
            padding: 40px 50px;
            overflow: hidden;
            background-image: url('pic/backgroundgreen.png');
            background-size: cover;
            background-position: center;
        }

        .welcome-content {
            position: relative;
            z-index: 1;
            text-align: right;
            transition: transform 0.3s ease;
        }

        .welcome-content:hover {
            transform: translateY(-5px);
        }

        .welcome-content h1 {
            font-size: 64px;
            font-weight: 300;
            margin-bottom: 15px;
            letter-spacing: 2px;
            color: #4f5a6a;
        }

        .welcome-content p {
            font-size: 16px;
            opacity: 0.9;
            line-height: 1.6;
            color: #4f5a6a;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .container {
                flex-direction: column;
                height: auto;
                max-width: 450px;
            }

            .login-section {
                flex: 1;
                padding: 40px 30px;
            }

            .welcome-section {
                order: -1;
                min-height: 300px;
                padding: 40px 30px;
                justify-content: center;
                align-items: center;
            }

            .welcome-content {
                text-align: center;
            }

            .welcome-content h1 {
                font-size: 48px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Left - Login Form -->
        <div class="login-section">
            <div class="logo">Education Management</div>

            <div class="user-icon">👤</div>

            <form method="post">
                <div class="input-group">
                    <input type="email" name="email" placeholder="USERNAME" required>
                </div>

                <div class="input-group">
                    <input type="password" name="password" placeholder="PASSWORD" required>
                </div>

                <div class="remember-forgot">
                    <label class="remember-me">
                        <input type="checkbox">
                        <span>Remember me</span>
                    </label>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>

                <button type="submit" class="login-btn">LOGIN</button>

                <div class="signup-link">
                    Don't have an account? <a href="register.php">Sign up</a>
                </div>
            </form>
        </div>

        <!-- Right - Welcome -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>Welcome.</h1>
                <p>ยินดีต้อนรับสู่ระบบการเรียนการสอน<br>เข้าถึงบทเรียนได้ทุกที่ ทุกเวลา</p>
            </div>
        </div>
    </div>
</body>
</html>