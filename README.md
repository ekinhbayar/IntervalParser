# IntervalParser

This is a work in progress.

---

Implements three methods which are:
 
 1. Interval Finder, finds time intervals and any leading/trailing data, returns it as a TimeInterval object.
 
  `findInterval(string $input, int $flags) : TimeInterval`
 
 2. Interval Parser, takes a string and returns it as a DateInterval after passing it through the 3# method below.
 
  `parseInterval(string $input): \DateInterval`
 
 3. Interval Normalizer, finds and replaces non-strtotime-compatible abbreviations with compatible ones. It does not accept leading data, though it will return trailing data and already compatible abbreviations intact.
 
  `normalizeTimeInterval(string $input): string`
 
---

Allowed flags for findInterval method are:

```
class IntervalFlags
{
    const INTERVAL_ONLY      = 0b00000000;
    const REQUIRE_TRAILING   = 0b00000001;
    const REQUIRE_LEADING    = 0b00000010;
    const MULTIPLE_INTERVALS = 0b00000100;
}
```

**Notes:** 
 - At the moment, the `MULTIPLE_INTERVALS` flag will throw an Error. This feature is not implemented, yet.
 - When leading data is allowed, you must separate it by "in" before specifying an interval.

---
 
The `TimeInterval` is a value object that represents an interval of time. It is pretty much a DateInterval that holds extra information:
 
```
  int $intervalOffset
  int $intervalLength
  DateInterval $interval
  string $leadingData
  string $trailingData
```


Basic usage:

```php
/**
 * Some other example inputs:
 *
 * foo in 7 weeks 8 days
 * 9 months 8 weeks 7 days 6 hours 5 minutes 2 seconds baz
 * remind me I have 10 minutes in 2 hours please
 *
 */
$trailing = '7mon6w5d4h3m2s bazinga!';
$leading  = 'foo in 9w8d7h6m5s';
$both = 'foo in 9d8h5m bar';
$onlyInterval = '9 mon 2 w 3 m 4 d';

$intervalParser = new IntervalParser();

$intervalAndTrailing = $intervalParser->findInterval($trailing, IntervalFlags::REQUIRE_TRAILING);
var_dump($intervalAndTrailing);

$intervalAndLeading = $intervalParser->findInterval($leading, IntervalFlags::REQUIRE_LEADING);
var_dump($intervalAndLeading);

$intervalWithBoth = $intervalParser->findInterval($both, IntervalFlags::REQUIRE_TRAILING | IntervalFlags::REQUIRE_LEADING);
var_dump($timeIntervalWithBoth);

$dateInterval = $intervalParser->parseInterval($onlyInterval);
var_dump($dateInterval);
```

---

Thanks a ton to @bwoebi, @pcrov, @PeeHaa, @DaveRandom for everything they made me learn and for all the help! :)