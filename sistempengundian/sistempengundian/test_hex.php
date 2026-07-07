<?php
$lines = file('results.php');
foreach($lines as $line) {
    if(strpos($line, 'Pemenang Jawatan') !== false) {
        var_dump($line);
        var_dump(bin2hex($line));
    }
}
?>
