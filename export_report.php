<?php
include 'config.php';
session_start();

// ตรวจสอบการ Login
if (!isset($_SESSION['user_id'])) { 
    header("Location: Page/login.php"); 
    exit(); 
}

// --- ส่วนของการ Export เป็นไฟล์ CSV ---
if (isset($_GET['export']) && $_GET['export'] == 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=report_grades_'.date('Y-m-d').'.csv');
    $output = fopen('php://output', 'w');
    
    // บรรทัดสำคัญ: ใส่ BOM เพื่อให้ Excel อ่านภาษาไทยออก (UTF-8)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); 
    
    // หัวตารางในไฟล์ Excel
    fputcsv($output, array('ชื่อนักเรียน', 'รายวิชา', 'คะแนน', 'สถานะ')); 

    $sql = "SELECT u.name as s_name, sub.name as sub_name, g.score 
            FROM grades g
            JOIN students s ON g.student_id = s.id
            JOIN users u ON s.user_id = u.id
            JOIN subjects sub ON g.subject_id = sub.id
            ORDER BY u.name, sub.name";
    $res = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($res)) {
        $status = ($row['score'] >= 50) ? 'ผ่าน' : 'ไม่ผ่าน';
        fputcsv($output, array($row['s_name'], $row['sub_name'], $row['score'], $status));
    }
    fclose($output);
    exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปผลการเรียน - ระบบจัดการการศึกษา</title>
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

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: white;
            padding: 25px 30px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            display: flex;
            align-items: center;
            gap: 20px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            border-color: #3d7560;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(61, 117, 96, 0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #b8e6d5 0%, #96dbc0 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            flex-shrink: 0;
        }

        .stat-info {
            flex: 1;
        }

        .stat-label {
            color: #7f8c8d;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .stat-value {
            color: #3d7560;
            font-size: 28px;
            font-weight: 700;
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
            flex-wrap: wrap;
            gap: 20px;
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

        /* Export Button */
        .btn-export {
            background: linear-gradient(135deg, #3d7560 0%, #4a8970 100%);
            color: white;
            padding: 12px 28px;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(61, 117, 96, 0.25);
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .btn-export:hover {
            background: linear-gradient(135deg, #2d5f4f 0%, #3d7560 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(61, 117, 96, 0.35);
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

        /* Score Badge */
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

        .score-high {
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            border-color: #28a745;
            color: #155724;
        }

        .score-low {
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            border-color: #dc3545;
            color: #721c24;
        }

        /* Status Badges */
        .status-pass {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
            color: #155724;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            border: 2px solid #28a745;
        }

        .status-pass::before {
            content: '✓';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 11px;
        }

        .status-fail {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
            color: #721c24;
            border-radius: 20px;
            font-weight: 600;
            font-size: 13px;
            border: 2px solid #dc3545;
        }

        .status-fail::before {
            content: '✕';
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            background: #dc3545;
            color: white;
            border-radius: 50%;
            font-weight: bold;
            font-size: 11px;
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

            .section-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .btn-export {
                width: 100%;
                justify-content: center;
            }

            .table-wrapper {
                overflow-x: auto;
            }

            .data-table {
                min-width: 600px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
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
                <div class="icon">📊</div>
                รายงานสรุปผลการเรียน
            </h1>
            <p class="page-subtitle">ดูรายงานคะแนนและผลการเรียนของนักเรียนทั้งหมด พร้อมส่งออกเป็นไฟล์ Excel</p>
        </div>

        <!-- Stats Cards -->
        <?php
        // นับสถิติ
        $total_records = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM grades"));
        $total_students = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT student_id FROM grades"));
        $total_subjects = mysqli_num_rows(mysqli_query($conn, "SELECT DISTINCT subject_id FROM grades"));
        
        // คำนวณคะแนนเฉลี่ย
        $avg_query = mysqli_query($conn, "SELECT AVG(score) as avg_score FROM grades");
        $avg_row = mysqli_fetch_assoc($avg_query);
        $avg_score = $avg_row['avg_score'] ? number_format($avg_row['avg_score'], 2) : '0.00';
        ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📝</div>
                <div class="stat-info">
                    <div class="stat-label">จำนวนบันทึกทั้งหมด</div>
                    <div class="stat-value"><?php echo number_format($total_records); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-info">
                    <div class="stat-label">จำนวนนักเรียน</div>
                    <div class="stat-value"><?php echo number_format($total_students); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📚</div>
                <div class="stat-info">
                    <div class="stat-label">จำนวนรายวิชา</div>
                    <div class="stat-value"><?php echo number_format($total_subjects); ?></div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">📈</div>
                <div class="stat-info">
                    <div class="stat-label">คะแนนเฉลี่ย</div>
                    <div class="stat-value"><?php echo $avg_score; ?></div>
                </div>
            </div>
        </div>

        <!-- Report Table -->
        <div class="main-card">
            <div class="section-header">
                <h2 class="section-title">รายละเอียดผลการเรียนทั้งหมด</h2>
                <a href="?export=csv" class="btn-export">
                    <span>📥</span>
                    <span>ดาวน์โหลดไฟล์ Excel (CSV)</span>
                </a>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ชื่อนักเรียน</th>
                            <th>รายวิชา</th>
                            <th style="text-align: center;">คะแนน</th>
                            <th style="text-align: center;">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT u.name as s_name, sub.name as sub_name, g.score 
                                FROM grades g
                                JOIN students s ON g.student_id = s.id
                                JOIN users u ON s.user_id = u.id
                                JOIN subjects sub ON g.subject_id = sub.id
                                ORDER BY u.name, sub.name";
                        $result = mysqli_query($conn, $sql);
                        
                        if (mysqli_num_rows($result) > 0) {
                            $no = 1;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $score = $row['score'];
                                $status_class = ($score >= 50) ? 'status-pass' : 'status-fail';
                                $status_text = ($score >= 50) ? 'ผ่าน' : 'ไม่ผ่าน';
                                
                                // เพิ่ม class สำหรับคะแนน
                                $score_class = 'score-badge';
                                if ($score >= 80) {
                                    $score_class .= ' score-high';
                                } elseif ($score < 50) {
                                    $score_class .= ' score-low';
                                }
                                
                                echo "<tr>
                                        <td><strong>{$no}</strong></td>
                                        <td><strong>".htmlspecialchars($row['s_name'])."</strong></td>
                                        <td>".htmlspecialchars($row['sub_name'])."</td>
                                        <td style='text-align: center;'><span class='{$score_class}'>{$score}</span></td>
                                        <td style='text-align: center;'><span class='{$status_class}'>{$status_text}</span></td>
                                      </tr>";
                                $no++;
                            }
                        } else {
                            echo "<tr><td colspan='5'>
                                    <div class='empty-state'>
                                        <div class='icon'>📋</div>
                                        <h3>ยังไม่มีข้อมูลผลการเรียน</h3>
                                        <p>ขณะนี้ยังไม่มีข้อมูลคะแนนในระบบ</p>
                                    </div>
                                  </td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</body>
</html>