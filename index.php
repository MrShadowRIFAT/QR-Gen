<?php
/**
 * QRcdr - php QR Code generator
 * @version   5.3.9
 * @link      http://veno.es/qrcdr/
 */
$version = '5.4.2';

if (version_compare(phpversion(), '5.4', '<')) {
    exit("QRcdr requires at least PHP version 5.4");
}

// https://stackoverflow.com/questions/11920026/replace-file-get-contents-with-curl
if (!filter_var( ini_get( 'allow_url_fopen' ), FILTER_VALIDATE_BOOLEAN ) )  {
    exit("Please enable <code>allow_url_fopen<code>");
}
if (!function_exists('mime_content_type')) {
    exit("Please enable the <code>fileinfo</code> extension");
}
// Update this path if you have a custom relative path inside config.php
require dirname(__FILE__)."/lib/functions.php";

if (qrcdr()->getConfig('debug_mode')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL ^ E_NOTICE);
}
$relative = qrcdr()->relativePath();
$absolute = qrcdr()->basePath();
$runtime = getenv('VERCEL') ? 'vercel' : 'default';

require $absolute.'/include/head.php';
?>
<!doctype html>
<html lang="<?php echo $lang; ?>" dir="<?php echo $rtl['dir']; ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, interactive-widget=resizes-content">
        <meta name="referrer" content="strict-origin-when-cross-origin">
        <meta name="color-scheme" content="dark light">
        <meta name="theme-color" content="#07080d">
        <title><?php echo qrcdr()->getString('title'); ?></title>
        <meta name="description" content="<?php echo qrcdr()->getString('description'); ?>">
        <meta name="keywords" content="<?php echo qrcdr()->getString('tags'); ?>">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?php echo $relative; ?>bootstrap/css/bootstrap<?php echo $rtl['css']; ?>.min.css" rel="stylesheet">
        <link href="<?php echo $relative; ?>css/font-awesome.min.css" rel="stylesheet">
        <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.0/dist/umd/lucide.min.js" integrity="sha384-JNhb/AfQ8tCvhjfm2WXKx9qovmn7LcndXYllHYDf2CcTBaBMAiPsjRJeC3f9U8V6" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <script src="<?php echo $relative; ?>js/jquery-3.7.1.min.js"></script>
        <?php
        $custom_page = false;
        $body_class = '';
        if (isset($_GET['p'])) {
            $page_sanitized = basename(preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['p']));
            $load_page = $absolute.'/template/'.$page_sanitized.'.html';
            if (!empty($page_sanitized) && file_exists($load_page)) {
                $custom_page = file_get_contents($load_page);
            }
        }
        qrcdr()->loadQRcdrCSS($version);
        if (!$custom_page) {
            $body_class = 'qrcdr';
            qrcdr()->loadPluginsCss();
        }
        qrcdr()->setMainColor(qrcdr()->getConfig('color_primary'));
        ?>
    </head>
    <body class="<?php echo $body_class; ?>" data-runtime="<?php echo $runtime; ?>">
        <?php
        if (file_exists($absolute.'/template/header.php')) {
            include $absolute.'/template/header.php';
        }
        if ($custom_page) {
            echo '<div class="container mt-4">'.$custom_page.'</div>';
        } else {
            // Generator
            include $absolute.'/include/generator.php';
        }
        qrcdr()->loadQRcdrJS($version);

        if (!$custom_page) {
            qrcdr()->loadPlugins();
        }
        if (file_exists($absolute.'/template/footer.php')) {
            include $absolute.'/template/footer.php';
        }
        ?>
    </body>
</html>
