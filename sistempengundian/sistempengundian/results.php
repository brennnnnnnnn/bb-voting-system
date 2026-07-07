<?php
session_start();
include 'db.php';
include 'lang.php';
if (!isset($_SESSION['role'])) { header("Location: index.php"); exit(); }
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
    <title>Keputusan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
    <style>
        .results-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
        }
        
        .result-card {
            background: rgba(0, 0, 0, 0.4); /* Darker glass for better contrast */
            backdrop-filter: blur(15px);
            -webkit-backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            border-top: 5px solid #FFD700;
            color: white;
        }
        
        .result-card h3 {
            margin: 0 0 15px 0;
            color: #FFD700;
            font-size: calc(22px * var(--font-scale, 1));
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding-bottom: 10px;
        }
        
        .total-votes {
            color: rgba(255, 255, 255, 0.9);
            font-size: calc(14px * var(--font-scale, 1));
            margin-bottom: 20px;
            font-style: italic;
        }
        
        .candidate-row {
            margin-bottom: 15px;
        }
        
        .c-info {
            display: block; /* Stack name and votes */
            margin-bottom: 5px;
            font-size: calc(14px * var(--font-scale, 1));
        }
        
        .c-name { 
            display: block;
            width: 100%;
            font-weight: 600; 
            color: white; 
            font-size: calc(15px * var(--font-scale, 1));
            text-shadow: 0 1px 2px rgba(0,0,0,0.8);
            margin-bottom: 2px;
            white-space: normal; /* Allow natural flow if absolutely needed, but usually 1 line now */
        }
        .c-votes { 
            display: block;
            width: 100%;
            text-align: right;
            color: #FFD700; 
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.8);
            font-size: calc(13px * var(--font-scale, 1));
        }
        
        .c-progress {
            background: rgba(255, 255, 255, 0.15);
            height: 14px;
            border-radius: 7px;
            overflow: hidden;
            margin-top: 5px;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.3);
        }
        
        .c-bar {
            background: linear-gradient(90deg, #FF4500, #FF8C00); /* Bright Orange/Red gradient */
            height: 100%;
            border-radius: 7px;
            width: 0;
            transition: width 1s ease-in-out;
            box-shadow: 0 0 10px rgba(255, 69, 0, 0.4);
        }
        
        /* Gold highlighting for winner */
        .candidate-row.winner .c-name { 
            color: #FFD700; 
            font-weight: 800; 
            font-size: calc(17px * var(--font-scale, 1)); /* Larger font for winner */
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.5), 0 2px 4px rgba(0,0,0,0.8);
        }
        .candidate-row.winner .c-bar { 
            background: linear-gradient(90deg, #FFD700, #DAA520); 
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.6);
        }
        
        /* Print Button */
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 24px;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: calc(14px * var(--font-scale, 1));
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #2c3e50, #34495e);
            box-shadow: 0 4px 12px rgba(52, 73, 94, 0.3);
        }
        .print-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(52, 73, 94, 0.5);
        }

        /* Admin Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
        th { background: #8B0000; color: white; }

        /* Print-only page - hidden on screen */
        .print-page {
            display: none;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 15mm 20mm;
            }
            html, body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
                font-family: 'Poppins', sans-serif !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            /* Hide ALL screen content */
            .top-header, .floating-logo, .nav-links, .lang-switcher,
            .print-btn, .no-print, .font-switcher,
            .main-content {
                display: none !important;
            }
            /* Show print page */
            .print-page {
                display: block !important;
                padding: 0 !important;
                margin: 0 !important;
                color: #1a2332 !important;
            }
            .print-page * {
                color: #1a2332 !important;
            }

            /* Header */
            .pp-label {
                font-size: 10px;
                text-transform: uppercase;
                letter-spacing: 2px;
                color: #3a4a6b !important;
                margin-bottom: 4px;
                font-weight: 600;
            }
            .pp-title {
                font-size: 22px;
                font-weight: 700;
                color: #1a2332 !important;
                margin: 0 0 6px 0;
            }
            .pp-subtitle {
                font-size: 11px;
                color: #5a6a8a !important;
                margin: 0 0 20px 0;
            }

            /* Step badge */
            .pp-badge {
                display: inline-block;
                border: 1.5px solid #d0d5dd;
                border-radius: 8px;
                padding: 6px 16px;
                font-size: 12px;
                font-weight: 600;
                color: #1a2332 !important;
                margin-bottom: 14px;
            }

            /* Vote rows */
            .pp-vote-list {
                margin-bottom: 25px;
            }
            .pp-vote-item {
                border: 1px solid #e0e4ea;
                border-radius: 10px;
                padding: 12px 16px;
                margin-bottom: 8px;
                font-size: 12px;
                font-weight: 500;
                color: #1a2332 !important;
            }

            /* Results section */
            .pp-results-title {
                font-size: 18px;
                font-weight: 700;
                color: #1a2332 !important;
                margin: 0 0 4px 0;
            }
            .pp-results-subtitle {
                font-size: 11px;
                color: #5a6a8a !important;
                margin: 0 0 18px 0;
            }

            /* Position cards */
            .pp-position-card {
                border: 1px solid #e0e4ea;
                border-radius: 12px;
                padding: 16px 18px;
                margin-bottom: 12px;
                break-inside: avoid;
                page-break-inside: avoid;
            }
            .pp-position-name {
                font-size: 14px;
                font-weight: 700;
                color: #1a2332 !important;
                margin: 0 0 10px 0;
                padding-bottom: 8px;
                border-bottom: 1px solid #eee;
            }
            .pp-candidate-row {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 7px 0;
                border-bottom: 1px solid #f0f2f5;
                font-size: 12px;
            }
            .pp-candidate-row:last-child {
                border-bottom: none;
            }
            .pp-cand-name {
                font-weight: 600;
                color: #1a2332 !important;
            }
            .pp-cand-votes {
                font-weight: 500;
                color: #3a4a6b !important;
                font-size: 11px;
            }
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
            <?php if(isset($_SESSION['role'])): ?>
                <?php if($_SESSION['role'] == 'admin'): ?>
                    <a href="admin_dashboard.php"><?php echo $t['nav_dashboard']; ?></a>
                <?php elseif($_SESSION['role'] == 'calon'): ?>
                    <a href="calon_dashboard.php"><?php echo $t['nav_profile']; ?></a>
                <?php else: ?>
                    <a href="vote.php"><?php echo $t['nav_vote']; ?></a>
                <?php endif; ?>
            <?php endif; ?>
            <a href="results.php" class="active"><?php echo $t['nav_results']; ?></a>
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
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
            <h2 style="margin:0; color:#8B0000; text-shadow: 1px 1px 3px rgba(0,0,0,0.1);"><?php echo $t['results_title']; ?></h2>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'pengguna'): ?>
                <button class="print-btn no-print" onclick="window.print();">🖨️ <?php echo ($lang == 'ms') ? 'Cetak Keputusan & Undian Saya' : 'Print Results & My Votes'; ?></button>
            <?php endif; ?>
        </div>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'pengguna'): ?>
            <?php
            $my_votes = $conn->query("SELECT j.nama_Jawatan, c.nama_Calon FROM undian u JOIN jawatan j ON u.id_Jawatan = j.id_Jawatan JOIN calon c ON u.id_Calon = c.id_Calon WHERE u.id_Pengguna = '" . $conn->real_escape_string($_SESSION['id']) . "'");
            if ($my_votes->num_rows > 0):
            ?>
            <div class="your-votes-card card" style="margin-bottom: 40px; background: rgba(0, 0, 0, 0.4); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; padding: 25px; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3); border-top: 5px solid #28a745; color: white;">
                <h2 style="color: #28a745; margin: 0 0 20px 0; font-size: calc(22px * var(--font-scale, 1)); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; text-shadow: 0 2px 4px rgba(0,0,0,0.5); border-bottom: 1px solid rgba(255, 255, 255, 0.2); padding-bottom: 10px;">📋 <?php echo ($lang == 'ms') ? 'Undian Saya' : 'My Votes'; ?></h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <?php while($mv = $my_votes->fetch_assoc()): ?>
                        <div style="background: rgba(255,255,255,0.1); padding: 15px; border-radius: 8px; border-left: 4px solid #FFD700;">
                            <div style="font-size: calc(13px * var(--font-scale, 1)); color: rgba(255,255,255,0.7); text-transform: uppercase; margin-bottom: 5px;"><?php 
                                $pos_key = 'pos_' . strtolower(str_replace(' ', '_', $mv["nama_Jawatan"]));
                                echo isset($t[$pos_key]) ? $t[$pos_key] : htmlspecialchars($mv["nama_Jawatan"]);
                            ?></div>
                            <div class="c-name" style="font-size: calc(16px * var(--font-scale, 1)); font-weight: 600; color: white;"><?php echo htmlspecialchars($mv['nama_Calon']); ?></div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="results-grid">
            <?php
            // 1. Fetch all detailed vote counts
            $sql = "SELECT j.nama_Jawatan, j.id_Jawatan, c.nama_Calon, c.id_Calon, COUNT(u.id_Undi) as total 
                    FROM jawatan j
                    LEFT JOIN undian u ON j.id_Jawatan = u.id_Jawatan 
                    LEFT JOIN calon c ON u.id_Calon = c.id_Calon 
                    GROUP BY j.id_Jawatan, c.id_Calon 
                    ORDER BY j.id_Jawatan ASC, total DESC";
                    
            $result = $conn->query($sql);
            
            // 2. Process data into a structured array
            $data = [];
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // Skip if no candidate (left join with no votes yet might return null candidate if query wasn't strict)
                    // But actually we want to see 0 votes too. 
                    // Better approach: Get all positions, then get candidates and their votes.
                    
                    if ($row['nama_Calon']) {
                         $data[$row['nama_Jawatan']][] = [
                            'name' => $row['nama_Calon'],
                            'votes' => $row['total']
                        ];
                    }
                }
            }
            
            // 3. To ensure we show ALL candidates even with 0 votes, let's do a slightly different logic or just trust the data.
            // For robustness, let's just loop through Jawatan and run specific queries. 
            // This is safer to ensure incomplete data doesn't break the layout.
            
            // Track candidates who already won a position (id => position name)
            $winners = [];
            
            $jawatan_res = $conn->query("SELECT * FROM jawatan ORDER BY id_Jawatan ASC");
            while($j = $jawatan_res->fetch_assoc()) {
                $j_id = $j['id_Jawatan'];
                $j_name = $j['nama_Jawatan'];
                
                // Translate position name
                $pos_key = 'pos_' . strtolower(str_replace(' ', '_', $j_name));
                $display_name = isset($t[$pos_key]) ? $t[$pos_key] : $j_name;
                
                // Get Total Votes for this position to calculate percentage
                $total_query = $conn->query("SELECT COUNT(*) as t FROM undian WHERE id_Jawatan='$j_id'");
                $total_votes = $total_query->fetch_assoc()['t'];
                
                echo "<div class='result-card'>";
                echo "<h3>$display_name</h3>";
                echo "<div class='total-votes'>".$t['total_votes'].": $total_votes</div>";
                
                // Get all candidates ranked by votes
                $calon_sql = "SELECT c.id_Calon, c.nama_Calon, COUNT(u.id_Undi) as votes 
                              FROM calon c 
                              LEFT JOIN undian u ON c.id_Calon = u.id_Calon AND u.id_Jawatan='$j_id'
                              GROUP BY c.id_Calon 
                              ORDER BY votes DESC";
                              
                $calon_res = $conn->query($calon_sql);
                
                $rank = 1;
                echo "<div class='candidates-ranking'>";
                $found_winner = false;
                while($c = $calon_res->fetch_assoc()) {
                    $percentage = ($total_votes > 0) ? round(($c['votes'] / $total_votes) * 100) : 0;
                    $already_won = isset($winners[$c['id_Calon']]);
                    $is_winner = (!$found_winner && $c['votes'] > 0 && !$already_won);
                    
                    // Track the winner for this position
                    if ($is_winner) {
                        $winners[$c['id_Calon']] = $display_name;
                        $found_winner = true;
                    }
                    
                    // Build remark for candidates who already won another position
                    $remark = '';
                    if ($already_won) {
                        $won_pos = $winners[$c['id_Calon']];
                        $remark = " <span style='font-size: calc(11px * var(--font-scale, 1)); color:#FFD700; background:rgba(255,215,0,0.15); padding:2px 8px; border-radius:10px; margin-left:5px;'>🏆  " 
                                . (($lang == 'ms') ? "Sudah menang $won_pos" : "Already won $won_pos") 
                                . "</span>";
                    }
                    
                    echo "<div class='candidate-row" . ($is_winner ? " winner" : "") . "'>";
                    echo "<div class='c-info'>";
                    echo "<span class='c-name'>" . $rank . ". " . $c['nama_Calon'] . $remark . "</span>";
                    echo "<span class='c-votes'>" . $c['votes'] . " ".$t['votes']." (" . $percentage . "%)</span>";
                    echo "</div>";
                    
                    echo "<div class='c-progress'>";
                    echo "<div class='c-bar' style='width: " . $percentage . "%'></div>";
                    echo "</div>";
                    echo "</div>";
                    $rank++;
                }
                echo "</div>"; // end candidates-ranking
                echo "</div>"; // end result-card
            }
            ?>
        </div>
    </div>

    <?php if($_SESSION['role'] == 'admin'): ?>
    <!-- Winners Summary -->
    <div class="card" style="margin-top: 30px; background: rgba(0,0,0,0.4); backdrop-filter: blur(15px); border: 1px solid rgba(255,255,255,0.15); color: white;">
        <h2 style="color: #FFD700; text-align: center; margin-bottom: 20px; font-size: calc(24px * var(--font-scale, 1));">🏆  <?php echo ($lang == 'ms') ? 'Pemenang Jawatan' : 'Position Winners'; ?></h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
            <?php
            if (!empty($winners)) {
                foreach ($winners as $calon_id => $won_position) {
                    $calon_data = $conn->query("SELECT nama_Calon FROM calon WHERE id_Calon='" . $conn->real_escape_string($calon_id) . "'")->fetch_assoc();
                    $nama = $calon_data['nama_Calon'];
                    echo "<div style='background: linear-gradient(135deg, rgba(139,0,0,0.6), rgba(218,165,32,0.4)); border: 1px solid rgba(255,215,0,0.3); border-radius: 15px; padding: 25px; text-align: center;'>";
                    echo "<div style='font-size: calc(36px * var(--font-scale, 1)); margin-bottom: 10px;'>🏆</div>";
                    echo "<div style='font-size: calc(13px * var(--font-scale, 1)); color: rgba(255,255,255,0.7); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;'>$won_position</div>";
                    echo "<div style='font-size: calc(18px * var(--font-scale, 1)); font-weight: 700; color: #FFD700; text-shadow: 0 2px 4px rgba(0,0,0,0.5);'>$nama</div>";
                    echo "</div>";
                }
            } else {
                echo "<div style='text-align:center; grid-column: 1/-1; color: rgba(255,255,255,0.6); padding: 20px;'>";
                echo ($lang == 'ms') ? 'Belum ada pemenang.' : 'No winners yet.';
                echo "</div>";
            }
            ?>
        </div>
    </div>

    <div class="card" style="margin-top: 30px;">
        <h2><?php echo $t['admin_details']; ?></h2>
        <table>
            <thead>
                <tr>
                    <th><?php echo $t['time']; ?></th>
                    <th><?php echo $t['username']; ?></th>
                    <th><?php echo $t['position']; ?></th>
                    <th><?php echo $t['candidate_chosen']; ?></th>
                </tr>
            </thead>
            <tbody>
            <?php
            // Check if tarikh_undi exists (for safety during migration)
            $col_check = $conn->query("SHOW COLUMNS FROM undian LIKE 'tarikh_undi'");
            $has_time = $col_check->num_rows > 0;
            
            $sql_detail = "SELECT u.*, p.nama_Pengguna, j.nama_Jawatan, c.nama_Calon 
                           FROM undian u 
                           JOIN pengguna p ON u.id_Pengguna = p.id_Pengguna 
                           JOIN jawatan j ON u.id_Jawatan = j.id_Jawatan 
                           JOIN calon c ON u.id_Calon = c.id_Calon 
                           ORDER BY " . ($has_time ? "u.tarikh_undi DESC" : "u.id_Undi DESC");
            
            $res_detail = $conn->query($sql_detail);
            
            if ($res_detail->num_rows > 0) {
                while($row = $res_detail->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . ($has_time ? $row["tarikh_undi"] : "-") . "</td>";
                    echo "<td>" . $row["nama_Pengguna"] . " (" . $row['id_Pengguna'] . ")</td>";
                    
                    $pos_key = 'pos_' . strtolower(str_replace(' ', '_', $row["nama_Jawatan"]));
                    $display_pos = isset($t[$pos_key]) ? $t[$pos_key] : $row["nama_Jawatan"];
                    
                    echo "<td>" . $display_pos . "</td>";
                    echo "<td>" . $row["nama_Calon"] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>".$t['no_data']."</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    </div>

    <!-- ========== PRINT-ONLY PAGE ========== -->
    <div class="print-page" id="printPage">
        <div class="pp-label"><?php echo ($lang == 'ms') ? 'UNDIAN SELESAI' : 'VOTING COMPLETE'; ?></div>
        <h1 class="pp-title"><?php echo ($lang == 'ms') ? 'Ringkasan Undian Anda' : 'Your Voting Summary'; ?></h1>
        <p class="pp-subtitle"><?php echo ($lang == 'ms') ? 'Terima kasih kerana mengundi. Berikut adalah maklumat undian anda.' : 'Thank you for voting. Below is your voting information.'; ?></p>

        <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'pengguna'): ?>
        <?php
        $print_votes = $conn->query("SELECT j.nama_Jawatan, c.nama_Calon FROM undian u JOIN jawatan j ON u.id_Jawatan = j.id_Jawatan JOIN calon c ON u.id_Calon = c.id_Calon WHERE u.id_Pengguna = '" . $conn->real_escape_string($_SESSION['id']) . "'");
        $vote_count = $print_votes ? $print_votes->num_rows : 0;
        if ($vote_count > 0):
        ?>
        <div class="pp-badge"><?php echo ($lang == 'ms') ? "Langkah $vote_count" : "Step $vote_count"; ?></div>
        <div class="pp-vote-list">
            <?php while($pv = $print_votes->fetch_assoc()): ?>
            <div class="pp-vote-item">
                <?php
                $pk = 'pos_' . strtolower(str_replace(' ', '_', $pv['nama_Jawatan']));
                $pn = isset($t[$pk]) ? $t[$pk] : $pv['nama_Jawatan'];
                echo ($lang == 'ms') ? "Jawatan: $pn - Calon: " : "Position: $pn - Candidate: ";
                echo htmlspecialchars($pv['nama_Calon']);
                ?>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        <h2 class="pp-results-title"><?php echo ($lang == 'ms') ? 'Keputusan Undian Semasa' : 'Current Voting Results'; ?></h2>
        <p class="pp-results-subtitle"><?php echo ($lang == 'ms') ? 'Berikut adalah kedudukan terkini bagi setiap calon.' : 'Below are the current standings for each candidate.'; ?></p>

        <?php
        $print_jawatan = $conn->query("SELECT * FROM jawatan ORDER BY id_Jawatan ASC");
        while($pj = $print_jawatan->fetch_assoc()):
            $pj_id = $pj['id_Jawatan'];
            $pk2 = 'pos_' . strtolower(str_replace(' ', '_', $pj['nama_Jawatan']));
            $pj_display = isset($t[$pk2]) ? $t[$pk2] : $pj['nama_Jawatan'];
            $print_calon = $conn->query("SELECT c.nama_Calon, COUNT(u.id_Undi) as votes FROM calon c LEFT JOIN undian u ON c.id_Calon = u.id_Calon AND u.id_Jawatan='$pj_id' GROUP BY c.id_Calon ORDER BY votes DESC");
        ?>
        <div class="pp-position-card">
            <h3 class="pp-position-name"><?php echo $pj_display; ?></h3>
            <?php while($pc = $print_calon->fetch_assoc()): ?>
            <div class="pp-candidate-row">
                <span class="pp-cand-name"><?php echo htmlspecialchars($pc['nama_Calon']); ?></span>
                <span class="pp-cand-votes"><?php echo $pc['votes'] . ' ' . (($lang == 'ms') ? 'undian' : 'votes'); ?></span>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endwhile; ?>
    </div>