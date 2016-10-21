<?php
/**
 * Main IntervalParser class
 *
 * PHP version 7+
 *
 * @category   IntervalParser
 * @author     Ekin H. Bayar <ekin@coproductivity.com>
 * @version    0.2.0
 */
namespace IntervalParser;

use \DateInterval;
use IntervalParser\IntervalFlags;
use IntervalParser\ParserSettings;

class IntervalParser
{
    /**
     * Set of regular expressions utilized to match/replace/validate parts of a given input.
     *
     * Don't forget to close parentheses when using $define
     */
    public static $define = "/(?(DEFINE)";

    # Definitions of sub patterns for a valid interval
    public static $integer  = <<<'REGEX'
    (?<integer>
       (?:\G|(?!\n))
       (\s*\b)?
       \d{1,5}
       \s*
    )
REGEX;

    # Starts with integer followed by time specified
    public static $timepart = <<<'REGEX'
    (?<timepart>
       (?&integer)
       ( s(ec(ond)?s?)?
       | m(on(ths?)?|in(ute)?s?)?
       | h(rs?|ours?)?
       | d(ays?)?
       | w(eeks?)?
       )
      )
REGEX;

    # Leading separator
    public static $leadingSeparator = "(?<leadingSeparator>\s?(?:in)\s?)";

    # Regex to match a valid interval, holds the value in $matches['interval']
    public static $intervalOnly = "^(?<interval>(?&timepart)++)$/uix";

    # Regex to match a valid interval and any trailing string, holds the interval in $matches['interval'], the rest in $matches['trailing']
    public static $intervalWithTrailingData = "^(?<interval>(?&timepart)++)(?<trailing>.+)*?$/uix";

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

    # Regex to handle an input that may have multiple intervals along with leading and/or trailing data
    public static $multipleIntervals = <<<'REGEX'
    ^(?<leading>.*?)?
     (?<sep>(?&leadingSeparator))?
     (?<interval>(?&timepart)++)
     (?<trailing>.*)
    /uix
REGEX;

    /**
     * IntervalParser constructor.
     *
     * Default settings are :
     *
     *  string $symbolSeparator  = ',',
     *  string $wordSeparator = null
     *
     * @param \IntervalParser\ParserSettings $settings
     */
    public function __construct(ParserSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Looks for a valid interval along with leading and/or trailing data IF the respective flags are set.
     * TimeInterval is essentially DateInterval with extra information such as interval offset & length, leading/trailing data.
     *
     * @param string $input
     * @param int $flags
     * @return TimeInterval|array
     * @throws FormatException
     */
    public function findInterval(string $input, int $flags = IntervalFlags::INTERVAL_ONLY)
    {
        if( $flags
            & ~IntervalFlags::INTERVAL_ONLY
            & ~IntervalFlags::REQUIRE_TRAILING
            & ~IntervalFlags::REQUIRE_LEADING
            & ~IntervalFlags::MULTIPLE_INTERVALS
        ){  throw new InvalidFlagException("You have tried to use an invalid flag combination."); }

        if($flags & IntervalFlags::INTERVAL_ONLY){

            $input = $this->normalizeTimeInterval($input);

            $definition = self::$define . self::$integer . self::$timepart .')';
            $expression = $definition . self::$intervalOnly;

            if(preg_match($expression, $input)){
                $intervalOffset = 0;
                $intervalLength = strlen($input);

                # create and return the interval object
                $interval = DateInterval::createFromDateString($input);
                return new TimeInterval($intervalOffset, $intervalLength, null, null, $interval);
            }

            throw new FormatException("Given input is not a valid interval.");
        }

        if($flags == (IntervalFlags::REQUIRE_LEADING | IntervalFlags::REQUIRE_TRAILING)){

            $expression = $this->settings->getLeadingSeparatorExpression();

            $leadingSeparation = preg_match($expression, $input, $matches, PREG_OFFSET_CAPTURE);
            if(!$leadingSeparation){
                throw new FormatException("Allowing leading data requires using a separator. Ie. foo in <interval>");
            }

            $leadingData = $matches[1][0] ?? null;
            $intervalAndTrailingData = $matches[2][0] ?? null;

            # throw early for missing parts
            if(!$leadingData){
                throw new FormatException("Given input does not contain a valid leading data.");
            }
            if(!$intervalAndTrailingData){
                throw new FormatException("Given input does not contain a valid interval and/or trailing data.");
            }

            $intervalOffset = $matches[2][1] ?? null;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $intervalAndTrailingData = $this->normalizeTimeInterval($intervalAndTrailingData);

            $definition = self::$define . self::$integer . self::$timepart .')';
            $expression = $definition . self::$intervalWithTrailingData;

            if(preg_match($expression, $intervalAndTrailingData, $parts)){

                $interval = $parts['interval'];
                $trailingData   = $parts['trailing'];
                $intervalLength = strlen($interval);

                # create and return the interval object
                $interval = DateInterval::createFromDateString($interval);
                return new TimeInterval($intervalOffset, $intervalLength, $leadingData, $trailingData,  $interval);
            }

            throw new FormatException("Given input does not contain a valid interval and/or trailing data.");
        }

        if($flags & IntervalFlags::REQUIRE_LEADING){

            $expression = $this->settings->getLeadingSeparatorExpression();

            $leadingSeparation = preg_match($expression, $input, $matches, PREG_OFFSET_CAPTURE);
            if(!$leadingSeparation){
                throw new FormatException("Allowing leading data requires using a separator. Ie. foo in <interval>");
            }

            $leadingData = $matches[1][0] ?? null;
            $intervalAndPossibleTrailingData = $matches[2][0] ?? null;

            if(!$leadingData){
                throw new FormatException("Could not find any valid leading data.");
            }

            if(!$intervalAndPossibleTrailingData){
                throw new FormatException("Could not find any valid interval and/or leading data.");
            }

            $intervalOffset = $matches[2][1] ?? null;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $safeInterval = $this->normalizeTimeInterval($intervalAndPossibleTrailingData);

            # since above normalization is expected to not return any trailing data, only check for a valid interval
            $definition = self::$define . self::$integer . self::$timepart .')';
            $expression = $definition . self::$intervalOnly;

            if(preg_match($expression, $safeInterval, $parts)){
                $interval = $parts['interval'];
                $intervalLength = strlen($interval);

                # create the interval object
                $interval = DateInterval::createFromDateString($interval);
                return new TimeInterval($intervalOffset, $intervalLength, $leadingData, null, $interval);
            }

            throw new FormatException("Given input does not contain a valid interval. Keep in mind trailing data is not allowed with current flag.");
        }

        if($flags & IntervalFlags::REQUIRE_TRAILING){

            $definition = self::$define . self::$integer . self::$timepart .')';
            $expression = $definition . self::$intervalWithTrailingData;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $safeInterval = $this->normalizeTimeInterval($input);

            # Separate interval from trailing data
            if(preg_match($expression, $safeInterval, $parts)){
                $trailingData = $parts['trailing'] ?? null;
                $interval = $parts['interval'] ?? null;

                if(!$interval){
                    throw new FormatException("Could not find any valid interval.");
                }

                if(!$trailingData){
                    throw new FormatException("Could not find any valid trailing data.");
                }

                $intervalLength = strlen($interval);
                $intervalOffset = 0; # since we don't allow leading data here

                # create the interval object
                $interval = DateInterval::createFromDateString($interval);
                return new TimeInterval($intervalOffset, $intervalLength, null, $trailingData, $interval);
            }

            throw new FormatException("Given input does not contain a valid interval. Keep in mind leading data is not allowed with current flag.");
        }

        if($flags & IntervalFlags::MULTIPLE_INTERVALS){

            $payload = [];
            $separator = ($this->settings->getSeparationType() == 'symbol')
                ? $this->settings->getSymbolSeparator()
                : $this->settings->getWordSeparator();

            $expression = "/(?J)\b(?:(?<match>.*?)\s?{$separator})\s?|\b(?<match>.*)/ui";

            if(preg_match_all($expression, $input, $intervals, PREG_SET_ORDER)){

                $intervalSet = array_filter(array_map(function($set){
                    foreach($iter = new IntervalIterator($set) as $key => $interval){
                        if($iter->key() === 'match') {
                            return $interval;
                        }
                    }
                }, $intervals));

                foreach($intervalSet as $key => $interval){

                    $definition = self::$define . self::$leadingSeparator . self::$integer . self::$timepart .')';
                    $expression = $definition . self::$multipleIntervals;

                    preg_match($expression, $interval, $matches);
                    $matches = array_filter($matches);

                    $leadingData = $matches['leading'] ?? null;
                    $leadingSep  = $matches['sep'] ?? null;
                    $interval    = $matches['interval'] ?? null;
                    $trailing    = $matches['trailing'] ?? null;

                    if(!$leadingData) $leadingData = $leadingSep ?? "";

                    $intervalOffset = (!$leadingSep) ? 0 : strlen($leadingData) + strlen($leadingSep);

                    # If interval contains non-strtotime-compatible abbreviations, replace them
                    $safeInterval = $this->normalizeTimeInterval($interval . $trailing);

                    # Separate intervals from trailing data
                    if(preg_match($expression, $safeInterval, $parts)){
                        $trailingData = $parts['trailing'] ?? null;
                        $interval = $parts['interval'] ?? null;
                        if(!$interval) continue;

                        $intervalLength = strlen($interval);
                        # create the interval object
                        $interval = DateInterval::createFromDateString($interval);
                        $payload[] =  new TimeInterval($intervalOffset, $intervalLength, $leadingData, $trailingData, $interval);
                    }
                }

                if($payload) return $payload;
            }
        }
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
                $int = $matches['int'];
                switch ($matches['time']) {
                    case 's':
                        $t = ($int == 1) ? ' second ' : ' seconds ';
                        break;
                    case 'm':
                        $t = ($int == 1) ? ' minute ' : ' minutes ';
                        break;
                    case 'h':
                        $t = ($int == 1) ? ' hour ' : ' hours ';
                        break;
                    case 'd':
                        $t = ($int == 1) ? ' day ' : ' days ';
                        break;
                    case 'w':
                        $t = ($int == 1) ? ' week ' : ' weeks ';
                        break;
                    case 'mon':
                        $t = ($int == 1) ? ' month ' : ' months ';
                        break;
                    case 'y':
                        $t = ($int == 1) ? ' year ' : ' years ';
                        break;
                }

                $t = $t ?? '';
                # rebuild the interval string
                $time = $int . $t;

                if(isset($matches['text'])){
                    $time .= trim($matches['text']);
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

        $definition = self::$define . self::$integer . self::$timepart .')';
        $expression = $definition . self::$intervalOnly;

        if(preg_match($expression, $input, $matches)){
            return DateInterval::createFromDateString($input);
        }

        throw new FormatException("Given string is not a valid time interval.");
    }
}

