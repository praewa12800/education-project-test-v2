<?php
include 'config.php';
session_start();

// ตรวจสอบ Login และสิทธิ์นักเรียน
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') { 
    die("หน้านี้สำหรับนักเรียนเท่านั้น <a href='admin_menu.php'>กลับหน้าหลัก</a>"); 
}

// ประมวลผลการส่งงาน
if (isset($_POST['upload'])) {
    $assignment_id = $_POST['assignment_id'];
    $user_id = $_SESSION['user_id'];
    
    // ดึง student_id จากตาราง students
    $st_res = mysqli_query($conn, "SELECT id FROM students WHERE user_id = '$user_id'");
    $st_row = mysqli_fetch_assoc($st_res);
    $student_id = $st_row['id'];

    $target_dir = "uploads/submissions/";
    if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
    
    $file_name = time() . "_" . basename($_FILES["fileToUpload"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        // ตรวจสอบว่ามีการส่งงานเก่าหรือไม่
        $check_sql = "SELECT id FROM submissions WHERE student_id = '$student_id' AND assignment_id = '$assignment_id'";
        $check_result = mysqli_query($conn, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            // อัปเดตงานเก่า
            $sql = "UPDATE submissions SET file_link = '$target_file', submitted_at = NOW() 
                    WHERE student_id = '$student_id' AND assignment_id = '$assignment_id'";
            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('ส่งงานใหม่สำเร็จ!'); window.location='submit_work.php';</script>";
            }
        } else {
            // เพิ่มงานใหม่
            $sql = "INSERT INTO submissions (student_id, assignment_id, file_link, submitted_at) 
                    VALUES ('$student_id', '$assignment_id', '$target_file', NOW())";
            if (mysqli_query($conn, $sql)) {
                echo "<script>alert('ส่งงานสำเร็จ!'); window.location='submit_work.php';</script>";
            }
        }
    } else {
        echo "<script>alert('เกิดข้อผิดพลาดในการอัปโหลดไฟล์');</script>";
    }
}

// ดึงข้อมูลงานและตรวจสอบว่าส่งแล้วหรือยัง
$user_id = $_SESSION['user_id'];
$st_res = mysqli_query($conn, "SELECT id FROM students WHERE user_id = '$user_id'");
$st_row = mysqli_fetch_assoc($st_res);
$student_id = $st_row['id'];

$query = "SELECT a.*, s.name as subject_name, 
          (SELECT COUNT(*) FROM submissions WHERE student_id = '$student_id' AND assignment_id = a.id) as is_submitted,
          (SELECT submitted_at FROM submissions WHERE student_id = '$student_id' AND assignment_id = a.id ORDER BY submitted_at DESC LIMIT 1) as submitted_date
          FROM assignments a 
          JOIN subjects s ON a.subject_id = s.id 
          ORDER BY a.due_date ASC";
$assignments = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งการบ้าน - ระบบจัดการการศึกษา</title>
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

        .page-header::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
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
            box-shadow: 0 4px 15px rgba(45, 95, 79, 0.15);
            border-left: 5px solid #2d5f4f;
        }

        .info-banner .icon {
            font-size: 36px;
            flex-shrink: 0;
        }

        .info-banner .content {
            flex: 1;
        }

        .info-banner .content h3 {
            color: #2d5f4f;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .info-banner .content p {
            color: #3a7760;
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
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .section-title::before {
            content: '';
            width: 6px;
            height: 32px;
            background: linear-gradient(180deg, #2d5f4f 0%, #3a7760 100%);
            border-radius: 4px;
        }

        /* Assignment Table */
        .assignment-table-wrapper {
            overflow-x: auto;
            border-radius: 14px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }

        .assignment-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
        }

        .assignment-table thead {
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 100%);
        }

        .assignment-table th {
            color: white;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
        }

        .assignment-table th:first-child {
            border-top-left-radius: 14px;
        }

        .assignment-table th:last-child {
            border-top-right-radius: 14px;
        }

        .assignment-table tbody tr {
            transition: all 0.3s ease;
            border-bottom: 1px solid #f0f3f5;
        }

        .assignment-table tbody tr:hover {
            background: linear-gradient(135deg, #f0f9f6 0%, #e8f5f0 100%);
        }

        .assignment-table tbody tr:last-child {
            border-bottom: none;
        }

        .assignment-table td {
            padding: 20px;
            font-size: 15px;
            color: #2c3e50;
            vertical-align: middle;
        }

        /* Badge Styles */
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
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .badge-exam {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }

        .badge-work {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
            color: white;
        }

        /* Subject Cell */
        .subject-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .subject-icon {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #b8e6d5 0%, #96dbc0 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
        }

        .subject-info .subject-name {
            font-weight: 600;
            color: #2d5f4f;
            margin-bottom: 4px;
        }

        .subject-info .subject-title {
            font-size: 13px;
            color: #7f8c8d;
        }

        /* Attachment Link */
        .attachment-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #2d5f4f;
            text-decoration: none;
            font-weight: 500;
            padding: 8px 16px;
            background: linear-gradient(135deg, #e8f5f0 0%, #d8ede5 100%);
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .attachment-link:hover {
            background: linear-gradient(135deg, #b8e6d5 0%, #a8dbc8 100%);
            transform: translateX(3px);
        }

        /* Date Display */
        .date-display {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
            color: #2d5f4f;
        }

        /* Upload Form in Table */
        .upload-form-inline {
            display: flex;
            flex-direction: column;
            gap: 10px;
            padding: 12px;
            background: linear-gradient(135deg, #f8fafb 0%, #f0f4f3 100%);
            border-radius: 10px;
            border: 2px solid #e0e7e9;
        }

        .file-input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .file-input-wrapper {
            position: relative;
        }

        .file-input-wrapper input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 2px solid #cbd5e0;
            border-radius: 8px;
            font-size: 13px;
            font-family: 'Prompt', sans-serif;
            background: white;
            cursor: pointer;
        }

        .file-input-wrapper input[type="file"]::-webkit-file-upload-button {
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 100%);
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            margin-right: 8px;
            font-size: 12px;
        }

        .btn-submit-inline {
            background: linear-gradient(135deg, #2d5f4f 0%, #3a7760 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(45, 95, 79, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-submit-inline:hover {
            background: linear-gradient(135deg, #1a4033 0%, #2d5f4f 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(45, 95, 79, 0.3);
        }

        .btn-submit-inline:active {
            transform: translateY(0);
        }

        .btn-resubmit-inline {
            background: linear-gradient(135deg, #f39c12 0%, #e67e22 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(243, 156, 18, 0.2);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }

        .btn-resubmit-inline:hover {
            background: linear-gradient(135deg, #e67e22 0%, #d35400 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(243, 156, 18, 0.3);
        }

        .btn-resubmit-inline:active {
            transform: translateY(0);
        }

        .file-hint {
            color: #7f8c8d;
            font-size: 11px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Submitted Status */
        .submitted-status {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 12px 16px;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-radius: 10px;
            border: 2px solid #28a745;
        }

        .submitted-status .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #155724;
            font-weight: 600;
            font-size: 14px;
        }

        .submitted-status .status-badge::before {
            content: '✓';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            font-weight: bold;
        }

        .submitted-status .submitted-date {
            color: #155724;
            font-size: 12px;
            margin-left: 32px;
        }

        /* Expired Status */
        .expired-status {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 12px 16px;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-radius: 10px;
            border: 2px solid #dc3545;
        }

        .expired-status .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #721c24;
            font-weight: 600;
            font-size: 14px;
        }

        .expired-status .status-badge::before {
            content: '✕';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            font-weight: bold;
        }

        .expired-status .submitted-date {
            color: #721c24;
            font-size: 12px;
            margin-left: 32px;
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
            color: #2d5f4f;
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

            .assignment-table-wrapper {
                overflow-x: auto;
            }

            .assignment-table {
                min-width: 1000px;
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
                <div class="icon">📝</div>
                รายการงานและส่งการบ้าน
            </h1>
            <p class="page-subtitle">จัดการส่งงานและติดตามงานที่ได้รับมอบหมายทั้งหมด</p>
        </div>

        <!-- Info Banner -->
        <div class="info-banner">
            <div class="icon">💡</div>
            <div class="content">
                <h3>วิธีการส่งงาน</h3>
                <p>เลือกไฟล์คำตอบจากแต่ละแถวในตารางด้านล่าง แล้วกดปุ่ม "ส่งงาน" เพื่อยืนยัน • สามารถส่งงานใหม่ได้ถ้ายังไม่หมดเวลากำหนดส่ง</p>
            </div>
        </div>

        <!-- Assignments Table -->
        <div class="main-card">
            <div class="section-header">
                <h2 class="section-title">งานที่ได้รับมอบหมายทั้งหมด</h2>
            </div>

            <?php if (mysqli_num_rows($assignments) > 0): ?>
                <div class="assignment-table-wrapper">
                    <table class="assignment-table">
                        <thead>
                            <tr>
                                <th>ประเภท</th>
                                <th>วิชา / หัวข้อ</th>
                                <th>เอกสารแนบ</th>
                                <th>กำหนดส่ง</th>
                                <th style="min-width: 320px;">ส่งงาน</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            mysqli_data_seek($assignments, 0);
                            $current_date = date('Y-m-d H:i:s');
                            while($row = mysqli_fetch_assoc($assignments)): 
                                $is_expired = strtotime($row['due_date']) < strtotime($current_date);
                            ?>
                            <tr>
                                <td>
                                    <span class="badge <?php echo ($row['type'] == 'exam') ? 'badge-exam' : 'badge-work'; ?>">
                                        <?php echo ($row['type'] == 'exam') ? 'ข้อสอบ' : 'การบ้าน'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="subject-cell">
                                        <div class="subject-icon">📖</div>
                                        <div class="subject-info">
                                            <div class="subject-name"><?php echo htmlspecialchars($row['subject_name']); ?></div>
                                            <div class="subject-title"><?php echo htmlspecialchars($row['title']); ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if($row['attachment_link']): ?>
                                        <a href="<?php echo htmlspecialchars($row['attachment_link']); ?>" target="_blank" class="attachment-link">
                                            ดาวน์โหลดโจทย์
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #95a5a6;">ไม่มีไฟล์แนบ</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="date-display">
                                        <?php echo date('d/m/Y', strtotime($row['due_date'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php if($row['is_submitted'] > 0 && $is_expired): ?>
                                        <!-- ส่งแล้วและหมดเวลา -->
                                        <div class="expired-status">
                                            <div class="status-badge">
                                                หมดเวลาส่งงาน
                                            </div>
                                            <div class="submitted-date">
                                                ส่งเมื่อ: <?php echo date('d/m/Y H:i น.', strtotime($row['submitted_date'])); ?>
                                            </div>
                                        </div>
                                    <?php elseif($row['is_submitted'] > 0 && !$is_expired): ?>
                                        <!-- ส่งแล้วแต่ยังไม่หมดเวลา - แสดงสถานะและให้ส่งใหม่ได้ -->
                                        <div class="upload-form-inline" style="background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%); border-color: #28a745;">
                                            <div class="submitted-status" style="padding: 8px 12px; margin-bottom: 8px;">
                                                <div class="status-badge" style="font-size: 13px;">
                                                    ส่งงานแล้ว
                                                </div>
                                                <div class="submitted-date">
                                                    ส่งเมื่อ: <?php echo date('d/m/Y H:i น.', strtotime($row['submitted_date'])); ?>
                                                </div>
                                            </div>
                                            
                                            <form method="post" enctype="multipart/form-data">
                                                <input type="hidden" name="assignment_id" value="<?php echo $row['id']; ?>">
                                                
                                                <div class="file-input-group">
                                                    <div class="file-input-wrapper">
                                                        <input type="file" name="fileToUpload" required>
                                                    </div>
                                                    <div class="file-hint">
                                                        PDF, Word, รูปภาพ, ZIP (ไม่เกิน 10 MB)
                                                    </div>
                                                </div>

                                                <button type="submit" name="upload" class="btn-resubmit-inline">
                                                    ส่งงานใหม่
                                                </button>
                                            </form>
                                        </div>
                                    <?php elseif(!$row['is_submitted'] && $is_expired): ?>
                                        <!-- ยังไม่ส่งและหมดเวลาแล้ว -->
                                        <div class="expired-status">
                                            <div class="status-badge">
                                                หมดเวลาส่งงาน
                                            </div>
                                            <div class="submitted-date">
                                                ยังไม่ได้ส่งงาน
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- ยังไม่ส่งและยังไม่หมดเวลา -->
                                        <div class="upload-form-inline">
                                            <form method="post" enctype="multipart/form-data">
                                                <input type="hidden" name="assignment_id" value="<?php echo $row['id']; ?>">
                                                
                                                <div class="file-input-group">
                                                    <div class="file-input-wrapper">
                                                        <input type="file" name="fileToUpload" required>
                                                    </div>
                                                    <div class="file-hint">
                                                        PDF, Word, รูปภาพ, ZIP (ไม่เกิน 10 MB)
                                                    </div>
                                                </div>

                                                <button type="submit" name="upload" class="btn-submit-inline">
                                                    ส่งงาน
                                                </button>
                                            </form>
                                        </div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <h3>ไม่มีงานที่ต้องส่ง</h3>
                    <p>ขณะนี้คุณไม่มีงานที่รอการส่ง</p>
                </div>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>