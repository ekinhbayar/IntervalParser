<?php declare(strict_types = 1);
/**
 * Value object representing a time interval.
 */

namespace IntervalParser;

use \DateInterval;

class TimeInterval
{
    private $intervalOffset;
    private $intervalLength;
    private $interval;
    private $leadingData;
    private $trailingData;

    /**
     * TimeInterval constructor.
     *
     * @param int $intervalOffset
     * @param int $intervalLength
     * @param DateInterval $interval
     * @param string|null $leadingData
     * @param string|null $trailingData
     */
    public function __construct(
        int $intervalOffset,
        int $intervalLength,
        DateInterval $interval,
        string $leadingData = null,
        string $trailingData = null
    )
    {
        $this->interval = $interval;
        $this->intervalOffset = $intervalOffset;
        $this->intervalLength = $intervalLength;
        $this->leadingData  = $leadingData;
        $this->trailingData = $trailingData;
    }

    public function getInterval() : DateInterval
    {
        return $this->interval;
    }

    public function getIntervalOffset() : int
    {
        return $this->intervalOffset;
    }

    public function getIntervalLength() : int
    {
        return $this->intervalLength;
    }

    public function getLeadingData() : string
    {
        return $this->leadingData;
    }

    public function getTrailingData() : string
    {
        return $this->trailingData;
    }

}

