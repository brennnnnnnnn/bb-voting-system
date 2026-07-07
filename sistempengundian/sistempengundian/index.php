<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'db.php';
include 'lang.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST['id'];
    $pass = $_POST['password'];

    if ($id === 'admin' && $pass === 'admin123') {
        $_SESSION['role'] = 'admin'; $_SESSION['login_success'] = true; header("Location: admin_dashboard.php"); exit();
    }

    if (isset($conn)) {
        $calon = $conn->query("SELECT * FROM calon WHERE BINARY id_Calon='$id' AND BINARY kata_laluan='$pass'");
        if ($calon && $calon->num_rows > 0) {
            $_SESSION['role'] = 'calon'; $_SESSION['id'] = $id; $_SESSION['login_success'] = true; header("Location: calon_dashboard.php"); exit();
        }

        $user = $conn->query("SELECT * FROM pengguna WHERE BINARY id_Pengguna='$id' AND BINARY password_Pengguna='$pass'");
        if ($user && $user->num_rows > 0) {
            $_SESSION['role'] = 'pengguna'; $_SESSION['id'] = $id; $_SESSION['login_success'] = true; header("Location: vote.php"); exit();
        }
    }
    $error = $t['error_invalid'];
}
?>

<!DOCTYPE html>
<html lang="ms">
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
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk - Briged Putera</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding-top: 80px; /* Space for header */
        }

         /* Top Header styles moved to style.css */

         /* Removed redundant gradient keyframes - now in style.css */

         /* Removed redundant bouncing logo styles - now in style.css */

        /* Main Container */
        .login-container {
            position: relative;
            z-index: 10;
            display: flex;
            width: 900px;
            min-height: 550px;
            background: rgba(255, 255, 255, 0.95);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
        }

        /* Left Panel - Gradient with Logo */
        .left-panel {
            width: 45%;
            background: linear-gradient(160deg, #8B0000 0%, #CD853F 50%, #DAA520 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 50px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,215,0,0.2) 0%, transparent 60%);
            animation: pulse 8s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.1); opacity: 0.5; }
        }

        .left-panel img {
            width: 160px;
            height: auto;
            margin-bottom: 25px;
            filter: drop-shadow(0 8px 20px rgba(0,0,0,0.3));
            position: relative;
            z-index: 1;
        }

        .left-panel h2 {
            font-size: calc(28px * var(--font-scale, 1));
            font-weight: 700;
            color: white;
            margin-bottom: 15px;
            text-shadow: 2px 2px 10px rgba(0,0,0,0.3);
            position: relative;
            z-index: 1;
        }

        .left-panel p {
            font-size: calc(14px * var(--font-scale, 1));
            color: rgba(255,255,255,0.9);
            line-height: 1.7;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
        }

        .btn-outline {
            padding: 12px 40px;
            border: 2px solid white;
            background: transparent;
            color: white;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 600;
            border-radius: 25px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            position: relative;
            z-index: 1;
        }

        .btn-outline:hover {
            background: white;
            color: #8B0000;
        }

        /* Right Panel - Form */
        .right-panel {
            width: 55%;
            padding: 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .right-panel h1 {
            font-size: calc(32px * var(--font-scale, 1));
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .right-panel .subtitle {
            color: #777;
            font-size: calc(14px * var(--font-scale, 1));
            margin-bottom: 30px;
        }

        .input-group {
            margin-bottom: 22px;
        }

        .input-group label {
            display: block;
            font-size: calc(13px * var(--font-scale, 1));
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-group input {
            width: 100%;
            padding: 15px 18px;
            border: 2px solid #e8e8e8;
            border-radius: 12px;
            font-size: calc(15px * var(--font-scale, 1));
            outline: none;
            transition: all 0.3s;
            background: #fafafa;
            color: #333;
        }

        .input-group input:focus {
            border-color: #CD5C5C;
            background: white;
            box-shadow: 0 0 0 4px rgba(205, 92, 92, 0.1);
        }

        .input-group input::placeholder {
            color: #bbb;
        }


        .options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: calc(13px * var(--font-scale, 1));
        }

        .options-row a {
            color: #CD5C5C;
            text-decoration: none;
            font-weight: 500;
        }

        .options-row a:hover {
            text-decoration: underline;
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #8B0000, #CD5C5C);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: calc(16px * var(--font-scale, 1));
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(139, 0, 0, 0.4);
        }

        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #777;
            font-size: calc(14px * var(--font-scale, 1));
        }

        .register-link a {
            color: #8B0000;
            font-weight: 600;
            text-decoration: none;
        }

        .register-link a:hover {
            text-decoration: underline;
        }

        .error-msg {
            background: #ffe0e0;
            color: #c00;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: calc(14px * var(--font-scale, 1));
            border-left: 4px solid #c00;
        }

        .info-badge {
            background: linear-gradient(135deg, #FFF8DC, #FFEFD5);
            border-left: 4px solid #DAA520;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-size: calc(12px * var(--font-scale, 1));
            color: #8B4513;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                width: 95%;
                max-width: 420px;
            }

            .left-panel {
                width: 100%;
                padding: 40px 30px;
            }

            .right-panel {
                width: 100%;
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body class="theme-bg">
    <!-- Top Header Bar -->
    <div class="top-header">
        <div class="header-center">
            <img src="logo.png" alt="BB Logo" onerror="this.style.display='none'">
            <div class="site-title"><?php echo $t['site_title']; ?> <span><?php echo $t['site_subtitle']; ?></span></div>
        </div>
        <div style="width: 150px;"></div> <!-- Spacer -->
        <div class="lang-switcher">
            <div class="font-switcher no-print" style="display:flex; gap:5px; margin-right:15px; align-items:center;">
                <button onclick="changeFontSize(-1)" 
                        class="lang-btn" 
                        style="background:transparent; 
                               padding:4px 10px; 
                               font-size: calc(12px * var(--font-scale, 1)); 
                               cursor:pointer;" 
                        title="Decrease Font Size">A-</button>
                <button onclick="changeFontSize(0)" 
                        class="lang-btn" 
                        style="background:transparent; 
                               padding:4px 10px; 
                               font-size: calc(14px * var(--font-scale, 1)); 
                               cursor:pointer;" 
                        title="Reset Font Size">A</button>
                <button onclick="changeFontSize(1)" 
                        class="lang-btn" 
                        style="background:transparent; 
                               padding:4px 10px; 
                               font-size: calc(16px * var(--font-scale, 1)); 
                               cursor:pointer;" 
                        title="Increase Font Size">A+</button>
            </div>
            <a href="<?php echo get_lang_url('ms'); ?>" class="lang-btn <?php echo $lang == 'ms' ? 'active' : ''; ?>">BM</a>
            <a href="<?php echo get_lang_url('en'); ?>" class="lang-btn <?php echo $lang == 'en' ? 'active' : ''; ?>">EN</a>
        </div>
    </div>

    <!-- Bouncing BB Logos -->
    <div class="floating-logo logo-large"></div>
    <div class="floating-logo logo-small"></div>

    <div class="login-container">
        <!-- Left Panel -->
        <div class="left-panel">
            <img src="logo.png" alt="BB Logo" onerror="this.style.display='none'">
            <h2><?php echo $t['welcome']; ?></h2>
            <p><?php echo $t['system_desc']; ?></p>
            <a href="register.php" class="btn-outline"><?php echo $t['register']; ?></a>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <h1><?php echo $t['login']; ?></h1>
            <p class="subtitle"><?php echo $t['login_subtitle']; ?></p>



            <!-- Toast Popup -->
            <div id="toast-popup" 
                 style="display:none; 
                        background:linear-gradient(135deg,#ff4444,#cc0000); 
                        color:#fff; 
                        padding:14px 20px; 
                        border-radius:12px; 
                        margin-bottom:20px; 
                        font-size: calc(14px * var(--font-scale, 1)); 
                        font-weight:500; 
                        text-align:center; 
                        box-shadow:0 4px 15px rgba(204,0,0,0.3); 
                        animation:slideDown 0.3s ease;">
                <span id="toast-msg"></span>
            </div>

            <style>
                @keyframes slideDown {
                    from { opacity:0; transform:translateY(-15px); }
                    to { opacity:1; transform:translateY(0); }
                }
            </style>

            <?php if(isset($error)): ?>
                <script>
                    alert('<?php echo addslashes($error); ?>');
                </script>
            <?php endif; ?>

            <?php if(isset($_GET['logout']) && $_GET['logout'] == '1' && !isset($error)): ?>
                <script>
                    alert('<?php echo ($lang == "ms") ? "Anda telah berjaya log keluar!" : "You have successfully logged out!"; ?>');
                </script>
            <?php endif; ?>

            <form method="POST" id="loginForm" novalidate>
                <div class="input-group" style="margin-top: 25px;">
                    <label><?php echo $t['id_user']; ?></label>
                    <input type="text" name="id" id="field_id" placeholder="<?php echo $t['id_placeholder']; ?>">
                </div>
                
                <div class="input-group">
                    <label><?php echo $t['password']; ?></label>
                    <input type="password" name="password" id="field_password" placeholder="<?php echo $t['pass_placeholder']; ?>">
                </div>

                <div class="options-row" style="justify-content: flex-end;">
                    <a href="forgot_password.php"><?php echo $t['forgot_password']; ?></a>
                </div>

                <button type="submit" class="btn-submit"><?php echo $t['login']; ?></button>
            </form>

            <div class="register-link">
                <?php echo $t['no_account']; ?> <a href="register.php"><?php echo $t['register_now']; ?></a>
            </div>
        </div>
    </div>

    <script>
    function showToast(msg) {
        var toast = document.getElementById('toast-popup');
        document.getElementById('toast-msg').textContent = '⚠️ ' + msg;
        toast.style.display = 'block';
        toast.style.animation = 'none';
        toast.offsetHeight;
        toast.style.animation = 'slideDown 0.3s ease';
        setTimeout(function() { toast.style.display = 'none'; }, 3000);
    }

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        var fields = [
            { id: 'field_id', label: '<?php echo addslashes($t["id_user"]); ?>' },
            { id: 'field_password', label: '<?php echo addslashes($t["password"]); ?>' }
        ];

        for (var i = 0; i < fields.length; i++) {
            var input = document.getElementById(fields[i].id);
            if (!input.value.trim()) {
                e.preventDefault();
                var msg = fields[i].label + ' <?php echo ($lang == "ms") ? "mesti diisi!" : "must be filled in!"; ?>';
                alert(msg);
                input.focus();
                return;
            }
        }
    });
    </script>
</body>
</html>