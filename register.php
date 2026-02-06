<?php
include 'config.php';

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password']; // ในงานจริงควรใช้ password_hash
    $role = $_POST['role'];

    // 1. ตรวจสอบ Email ซ้ำ
    $check_email = mysqli_query($conn, "SELECT id FROM users WHERE email = '$email'");
    if (mysqli_num_rows($check_email) > 0) {
        echo "<script>alert('Email นี้มีในระบบแล้ว');</script>";
    } else {
        // 2. บันทึกลงตาราง users
        $sql_user = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$password', '$role')";
        
        if (mysqli_query($conn, $sql_user)) {
            $user_id = mysqli_insert_id($conn); // ดึง ID ล่าสุดที่เพิ่งสร้าง
            
            // 3. บันทึกลงตารางแยกตาม Role ตาม Schema
            if ($role == 'student') {
                mysqli_query($conn, "INSERT INTO students (user_id, class_level) VALUES ('$user_id', 'Unassigned')");
            } else if ($role == 'teacher') {
                mysqli_query($conn, "INSERT INTO teachers (user_id, department) VALUES ('$user_id', 'General')");
            }
            
            echo "<script>alert('ลงทะเบียนสำเร็จ'); window.location='login.php';</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - ระบบจัดการการเรียนการสอน</title>
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
            min-height: 600px;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        /* Left Side - Register Form */
        .register-section {
            flex: 0 0 400px;
            padding: 40px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: #ffffff;
        }

        .logo {
            font-size: 13px;
            font-weight: 500;
            color: #666;
            margin-bottom: 30px;
        }

        .user-icon {
            width: 70px;
            height: 70px;
            background: #b8e6d5;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            font-size: 32px;
        }

        .form-title {
            text-align: center;
            font-size: 18px;
            font-weight: 500;
            color: #2d5f4f;
            margin-bottom: 25px;
        }

        .input-group {
            margin-bottom: 14px;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            color: #666;
            margin-bottom: 6px;
            font-weight: 400;
        }

        .input-group input,
        .input-group select {
            width: 100%;
            padding: 14px 18px;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s;
        }

        .input-group input:focus,
        .input-group select:focus {
            outline: none;
            border-color: #a8dbc8;
        }

        .input-group select {
            cursor: pointer;
            background-color: white;
        }

        .register-btn {
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
            margin-top: 8px;
            margin-bottom: 15px;
        }

        .register-btn:hover {
            background: linear-gradient(135deg, #1a4033 0%, #2d5f4f 100%);
        }

        .login-link {
            text-align: center;
            color: #666;
            font-size: 13px;
        }

        .login-link a {
            color: #2d5f4f;
            text-decoration: none;
            font-weight: 500;
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

            .register-section {
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
        <!-- Left - Register Form -->
        <div class="register-section">
            <div class="logo">Education Management</div>

            <div class="user-icon">👤</div>

            <div class="form-title">ลงทะเบียนนักเรียน/ครู</div>

            <form method="post">
                <div class="input-group">
                    <label>ชื่อ-นามสกุล</label>
                    <input type="text" name="name" placeholder="กรอกชื่อ-นามสกุล" required>
                </div>

                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="example@email.com" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="กรอกรหัสผ่าน" required>
                </div>

                <div class="input-group">
                    <label>บทบาท</label>
                    <select name="role" required>
                        <option value="student">นักเรียน</option>
                        <option value="teacher">ครู</option>
                        <!-- <option value="admin">ผู้ดูแลระบบ (Admin)</option> -->
                    </select>
                </div>

                <button type="submit" name="register" class="register-btn">สมัครสมาชิก</button>

                <div class="login-link">
                    มีบัญชีอยู่แล้ว? <a href="login.php">เข้าสู่ระบบ</a>
                </div>
            </form>
        </div>

        <!-- Right - Welcome -->
        <div class="welcome-section">
            <div class="welcome-content">
                <h1>Join Us.</h1>
                <p>เริ่มต้นการเรียนรู้กับเรา<br>สมัครสมาชิกเพื่อเข้าถึงระบบการเรียนการสอน</p>
            </div>
        </div>
    </div>
</body>
</html>