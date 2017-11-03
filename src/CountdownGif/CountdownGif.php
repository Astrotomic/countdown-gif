<?php

namespace Astrotomic\CountdownGif;

use Astrotomic\CountdownGif\Helper\Font;
use Astrotomic\CountdownGif\Helper\Formatter;
use DateTime;
use Imagick;
use ImagickDraw;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CountdownGif
{
    /**
     * @var DateTime
     */
    protected $now;

    /**
     * @var DateTime
     */
    protected $target;

    /**
     * @var int
     */
    protected $runtime;

    /**
     * @var string
     */
    protected $default;

    /**
     * @var Formatter
     */
    protected $formatter;

    /**
     * @var Imagick
     */
    protected $background;

    /**
     * @var Font
     */
    protected $font;

    /**
     * @var CacheItemPoolInterface
     */
    protected $cache;

    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    protected $cacheItemClass;

    /**
     * CountdownGif constructor.
     * @param DateTime $now
     * @param DateTime $target
     * @param int $runtime
     * @param Formatter $formatter
     * @param Imagick $background
     * @param Font $font
     * @param string $default
     * @param CacheItemPoolInterface $cache
     * @param string $cacheItemClass
     */
    public function __construct(DateTime $now, DateTime $target, $runtime, Formatter $formatter, Imagick $background, Font $font, $default = null, CacheItemPoolInterface $cache = null, $cacheItemClass = null)
    {
        $this->now = $now;
        $this->target = $target;
        $this->runtime = $runtime;
        $this->default = $default;
        $this->formatter = $formatter;
        $this->background = $background;
        $this->font = $font;
        $this->cache = $cache;
        $this->cacheItemClass = $cacheItemClass;
        $this->generateIdentifier();
    }


    /**
     * @param int $posY
     * @param int $posX
     * @return Imagick
     */
    public function generate($posY, $posX)
    {
        $gif = new Imagick();
        $gif->setFormat('gif');
        $draw = $this->font->getImagickDraw();
        for ($i = 0; $i <= $this->getRuntime(); $i++) {
            $frame = $this->generateFrame($draw, $posY, $posX, $this->getDiff() - $i);
            $gif->addImage($frame);
        }
        return $gif;
    }

    /**
     * @param ImagickDraw $draw
     * @param int $posY
     * @param int $posX
     * @param int $seconds
     * @return Imagick
     */
    protected function generateFrame($draw, $posY, $posX, $seconds)
    {
        $seconds = max(0, $seconds);
        $key = $this->getPrefixedKey($seconds);
        if($this->isCacheable() && $this->cache->hasItem($key)) {
            return $this->cache->getItem($key)->get();
        }
        $text = $this->default;
        if (empty($text) || $seconds > 0) {
            $text = $this->formatter->getFormatted($seconds);
        }
        $frame = clone $this->background;
        $dimensions = $frame->queryFontMetrics($draw, $text);
        $posY = $posY + $dimensions['textHeight'] * 0.65 / 2;
        $frame->annotateImage($draw, $posX, $posY, 0, $text);
        $frame->setImageDelay(100);
        $this->cacheFrame($frame);
        return $frame;
    }

    /**
     * @return int
     */
    protected function getDiff()
    {
        return $this->target->getTimestamp() - $this->now->getTimestamp();
    }

    /**
     * @return int
     */
    protected function getRuntime()
    {
        return min($this->runtime, max(0, $this->getDiff()));
    }

    protected function generateIdentifier()
    {
        $array = [
            'target' => [
                'timestamp' => $this->target->getTimestamp(),
                'timezone' => $this->target->getTimezone()->getName(),
            ],
            'default' => $this->default,
            'formatter' => [
                'format' => $this->formatter->getFormat(),
                'pads' => $this->formatter->getPads(),
            ],
            'background' => [
                'width' => $this->background->getImageWidth(),
                'height' => $this->background->getImageHeight(),
            ],
            'font' => [
                'family' => $this->font->getFamily(),
                'size' => $this->font->getSize(),
                'color' => $this->font->getColor(),
            ],
        ];
        $json = json_encode($array);
        $hash = hash('sha256', $json);

        $this->identifier = $hash;
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getPrefixedKey($key)
    {
        return $this->identifier.'_'.$key;
    }

    protected function isCacheable()
    {
        return (is_subclass_of($this->cache,  CacheItemPoolInterface::class) && is_subclass_of($this->cacheItemClass,  CacheItemInterface::class));
    }

    /**
     * @param Imagick $frame
     * @return bool
     */
    protected function cacheFrame(Imagick $frame)
    {
        if(!$this->isCacheable()) {
            return false;
        }
        if(!is_subclass_of($this->cacheItemClass,  CacheItemInterface::class)) {
            return false;
        }
        $item = new $this->cacheItemClass();
        $item->set($frame);
        $item->expiresAt($this->target);
        return $this->cache->save($item);
    }
}
