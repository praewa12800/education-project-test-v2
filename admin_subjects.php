<?php
include 'config.php'; 
session_start();

// ตรวจสอบ Login (ป้องกันคนแอบเข้า)
if (!isset($_SESSION['user_id'])) {
    header("Location: Page/login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// 1. จัดการการเพิ่มวิชา (เฉพาะครู)
if ($role == 'teacher' && isset($_POST['add_subject'])) {
    $name = mysqli_real_escape_string($conn, $_POST['subject_name']);
    $sql = "INSERT INTO subjects (name) VALUES ('$name')";
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('สร้างหลักสูตรสำเร็จ!'); window.location='admin_subjects.php';</script>";
    }
}

// 2. ดึงข้อมูลวิชามาแสดง (ดูได้ทุกคน)
$result_subjects = mysqli_query($conn, "SELECT * FROM subjects");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการรายวิชา - ระบบจัดการการศึกษา</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #f8faf9 0%, #e8f5f0 100%);
            min-height: 100vh;
            color: #2c3e50;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 30px;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 50%, #4a9070 100%);
            padding: 50px 60px;
            border-radius: 24px;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(45, 95, 79, 0.3);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .page-title {
            color: white;
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 20px;
            position: relative;
            z-index: 1;
        }

        .page-title .icon {
            width: 60px;
            height: 60px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }

        .page-subtitle {
            color: rgba(255, 255, 255, 0.95);
            font-size: 17px;
            margin-left: 80px;
            position: relative;
            z-index: 1;
        }

        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stat-item {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 25px;
            transition: all 0.3s ease;
            border-left: 5px solid #2d5f4f;
        }

        .stat-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.15);
        }

        .stat-item .stat-icon {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, #b8e6d5 0%, #a8dbc8 100%);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            flex-shrink: 0;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-item .stat-content h3 {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }

        .stat-item .stat-content .number {
            color: #2d5f4f;
            font-size: 32px;
            font-weight: 700;
        }

        /* Card Container */
        .card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 35px;
            padding-bottom: 25px;
            border-bottom: 3px solid #f0f3f5;
        }

        .card-title {
            font-size: 26px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
            color: #2d5f4f;
        }

        .card-title::before {
            content: '';
            width: 6px;
            height: 35px;
            background: linear-gradient(180deg, #2d5f4f 0%, #3a7760 100%);
            border-radius: 4px;
        }

        /* Table Styles */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 14px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .data-table thead {
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 100%);
        }

        .data-table th {
            color: white;
            padding: 20px 25px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .data-table th:first-child {
            border-top-left-radius: 14px;
        }

        .data-table th:last-child {
            border-top-right-radius: 14px;
        }

        .data-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f0f3f5;
        }

        .data-table tbody tr:hover {
            background: linear-gradient(135deg, #f0f9f6 0%, #e8f5f0 100%);
            transform: scale(1.005);
            box-shadow: 0 4px 15px rgba(45, 95, 79, 0.1);
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table td {
            padding: 22px 25px;
            font-size: 15px;
            color: #2c3e50;
        }

        /* Subject Cell */
        .subject-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .subject-icon-table {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #b8e6d5 0%, #a8dbc8 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .subject-info .subject-name {
            font-weight: 600;
            color: #2d5f4f;
            margin-bottom: 4px;
            font-size: 16px;
        }

        .subject-info .subject-code {
            font-size: 13px;
            color: #7f8c8d;
            font-weight: 500;
        }

        /* Badge */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .badge-active {
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 100%);
        }

        /* Actions */
        .subject-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
        }

        .btn-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .btn-icon.edit {
            background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
            color: white;
        }

        .btn-icon.delete {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
            color: white;
        }

        .btn-icon:hover {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.25);
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 80px 40px;
            background: linear-gradient(135deg, #f0f9f6 0%, #e0f2ed 100%);
            border-radius: 20px;
            border: 3px dashed rgba(45, 95, 79, 0.3);
        }

        .empty-state .icon {
            font-size: 80px;
            margin-bottom: 25px;
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-20px); }
        }

        .empty-state h3 {
            color: #2d5f4f;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 12px;
        }

        .empty-state p {
            color: #7f8c8d;
            font-size: 16px;
        }

        /* Form Section */
        .form-section {
            background: linear-gradient(135deg, #d4fc79 0%, #96e6a1 100%);
            padding: 40px;
            border-radius: 20px;
            border: 3px dashed rgba(45, 95, 79, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        .form-section h3 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: #2d5f4f;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            color: #2d5f4f;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .form-control {
            width: 100%;
            padding: 18px 24px;
            border: 3px solid rgba(255, 255, 255, 0.6);
            border-radius: 14px;
            font-size: 16px;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }

        .form-control:focus {
            outline: none;
            border-color: #2d5f4f;
            box-shadow: 0 0 0 5px rgba(45, 95, 79, 0.2);
            background: white;
        }

        .btn-submit {
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 100%);
            color: white;
            padding: 18px 40px;
            border: none;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 20px rgba(45, 95, 79, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(45, 95, 79, 0.4);
        }

        /* Student Table */
        .student-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .student-avatar {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #b8e6d5 0%, #a8dbc8 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
            border: 3px solid white;
        }

        .student-name {
            font-weight: 600;
            color: #2d5f4f;
            font-size: 16px;
        }

        .badge-you {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 20px 15px;
            }

            .page-header {
                padding: 35px 30px;
            }

            .page-title {
                font-size: 26px;
            }

            .stats-bar {
                grid-template-columns: 1fr;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            .data-table {
                min-width: 700px;
            }

            .form-section {
                padding: 30px 25px;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        
        <!-- Page Header -->
        <div class="page-header">
            <h1 class="page-title">
                <div class="icon">📚</div>
                จัดการรายวิชาและข้อมูลนักเรียน
            </h1>
            <p class="page-subtitle">ระบบจัดการหลักสูตรและข้อมูลนักเรียนในสถาบัน แบบครบวงจรด้วยเทคโนโลยีสมัยใหม่</p>
        </div>

        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-icon">📖</div>
                <div class="stat-content">
                    <h3>รายวิชาทั้งหมด</h3>
                    <div class="number">
                        <?php 
                        mysqli_data_seek($result_subjects, 0);
                        echo mysqli_num_rows($result_subjects); 
                        ?>
                    </div>
                </div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">👥</div>
                <div class="stat-content">
                    <h3>นักเรียนในระบบ</h3>
                    <div class="number">
                        <?php 
                        $count_students = mysqli_query($conn, "SELECT COUNT(*) as count FROM students");
                        echo mysqli_fetch_assoc($count_students)['count'];
                        ?>
                    </div>
                </div>
            </div>

            <?php if ($role == 'teacher'): ?>
            <div class="stat-item">
                <div class="stat-icon">👨‍🏫</div>
                <div class="stat-content">
                    <h3>ครูผู้สอน</h3>
                    <div class="number">
                        <?php 
                        $count_teachers = mysqli_query($conn, "SELECT COUNT(*) as count FROM teachers");
                        echo mysqli_fetch_assoc($count_teachers)['count'];
                        ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Subjects Table -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">รายวิชาทั้งหมดในระบบ</h2>
            </div>

            <?php 
            mysqli_data_seek($result_subjects, 0);
            if (mysqli_num_rows($result_subjects) > 0): 
            ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ชื่อวิชา</th>
                                <th>รหัสวิชา</th>
                                <th>สถานะ</th>
                                <?php if ($role == 'teacher'): ?>
                                <th>จัดการ</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $subject_icons = ['📘', '📗', '📙', '📕', '📓', '📔', '📒', '📖'];
                            $icon_index = 0;
                            $row_number = 1;
                            while($row = mysqli_fetch_assoc($result_subjects)): 
                            ?>
                            <tr>
                                <td>
                                    <div class="subject-icon-table">
                                        <?php echo $subject_icons[$icon_index % count($subject_icons)]; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="subject-info">
                                        <div class="subject-name"><?php echo htmlspecialchars($row['name']); ?></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="subject-code">
                                        <strong>SUB<?php echo str_pad($row['id'], 4, '0', STR_PAD_LEFT); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-active">
                                        ✓ เปิดใช้งาน
                                    </span>
                                </td>
                                <?php if ($role == 'teacher'): ?>
                                <td>
                                    <div class="subject-actions">
                                        <button class="btn-icon edit" title="แก้ไข">✏️</button>
                                        <button class="btn-icon delete" title="ลบ">🗑️</button>
                                    </div>
                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php 
                            $icon_index++;
                            $row_number++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="icon">📚</div>
                    <h3>ยังไม่มีรายวิชาในระบบ</h3>
                    <p>เริ่มต้นสร้างรายวิชาใหม่เพื่อเริ่มการเรียนการสอนที่มีประสิทธิภาพ</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Students Table (For Students Role) -->
        <?php if ($role == 'student'): ?>
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">รายชื่อนักเรียนในระบบ</h2>
                </div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>ชื่อ-นามสกุล</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $students_query = "SELECT u.name FROM users u JOIN students s ON u.id = s.user_id WHERE u.role = 'student'";
                            $res_students = mysqli_query($conn, $students_query);
                            $student_number = 1;
                            while($st = mysqli_fetch_assoc($res_students)): 
                            ?>
                            <tr>
                                <td>
                                    <div class="student-avatar">👤</div>
                                </td>
                                <td>
                                    <div class="student-name"><?php echo htmlspecialchars($st['name']); ?></div>
                                </td>
                                <td>
                                    <?php if($st['name'] == $_SESSION['name']): ?>
                                        <span class="badge badge-you">⭐ คุณ</span>
                                    <?php else: ?>
                                        <span class="badge">📚 นักเรียน</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php 
                            $student_number++;
                            endwhile; 
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Add Subject Form (For Teachers) -->
        <?php if ($role == 'teacher'): ?>
            <div class="card">
                <div class="form-section">
                    <h3>🆕 สร้างหลักสูตรใหม่</h3>
                    <form method="post">
                        <div class="form-group">
                            <label for="subject_name">ชื่อวิชา</label>
                            <input 
                                type="text" 
                                id="subject_name"
                                name="subject_name" 
                                class="form-control"
                                required 
                                placeholder="ระบุชื่อวิชา เช่น ภาษาไทย, คณิตศาสตร์, วิทยาศาสตร์, สังคมศึกษา"
                            >
                        </div>
                        <button type="submit" name="add_subject" class="btn-submit">
                            <span>💾</span>
                            <span>บันทึกรายวิชา</span>
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>
</html>