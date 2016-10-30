<?php declare(strict_types = 1);

namespace IntervalParserTests;

use IntervalParser\TimeInterval;
use PHPUnit\Framework\TestCase;

# Value object representing a time interval.
class TimeIntervalTest extends TestCase
{
    public function testGetInterval()
    {
        $dateInterval = new \DateInterval('P1D');
        $timeInterval = new TimeInterval($dateInterval, 1, 2, 'foo', 'bar');

        $this->assertInstanceOf(\DateInterval::class, $timeInterval->getInterval());
        $this->assertSame($dateInterval, $timeInterval->getInterval());
    }

    public function testGetIntervalOffset()
    {
        $timeInterval = new TimeInterval(new \DateInterval('P1D'), 1, 2, 'foo', 'bar');

        $this->assertSame(1, $timeInterval->getIntervalOffset());
    }

    public function testGetIntervalLength()
    {
        $timeInterval = new TimeInterval(new \DateInterval('P1D'), 1, 2, 'foo', 'bar');

        $this->assertSame(2, $timeInterval->getIntervalLength());
    }

    public function testGetLeadingData()
    {
        $timeInterval = new TimeInterval(new \DateInterval('P1D'), 1, 2, 'foo', 'bar');

        $this->assertSame('foo', $timeInterval->getLeadingData());
    }

    public function testGetLeadingDataReturnsEmptyStringWhenNotSet()
    {
        $timeInterval = new TimeInterval(new \DateInterval('P1D'), 1, 2);

        $this->assertSame('', $timeInterval->getLeadingData());
    }

    public function testGetTrailingData()
    {
        $timeInterval = new TimeInterval(new \DateInterval('P1D'), 1, 2, 'foo', 'bar');

        $this->assertSame('bar', $timeInterval->getTrailingData());
    }

    public function testGetTrailingDataReturnsEmptyStringWhenNotSet()
    {
        $timeInterval = new TimeInterval(new \DateInterval('P1D'), 1, 2);

        $this->assertSame('', $timeInterval->getTrailingData());
    }
}
