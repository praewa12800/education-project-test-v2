<?php
include 'config.php';
session_start();

// ตรวจสอบสิทธิ์: เฉพาะครู
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'teacher') { 
    die("หน้านี้สำหรับครูเท่านั้น <a href='admin_menu.php'>กลับหน้าหลัก</a>"); 
}

if (isset($_POST['create'])) {
    $subject_id = $_POST['subject_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $type = $_POST['type'];
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = $_POST['due_date'];
    
    $attachment = "";
    if (!empty($_FILES["attachment"]["name"])) {
        $target_dir = "uploads/questions/";
        if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
        $attachment = $target_dir . time() . "_" . basename($_FILES["attachment"]["name"]);
        move_uploaded_file($_FILES["attachment"]["tmp_name"], $attachment);
    }

    $sql = "INSERT INTO assignments (subject_id, title, type, description, attachment_link, due_date) 
            VALUES ('$subject_id', '$title', '$type', '$description', '$attachment', '$due_date')";
    
    if (mysqli_query($conn, $sql)) {
        echo "<script>alert('ประกาศสำเร็จ!'); window.location='admin_menu.php';</script>";
    }
}
$subjects = mysqli_query($conn, "SELECT * FROM subjects");
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งการบ้าน/ข้อสอบ - ระบบจัดการการศึกษา</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;600&family=Prompt:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-green: #3d7560;
            --secondary-green: #4a8970;
            --accent-green: #eef7f4;
            --white: #ffffff;
            --text-dark: #2c3e50;
            --text-gray: #7f8c8d;
            --border-color: #e0e6ed;
            --shadow: 0 10px 25px rgba(0,0,0,0.05);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body { 
            font-family: 'Prompt', 'Sarabun', sans-serif; 
            background-color: #f8fafb; 
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container { 
            max-width: 1400px; /* ตามความต้องการ */
            margin: 0 auto; 
            padding: 40px 30px; 
        }

        /* --- Header Section --- */
        .header-banner {
            background: linear-gradient(135deg, var(--primary-green) 0%, var(--secondary-green) 100%);
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 25px;
            box-shadow: 0 15px 30px rgba(61, 117, 96, 0.15);
        }

        .header-banner .icon-box {
            background: rgba(255, 255, 255, 0.2);
            width: 70px;
            height: 70px;
            border-radius: 18px;
            font-size: 32px;
            display: flex; align-items: center; justify-content: center;
            backdrop-filter: blur(5px);
        }

        .header-banner h1 { margin: 0; font-size: 26px; font-weight: 600; }
        .header-banner p { margin: 5px 0 0; font-size: 16px; opacity: 0.9; font-weight: 300; }

        /* --- Info Card --- */
        .info-card {
            background: var(--accent-green);
            padding: 18px 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
            border-left: 6px solid var(--primary-green);
            color: var(--primary-green);
        }
        .info-card b { font-weight: 600; }

        /* --- Main Form Card --- */
        .form-card {
            background: var(--white);
            border-radius: 20px;
            padding: 45px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(0,0,0,0.02);
        }

        .form-card h2 {
            font-size: 20px;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f4f6f8;
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--primary-green);
        }

        /* Grid Layout */
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 25px;
        }

        .full-width { grid-column: span 2; }

        .form-group { margin-bottom: 5px; }
        .form-group label {
            display: block;
            font-size: 15px;
            font-weight: 500;
            margin-bottom: 10px;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 12px 18px;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-family: 'Sarabun', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #fcfdfe;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-green);
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(61, 117, 96, 0.1);
        }

        /* --- Custom Radio Buttons --- */
        .type-selector {
            display: flex;
            gap: 15px;
        }
        .type-option {
            flex: 1;
            position: relative;
        }
        .type-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }
        .type-option label {
            display: block;
            text-align: center;
            padding: 12px;
            background: #f8f9fa;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
            color: var(--text-gray);
        }
        .type-option input:checked + label {
            background: var(--primary-green);
            color: white;
            border-color: var(--primary-green);
            box-shadow: 0 4px 12px rgba(61, 117, 96, 0.2);
        }

        /* --- Submit Button --- */
        .btn-submit {
            background: var(--primary-green);
            color: white;
            border: none;
            padding: 16px 30px;
            border-radius: 14px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(61, 117, 96, 0.2);
        }
        
        .btn-submit:hover {
            background: var(--secondary-green);
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(61, 117, 96, 0.3);
        }

        .btn-submit:active { transform: translateY(0); }

        /* Responsive */
        @media (max-width: 992px) {
            .form-grid { grid-template-columns: 1fr; }
            .full-width { grid-column: span 1; }
            .container { padding: 20px; }
            .form-card { padding: 30px; }
        }
    </style>
</head>
<body>

    <?php include 'navbar.php'; ?>

    <div class="container">
        
        <div class="header-banner">
            <div class="icon-box">📝</div>
            <div>
                <h1>สร้างภารกิจใหม่</h1>
                <p>จัดการการเรียนการสอนโดยการมอบหมายงานหรือจัดทำแบบทดสอบให้แก่นักเรียน</p>
            </div>
        </div>

        <div class="info-card">
            <span>💡 <b>คำแนะนำสำหรับอาจารย์:</b> ท่านสามารถแนบไฟล์โจทย์ (PDF/IMG) เพื่อให้นักเรียนศึกษาข้อมูลก่อนลงมือทำได้</span>
        </div>

        <div class="form-card">
            <h2><span style="font-size: 24px;">📋</span> กรอกข้อมูลรายละเอียดงาน</h2>

            <form method="post" enctype="multipart/form-data">
                <div class="form-grid">
                    
                    <div class="form-group">
                        <label>เลือกรายวิชาที่ต้องการ</label>
                        <select name="subject_id" class="form-control" required>
                            <option value="">-- คลิกเพื่อเลือกรายวิชา --</option>
                            <?php 
                            mysqli_data_seek($subjects, 0);
                            while($s = mysqli_fetch_assoc($subjects)): 
                            ?>
                                <option value="<?php echo $s['id']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>ประเภทของงาน</label>
                        <div class="type-selector">
                            <div class="type-option">
                                <input type="radio" name="type" value="homework" id="hw" checked>
                                <label for="hw">📝 การบ้าน</label>
                            </div>
                            <div class="type-option">
                                <input type="radio" name="type" value="exam" id="ex">
                                <label for="ex">🎯 ข้อสอบ</label>
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>หัวข้องาน / ชื่อการทดสอบ</label>
                        <input type="text" name="title" class="form-control" required placeholder="เช่น แบบฝึกหัดที่ 1 เรื่องโครงสร้างประโยค">
                    </div>

                    <div class="form-group full-width">
                        <label>รายละเอียด / คำชี้แจงเพิ่มเติม</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="ระบุสิ่งที่นักเรียนต้องปฏิบัติ หรือเกณฑ์การให้คะแนน..."></textarea>
                    </div>

                    <div class="form-group">
                        <label>ไฟล์โจทย์ประกอบ (ถ้ามี)</label>
                        <input type="file" name="attachment" class="form-control">
                        <small style="color: var(--text-gray); font-size: 12px; margin-top: 5px; display: block;">รองรับไฟล์ PDF, JPG, PNG ขนาดไม่เกิน 5MB</small>
                    </div>

                    <div class="form-group">
                        <label>กำหนดวันสิ้นสุดการส่งงาน</label>
                        <input type="date" name="due_date" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                    </div>

                </div>

                <button type="submit" name="create" class="btn-submit">
                    🚀 ประกาศและส่งข้อมูลไปยังนักเรียน
                </button>
            </form>
        </div>
    </div>

</body>
</html>