<?php declare(strict_types=1);

namespace IntervalParser;

class IntervalIterator implements \Iterator
{
    private $var = [];

    public function __construct($array)
    {
        if (is_array($array)) {
            $this->var = $array;
        }
    }

    public function rewind()
    {
        reset($this->var);
    }

    public function current()
    {
        $var = current($this->var);
        return $var;
    }

    public function key()
    {
        $var = key($this->var);
        return $var;
    }

    public function next()
    {
        $var = next($this->var);
        return $var;
    }

    public function valid()
    {
        $key = key($this->var);
        $var = ($key !== null && $key !== false);
        return $var;
    }
}
