<?php
include 'config.php';
session_start();

// ตรวจสอบสิทธิ์: เฉพาะครู
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') {
    die("หน้านี้สำหรับครูเท่านั้น <a href='admin_menu.php'>กลับหน้าหลัก</a>");
}

$sql = "SELECT 
            s.id AS submission_id,
            u.name AS student_name,
            sub.name AS subject_name, -- ดึงชื่อวิชามาตรงนี้
            a.title AS task_title,
            a.type AS task_type,
            a.attachment_link AS question_file,
            s.file_link AS student_file,
            s.submitted_at
        FROM submissions s
        JOIN students st ON s.student_id = st.id
        JOIN users u ON st.user_id = u.id
        JOIN assignments a ON s.assignment_id = a.id
        JOIN subjects sub ON a.subject_id = sub.id -- JOIN กับตารางวิชา
        ORDER BY s.submitted_at DESC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบตรวจงานนักเรียน - Evaluation Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #3d7560;
            --secondary-green: #4a8970;
            --accent-green: #eef7f4;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --text-muted: #7f8c8d;
            --shadow: 0 10px 30px rgba(0,0,0,0.05);
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Prompt', 'Sarabun', sans-serif; 
            margin: 0; 
            background-color: #f8fafb; 
            color: var(--text-dark);
        }

        .container-fluid { 
            max-width: 1400px; 
            margin: 30px auto; 
            padding: 0 25px;
        }

        /* --- Header Banner --- */
        .header-banner {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            color: white;
            padding: 45px 50px;
            border-radius: 24px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 35px;
            box-shadow: 0 15px 35px rgba(61, 117, 96, 0.2);
            position: relative;
            overflow: hidden;
        }

        .header-banner .icon-box {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 20px;
            display: flex; align-items: center; justify-content: center;
            font-size: 38px;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .header-banner h1 { margin: 0; font-size: 30px; font-weight: 600; }
        .header-banner p { margin: 8px 0 0; font-size: 18px; opacity: 0.85; }

        /* --- Stats Grid --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: var(--white);
            padding: 25px 35px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 60px; height: 60px;
            background: var(--accent-green);
            border-radius: 15px;
            display: flex; align-items: center; justify-content: center;
            color: var(--primary-green);
            font-size: 26px;
        }

        .stat-info .label { font-size: 14px; color: var(--text-muted); display: block; }
        .stat-info .value { font-size: 24px; font-weight: 700; color: var(--primary-green); }

        /* --- Content Card --- */
        .content-card {
            background: var(--white);
            border-radius: 24px;
            padding: 40px;
            box-shadow: var(--shadow);
        }

        .content-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 35px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f4f7f6;
        }

        .table-container { overflow-x: auto; }

        table { width: 100%; border-collapse: separate; border-spacing: 0 12px; margin-top: -12px; }

        thead th { 
            color: var(--text-muted);
            padding: 15px 25px;
            text-align: left;
            font-size: 14px;
            font-weight: 600;
        }

        tbody tr {
            background-color: var(--white);
            transition: var(--transition);
        }

        tbody td { 
            padding: 22px 25px; 
            font-size: 15px;
            border-top: 1px solid #f8fafb;
            border-bottom: 1px solid #f8fafb;
        }

        tbody tr td:first-child { border-left: 1px solid #f8fafb; border-top-left-radius: 15px; border-bottom-left-radius: 15px; }
        tbody tr td:last-child { border-right: 1px solid #f8fafb; border-top-right-radius: 15px; border-bottom-right-radius: 15px; }

        tbody tr:hover { 
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }

        /* UI Elements */
        .subject-tag {
            background: #eef2f7;
            color: #455a64;
            padding: 5px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            display: inline-block;
            border: 1px solid #d1d9e6;
        }

        .badge {
            padding: 8px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
        }
        .bg-exam { background: #fff5f5; color: #e74c3c; border: 1px solid #ffdada; }
        .bg-homework { background: #f0f9f4; color: #27ae60; border: 1px solid #c6efd8; }

        .btn-action {
            background: var(--primary-green);
            color: white;
            text-decoration: none;
            padding: 10px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-action:hover { background: var(--secondary-green); box-shadow: 0 6px 15px rgba(61, 117, 96, 0.3); }

        .student-name { font-weight: 600; color: var(--text-dark); }
        .time-text { color: var(--text-muted); font-size: 13px; }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container-fluid">
        <div class="header-banner">
            <div class="icon-box">📂</div>
            <div>
                <h1>Evaluation Center</h1>
                <p>ศูนย์รวมการตรวจสอบผลงานและประเมินคะแนนนักเรียนรายวิชา</p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📥</div>
                <div class="stat-info">
                    <span class="label">งานที่ส่งแล้วทั้งหมด</span>
                    <span class="value"><?php echo mysqli_num_rows($result); ?> รายการ</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">👩‍🏫</div>
                <div class="stat-info">
                    <span class="label">ครูผู้ตรวจ</span>
                    <span class="value"><?php echo htmlspecialchars($_SESSION['name']); ?></span>
                </div>
            </div>
        </div>

        <div class="content-card">
            <div class="content-header">
                <h2><span>📊</span> รายการส่งงานแยกตามวิชา</h2>
                <div style="color: var(--text-muted); font-size: 14px;">อัปเดตข้อมูลล่าสุด</div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="60">#</th>
                            <th width="180">วัน-เวลาที่ส่ง</th>
                            <th width="200">รายวิชา</th> <th>ข้อมูลนักเรียน</th>
                            <th>หัวข้อภาระงาน</th>
                            <th width="160">ประเภท</th>
                            <th width="160" style="text-align: center;">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $i = 1;
                        if (mysqli_num_rows($result) > 0): 
                            while($row = mysqli_fetch_assoc($result)): 
                        ?>
                        <tr>
                            <td><strong><?php echo str_pad($i++, 2, "0", STR_PAD_LEFT); ?></strong></td>
                            <td>
                                <div class="time-text">
                                    <span style="display:block; color: var(--text-dark); font-weight: 500;">
                                        <?php echo date('d M Y', strtotime($row['submitted_at'])); ?>
                                    </span>
                                    <?php echo date('H:i', strtotime($row['submitted_at'])); ?> น.
                                </div>
                            </td>
                            <td>
                                <span class="subject-tag">
                                    <?php echo htmlspecialchars($row['subject_name']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="student-name"><?php echo htmlspecialchars($row['student_name']); ?></div>
                                <div style="font-size: 11px; color: var(--text-muted);">ID: SUB-<?php echo $row['submission_id']; ?></div>
                            </td>
                            <td>
                                <div style="font-weight: 500;"><?php echo htmlspecialchars($row['task_title']); ?></div>
                            </td>
                            <td>
                                <span class="badge <?php echo ($row['task_type'] == 'exam') ? 'bg-exam' : 'bg-homework'; ?>">
                                    <?php echo ($row['task_type'] == 'exam') ? '🚩 ข้อสอบ' : '📝 การบ้าน'; ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <a href="<?php echo htmlspecialchars($row['student_file']); ?>" target="_blank" class="btn-action">
                                    ตรวจงาน
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                        <tr>
                            <td colspan="7" style="text-align:center; padding: 100px;">
                                <h3 style="color: var(--text-muted);">ไม่พบข้อมูลการส่งงาน</h3>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>