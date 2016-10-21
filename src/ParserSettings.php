<?php

namespace IntervalParser;

/**
 * Class ParserSettings
 * @package IntervalParser
 *
 * @property string $leadingDataSeparator Leading data separator, default 'in'
 */
class ParserSettings extends \ArrayObject
{
    /**
     * @inheritdoc
     */
    public function __construct($input = [], $flags = 0, $iterator_class = 'ArrayIterator')
    {
        $input = $this->initDefaultSettings($input);

        parent::__construct($input, $flags, $iterator_class);
    }

    /**
     * Initialize input array with default setting
     *
     * @param array $input
     * @return array
     */
    protected function initDefaultSettings(array $input) : array
    {
        if (is_array($input)) {
            if (!isset($input['leadingDataSeparator'])) {
                $input['leadingDataSeparator'] = 'in';
            }
        }

        return $input;
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        if (!$this->offsetExists($name)) {
            throw new \InvalidArgumentException("Undefined index {$name} in ParserSettings");
        }

        return $this->offsetGet($name);
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        $this->offsetSet($name, $value);
    }
}