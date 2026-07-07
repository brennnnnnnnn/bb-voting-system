<?php
session_start();
include 'db.php';
include 'lang.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: index.php"); exit(); }

$action = isset($_GET['action']) ? $_GET['action'] : 'add';
if (isset($_POST['action'])) $action = $_POST['action'];

$id = isset($_GET['id']) ? $_GET['id'] : '';
if (isset($_POST['id'])) $id = $_POST['id'];

// Initialize variables
$data = ['id_Calon'=>'', 'nama_Calon'=>'', 'password'=>''];

// Fetch data if editing (only if not POST)
if ($action == 'edit' && $id != '' && $_SERVER["REQUEST_METHOD"] != "POST") {
    $result = $conn->query("SELECT * FROM calon WHERE id_Calon='$id'");
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
    }
}

// Handle Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $c_id = $conn->real_escape_string($_POST['id']);
    $c_nama = $conn->real_escape_string($_POST['nama']);
    $c_pass = $conn->real_escape_string($_POST['password']);

    if ($action == 'add') {
        $check = $conn->query("SELECT id_Calon FROM calon WHERE id_Calon='$c_id'");
        if ($check->num_rows > 0) {
            echo "<script>alert('" . $t['id_exists'] . "');</script>";
        } else {
            $sql = "INSERT INTO calon (id_Calon, nama_Calon, kata_laluan) VALUES ('$c_id', '$c_nama', '$c_pass')";
            if ($conn->query($sql)) {
                echo "<script>alert('" . $t['success_add_candidate'] . "'); window.location.href='admin_dashboard.php';</script>";
            } else {
                echo "<script>alert('Error Adding: " . $conn->error . "');</script>";
            }
        }
    } else {
        $sql = "UPDATE calon SET nama_Calon='$c_nama', kata_laluan='$c_pass' WHERE id_Calon='$c_id'";
        if ($conn->query($sql)) {
            echo "<script>alert('" . $t['success_update_candidate'] . "'); window.location.href='admin_dashboard.php';</script>";
        } else {
             echo "<script>alert('Error Updating: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <script>
    (function(){
        let currentScale = parseFloat(localStorage.getItem('fontSizeScale')) || 1.0;
        document.documentElement.style.setProperty("--font-scale", currentScale);
    })();
    function changeFontSize(direction) {
        let currentScale = parseFloat(localStorage.getItem('fontSizeScale')) || 1.0;
        if (direction === 0) currentScale = 1.0;
        else if (direction > 0) currentScale = Math.min(1.4, currentScale + 0.1);
        else currentScale = Math.max(0.7, currentScale - 0.1);
        document.documentElement.style.setProperty("--font-scale", currentScale);
        localStorage.setItem('fontSizeScale', currentScale);
    }
    </script>
    <title>Urus Calon</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body class="theme-bg">
    <!-- Bouncing BB Logos -->
    <div class="floating-logo logo-large"></div>
    <div class="floating-logo logo-small"></div>
    <div class="top-header">
        <div class="header-center">
            <img src="logo.png" alt="BB Logo" onerror="this.style.display='none'">
            <div class="site-title"><?php echo $t['site_title']; ?> <span><?php echo $t['site_subtitle']; ?></span></div>
        </div>
        
        <div class="nav-links">
            <a href="admin_dashboard.php"><?php echo $t['nav_back_dashboard']; ?></a>
            <a href="logout.php"><?php echo $t['nav_logout']; ?></a>
        </div>

        <div class="lang-switcher">
            <div class="font-switcher no-print" style="display:flex; gap:5px; margin-right:15px; align-items:center;">
                <button onclick="changeFontSize(-1)" class="lang-btn" style="background:transparent; padding:4px 10px; font-size: calc(12px * var(--font-scale, 1)); cursor:pointer;" title="Decrease Font Size">A-</button>
                <button onclick="changeFontSize(0)" class="lang-btn" style="background:transparent; padding:4px 10px; font-size: calc(14px * var(--font-scale, 1)); cursor:pointer;" title="Reset Font Size">A</button>
                <button onclick="changeFontSize(1)" class="lang-btn" style="background:transparent; padding:4px 10px; font-size: calc(16px * var(--font-scale, 1)); cursor:pointer;" title="Increase Font Size">A+</button>
            </div>
            <a href="<?php echo get_lang_url('ms'); ?>" class="lang-btn <?php echo $lang == 'ms' ? 'active' : ''; ?>">BM</a>
            <a href="<?php echo get_lang_url('en'); ?>" class="lang-btn <?php echo $lang == 'en' ? 'active' : ''; ?>">EN</a>
        </div>
    </div>

    <div class="main-content">
        <div class="card card-small">
            <h2 style="text-align:center;"><?php echo ($action == 'edit') ? $t['update_candidate'] : $t['add_candidate']; ?></h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <label><?php echo $t['id_user']; ?></label>
                <input type="text" name="id" value="<?php echo isset($data['id_Calon']) ? $data['id_Calon'] : ''; ?>" required 
                <?php if($action == 'edit') echo 'readonly onclick="alert(\'' . $t['id_readonly'] . '\')" style="cursor: not-allowed; opacity: 0.7;"'; ?>>

                <label><?php echo $t['name']; ?></label>
                <input type="text" name="nama" value="<?php echo $data['nama_Calon']; ?>" required>

                <label><?php echo $t['password']; ?></label>
                <input type="text" name="password" value="<?php echo isset($data['kata_laluan']) ? $data['kata_laluan'] : $data['password']; ?>" required>

                <button type="submit" class="btn"><?php echo $t['save']; ?></button>
            </form>

            <div style="text-align:center; margin-top:15px;">
                <a href="admin_dashboard.php" style="color:#c0392b; text-decoration:none;"><?php echo $t['cancel']; ?></a>
            </div>
        </div>
    </div>
    
    <footer>&copy; 2026 Briged Putera</footer>
</body>
</html>