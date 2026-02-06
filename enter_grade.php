<?php
include 'config.php';
session_start();

// ตรวจสอบ Login
if (!isset($_SESSION['user_id'])) {
    header("Location: Page/login.php");
    exit();
}

$role = $_SESSION['role'];
$user_id = $_SESSION['user_id'];

// --- ส่วนของครู: จัดการการบันทึกคะแนน ---
if ($role == 'teacher') {
    if (isset($_POST['submit_grade'])) {
        $student_id = mysqli_real_escape_string($conn, $_POST['student_id']);
        $sub_id = mysqli_real_escape_string($conn, $_POST['subject_id']);
        $score = mysqli_real_escape_string($conn, $_POST['score']);

        // ตรวจสอบว่าเคยกรอกเกรดวิชานี้ให้นักเรียนคนนี้ไปหรือยัง
        $check_sql = "SELECT id FROM grades WHERE student_id = '$student_id' AND subject_id = '$sub_id'";
        $check_res = mysqli_query($conn, $check_sql);

        if (mysqli_num_rows($check_res) > 0) {
            $sql = "UPDATE grades SET score = '$score' WHERE student_id = '$student_id' AND subject_id = '$sub_id'";
        } else {
            $sql = "INSERT INTO grades (student_id, subject_id, score) VALUES ('$student_id', '$sub_id', '$score')";
        }

        if (mysqli_query($conn, $sql)) {
            echo "<script>alert('บันทึกคะแนนเรียบร้อยแล้ว'); window.location='enter_grade.php';</script>";
        }
    }

    // ดึงรายชื่อนักเรียนและวิชามาใส่ใน Dropdown
    $students_list = mysqli_query($conn, "SELECT s.id, u.name FROM students s JOIN users u ON s.user_id = u.id");
    $subjects_list = mysqli_query($conn, "SELECT * FROM subjects");
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการเกรดและคะแนน - ระบบจัดการการศึกษา</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #e8f5f0 0%, #f0f9f6 50%, #e0f2ed 100%);
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
            background: linear-gradient(135deg, #3d7560 0%, #4a8970 50%, #5a9d80 100%);
            padding: 50px 60px;
            border-radius: 24px;
            margin-bottom: 40px;
            box-shadow: 0 20px 60px rgba(61, 117, 96, 0.3);
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
            font-weight: 400;
        }

        /* Info Banner */
        .info-banner {
            background: linear-gradient(135deg, #b8e6d5 0%, #96dbc0 100%);
            padding: 25px 35px;
            border-radius: 16px;
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 15px rgba(61, 117, 96, 0.15);
            border-left: 5px solid #3d7560;
        }

        .info-banner .icon {
            font-size: 36px;
            flex-shrink: 0;
        }

        .info-banner .content h3 {
            color: #3d7560;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .info-banner .content p {
            color: #4a8970;
            font-size: 14px;
            line-height: 1.6;
        }

        /* Main Card */
        .main-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            margin-bottom: 35px;
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .section-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f0f3f5;
        }

        .section-title {
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 15px;
            background: linear-gradient(135deg, #3d7560 0%, #4a8970 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title::before {
            content: '';
            width: 6px;
            height: 32px;
            background: linear-gradient(180deg, #3d7560 0%, #4a8970 100%);
            border-radius: 4px;
        }

        /* Form Styles */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-label {
            color: #3d7560;
            font-size: 15px;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #cbd5e0;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            background: white;
        }

        .form-control:focus {
            outline: none;
            border-color: #3d7560;
            box-shadow: 0 0 0 4px rgba(61, 117, 96, 0.1);
        }

        .btn-submit {
            background: linear-gradient(135deg, #3d7560 0%, #4a8970 100%);
            color: white;
            padding: 16px 40px;
            border: none;
            border-radius: 14px;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(61, 117, 96, 0.3);
            width: 100%;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #2d5f4f 0%, #3d7560 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(61, 117, 96, 0.4);
        }

        /* Table Styles */
        .table-wrapper {
            overflow-x: auto;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .data-table thead {
            background: linear-gradient(135deg, #3d7560 0%, #4a8970 100%);
        }

        .data-table th {
            color: white;
            padding: 18px 20px;
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
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table td {
            padding: 20px;
            font-size: 15px;
            color: #2c3e50;
        }

        /* Status Badges */
        .status-pass {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid #28a745;
        }

        .status-pass::before {
            content: '✓';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 12px;
        }

        .status-fail {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            border: 2px solid #dc3545;
        }

        .status-fail::before {
            content: '✕';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 12px;
        }

        .score-badge {
            display: inline-block;
            padding: 8px 20px;
            background: linear-gradient(135deg, #b8e6d5 0%, #96dbc0 100%);
            color: #3d7560;
            border-radius: 12px;
            font-weight: 700;
            font-size: 18px;
            border: 2px solid #3d7560;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 30px;
            background: linear-gradient(135deg, #f8fafb 0%, #f0f4f3 100%);
            border-radius: 16px;
            border: 2px dashed #cbd5e0;
        }

        .empty-state .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #3d7560;
            font-size: 20px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #7f8c8d;
            font-size: 15px;
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
                flex-direction: column;
                align-items: flex-start;
            }

            .page-subtitle {
                margin-left: 0;
                margin-top: 10px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            .data-table {
                min-width: 600px;
            }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        
        <?php if ($role == 'teacher'): ?>
            <!-- Page Header สำหรับครู -->
            <div class="page-header">
                <h1 class="page-title">
                    <div class="icon">📊</div>
                    จัดการคะแนนนักเรียน
                </h1>
                <p class="page-subtitle">บันทึกและจัดการคะแนนสอบ การบ้าน และผลการเรียนของนักเรียน</p>
            </div>

            <!-- Info Banner -->
            <div class="info-banner">
                <div class="icon">💡</div>
                <div class="content">
                    <h3>วิธีการบันทึกคะแนน</h3>
                    <p>เลือกนักเรียนและรายวิชาที่ต้องการบันทึกคะแนน กรอกคะแนนที่ได้ (0-100) แล้วกดบันทึกข้อมูล • หากมีคะแนนเดิมอยู่แล้ว ระบบจะอัปเดตเป็นคะแนนใหม่</p>
                </div>
            </div>

            <!-- Form Card -->
            <div class="main-card">
                <div class="section-header">
                    <h2 class="section-title">บันทึกคะแนนนักเรียน</h2>
                </div>

                <form method="post">
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">เลือกนักเรียน</label>
                            <select name="student_id" class="form-control" required>
                                <option value="">-- ค้นหารายชื่อนักเรียน --</option>
                                <?php 
                                mysqli_data_seek($students_list, 0);
                                while($row = mysqli_fetch_assoc($students_list)): 
                                ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">เลือกรายวิชา</label>
                            <select name="subject_id" class="form-control" required>
                                <option value="">-- ค้นหารายชื่อวิชา --</option>
                                <?php 
                                mysqli_data_seek($subjects_list, 0);
                                while($row = mysqli_fetch_assoc($subjects_list)): 
                                ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label">คะแนนที่ได้ (0-100)</label>
                            <input type="number" name="score" class="form-control" min="0" max="100" step="0.01" required placeholder="กรอกคะแนน เช่น 85.5">
                        </div>
                    </div>

                    <button type="submit" name="submit_grade" class="btn-submit">บันทึกข้อมูลคะแนน</button>
                </form>
            </div>

        <?php else: ?>
            <!-- Page Header สำหรับนักเรียน -->
            <div class="page-header">
                <h1 class="page-title">
                    <div class="icon">🎓</div>
                    ผลการเรียนของคุณ
                </h1>
                <p class="page-subtitle">ดูคะแนนและผลการประเมินในแต่ละรายวิชา</p>
            </div>

            <!-- Results Card -->
            <div class="main-card">
                <div class="section-header">
                    <h2 class="section-title">ผลคะแนนทั้งหมด</h2>
                </div>

                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>รายวิชา</th>
                                <th style="text-align: center;">คะแนนที่ได้</th>
                                <th style="text-align: center;">การประเมิน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT s.name as subject_name, g.score 
                                    FROM grades g 
                                    JOIN subjects s ON g.subject_id = s.id 
                                    WHERE g.student_id = (SELECT id FROM students WHERE user_id = '$user_id')";
                            $res = mysqli_query($conn, $sql);
                            
                            if (mysqli_num_rows($res) > 0) {
                                while($row = mysqli_fetch_assoc($res)) {
                                    $score = $row['score'];
                                    $status_class = ($score >= 50) ? 'status-pass' : 'status-fail';
                                    $status_text = ($score >= 50) ? 'ผ่าน' : 'ไม่ผ่าน';
                                    
                                    echo "<tr>
                                            <td><strong>".htmlspecialchars($row['subject_name'])."</strong></td>
                                            <td style='text-align: center;'><span class='score-badge'>{$score}</span></td>
                                            <td style='text-align: center;'><span class='{$status_class}'>{$status_text}</span></td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='3'>
                                        <div class='empty-state'>
                                            <div class='icon'>📋</div>
                                            <h3>ยังไม่มีข้อมูลคะแนน</h3>
                                            <p>ขณะนี้ยังไม่มีข้อมูลคะแนนในระบบ</p>
                                        </div>
                                      </td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>