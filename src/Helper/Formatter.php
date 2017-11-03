<?php

namespace Astrotomic\CountdownGif\Helper;

class Formatter
{
    const PLACEHOLDER_DAY = '{d}';
    const PLACEHOLDER_HOUR = '{h}';
    const PLACEHOLDER_MINUTE = '{m}';
    const PLACEHOLDER_SECOND = '{s}';

    const DAY = 'day';
    const HOUR = 'hour';
    const MINUTE = 'minute';
    const SECOND = 'second';

    /**
     * @var string
     */
    protected $format = self::PLACEHOLDER_DAY . ':' . self::PLACEHOLDER_HOUR . ':' . self::PLACEHOLDER_MINUTE . ':' . self::PLACEHOLDER_SECOND;

    /**
     * @var array
     */
    protected $pads = [];

    public function __construct($format = null, array $pads = [])
    {
        $this->setFormat($format);

        foreach ($pads as $unit => $pad) {
            $this->setPad($unit, $pad);
        }
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return Formatter
     */
    public function setFormat($format)
    {
        if (is_string($format)) {
            $this->format = $format;
        }

        return $this;
    }

    public function getPads()
    {
        return $this->pads;
    }

    /**
     * @param string $unit
     * @return int
     */
    public function getPad($unit)
    {
        if (array_key_exists($unit, $this->pads)) {
            return $this->pads[$unit];
        }

        return 2;
    }

    /**
     * @param string $unit
     * @param int $pad
     * @return Formatter
     */
    public function setPad($unit, $pad)
    {
        $this->pads[$unit] = $pad;

        return $this;
    }

    /**
     * @param int $seconds
     * @return string
     */
    public function getFormatted($seconds)
    {
        $units = $this->splitToUnits($seconds);
        return str_replace(array_keys($units), array_values($units), $this->getFormat());
    }

    /**
     * @param int $seconds
     * @return array
     */
    protected function splitToUnits($seconds)
    {
        $days = floor($seconds / (24 * 60 * 60));
        $seconds -= $days * (24 * 60 * 60);
        $hours = floor($seconds / (60 * 60));
        $seconds -= $hours * (60 * 60);
        $minutes = floor($seconds / 60);
        $seconds -= $minutes * 60;

        return [
            '{d}' => str_pad($days, $this->getPad(self::DAY), '0', STR_PAD_LEFT),
            '{h}' => str_pad($hours, $this->getPad(self::HOUR), '0', STR_PAD_LEFT),
            '{m}' => str_pad($minutes, $this->getPad(self::MINUTE), '0', STR_PAD_LEFT),
            '{s}' => str_pad($seconds, $this->getPad(self::SECOND), '0', STR_PAD_LEFT),
        ];
    }
}
