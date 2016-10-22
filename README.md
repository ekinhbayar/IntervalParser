# IntervalParser

This is a work in progress.

---

This library provides three classes:
 
 1. `IntervalFinder`, finds time intervals and any leading/trailing data, returns it as a TimeInterval object.
 
  `IntervalFinder::find(string $input, int $flags) : TimeInterval`
 
 2. `Parser`, takes a string and returns it as a DateInterval after passing it through the 3# method below.
 
  `Parser::parse(string $input): \DateInterval`
 
 3. `Normalizer`, finds and replaces non-strtotime-compatible abbreviations with compatible ones. It does not accept leading data, though it will return trailing data and already compatible abbreviations intact.
 
  `Normalizer::normalize(string $input): string`
 
---

Allowed flags for `IntervalFinder::find` method are:

```
class IntervalFlags
{
    const INTERVAL_ONLY      = 0b00000000;
    const REQUIRE_TRAILING   = 0b00000001;
    const REQUIRE_LEADING    = 0b00000010;
    const MULTIPLE_INTERVALS = 0b00000100;
}
```

`IntervalFinder` takes a `ParserSettings` object that allows you to set which separators to use, defaults being:

```
string $leadingSeparationString = "in",
bool $keepLeadingSeparator = false,
int $multipleSeparationType = self::SYMBOL,
string $multipleSeparationSymbol  = ",",
string $multipleSeparationWord = null
```

---
 
The `TimeInterval` is a value object that represents an interval of time. It is pretty much a DateInterval that holds extra information:
 
```
  int $intervalOffset
  int $intervalLength
  DateInterval $interval
  string $leadingData
  string $trailingData
```
---

**Installation**

`$ composer require ekinhbayar/interval-parser`


**Basic usage**

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

# Set ParserSettings for IntervalFinder
$settings = new ParserSettings("in", false);
$intervalFinder = new IntervalFinder($settings, new Normalizer());

$intervalAndTrailing = $intervalFinder->find($trailing, IntervalFlags::REQUIRE_TRAILING);
var_dump($intervalAndTrailing);

$intervalAndLeading = $intervalFinder->find($leading, IntervalFlags::REQUIRE_LEADING);
var_dump($intervalAndLeading);

$intervalWithBoth = $intervalFinder->find($both, IntervalFlags::REQUIRE_TRAILING | IntervalFlags::REQUIRE_LEADING);
var_dump($timeIntervalWithBoth);

$intervalParser = new Parser(new Normalizer());

$dateInterval = $intervalParser->parse($onlyInterval);
var_dump($dateInterval);

# Multiple Intervals

# 1. Comma Separated
$multiple = 'foo in 9d8h5m bar , baz in 5 minutes, foo in 2 days 4 minutes boo, in 1 hr, 10 days';
$multipleIntervals = $intervalFinder->find($multiple, IntervalFlags::MULTIPLE_INTERVALS);
var_dump($multipleIntervals);

# 2. Separated by a defined-on-settings word

$settings = new ParserSettings("in", 1, ',', 'then');
$intervalFinder = new IntervalFinder($settings);

$wordSeparated = 'foo in 9d8h5m bar then baz in 5 minutes then foo in 2 days 4 minutes boo then in 1 hr then 10 days';
$wordSeparatedIntervals = $intervalFinder->find($wordSeparated, IntervalFlags::MULTIPLE_INTERVALS);
var_dump($wordSeparatedIntervals);
```

---

Thanks a ton to [Pieter](https://github.com/PeeHaa), [Chris](https://github.com/DaveRandom), [Bob](https://github.com/bwoebi),  [Paul](https://github.com/pcrov) for everything they made me learn and for all their help! :-)
