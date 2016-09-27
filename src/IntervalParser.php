<?php
/**
 * Main IntervalParser class
 *
 * PHP version 7+
 *
 * @category   IntervalParser
 * @author     Ekin H. Bayar <ekin@coproductivity.com>
 * @version    1.0.0
 */

namespace IntervalParser;

use \DateInterval;
use IntervalParser\IntervalFlags;

class IntervalParser
{

    /**
     * Set of regular expressions utilized to match/replace/validate parts of a given input
     *
     * Thanks a ton to @bwoebi and @pcrov for helping out on all regexes <3
     *
     * If leading text is required, it should separate time and text by in|at
     * ie. foo in 9 weeks 5 days
     *
     * @var string $separatorExpression
     */
    public static $leadingDataSeparator = "/(.*)\s+(?:in)\s+(.*)/ui";

    # Definitions of sub patterns for a valid interval
    public static $intervalSeparatorDefinitions = <<<'REGEX'
    /(?(DEFINE)
      (?<integer>
       (?:\G|(?!\n))
       (\s*\b)?
       \d{1,5}
       \s*
      )
      (?<timepart>
       (?&integer)
       ( s(ec(ond)?s?)?
       | m(on(ths?)?|in(ute)?s?)?
       | h(rs?|ours?)?
       | d(ays?)?
       | w(eeks?)?
       )
      )
    )
REGEX;

    public static $intervalOnly = "^(?<interval>(?&timepart)++)$/uix";

    public static $intervalWithTrailingData = "^(?<interval>(?&timepart)++)(?<trailing>.+)$/uix";

    /**
     * Used to turn a given non-strtotime-compatible time string into a compatible one
     * Only modifies the non-strtotime-compatible time strings provided leaving the rest intact
     *
     * @var string $normalizerExpression
     */
    public static $normalizerExpression = <<<'REGEX'
    ~
    # grab the integer part of time string
    \s? (?<int> \d{1,5}) \s?
    # match only the shortest abbreviations that aren't supported by strtotime
    (?<time>
      (?: s (?=(?:ec(?:ond)?s?)?(?:\b|\d))
        | m (?=(?:in(?:ute)?s?)?(?:\b|\d))
        | h (?=(?:(?:ou)?rs?)?(?:\b|\d))
        | d (?=(?:ays?)?(?:\b|\d))
        | w (?=(?:eeks?)?(?:\b|\d))
        | mon (?=(?:(?:th)?s?)?(?:\b|\d))
      )
    )
    [^\d]*?(?=\b|\d)
    # do only extract start of string
    | (?<text> .+)
    ~uix
REGEX;


    /**
     * Looks for a valid interval along with leading and/or trailing data IF the respective flags are set.
     * TimeInterval is essentially DateInterval with extra information such as interval offset & length, leading/trailing data.
     * TODO: MULTIPLE_INTERVALS is not yet implemented.
     *
     * @param string $input
     * @param int $flags
     * @return TimeInterval
     * @throws \Error|\InvalidArgumentException
     */
    public function findInterval(string $input, int $flags = IntervalFlags::INTERVAL_ONLY) : TimeInterval
    {
        if($flags & IntervalFlags::INTERVAL_ONLY){

            $input = $this->normalizeTimeInterval($input);

            if(preg_match(self::$intervalOnly, $input)){
                $intervalOffset = 0;
                $intervalLength = strlen($input);

                # create and return the interval object
                $interval = DateInterval::createFromDateString($input);
                return new TimeInterval($intervalOffset, $intervalLength, $interval, null, null);
            }

            throw new \InvalidArgumentException("Given input is not a valid interval.");
        }

        if($flags == (IntervalFlags::REQUIRE_LEADING | IntervalFlags::REQUIRE_TRAILING)){

            # Requires the "in" separator, TODO: allow at|this|next too
            $leadingSeparation = preg_match(self::$leadingDataSeparator, $input, $matches, PREG_OFFSET_CAPTURE);
            if(!$leadingSeparation){
                throw new \Error("Allowing leading data requires using a separator. Ie. foo in <interval>");
            }

            $leadingData = $matches[1][0] ?? null;
            $intervalAndTrailingData = $matches[2][0] ?? null;

            # throw early for missing parts
            if(!$leadingData){
                throw new \InvalidArgumentException("Given input does not contain a valid leading data.");
            }
            if(!$intervalAndTrailingData){
                throw new \InvalidArgumentException("Given input does not contain a valid interval and/or trailing data.");
            }

            $intervalOffset = $matches[2][1] ?? null;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $intervalAndTrailingData = $this->normalizeTimeInterval($intervalAndTrailingData);

            $expression = self::$intervalSeparatorDefinitions . self::$intervalWithTrailingData;

            if(preg_match($expression, $intervalAndTrailingData, $parts)){

                $interval = $parts['interval'];
                $trailingData   = $parts['trailing'];
                $intervalLength = strlen($interval);

                # create and return the interval object
                $interval = DateInterval::createFromDateString($interval);
                return new TimeInterval($intervalOffset, $intervalLength, $interval, $leadingData, $trailingData);
            }

            throw new \InvalidArgumentException("Given input does not contain a valid interval and/or trailing data.");
        }

        if($flags & IntervalFlags::REQUIRE_LEADING){

            # Requires the "in" separator, TODO: allow at|this|next too
            $leadingSeparation = preg_match(self::$leadingDataSeparator, $input, $matches, PREG_OFFSET_CAPTURE);
            if(!$leadingSeparation){
                throw new \Error("Allowing leading data requires using a separator. Ie. foo in <interval>");
            }

            $leadingData = $matches[1][0] ?? null;
            $intervalAndPossibleTrailingData = $matches[2][0] ?? null;

            if(!$leadingData || !$intervalAndPossibleTrailingData){
                throw new \Error("Could not find any valid interval and/or leading data.");
            }

            $intervalOffset = $matches[2][1] ?? null;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $safeInterval = $this->normalizeTimeInterval($intervalAndPossibleTrailingData);

            # since above normalization is expected to not return any trailing data, only check for a valid interval
            $expression = self::$intervalSeparatorDefinitions . self::$intervalOnly;

            if(preg_match($expression, $safeInterval, $parts)){
                $interval = $parts['interval'];
                $intervalLength = strlen($interval);

                # create the interval object
                $interval = DateInterval::createFromDateString($interval);
                return new TimeInterval($intervalOffset, $intervalLength, $interval, $leadingData, null);
            }

            throw new \InvalidArgumentException("Given input does not contain a valid interval. Keep in mind trailing data is not allowed with currently specified flag.");
        }

        if($flags & IntervalFlags::REQUIRE_TRAILING){

            $expression = self::$intervalSeparatorDefinitions . self::$intervalWithTrailingData;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $safeInterval = $this->normalizeTimeInterval($input);

            # Separate interval from trailing data
            if(preg_match($expression, $safeInterval, $parts)){
                $trailingData = $parts['trailing'] ?? null;
                $interval = $parts['interval'] ?? null;

                if(!$trailingData || !$interval){
                    throw new \Error("Could not find any valid interval and/or trailing data...");
                }

                $intervalLength = strlen($interval);
                $intervalOffset = 0; # since we don't allow leading data here

                # create the interval object
                $interval = DateInterval::createFromDateString($interval);
                return new TimeInterval($intervalOffset, $intervalLength, $interval, null, $trailingData);
            }

            throw new \InvalidArgumentException("Given input does not contain a valid interval. Keep in mind leading data is not allowed with currently specified flag.");
        }

        if($flags & IntervalFlags::MULTIPLE_INTERVALS){
            throw new \Error("I'm sorry, multiple intervals is not allowed/implemented, yet.");
        }

        if( $flags
            & ~IntervalFlags::INTERVAL_ONLY
            & ~IntervalFlags::REQUIRE_TRAILING
            & ~IntervalFlags::REQUIRE_LEADING
            & ~IntervalFlags::MULTIPLE_INTERVALS
        ){  throw new \InvalidArgumentException("You have tried to use an invalid flag combination."); }

    }

    /**
     * Turns any non-strtotime-compatible time string into a compatible one.
     * If the passed input has trailing data, it won't be lost since within the callback the input is reassembled.
     * However no leading data is accepted.
     *
     * @param string $input
     * @return string
     */
    public function normalizeTimeInterval(string $input): string
    {
        $output = preg_replace_callback(self::$normalizerExpression,
            function ($matches) {
                switch ($matches['time']) {
                    case 's':
                        $t = ' seconds ';
                        break;
                    case 'm':
                        $t = ' minutes ';
                        break;
                    case 'h':
                        $t = ' hours ';
                        break;
                    case 'd':
                        $t = ' days ';
                        break;
                    case 'w':
                        $t = ' weeks ';
                        break;
                    case 'mon':
                        $t = ' months ';
                        break;
                    case 'y':
                        $t = ' years ';
                        break;
                }

                $t = $t ?? '';
                # rebuild the interval string
                $time = $matches['int'] . $t;

                if(isset($matches['text'])){
                    $time .= $matches['text'];
                }

                return $time;

            }, $input);

        return trim($output);
    }

    /**
     * Normalizes any non-strtotime-compatible time string, then validates the interval and returns a DateInterval object.
     * No leading or trailing data is accepted.
     *
     * @param string $input
     * @return DateInterval
     * @throws \InvalidArgumentException
     */
    public function parseInterval(string $input): \DateInterval
    {
        $input = trim($this->normalizeTimeInterval($input));

        $expression = self::$intervalSeparatorDefinitions . self::$intervalOnly;

        if(preg_match($expression, $input, $matches)){
            return DateInterval::createFromDateString($input);
        }

        throw new \InvalidArgumentException("Given string is not a valid time interval.");
    }

}

