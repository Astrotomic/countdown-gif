<?php
require '../vendor/autoload.php';

use Intervention\Image\ImageManager;

$manager = new ImageManager(array('driver' => 'imagick'));

$gif = new Imagick();
$gif->setFormat("gif");

$seconds = 60;
for ($i = 0; $i <= $seconds; ++$i) {
    $frame = $manager->canvas(500, 50, '#ffffff');
    $frame->text(str_pad($seconds-$i, 4, '0', STR_PAD_LEFT), 250, 25, function($font) {
        $font->file(realpath('../resources/assets/fonts/lato-regular.ttf'));
        $font->size(48);
        $font->color('#ff0000');
        $font->align('center');
        $font->valign('center');
    });
    $frame = $frame->getCore();
    $frame->setImageDelay(100);
    $gif->addImage($frame);
}

header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: image/gif');
echo $gif->getImagesBlob();
