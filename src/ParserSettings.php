<?php declare(strict_types = 1);
/** Parser Settings
 *
 * IntervalParser takes a ParserSettings object which is handy for when you want to deal with multiple intervals.
 *
 * When parsing multiple intervals if you don't supply your own settings,
 * the parser will try to find comma separated intervals within given input by default.
 * However there is no default value for $wordSeparator (actually, there is "word").
 *
 * Example:
 *
 *     $parserSettings = new ParserSettings(1, ',', 'then');
 *     # works for "2 days then 5 months then 2 hours"
 *     $intervalParser = new IntervalParser($parserSettings);
 *
 */
namespace IntervalParser;

class ParserSettings
{
    const SYMBOL = 0b00000000;
    const STRING = 0b00000001;

    private $multipleSeparationType;
    private $multipleSeparationSymbol;
    private $multipleSeparationWord;
    private $leadingSeparationString;
    private $keepLeadingSeparator;

    # Leading separator with capturing groups
    public static $leadingGroupSeparator = /* @lang text */ '/(.*)\s+(?:in)\s+(.*)/ui';
    public static $symbolSeparator = /* @lang text */ '/(?<first>[^,]*)\s?,\s?(?<next>.*)$/ui';
    public static $wordSeparator = /* @lang text */ '/^(?<first>.*?)\s?foo\s?(?<next>.*)$/ui';


    public function __construct(
        string $leadingSeparationString = 'in',
        bool $keepLeadingSeparator = false,
        int $multipleSeparationType = self::SYMBOL,
        string $multipleSeparationSymbol = ',',
        string $multipleSeparationWord = 'foo'
    )
    {
        $this->leadingSeparationString = $leadingSeparationString;
        $this->keepLeadingSeparator = $keepLeadingSeparator;
        $this->multipleSeparationType = $multipleSeparationType;
        $this->multipleSeparationSymbol = $multipleSeparationSymbol;
        $this->multipleSeparationWord = $multipleSeparationWord;
    }

    public function getLeadingSeparator() : string
    {
        return $this->leadingSeparationString;
    }

    public function getSymbolSeparator() : string
    {
        return $this->multipleSeparationSymbol;
    }

    public function getWordSeparator() : string
    {
        return $this->multipleSeparationWord;
    }

    public function getLeadingSeparatorExpression() : string
    {
        $expression = preg_replace("/in/", $this->getLeadingSeparator(), self::$leadingGroupSeparator);
        return $expression;
    }

    public function getSymbolSeparatorExpression() : string
    {
        $expression = preg_replace("/,/", $this->getSymbolSeparator(), self::$symbolSeparator);
        return $expression;
    }

    public function getWordSeparatorExpression() : string
    {
        $expression = preg_replace("/foo/", $this->getWordSeparator(), self::$wordSeparator);
        return $expression;
    }

    public function keepLeadingSeparator() : bool
    {
        return $this->keepLeadingSeparator;
    }

    public function getSeparationType(): string
    {
        return ($this->multipleSeparationType == self::STRING) ? 'string' : 'symbol';
    }
}
