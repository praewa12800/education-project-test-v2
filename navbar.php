<?php
// ตรวจสอบตำแหน่งไฟล์เพื่อกำหนด path ของลิงก์
$current_dir = basename(dirname($_SERVER['PHP_SELF']));
$path_prefix = ($current_dir == 'admin' || $current_dir == 'work' || $current_dir == 'grade' || $current_dir == 'Page') ? '../' : '';
?>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    
    .navbar {
        background: linear-gradient(135deg, #b8e6d5 0%, #a8dbc8 100%);
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 40px;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        position: relative;
    }
    
    .navbar-brand {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .navbar-logo {
        width: 80px;
        height: 50px;
        background-color: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: bold;
        color: #2d5f4f;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .navbar-title {
        display: flex;
        flex-direction: column;
    }
    
    .navbar-title h1 {
        font-size: 16px;
        color: #2d5f4f;
        font-weight: 600;
    }
    
    .navbar-title p {
        font-size: 13px;
        color: #5a8a7a;
        font-weight: 400;
    }
    
    /* Hamburger Menu Button */
    .menu-toggle {
        display: none;
        flex-direction: column;
        cursor: pointer;
        padding: 5px;
        z-index: 1001;
    }
    
    .menu-toggle span {
        width: 25px;
        height: 3px;
        background-color: #2d5f4f;
        margin: 3px 0;
        transition: all 0.3s ease;
        border-radius: 3px;
    }
    
    .menu-toggle.active span:nth-child(1) {
        transform: rotate(45deg) translate(8px, 8px);
    }
    
    .menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }
    
    .menu-toggle.active span:nth-child(3) {
        transform: rotate(-45deg) translate(7px, -7px);
    }
    
    .navbar-links {
        display: flex;
        gap: 5px;
        align-items: center;
        flex: 1;
        justify-content: flex-start;
        margin-left: 40px;
    }
    
    .navbar-links a {
        color: #2d5f4f;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s ease;
        white-space: nowrap;
    }
    
    .navbar-links a:hover {
        background-color: rgba(255, 255, 255, 0.5);
        color: #1a4033;
    }
    
    .navbar-links a.active {
        background-color: white;
        color: #2d5f4f;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .user-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .user-info {
        display: flex;
        align-items: center;
        gap: 10px;
        background-color: white;
        padding: 8px 15px;
        border-radius: 25px;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    
    .user-avatar {
        width: 35px;
        height: 35px;
        background-color: #2d5f4f;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
        font-size: 16px;
    }
    
    .user-details {
        display: flex;
        flex-direction: column;
        line-height: 1.3;
    }
    
    .user-name {
        font-size: 14px;
        font-weight: 600;
        color: #2d5f4f;
    }
    
    .user-role {
        font-size: 12px;
        color: #5a8a7a;
    }
    
    .logout-btn {
        background-color: #2d5f4f;
        color: white;
        text-decoration: none;
        padding: 10px 20px;
        border-radius: 6px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        white-space: nowrap;
    }
    
    .logout-btn:hover {
        background-color: #1a4033;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }
    
    /* Responsive Design */
    @media (max-width: 992px) {
        .navbar {
            padding: 15px 20px;
        }
        
        .menu-toggle {
            display: flex;
        }
        
        .navbar-links {
            position: fixed;
            top: 0;
            right: -100%;
            height: 100vh;
            width: 280px;
            background: linear-gradient(135deg, #b8e6d5 0%, #a8dbc8 100%);
            flex-direction: column;
            align-items: flex-start;
            padding: 80px 20px 20px 20px;
            margin: 0;
            box-shadow: -2px 0 10px rgba(0, 0, 0, 0.1);
            transition: right 0.3s ease;
            z-index: 1000;
            overflow-y: auto;
        }
        
        .navbar-links.active {
            right: 0;
        }
        
        .navbar-links a {
            width: 100%;
            padding: 12px 15px;
            margin-bottom: 5px;
        }
        
        .user-section {
            flex-direction: column;
            gap: 10px;
        }
        
        .user-info {
            order: 2;
        }
        
        .logout-btn {
            order: 1;
        }
    }
    
    @media (max-width: 768px) {
        .navbar {
            padding: 12px 15px;
        }
        
        .navbar-logo {
            width: 60px;
            height: 40px;
        }
        
        .navbar-title h1 {
            font-size: 14px;
        }
        
        .navbar-title p {
            font-size: 11px;
        }
        
        .user-details {
            display: none;
        }
        
        .user-info {
            padding: 5px;
        }
        
        .logout-btn {
            padding: 8px 15px;
            font-size: 13px;
        }
    }
    
    @media (max-width: 480px) {
        .navbar-brand {
            gap: 10px;
        }
        
        .navbar-title h1 {
            font-size: 12px;
        }
        
        .navbar-title p {
            display: none;
        }
    }
    
    /* Overlay สำหรับปิดเมนูเมื่อคลิกข้างนอก */
    .menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
    }
    
    .menu-overlay.active {
        display: block;
    }
</style>

<div class="navbar">
    <div class="navbar-brand">
        <div class="navbar-logo">
            <img src="<?php echo $path_prefix; ?>expensive-house.png" alt="Logo" style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <div class="navbar-title">
            <h1>ระบบจัดการการเรียนการสอน</h1>
            <p>Education Management</p>
        </div>
    </div>
    
    <div class="menu-toggle" id="menuToggle">
        <span></span>
        <span></span>
        <span></span>
    </div>
    
    <div class="navbar-links" id="navbarLinks">
        <a href="<?php echo $path_prefix; ?>admin_menu.php">หน้าหลัก/Dashboard</a>
        <a href="<?php echo $path_prefix; ?>admin_subjects.php">รายวิชา/หลักสูตร</a>

        <?php if ($_SESSION['role'] == 'teacher'): ?>
            <a href="<?php echo $path_prefix; ?>create_assignment.php">สั่งการบ้าน/ข้อสอบ</a>
            <a href="<?php echo $path_prefix; ?>check_submissions.php">ตรวจงานนักเรียน</a>
            <a href="<?php echo $path_prefix; ?>enter_grade.php">บันทึกเกรด</a>
        <?php else: ?>
            <a href="<?php echo $path_prefix; ?>submit_work.php">ส่งการบ้าน/ข้อสอบ</a>
            <a href="<?php echo $path_prefix; ?>enter_grade.php">ผลการเรียน</a>
        <?php endif; ?>

        <a href="<?php echo $path_prefix; ?>export_report.php">รายงาน (Export)</a>
    </div>

    <div class="user-section">
        <div class="user-info">
            <div class="user-avatar">
                <?php 
                    // แสดงตัวอักษรแรกของชื่อ
                    echo mb_substr($_SESSION['name'], 0, 1, 'UTF-8'); 
                ?>
            </div>
            <div class="user-details">
                <span class="user-name"><?php echo $_SESSION['name']; ?></span>
                <span class="user-role"><?php echo $_SESSION['role'] == 'teacher' ? 'ครู' : 'นักเรียน'; ?></span>
            </div>
        </div>
        <a href="<?php echo $path_prefix; ?>logout.php" class="logout-btn">ออกจากระบบ</a>
    </div>
</div>

<div class="menu-overlay" id="menuOverlay"></div>

<script>
    const menuToggle = document.getElementById('menuToggle');
    const navbarLinks = document.getElementById('navbarLinks');
    const menuOverlay = document.getElementById('menuOverlay');
    
    // Toggle menu เมื่อคลิกปุ่ม hamburger
    menuToggle.addEventListener('click', function() {
        menuToggle.classList.toggle('active');
        navbarLinks.classList.toggle('active');
        menuOverlay.classList.toggle('active');
    });
    
    // ปิด menu เมื่อคลิก overlay
    menuOverlay.addEventListener('click', function() {
        menuToggle.classList.remove('active');
        navbarLinks.classList.remove('active');
        menuOverlay.classList.remove('active');
    });
    
    // ปิด menu เมื่อคลิกลิงก์
    const menuLinks = navbarLinks.querySelectorAll('a');
    menuLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                menuToggle.classList.remove('active');
                navbarLinks.classList.remove('active');
                menuOverlay.classList.remove('active');
            }
        });
    });
</script>