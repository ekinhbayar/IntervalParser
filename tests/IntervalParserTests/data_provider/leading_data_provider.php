<?php
/**
 * This file contains test data for interval parser with leading data. First element is $input, second
 * is expected TimeInterval object.
 *
 * @todo Add more tests data
 */

use IntervalParser\TimeInterval;

return [
    [
        'foo in 9w8d7h6m5s',
        new TimeInterval(new \DateInterval('P71DT7H6M5S'), 7, 42, 'foo', null),
    ]
];
