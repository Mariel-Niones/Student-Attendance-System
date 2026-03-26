<?php

if (!isset($_GET['code']) || empty($_GET['code'])) {
    exit('No code provided');
}

$code = $_GET['code'];

// Simple barcode generator (Code 39)
function drawBarcode($text) {
    $barWidth = 2;
    $height = 60;

    $patterns = [
        '0'=>"101001101101",'1'=>"110100101011",'2'=>"101100101011",
        '3'=>"110110010101",'4'=>"101001101011",'5'=>"110100110101",
        '6'=>"101100110101",'7'=>"101001011011",'8'=>"110100101101",
        '9'=>"101100101101",'A'=>"110101001011",'B'=>"101101001011",
        'C'=>"110110100101",'D'=>"101011001011",'E'=>"110101100101",
        'F'=>"101101100101",'G'=>"101010011011",'H'=>"110101001101",
        'I'=>"101101001101",'J'=>"101011001101",'K'=>"110101010011",
        'L'=>"101101010011",'M'=>"110110101001",'N'=>"101011010011",
        'O'=>"110101101001",'P'=>"101101101001",'Q'=>"101010110011",
        'R'=>"110101011001",'S'=>"101101011001",'T'=>"101011011001",
        'U'=>"110010101011",'V'=>"100110101011",'W'=>"110011010101",
        'X'=>"100101101011",'Y'=>"110010110101",'Z'=>"100110110101",
        '-'=>"100101011011",'.'=>"110010101101",' '=>"100110101101",
        '*'=>"100101101101" // start/stop
    ];

    $text = "*" . strtoupper($text) . "*";

    $width = strlen($text) * 13 * $barWidth;
    $image = imagecreate($width, $height);

    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);

    $x = 0;

    for ($i = 0; $i < strlen($text); $i++) {
        $char = $text[$i];

        if (!isset($patterns[$char])) continue;

        $pattern = $patterns[$char];

        for ($j = 0; $j < strlen($pattern); $j++) {
            $color = ($pattern[$j] == '1') ? $black : $white;
            imagefilledrectangle($image, $x, 0, $x + $barWidth, $height, $color);
            $x += $barWidth;
        }

        // space between characters
        $x += $barWidth;
    }

    header("Content-Type: image/png");
    imagepng($image);
    imagedestroy($image);
}

drawBarcode($code);
?>