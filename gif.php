<?php
require '../vendor/autoload.php';

use Intervention\Image\ImageManager;
use Symfony\Component\HttpFoundation\Request;
use Intervention\Image\AbstractFont;
use Carbon\Carbon;

$request = Request::createFromGlobals();

// basic setting
$timezone = $request->get('tz', 'Europe/Berlin');
$now = Carbon::parse('now', $timezone);
$target = Carbon::parse($request->get('t', 'now'), $timezone);
$diff = $now->diffInSeconds($target, false);
$seconds = max(0, min(60, $request->get('s', 10)));
$default = $request->get('d');
$format = $request->get('f', 'd:h:m:s');

// image settings
$width = intval($request->get('w', 500));
$height = intval($request->get('h', 50));
$background = $request->get('bg', '#ffffff');

// font settings
$fontType = $request->get('ft', 'lato');
$fontSize = intval($request->get('fs', 48));
$fontColor = $request->get('fc', '#ff0000');
switch ($fontType) {
    case 'lato':
        $fontFile = realpath('../resources/assets/fonts/lato-regular.ttf');
        break;
    default:
        $fontFile = realpath('../resources/assets/fonts/lato-regular.ttf');
        break;
}

/*
 * do not touch this
 * image generation by url parameters
 */
function secondsToUnits($seconds) {
    $days = floor($seconds / (24 * 60 * 60));
    $seconds -= $days * (24 * 60 * 60);
    $hours = floor($seconds / (60 * 60));
    $seconds -= $hours * (60 * 60);
    $minutes = floor($seconds / 60);
    $seconds -= $minutes * 60;

    return [
        'd' => str_pad($days, 2, '0', STR_PAD_LEFT),
        'h' => str_pad($hours, 2, '0', STR_PAD_LEFT),
        'm' => str_pad($minutes, 2, '0', STR_PAD_LEFT),
        's' => str_pad($seconds, 2, '0', STR_PAD_LEFT),
    ];
}

$seconds = min($seconds, max(0, $diff));

$manager = new ImageManager(['driver' => 'imagick']);
$gif = new Imagick();
$gif->setFormat('gif');
for ($i = 0; $i <= $seconds; $i++) {
    $text = $default;
    if($diff >= 0) {
        $units = secondsToUnits($diff - $i);
        $text = str_replace(array_keys($units), array_values($units), $format);
    }
    $frame = $manager->canvas($width, $height, $background);
    $frame->text($text, $width / 2, $height / 2, function (AbstractFont $font) use ($fontFile, $fontSize, $fontColor) {
        $font->file($fontFile);
        $font->size($fontSize);
        $font->color($fontColor);
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
