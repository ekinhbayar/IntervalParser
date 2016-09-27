# IntervalParser

This is a work in progress.

Basic usage:

```php
$trailingString = '7mon6w5d4h3m2s bazinga!';
$leadingString = 'foo in 9w8d7h6m5s';
$both = 'foo in 9d8h5m bar';
$onlyInterval = '9 mon 2 w 3 m 4 d';

$intervalParser = new TimeParser();

$timeIntervalWithTrailingData = $intervalParser->findInterval($trailingString, IntervalFlags::REQUIRE_TRAILING);
var_dump($timeIntervalWithTrailingData);

$timeIntervalWithLeadingData = $intervalParser->findInterval($leadingString, IntervalFlags::REQUIRE_LEADING);
var_dump($timeIntervalWithLeadingData);

$timeIntervalWithBoth = $intervalParser->findInterval($both, IntervalFlags::REQUIRE_TRAILING | IntervalFlags::REQUIRE_LEADING);
var_dump($timeIntervalWithBoth);

$dateInterval = $intervalParser->parseInterval($onlyInterval);
var_dump($dateInterval);
```