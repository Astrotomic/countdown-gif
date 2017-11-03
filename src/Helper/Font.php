<?php

namespace Astrotomic\CountdownGif\Helper;

use Imagick;
use ImagickDraw;
use ImagickPixel;
use InvalidArgumentException;

class Font
{
    /**
     * @var string
     */
    protected $family;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var string
     */
    protected $color;

    /**
     * @var array
     */
    protected $fonts = [];

    /**
     * Font constructor.
     * @param string $family
     * @param int $size
     * @param string $color
     * @param array $fonts
     */
    public function __construct($family, $size, $color, array $fonts)
    {
        $this->setFamily($family);
        $this->setSize($size);
        $this->setColor($color);
        foreach ($fonts as $fontFamily => $fontFile) {
            $this->addFont($fontFamily, $fontFile);
        }
    }

    /**
     * @return string
     */
    public function getFamily()
    {
        return $this->family;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $family
     * @return Font
     */
    public function setFamily($family)
    {
        $this->family = $family;

        return $this;
    }

    /**
     * @param int $size
     * @return Font
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * @param string $color
     * @return Font
     */
    public function setColor($color)
    {
        $this->color = $color;

        return $this;
    }

    /**
     * @param string $family
     * @param string $file
     * @return Font
     */
    public function addFont($family, $file)
    {
        $file = realpath($file);
        if ($file) {
            $this->fonts[$family] = $file;
        }

        return $this;
    }

    /**
     * @return string
     * @throws InvalidArgumentException
     */
    protected function getFontFile()
    {
        if (array_key_exists($this->getFamily(), $this->fonts)) {
            return $this->fonts[$this->family];
        }
        throw new InvalidArgumentException(sprintf('There is no font file for selected family [%s].', $this->getFamily()));
    }

    /**
     * @return ImagickDraw
     */
    public function getImagickDraw()
    {
        $draw = new \ImagickDraw();
        $draw->setStrokeAntialias(true);
        $draw->setTextAntialias(true);
        $draw->setFont($this->getFontFile());
        $draw->setFontSize($this->getSize());
        $draw->setFillColor(new ImagickPixel($this->getColor()));
        $draw->setTextAlignment(Imagick::ALIGN_CENTER);

        return $draw;
    }
}
