<?php
session_start();
include 'db.php';
include 'lang.php';
if (!isset($_SESSION['id']) || $_SESSION['role'] != 'pengguna') { header("Location: index.php"); exit(); }

$id_pengguna = $_SESSION['id'];
$msg = "";

// FETCH USER NAME FOR WELCOME MESSAGE
$user_sql = "SELECT nama_Pengguna FROM pengguna WHERE id_Pengguna = '$id_pengguna'";
$user_result = $conn->query($user_sql);
$user_data = $user_result->fetch_assoc();
$nama_penuh = $user_data['nama_Pengguna'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle batch votes from standard form submission
    $count = 0;
    $log = "Timestamp: " . date("Y-m-d H:i:s") . "\n";
    $log .= "User: $id_pengguna\n";
    $log .= "POST Data: " . print_r($_POST, true) . "\n";
    
    foreach ($_POST as $key => $val) {
        if (strpos($key, 'jawatan_') === 0) {
            $id_jawatan = substr($key, 8); // remove 'jawatan_' prefix
            $id_calon = $val;
            
            $log .= "Processing: Jawatan=$id_jawatan, Calon=$id_calon... ";
            
            // Validate inputs
            $id_jawatan = $conn->real_escape_string($id_jawatan);
            $id_calon = $conn->real_escape_string($id_calon);
            
            // Check if already voted for this position
            $check = $conn->query("SELECT * FROM undian WHERE id_Pengguna='$id_pengguna' AND id_Jawatan='$id_jawatan'");
            if ($check->num_rows == 0) {
                // Generate a unique ID for the vote
                $id_undi = uniqid();
                $insert_sql = "INSERT INTO undian (id_Undi, id_Pengguna, id_Calon, id_Jawatan) VALUES ('$id_undi', '$id_pengguna', '$id_calon', '$id_jawatan')";
                
                if ($conn->query($insert_sql)) {
                    $count++;
                    $log .= "SUCCESS (ID: $id_undi)\n";
                } else {
                    // Show error to user
                    $msg .= "<script>alert('Error recording vote for $id_jawatan: " . addslashes($conn->error) . "');</script>";
                    $log .= "FAIL: " . $conn->error . "\n";
                }
            } else {
                $log .= "ALREADY VOTED\n";
            }
        }
    }
    file_put_contents('debug_log.txt', $log, FILE_APPEND);
    
    if ($count > 0) {
        $msg = "<script>alert('" . addslashes($t['voted_success']) . "');</script>";
    } else if (count($_POST) > 0) {
         // Post was submitted but no new votes recorded (maybe refresh or already voted)
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
    <title>Undian</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .vote-header {
            background: rgba(139, 0, 0, 0.9);
            backdrop-filter: blur(10px);
            color: white;
            padding: 40px 20px;
            text-align: center;
            margin-bottom: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            border: 2px solid rgba(255, 215, 0, 0.5);
        }
        
        .vote-header h1 {
            margin: 0 0 10px 0;
            font-size: calc(32px * var(--font-scale, 1));
        }
        
        .vote-header p {
            margin: 0;
            opacity: 0.95;
            font-size: calc(16px * var(--font-scale, 1));
        }
        
        .positions-container {
            display: grid;
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .position-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            color: white;
        }
        
        .position-card h2 {
            color: #FFD700;
            margin: 0 0 25px 0;
            font-size: calc(24px * var(--font-scale, 1));
            border-bottom: 3px solid #FFD700;
            padding-bottom: 15px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.3);
        }
        
        .position-card.voted {
            background: rgba(40, 167, 69, 0.1);
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 2px solid rgba(40, 167, 69, 0.3);
            border-left: 8px solid #28a745;
        }
        
        .voted-indicator {
            display: inline-block;
            background: rgba(40, 167, 69, 0.8);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: bold;
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.3);
        }
        
        .candidates-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .candidate-option {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: rgba(255, 255, 255, 0.05);
            gap: 15px;
            color: #eee;
        }
        
        .candidate-option:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.4);
            transform: translateX(5px);
        }

        .candidate-option input[type="radio"] {
            appearance: none;
            -webkit-appearance: none;
            width: 22px;
            height: 22px;
            border: 2px solid rgba(255, 215, 0, 0.5);
            border-radius: 50%;
            outline: none;
            cursor: pointer;
            position: relative;
            background: rgba(255, 255, 255, 0.1);
            transition: all 0.3s;
            flex-shrink: 0;
            margin: 0; /* Override global input margin */
        }

        .candidate-option input[type="radio"]:checked {
            border-color: #FFD700;
            background: rgba(255, 215, 0, 0.2);
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3);
        }

        .candidate-option input[type="radio"]:checked::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 10px;
            background: #FFD700;
            border-radius: 50%;
            box-shadow: 0 0 10px #FFD700;
        }

        .candidate-option span {
            font-size: calc(16px * var(--font-scale, 1));
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        
        
        .candidate-option input[type="radio"]:checked + label {
            font-weight: bold;
            color: #8B0000;
        }
        
        .submit-container {
            text-align: center;
            padding: 30px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 2px solid #e0e0e0;
        }
        
        .submit-container p {
            margin: 0 0 20px 0;
            color: #666;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 500;
        }
        
        .submit-btn {
            padding: 15px 40px;
            background: linear-gradient(135deg, #8B0000, #CD5C5C);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            font-size: calc(18px * var(--font-scale, 1));
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 300px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .submit-btn:hover:not(:disabled) {
            background: #8B0000;
            box-shadow: 0 4px 12px rgba(139, 0, 0, 0.4);
            transform: translateY(-2px);
        }
        
        .submit-btn:active:not(:disabled) {
            transform: scale(0.98);
        }
        
        .submit-btn:disabled {
            background: #ccc;
            color: #999;
            cursor: not-allowed;
            opacity: 0.6;
        }
        
        .progress-indicator {
            text-align: center;
            margin-bottom: 30px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 8px;
            border: 2px solid #8B0000;
            backdrop-filter: blur(5px);
        }
        
        .progress-indicator p {
            margin: 0;
            font-size: calc(16px * var(--font-scale, 1));
            font-weight: bold;
            color: #8B0000;
        }
        
        .progress-bar {
            width: 100%;
            height: 10px;
            background: #e0e0e0;
            border-radius: 5px;
            margin-top: 10px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #8B0000, #FFD700);
            width: 0%;
            transition: width 0.3s ease;
            border-radius: 5px;
        }
        

        
        .completion-box {
            text-align: center;
            padding: 20px;
            background: #e8f5e9;
            border: 2px solid #28a745;
            border-radius: 10px;
            margin-top: 20px;
        }
        
        .completion-box h3 {
            color: #2e7d32;
            margin: 0 0 10px 0;
        }
        
        .logout-btn-large {
            display: inline-block;
            margin-top: 15px;
            padding: 15px 30px;
            background: #c0392b;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            font-size: calc(18px * var(--font-scale, 1));
            transition: background 0.3s;
        }
        
        .logout-btn-large:hover {
            background: #a93226;
        }
    </style>
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
            <a href="vote.php" class="active"><?php echo $t['nav_vote']; ?></a>
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
            <script>alert('<?php echo ($lang == "ms") ? "Berjaya log masuk, " : "Successfully logged in, "; ?><?php echo addslashes($nama_penuh); ?>!');</script>
        <?php endif; ?>
        <div class="vote-header">
            <h1><?php echo $t['vote_welcome']; ?>, <?php echo $nama_penuh; ?>!</h1>
            <p><?php echo $t['vote_instruction']; ?></p>
        </div>
        
        <?php if (!empty($msg)) echo $msg; ?>
        
        <div class="progress-indicator">
            <p><?php echo $t['progress']; ?>: <span id="voted-count">0</span>/3 <?php echo strtolower($t['position']); ?></p>
            <div class="progress-bar">
                <div class="progress-fill" id="progress-fill"></div>
            </div>
        </div>
        
        <form id="voting-form" method="POST">
            <div class="positions-container">
                <?php
                $jawatan_res = $conn->query("SELECT * FROM jawatan");
                $positions = [];
                while($j = $jawatan_res->fetch_assoc()) {
                    $positions[] = $j;
                }
                
                foreach($positions as $j) {
                    $voted = $conn->query("SELECT id_Calon FROM undian WHERE id_Pengguna='$id_pengguna' AND id_Jawatan='" . $j['id_Jawatan'] . "'");
                    $is_voted = $voted->num_rows > 0;
                    
                    // Translate position name
                    $pos_key = 'pos_' . strtolower(str_replace(' ', '_', $j['nama_Jawatan']));
                    $display_name = isset($t[$pos_key]) ? $t[$pos_key] : $j['nama_Jawatan'];
                    
                    echo "<div class='position-card" . ($is_voted ? " voted" : "") . "'>";
                    echo "<h2>" . $display_name . "</h2>";
                    
                    if ($is_voted) {
                        $voted_data = $voted->fetch_assoc();
                        $voted_calon = $conn->query("SELECT nama_Calon FROM calon WHERE id_Calon='" . $voted_data['id_Calon'] . "'")->fetch_assoc();
                        echo "<div class='voted-indicator'>" . $t['already_voted'] . ": " . $voted_calon['nama_Calon'] . "</div>";
                        echo "<input type='hidden' name='jawatan_" . $j['id_Jawatan'] . "' value='" . $voted_data['id_Calon'] . "'>";
                    } else {
                        echo "<div class='candidates-list'>";
                        $calon_res = $conn->query("SELECT * FROM calon ORDER BY nama_Calon ASC");
                        while($c = $calon_res->fetch_assoc()) {
                            echo "<label class='candidate-option'>";
                            echo "<input type='radio' name='jawatan_" . $j['id_Jawatan'] . "' value='" . $c['id_Calon'] . "' class='jawatan-radio'>";
                            echo "<span>" . $c['nama_Calon'] . "</span>";
                            echo "</label>";
                        }
                        echo "</div>";
                    }
                    
                    echo "</div>";
                }
                ?>
            </div>
            
            <div class="submit-container">
                <p id="submit-message"><?php echo $t['submit_msg_pending']; ?></p>
                <button type="submit" id="submit-btn" class="submit-btn" disabled><?php echo $t['submit_all']; ?></button>
            </div>
        </form>
    </div>
    <footer>&copy; 2026 Briged Putera</footer>
    
    <script>
        const form = document.getElementById('voting-form');
        const submitBtn = document.getElementById('submit-btn');
        const submitMessage = document.getElementById('submit-message');
        const progressFill = document.getElementById('progress-fill');
        const votedCount = document.getElementById('voted-count');
        const radios = document.querySelectorAll('.jawatan-radio');
        
        function checkProgress() {
            const jawatans = ['J1', 'J2', 'J3'];
            
            let selected = 0;
            jawatans.forEach(id => {
                // Check if already voted (hidden input)
                const hidden = document.querySelector(`input[type="hidden"][name="jawatan_${id}"]`);
                // Check if currently selected (radio)
                const checked = document.querySelector(`input[name="jawatan_${id}"]:checked`);
                
                if (hidden || checked) {
                    selected++;
                }
            });
            
            votedCount.textContent = selected;
            const percentage = (selected / 3) * 100;
            progressFill.style.width = percentage + '%';
            
            if (selected === 3) {
                 // Check if all are actually submitted (all are hidden inputs)
                 const allSubmitted = jawatans.every(id => document.querySelector(`input[type="hidden"][name="jawatan_${id}"]`));
                 
                 if (allSubmitted) {
                     submitBtn.style.display = 'none';
                     submitMessage.innerHTML = '<div class="completion-box"><h3><?php echo $t['congrats']; ?></h3><p><?php echo $t['thanks_voted']; ?></p><a href="logout.php" class="logout-btn-large"><?php echo $t['nav_logout']; ?></a></div>';
                 } else {
                     submitBtn.disabled = false;
                     submitMessage.textContent = '<?php echo $t['submit_msg_complete']; ?>';
                     submitMessage.style.color = '#28a745';
                 }
            } else {
                submitBtn.disabled = true;
                submitMessage.textContent = '<?php echo str_replace("%s", "' + (3 - selected) + '", $t['pilih_lagi']); ?>';
                submitMessage.style.color = '#666';
            }
        }
        
        radios.forEach(radio => {
            radio.addEventListener('change', checkProgress);
        });
        
        // Form submission is now handled natively by the browser
        // We just need to ensure the button is enabled/disabled correctly by checkProgress
        
        checkProgress();
    </script>
</body>
</html>