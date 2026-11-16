<?php
/**
 * QRcdr - php QR Code generator
 * ajax/png.php
 *
 * PHP version 5.4+
 *
 * @category  PHP
 * @package   QRcdr
 * @author    Nicola Franchini <info@veno.it>
 * @copyright 2015-2020 Nicola Franchini
 * @license   item sold on codecanyon https://codecanyon.net/item/qrcdr-responsive-qr-code-generator/9226839
 * @link      http://veno.es/qrcdr/
 */
if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest')
) {
    exit;
}
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', '0');
require dirname(dirname(__FILE__)).'/config.php';

$imgdata = isset($_POST['imgdata']) ? $_POST['imgdata'] : false;
$filename = isset($_POST['filename']) ? basename($_POST['filename']) : false;

if ($imgdata && $filename) {
    $maindir = $_CONFIG['qrcodes_dir'].'/';
    $savedir = '../'.$maindir;
    $basename = basename($filename, '.png');

    if (file_exists($savedir.$basename.'.svg')) {
        if (!file_exists($savedir.$filename)) {
            $content = false;
            if (preg_match('/^data:image\/(?:png|jpeg|svg\+xml);base64,(.+)$/', $imgdata, $matches)) {
                $content = base64_decode($matches[1]);
            }
            if (!$content) {
                exit('error');
            }
            file_put_contents($savedir.$filename, $content);
        }
        echo $maindir.$filename;
        exit;
    }
    exit('error');
}
exit('error');
