<?php declare(strict_types = 1);

namespace IntervalParser;

class IntervalIterator implements \Iterator
{
    private $intervals = [];

    public function __construct(array $intervals)
    {
        $this->intervals = $intervals;
    }

    public function rewind()
    {
        reset($this->intervals);
    }

    public function current()
    {
        return current($this->intervals);
    }

    public function key()
    {
        return key($this->intervals);
    }

    public function next()
    {
        return next($this->intervals);
    }

    public function valid()
    {
        return key($this->intervals) !== null;
    }
}
