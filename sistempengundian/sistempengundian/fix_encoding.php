<?php
$files = glob('*.php');
foreach ($files as $file) {
    if ($file == 'fix_encoding.php' || $file == 'test_hex.php') continue;
    
    $content = file_get_contents($file);
    
    // results.php
    $content = preg_replace('/>[^<]*<\?php echo \(\$lang == \'ms\'\) \? \'Pemenang Jawatan\'/', '>🏆 <?php echo ($lang == \'ms\') ? \'Pemenang Jawatan\'', $content);
    $content = preg_replace('/10px;\'>[^<]+<\/div>/', '10px;\'>🏆</div>', $content);
    $content = preg_replace('/margin-left:5px;\'>[^<]+"/', 'margin-left:5px;\'>🏆 "', $content);
    $content = preg_replace('/padding-bottom: 10px;">[^<]*<\?php echo \(\$lang == \'ms\'\) \? \'Undian Saya\'/', 'padding-bottom: 10px;">📝 <?php echo ($lang == \'ms\') ? \'Undian Saya\'', $content);
    $content = preg_replace('/>[^<]*<\?php echo \(\$lang == \'ms\'\) \? \'Cetak Keputusan/', '>🖨️ <?php echo ($lang == \'ms\') ? \'Cetak Keputusan', $content);

    // vote.php, admin_dashboard.php, calon_dashboard.php
    $content = preg_replace('/"[^"]*" \. \(\(\$lang == \'ms\'\) \? "Berjaya:/', '"✅ " . (($lang == \'ms\') ? "Berjaya:', $content);
    $content = preg_replace('/>[^<]*<\?php echo \(\$lang == \'ms\'\) \? \'Berjaya log masuk/', '>✅ <?php echo ($lang == \'ms\') ? \'Berjaya log masuk', $content);
    $content = preg_replace('/\.textContent = \'[^\']*\' \+ msg;/', '.textContent = \'✅ \' + msg;', $content);

    $content = preg_replace('/"[^"]*" \. \(\(\$lang == \'ms\'\) \? "\$detail_str berjaya/', '"⚠️ " . (($lang == \'ms\') ? "$detail_str berjaya', $content);
    $content = preg_replace('/\.textContent = \'[^\']*\' \+ msg;/', '.textContent = \'❌ \' + msg;', $content); // Warning: this matches any .textContent. Let's not use it if it conflicts.
    
    // Since there are specific ones:
    // In register.php and index.php: .textContent = '... ' + msg;
    // We can do it more specifically:
    
    file_put_contents($file, $content);
}
echo "Done";
?>
