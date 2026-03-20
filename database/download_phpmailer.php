<?php
$files = [
    'Exception.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/Exception.php',
    'PHPMailer.php' => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/PHPMailer.php',
    'SMTP.php'      => 'https://raw.githubusercontent.com/PHPMailer/PHPMailer/master/src/SMTP.php',
];

$dir = __DIR__ . '/../core/PHPMailer';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

foreach ($files as $name => $url) {
    $content = file_get_contents($url);
    if ($content) {
        file_put_contents("$dir/$name", $content);
        echo "Downloaded $name<br>\n";
    } else {
        echo "Failed to download $name<br>\n";
    }
}
echo "PHPMailer download complete.";
