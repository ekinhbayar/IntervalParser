<?php declare(strict_types=1);
/**
 * Time string normalizer
 *
 * PHP version 7+
 *
 * @category   IntervalParser
 * @author     Ekin H. Bayar <ekin@coproductivity.com>
 * @version    0.2.0
 */
namespace IntervalParser;

class Normalizer
{
    /**
     * Used to turn a given non-strtotime-compatible time string into a compatible one
     * Only modifies the non-strtotime-compatible time strings provided leaving the rest intact
     *
     * @var string $pattern
     */
    public static $pattern = <<<'REGEX'
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
     * Turns any non-strtotime-compatible time string into a compatible one.
     * If the passed input has trailing data, it won't be lost since within the callback the input is reassembled.
     * However no leading data is accepted.
     *
     * @param string $input
     * @return string
     */
    public function normalize(string $input): string
    {
        $output = preg_replace_callback(self::$pattern,
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
}
