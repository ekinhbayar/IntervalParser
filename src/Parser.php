<?php declare(strict_types = 1);
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

class Parser
{
    /**
     * @var Normalizer
     */
    private $normalizer;

    /**
     * Creates instance
     *
     * @param Normalizer $normalizer
     */
    public function __construct(Normalizer $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * Normalizes any non-strtotime-compatible time string, then validates the interval and returns a DateInterval object.
     * No leading or trailing data is accepted.
     *
     * @param string $input
     * @return \DateInterval
     * @throws FormatException
     */
    public function parse(string $input): \DateInterval
    {
        $input = trim($this->normalizer->normalize($input));

        $definition = Pattern::DEFINE . Pattern::INTEGER . Pattern::TIME_PART . ')';
        $expression = $definition . Pattern::INTERVAL_ONLY;

        if (preg_match($expression, $input, $matches)) {
            return \DateInterval::createFromDateString($input);
        }

        throw new FormatException("Given string is not a valid time interval.");
    }
}
