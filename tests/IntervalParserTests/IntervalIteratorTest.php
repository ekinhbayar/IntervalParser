<?php

namespace IntervalIteratorTest;


use IntervalParser\IntervalIterator;

class IntervalIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testNext()
    {
        $intervalInstance = new IntervalIterator([
            'foo',
            'bar',
        ]);
        $intervalInstance->next();

        $this->assertInstanceOf(\Iterator::class, $intervalInstance);
        $this->assertEquals('bar', $intervalInstance->current());
    }

    public function testCurrent()
    {
        $intervalInstance = new IntervalIterator([
            'foo',
            'bar',
        ]);

        $this->assertEquals('foo', $intervalInstance->current());
    }

    public function testRewind()
    {
        $intervalInstance = new IntervalIterator([
            'foo',
            'bar',
        ]);

        $intervalInstance->next();
        $intervalInstance->rewind();

        $this->assertEquals('foo', $intervalInstance->current());
    }

    public function testKey()
    {
        $intervalInstance = new IntervalIterator([
            1 => 'foo',
            2 => 'bar',
        ]);

        $intervalInstance->key();
        $this->assertEquals(1, $intervalInstance->key());

        $intervalInstance->next();
        $intervalInstance->key();
        $this->assertEquals(2, $intervalInstance->key());
    }

    public function testValid()
    {
        $intervalInstance = new IntervalIterator([
            null => 'foo',
            2 => 'bar',
        ]);

        $intervalInstance->next();
        $this->assertTrue($intervalInstance->valid());
    }
}