<?php
/**
 * This file contains test data for interval parser with trailing data. First element is $input, second
 * is expected TimeInterval object.
 *
 * @todo Add more tests data
 */

use IntervalParser\TimeInterval;

return [
    [
        '7mon6w5d4h3m2s bazinga!',
        new TimeInterval(new \DateInterval('P7M47DT4H3M2S'), 0, 51, null, ' bazinga!'),
    ],
    [
        '31d12h30m0s foo',
        new TimeInterval(new \DateInterval('P31DT12H30M'), 0, 37, null, ' foo'),
    ],
];
