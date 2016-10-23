<?php declare(strict_types = 1);
/**
 * Set of regular expressions utilized to match/replace/validate parts of a given input.
 *
 * PHP version 7+
 *
 * @category   IntervalParser
 * @author     Ekin H. Bayar <ekin@coproductivity.com>
 * @version    0.2.0
 */
namespace IntervalParser;

class Pattern
{
    /**
     * Don't forget to close parentheses when using $define
     */
    const DEFINE = "/(?(DEFINE)";

    # Definitions of sub patterns for a valid interval
    const INTEGER = <<<'REGEX'
    (?<integer>
       (?:\G|(?!\n))
       (\s*\b)?
       \d{1,5}
       \s*
    )
REGEX;

    # Starts with integer followed by time specified
    const TIME_PART = <<<'REGEX'
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

    # Regex to match a valid interval, holds the value in $matches['interval']
    const INTERVAL_ONLY = "^(?<interval>(?&timepart)++)$/uix";
}
