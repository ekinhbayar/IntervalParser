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
        'foo in 9d8h5m bar',
        new TimeInterval(new \DateInterval('P9DT8H5M'), 7, 24, 'foo', ' bar'),
    ]
];