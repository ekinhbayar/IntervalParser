<?php declare(strict_types = 1);

namespace IntervalParser;

use \DateInterval;

# Value object representing a time interval.
class TimeInterval
{
    private $intervalOffset;
    private $intervalLength;
    private $leadingData;
    private $trailingData;
    private $interval;

    public function __construct(
        $interval,
        int $intervalOffset,
        int $intervalLength,
        string $leadingData  = null,
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
