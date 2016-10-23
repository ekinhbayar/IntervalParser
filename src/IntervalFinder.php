<?php declare(strict_types = 1);
/**
 * Finds intervals inside strings
 *
 * PHP version 7+
 *
 * @category   IntervalParser
 * @author     Ekin H. Bayar <ekin@coproductivity.com>
 * @version    0.2.0
 */
namespace IntervalParser;

class IntervalFinder
{
    # Leading separator
    const LEADING_SEPARATOR = "(?<leadingSeparator>\s?(?:in)\s?)";

    # Regex to match a valid interval and any trailing string, holds the interval in $matches['interval'], the rest in $matches['trailing']
    const INTERVAL_WITH_TRAILING_DATA = "^(?<interval>(?&timepart)++)(?<trailing>.+)*?$/uix";

    # Regex to handle an input that may have multiple intervals along with leading and/or trailing data
    const MULTIPLE_INTERVALS = <<<'REGEX'
    ^(?<leading>.*?)?
     (?<sep>(?&leadingSeparator))?
     (?<interval>(?&timepart)++)
     (?<trailing>.*)
    /uix
REGEX;

    /**
     * @var ParserSettings
     */
    private $settings;

    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * IntervalFinder constructor.
     *
     * Default settings are :
     *
     *  string $symbolSeparator  = ',',
     *  string $wordSeparator = null
     *
     * @param \IntervalParser\ParserSettings $settings
     * @param \IntervalParser\Normalizer $normalizer
     */
    public function __construct(ParserSettings $settings, Normalizer $normalizer)
    {
        $this->settings   = $settings;
        $this->normalizer = $normalizer;
    }

    /**
     * Looks for a valid interval along with leading and/or trailing data IF the respective flags are set.
     * TimeInterval is essentially DateInterval with extra information such as interval offset & length, leading/trailing data.
     *
     * @param string $input
     * @param int $flags
     * @return TimeInterval|array
     * @throws InvalidFlagException
     * @throws FormatException
     */
    public function find(string $input, int $flags = IntervalFlags::INTERVAL_ONLY)
    {
        if ($flags
            & ~IntervalFlags::INTERVAL_ONLY
            & ~IntervalFlags::REQUIRE_TRAILING
            & ~IntervalFlags::REQUIRE_LEADING
            & ~IntervalFlags::MULTIPLE_INTERVALS
        ) {  throw new InvalidFlagException("You have tried to use an invalid flag combination."); }

        if ($flags & IntervalFlags::INTERVAL_ONLY) {

            $input = $this->normalizer->normalize($input);

            $definition = Pattern::DEFINE . Pattern::INTEGER . Pattern::TIME_PART . ')';
            $expression = $definition . Pattern::INTERVAL_ONLY;

            if (preg_match($expression, $input)) {
                $intervalOffset = 0;
                $intervalLength = strlen($input);

                # create and return the interval object
                $interval = \DateInterval::createFromDateString($input);
                return new TimeInterval($interval, $intervalOffset, $intervalLength);
            }

            throw new FormatException("Given input is not a valid interval.");
        }

        if ($flags == (IntervalFlags::REQUIRE_LEADING | IntervalFlags::REQUIRE_TRAILING)) {

            $expression = $this->settings->getLeadingSeparatorExpression();

            $leadingSeparation = preg_match($expression, $input, $matches, PREG_OFFSET_CAPTURE);
            if (!$leadingSeparation) {
                throw new FormatException("Allowing leading data requires using a separator. Ie. foo in <interval>");
            }

            $leadingData = $matches[1][0] ?? null;
            $intervalAndTrailingData = $matches[2][0] ?? null;

            # throw early for missing parts
            if (!$leadingData) {
                throw new FormatException("Given input does not contain a valid leading data.");
            }
            if (!$intervalAndTrailingData) {
                throw new FormatException("Given input does not contain a valid interval and/or trailing data.");
            }

            $intervalOffset = $matches[2][1] ?? null;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $intervalAndTrailingData = $this->normalizer->normalize($intervalAndTrailingData);

            $definition = Pattern::DEFINE . Pattern::INTEGER . Pattern::TIME_PART . ')';
            $expression = $definition . self::INTERVAL_WITH_TRAILING_DATA;

            if (preg_match($expression, $intervalAndTrailingData, $parts)) {

                $interval = $parts['interval'];
                $trailingData   = $parts['trailing'];
                $intervalLength = strlen($interval);

                # create and return the interval object
                $interval = \DateInterval::createFromDateString($interval);
                return new TimeInterval($interval, $intervalOffset, $intervalLength, $leadingData, $trailingData);
            }

            throw new FormatException("Given input does not contain a valid interval and/or trailing data.");
        }

        if ($flags & IntervalFlags::REQUIRE_LEADING) {

            $expression = $this->settings->getLeadingSeparatorExpression();

            $leadingSeparation = preg_match($expression, $input, $matches, PREG_OFFSET_CAPTURE);
            if (!$leadingSeparation) {
                throw new FormatException("Allowing leading data requires using a separator. Ie. foo in <interval>");
            }

            $leadingData = $matches[1][0] ?? null;
            $intervalAndPossibleTrailingData = $matches[2][0] ?? null;

            if (!$leadingData) {
                throw new FormatException("Could not find any valid leading data.");
            }

            if (!$intervalAndPossibleTrailingData) {
                throw new FormatException("Could not find any valid interval and/or leading data.");
            }

            $intervalOffset = $matches[2][1] ?? null;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $safeInterval = $this->normalizer->normalize($intervalAndPossibleTrailingData);

            # since above normalization is expected to not return any trailing data, only check for a valid interval
            $definition = Pattern::DEFINE . Pattern::INTEGER . Pattern::TIME_PART . ')';
            $expression = $definition . Pattern::INTERVAL_ONLY;

            if (preg_match($expression, $safeInterval, $parts)) {
                $interval = $parts['interval'];
                $intervalLength = strlen($interval);

                # create the interval object
                $interval = \DateInterval::createFromDateString($interval);
                return new TimeInterval($interval, $intervalOffset, $intervalLength, $leadingData);
            }

            throw new FormatException("Given input does not contain a valid interval. Keep in mind trailing data is not allowed with current flag.");
        }

        if ($flags & IntervalFlags::REQUIRE_TRAILING) {

            $definition = Pattern::DEFINE . Pattern::INTEGER . Pattern::TIME_PART . ')';
            $expression = $definition . self::INTERVAL_WITH_TRAILING_DATA;

            # If interval contains non-strtotime-compatible abbreviations, replace 'em
            $safeInterval = $this->normalizer->normalize($input);

            # Separate interval from trailing data
            if (preg_match($expression, $safeInterval, $parts)) {
                $trailingData = $parts['trailing'] ?? null;
                $interval = $parts['interval'] ?? null;

                if (!$interval) {
                    throw new FormatException("Could not find any valid interval.");
                }

                if (!$trailingData) {
                    throw new FormatException("Could not find any valid trailing data.");
                }

                $intervalLength = strlen($interval);
                $intervalOffset = 0; # since we don't allow leading data here

                # create the interval object
                $interval = \DateInterval::createFromDateString($interval);
                return new TimeInterval($interval, $intervalOffset, $intervalLength, null, $trailingData);
            }

            throw new FormatException("Given input does not contain a valid interval. Keep in mind leading data is not allowed with current flag.");
        }

        if ($flags & IntervalFlags::MULTIPLE_INTERVALS) {

            $payload = [];
            $separator = ($this->settings->getSeparationType() == 'symbol')
                ? $this->settings->getSymbolSeparator()
                : $this->settings->getWordSeparator();

            $expression = "/(?J)\b(?:(?<match>.*?)\s?{$separator})\s?|\b(?<match>.*)/ui";

            if (preg_match_all($expression, $input, $intervals, PREG_SET_ORDER)) {

                $intervalSet = array_filter(array_map(function($set) {
                    foreach ($iter = new IntervalIterator($set) as $key => $interval) {
                        if ($iter->key() === 'match') {
                            return $interval;
                        }
                    }
                }, $intervals));

                foreach ($intervalSet as $key => $interval) {

                    $definition = Pattern::DEFINE . self::LEADING_SEPARATOR . Pattern::INTEGER . Pattern::TIME_PART . ')';
                    $expression = $definition . self::MULTIPLE_INTERVALS;

                    preg_match($expression, $interval, $matches);
                    $matches = array_filter($matches);

                    $leadingData = $matches['leading'] ?? null;
                    $leadingSep  = $matches['sep'] ?? null;
                    $interval    = $matches['interval'] ?? null;
                    $trailing    = $matches['trailing'] ?? null;

                    if (!$leadingData) $leadingData = $leadingSep ?? "";

                    $intervalOffset = (!$leadingSep) ? 0 : strlen($leadingData) + strlen($leadingSep);

                    # If interval contains non-strtotime-compatible abbreviations, replace them
                    $safeInterval = $this->normalizer->normalize($interval . $trailing);

                    # Separate intervals from trailing data
                    if (preg_match($expression, $safeInterval, $parts)) {
                        $trailingData = $parts['trailing'] ?? null;
                        $interval = $parts['interval'] ?? null;
                        if (!$interval) continue;

                        $intervalLength = strlen($interval);
                        # create the interval object
                        $interval = \DateInterval::createFromDateString($interval);
                        $payload[] = new TimeInterval($interval, $intervalOffset, $intervalLength, $leadingData, $trailingData);
                    }
                }

                if ($payload) return $payload;
            }
        }
    }
}
