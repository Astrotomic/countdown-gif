

## Example

```php
$request = \Illuminate\Http\Request::createFromGlobals();
$timezone = timezone_open((string) $request->get('tz', 'Europe/Berlin'));
$now = new DateTime('now', $timezone);
$target = new DateTime($request->get('t', 'now'), $timezone);
$runtime = max(0, min(300, $request->get('r', 10)));
$default = $request->get('d');
$format = $request->get('f', '{d}:{h}:{m}:{s}');

$width = intval($request->get('w', 500));
$height = intval($request->get('h', 50));
$bgColor = '#'.$request->get('bg', 'ffffff');

$fontType = $request->get('ft');
$fontSize = intval($request->get('fs', 48));
$fontColor = $request->get('fc', '#ff0000');

$formatter = new \Astrotomic\CountdownGif\Helper\Formatter($format);

$background = new Imagick();
$background->setFormat('png');
$background->newImage($width, $height, $bgColor);

$font = new \Astrotomic\CountdownGif\Helper\Font($fontType, $fontSize, $fontColor, [
    'lato' => resource_path('lato-regular.ttf'),
]);

$redis = new \Redis();
$config = app('config')->get('database.redis.default');
$redis->connect($config['host'], $config['port']);
$redisPool = new \Cache\Adapter\Redis\RedisCachePool($redis);

$countDownGif = new \Astrotomic\CountdownGif\CountdownGif($now, $target, $runtime, $formatter, $background, $font, $default, $redisPool, \Cache\Adapter\Common\CacheItem::class);
$gif = $countDownGif->generate($background->getImageWidth() / 2, $background->getImageHeight() / 2);

header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Content-Type: image/gif');
echo $gif->getImagesBlob();
``## Treeware

You're free to use this package, but if it makes it to your production environment I would highly appreciate you buying the world a tree.

It’s now common knowledge that one of the best tools to tackle the climate crisis and keep our temperatures from rising above 1.5C is to [plant trees](https://www.bbc.co.uk/news/science-environment-48870920). If you contribute to my forest you’ll be creating employment for local families and restoring wildlife habitats.

You can buy trees at [offset.earth/treeware](https://plant.treeware.earth/Astrotomic/countdown-gif)

Read more about Treeware at [treeware.earth](https://treeware.earth)### Security

If you discover any security related issues, please check [SECURITY](https://github.com/Astrotomic/.github/blob/master/SECURITY.md) for steps to report it.
