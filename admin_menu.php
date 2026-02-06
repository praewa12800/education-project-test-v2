<?php
include 'config.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: Page/login.php"); exit(); }

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$name = $_SESSION['name'];
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ระบบจัดการการศึกษา</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&family=Prompt:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #3d7560;
            --secondary-green: #4a8970;
            --accent-green: #e8f5f0;
            --bg-color: #f8fafb;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --text-muted: #7f8c8d;
            --shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Prompt', 'Sarabun', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 1400px; /* ตามความต้องการของผู้ใช้ */
            margin: 0 auto;
            padding: 40px 30px;
        }

        /* --- Welcome Header (Modernized) --- */
        .welcome-header {
            background: linear-gradient(135deg, #3d7560 0%, #2d5a4a 100%);
            border-radius: 24px;
            padding: 50px 60px;
            color: white;
            margin-bottom: 40px;
            box-shadow: 0 20px 40px rgba(61, 117, 96, 0.2);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .welcome-header::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .welcome-text h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            letter-spacing: -0.5px;
        }

        .welcome-text p {
            opacity: 0.85;
            font-size: 18px;
            font-weight: 300;
        }

        .role-badge {
            background: rgba(255, 255, 255, 0.15);
            padding: 12px 28px;
            border-radius: 16px;
            font-size: 15px;
            font-weight: 500;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        /* --- Stats Grid --- */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 45px;
        }

        .stat-card {
            background: var(--white);
            padding: 30px;
            border-radius: 20px;
            box-shadow: var(--shadow);
            display: flex;
            align-items: center;
            gap: 25px;
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.03);
        }

        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.08);
            border-color: var(--primary-green);
        }

        .stat-icon {
            width: 65px;
            height: 65px;
            background: var(--accent-green);
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            color: var(--primary-green);
            flex-shrink: 0;
        }

        .stat-info h3 {
            font-size: 15px;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 4px;
        }

        .stat-info .number {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-green);
        }

        /* --- Section Titles --- */
        .section-title {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--text-dark);
        }

        .section-title span {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        /* --- Task/Action Lists --- */
        .task-list, .quick-actions {
            display: grid;
            gap: 20px;
        }

        .task-item {
            background: var(--white);
            padding: 25px 35px;
            border-radius: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 6px solid #e0e0e0;
            box-shadow: var(--shadow);
            transition: var(--transition);
        }

        .task-item:hover {
            transform: scale(1.01);
            background: #fafafa;
        }

        .task-item.urgent { border-left-color: #ff5e57; }

        .task-main h4 { font-size: 18px; font-weight: 600; margin-bottom: 6px; color: var(--text-dark); }
        .task-sub { font-size: 14px; color: var(--text-muted); }

        .btn-action {
            background: var(--primary-green);
            color: white;
            padding: 12px 30px;
            border-radius: 12px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 4px 15px rgba(61, 117, 96, 0.2);
        }

        .btn-action:hover { 
            background: var(--secondary-green); 
            box-shadow: 0 6px 20px rgba(61, 117, 96, 0.3);
        }

        /* --- Quick Actions Grid --- */
        .quick-actions {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        }

        .action-link {
            background: var(--white);
            text-align: center;
            padding: 40px 30px;
            border-radius: 20px;
            text-decoration: none;
            color: var(--text-dark);
            transition: var(--transition);
            border: 1px solid rgba(0,0,0,0.03);
            box-shadow: var(--shadow);
        }

        .action-link:hover {
            border-color: var(--primary-green);
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        }

        .action-link .icon { 
            font-size: 40px; 
            margin-bottom: 20px; 
            display: block;
            background: var(--accent-green);
            width: 80px;
            height: 80px;
            line-height: 80px;
            border-radius: 50%;
            margin-left: auto;
            margin-right: auto;
        }

        .action-link strong { font-size: 18px; display: block; margin-bottom: 8px; }

        /* --- Responsive --- */
        @media (max-width: 992px) {
            .container { padding: 20px; }
            .welcome-header { padding: 40px; }
        }

        @media (max-width: 768px) {
            .welcome-header { flex-direction: column; text-align: center; gap: 25px; padding: 40px 20px; }
            .task-item { flex-direction: column; align-items: flex-start; gap: 20px; }
            .btn-action { width: 100%; text-align: center; }
            .welcome-text h1 { font-size: 26px; }
        }
    </style>
</head>
<body>

<?php include 'navbar.php'; ?>

<div class="container">
    <header class="welcome-header">
        <div class="welcome-text">
            <h1>สวัสดีคุณ, <?php echo htmlspecialchars($name); ?></h1>
            <p>ยินดีต้อนรับสู่ระบบจัดการการศึกษาของคุณในวันนี้</p>
        </div>
        <div class="role-badge">
            <?php 
                $role_text = ($role == 'teacher') ? 'อาจารย์ผู้สอน' : ($role == 'admin' ? 'ผู้ดูแลระบบ' : 'นักเรียน');
                $role_icon = ($role == 'teacher') ? '👨‍🏫' : '👨‍🎓';
                echo "<span>$role_icon</span>" . $role_text;
            ?>
        </div>
    </header>

    <?php if ($role == 'student'): ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <h3>งานที่ได้รับมอบหมาย</h3>
                    <div class="number">
                        <?php
                        $total_assignments = mysqli_query($conn, "SELECT COUNT(*) as count FROM assignments");
                        echo mysqli_fetch_assoc($total_assignments)['count'];
                        ?>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">✅</div>
                <div class="stat-info">
                    <h3>ส่งงานแล้ว</h3>
                    <div class="number">
                        <?php
                        $submitted = mysqli_query($conn, "SELECT COUNT(*) as count FROM submissions WHERE student_id = (SELECT id FROM students WHERE user_id = '$user_id')");
                        echo mysqli_fetch_assoc($submitted)['count'];
                        ?>
                    </div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">⏰</div>
                <div class="stat-info">
                    <h3>งานที่รอการส่ง</h3>
                    <div class="number">
                        <?php
                        $pending_sql = "SELECT COUNT(*) as count FROM assignments a WHERE a.id NOT IN (SELECT assignment_id FROM submissions WHERE student_id = (SELECT id FROM students WHERE user_id = '$user_id'))";
                        $pending = mysqli_query($conn, $pending_sql);
                        echo mysqli_fetch_assoc($pending)['count'];
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <h2 class="section-title"><span>📝</span> รายการงานที่ต้องทำ</h2>
        <div class="task-list">
            <?php
            $sql = "SELECT a.*, s.name as subject_name FROM assignments a JOIN subjects s ON a.subject_id = s.id
                    WHERE a.id NOT IN (SELECT assignment_id FROM submissions WHERE student_id = (SELECT id FROM students WHERE user_id = '$user_id'))
                    ORDER BY a.due_date ASC LIMIT 5";
            $res = mysqli_query($conn, $sql);

            if (mysqli_num_rows($res) > 0) {
                while($row = mysqli_fetch_assoc($res)) {
                    $due = new DateTime($row['due_date']);
                    $today = new DateTime();
                    $diff = $today->diff($due)->days;
                    $is_urgent = ($diff <= 3 && $due > $today) ? 'urgent' : '';
                    
                    echo "<div class='task-item $is_urgent'>";
                    echo "  <div class='task-main'>";
                    echo "    <h4>" . htmlspecialchars($row['title']) . "</h4>";
                    echo "    <div class='task-sub'>วิชา: " . htmlspecialchars($row['subject_name']) . " | กำหนดส่ง: " . $due->format('d M Y') . "</div>";
                    echo "  </div>";
                    echo "  <a href='submit_work.php?id={$row['id']}' class='btn-action'>ส่งงานตอนนี้</a>";
                    echo "</div>";
                }
            } else {
                echo "<div style='text-align:center; padding:60px; background:white; border-radius:20px; box-shadow: var(--shadow);'>
                        <div style='font-size: 50px; margin-bottom: 15px;'>🎉</div>
                        <h3 style='color: var(--primary-green)'>ไม่มีงานค้างในขณะนี้</h3>
                        <p style='color: var(--text-muted)'>คุณจัดการงานทุกอย่างเรียบร้อยแล้ว ยอดเยี่ยมมาก!</p>
                      </div>";
            }
            ?>
        </div>

    <?php else: ?>
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <h3>จำนวนนักเรียน</h3>
                    <div class="number"><?php echo mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM students"))['count']; ?> คน</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <h3>งานที่มอบหมาย</h3>
                    <div class="number"><?php echo mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM assignments"))['count']; ?> งาน</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-info">
                    <h3>งานที่รอการตรวจ</h3>
                    <div class="number"><?php echo mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM submissions"))['count']; ?> รายการ</div>
                </div>
            </div>
        </div>

        <h2 class="section-title"><span>⚡</span> จัดการระบบอย่างรวดเร็ว</h2>
        <div class="quick-actions">
            <a href="create_assignment.php" class="action-link">
                <span class="icon">➕</span>
                <strong>สร้างงานใหม่</strong>
                <p style="font-size:14px; color:var(--text-muted);">มอบหมายการบ้านหรือบททดสอบใหม่</p>
            </a>
            <a href="check_submissions.php" class="action-link">
                <span class="icon">🔎</span>
                <strong>ตรวจผลงาน</strong>
                <p style="font-size:14px; color:var(--text-muted);">ดูรายการส่งงานและให้คะแนนนักเรียน</p>
            </a>
            <!-- <a href="report_grades.php" class="action-link">
                <span class="icon">📈</span>
                <strong>รายงานสรุป</strong>
                <p style="font-size:14px; color:var(--text-muted);">ดูภาพรวมผลการเรียนทั้งหมด</p>
            </a> -->
        </div>
    <?php endif; ?>
</div>

</body>
</html>