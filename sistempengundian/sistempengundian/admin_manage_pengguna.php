<?php
session_start();
include 'db.php';
include 'lang.php';
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { header("Location: index.php"); exit(); }

// Debug Logger
function log_debug($msg) {
    file_put_contents('debug_form.txt', "[" . date('Y-m-d H:i:s') . "] " . $msg . "\n", FILE_APPEND);
}

log_debug("Page Loaded. Method: " . $_SERVER['REQUEST_METHOD'] . " Params: " . print_r($_REQUEST, true));

$action = isset($_GET['action']) ? $_GET['action'] : 'add';
if (isset($_POST['action'])) $action = $_POST['action']; // Persist action on POST

$id = isset($_GET['id']) ? $_GET['id'] : '';
if (isset($_POST['id'])) $id = $_POST['id'];

// Initialize variables to prevent errors
$data = ['id_Pengguna'=>'', 'nama_Pengguna'=>'', 'kelas_Pengguna'=>'', 'password'=>''];

// Fetch data if editing (only if not POST, to avoid overwriting input on error)
if ($action == 'edit' && $id != '' && $_SERVER["REQUEST_METHOD"] != "POST") {
    $result = $conn->query("SELECT * FROM pengguna WHERE id_Pengguna='$id'");
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        // Check both column names just in case
        if (isset($data['password_Pengguna'])) $data['password'] = $data['password_Pengguna'];
    }
}

// Handle Form Submit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $p_id = $conn->real_escape_string($_POST['id']);
    $p_nama = $conn->real_escape_string($_POST['nama']);
    $p_kelas = $conn->real_escape_string($_POST['kelas']);
    $p_pass = $conn->real_escape_string($_POST['password']);
    
    // Capture Old ID for updates
    $p_old_id = isset($_POST['old_id']) ? $conn->real_escape_string($_POST['old_id']) : $p_id;
    
    log_debug("Processing POST. Action: $action, ID: $p_id");

    if ($action == 'add') {
        // Strict ID validation only for NEW users
        if (!preg_match("/^\d{5}$/", $p_id)) {
            echo "<script>alert('" . addslashes($t['format_id_error']) . "');</script>";
            log_debug("Validation Error: Invalid ID format");
        } elseif (!preg_match("/^[A-Za-z\s]+$/", $p_nama)) {
            echo "<script>alert('" . addslashes($t['name_error']) . "');</script>";
            log_debug("Validation Error: Invalid Name format");
        } else {
            $check = $conn->query("SELECT id_Pengguna FROM pengguna WHERE id_Pengguna='$p_id'");
            if ($check->num_rows > 0) {
                log_debug("Error: Duplicate ID $p_id");
                echo "<script>alert('" . addslashes($t['id_exists']) . "');</script>";
            } else {
                $sql = "INSERT INTO pengguna (id_Pengguna, nama_Pengguna, kelas_Pengguna, password_Pengguna) VALUES ('$p_id', '$p_nama', '$p_kelas', '$p_pass')";
                log_debug("SQL Add: $sql");
                if ($conn->query($sql)) {
                    log_debug("Success Add");
                    echo "<script>alert('" . addslashes($t['success_add_user']) . "'); window.location.href='admin_dashboard.php';</script>";
                } else {
                    log_debug("Fail Add: " . $conn->error);
                    echo "<script>alert('Error Adding: " . addslashes($conn->error) . "');</script>";
                }
            }
        }
    } else {
        // Edit Mode
        // Check if ID changed and new ID already exists
        if ($p_id != $p_old_id) {
             $check = $conn->query("SELECT id_Pengguna FROM pengguna WHERE id_Pengguna='$p_id'");
             if ($check->num_rows > 0) {
                 echo "<script>alert('Error: New ID $p_id already exists!');</script>";
                 // Stop execution here to avoid broken state
                 $conn->close();
                 exit();
             }
             
             // Update Foreign Keys First (Manual Cascade)
             $conn->query("UPDATE undian SET id_Pengguna='$p_id' WHERE id_Pengguna='$p_old_id'");
        }
        
        $sql = "UPDATE pengguna SET id_Pengguna='$p_id', nama_Pengguna='$p_nama', kelas_Pengguna='$p_kelas', password_Pengguna='$p_pass' WHERE id_Pengguna='$p_old_id'";
        log_debug("SQL Edit: $sql");
        if ($conn->query($sql)) {
            log_debug("Success Edit");
            echo "<script>alert('" . addslashes($t['success_update_user']) . "'); window.location.href='admin_dashboard.php';</script>";
        } else {
            log_debug("Fail Edit: " . $conn->error);
            echo "<script>alert('Error Updating: " . addslashes($conn->error) . "');</script>";
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
    <title>Urus Pengguna</title>
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
            <h2 style="text-align:center;"><?php echo ($action == 'edit') ? $t['update_user'] : $t['add_user']; ?></h2>
            
            <form method="POST">
                <input type="hidden" name="action" value="<?php echo $action; ?>">
                <input type="hidden" name="old_id" value="<?php echo isset($data['id_Pengguna']) ? $data['id_Pengguna'] : ''; ?>">
                
                <label><?php echo $t['id_user']; ?></label>
                <input type="text" name="id" value="<?php echo isset($data['id_Pengguna']) ? $data['id_Pengguna'] : ''; ?>" title="<?php echo $t['id_hint']; ?>" required 
                <?php if($action == 'edit') echo 'readonly onclick="alert(\'' . addslashes($t['id_readonly']) . '\')" style="cursor: not-allowed; opacity: 0.7;"'; ?>>

                <label><?php echo $t['name']; ?></label>
                <input type="text" name="nama" value="<?php echo $data['nama_Pengguna']; ?>" pattern="[A-Za-z\s]+" title="<?php echo $t['name_hint']; ?>" required>

                <label><?php echo $t['class']; ?></label>
                <input type="text" name="kelas" value="<?php echo $data['kelas_Pengguna']; ?>" required>

                <label><?php echo $t['password']; ?></label>
                <input type="text" name="password" value="<?php echo isset($data['password_Pengguna']) ? $data['password_Pengguna'] : $data['password']; ?>" required>

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