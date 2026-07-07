<?php
session_start();
include 'db.php';
include 'lang.php';
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'calon') { header("Location: index.php"); exit(); }
$id = $_SESSION['id'];
$msg = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama']; $pass = $_POST['password'];
    $conn->query("UPDATE calon SET nama_Calon='$nama', kata_laluan='$pass' WHERE id_Calon='$id'");
    $msg = "<script>alert('" . addslashes($t['update_success']) . "');</script>";
}
$user = $conn->query("SELECT * FROM calon WHERE id_Calon='$id'")->fetch_assoc();
$votes = $conn->query("SELECT COUNT(*) as total FROM undian WHERE id_Calon='$id'")->fetch_assoc();
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
    </script><title>Dashboard Calon</title><link rel="stylesheet" href="style.css?v=<?php echo time(); ?>"></head>
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
            <a href="calon_dashboard.php" class="active"><?php echo $t['nav_profile']; ?></a>
            <a href="results.php"><?php echo $t['nav_results']; ?></a>
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
    <div class="main-content" style="position: relative; z-index: 10;">
        <?php if(isset($_SESSION['login_success']) && $_SESSION['login_success']): ?>
            <?php unset($_SESSION['login_success']); ?>
            <script>alert('<?php echo ($lang == "ms") ? "Berjaya log masuk, " : "Successfully logged in, "; ?><?php echo addslashes($user["nama_Calon"]); ?>!');</script>
        <?php endif; ?>
        <div class="card">
            <h2><?php echo $t['candidate_profile']; ?>: <?php echo $user['nama_Calon']; ?></h2>
            <div style="background: rgba(255, 255, 255, 0.15); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); padding: 30px; border-radius: 12px; text-align: center; margin-bottom: 25px; border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);">
                <h3 style="margin:0; color: #FFD700; text-shadow: 1px 1px 3px rgba(0,0,0,0.3);"><?php echo $t['current_votes']; ?></h3>
                <h1 style="color:white; font-size: calc(48px * var(--font-scale, 1)); margin:10px 0; text-shadow: 0 5px 15px rgba(0,0,0,0.3);"><?php echo $votes['total']; ?></h1>
            </div>
            <?php echo $msg; ?>
            <form method="POST">
                <label><?php echo $t['id_user']; ?></label><input type="text" value="<?php echo $user['id_Calon']; ?>" disabled>
                <label><?php echo $t['name']; ?></label><input type="text" name="nama" value="<?php echo $user['nama_Calon']; ?>" required>
                <label><?php echo $t['password']; ?></label><input type="password" name="password" value="<?php echo isset($user['kata_laluan']) ? $user['kata_laluan'] : (isset($user['password']) ? $user['password'] : ''); ?>" required>
                <button type="submit" class="btn"><?php echo $t['save_changes']; ?></button>
            </form>
        </div>
    </div>
    <footer>&copy; 2026 Briged Putera</footer>
</body>
</html>