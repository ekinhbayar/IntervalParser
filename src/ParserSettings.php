<?php
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

    private $separationType;
    private $symbol;
    private $word;

    public static $symbolSeparator = "/(?<first>[^,]*)\s?,\s?(?<next>.*)$/ui";
    public static $wordSeparator = "/^(?<first>.*?)\s?word\s?(?<next>.*)$/ui";


    /**
     * ParserSettings constructor.
     *
     * @param int $separationType
     * @param string $symbol
     * @param string|null $word
     */
    public function __construct(
        int $separationType = self::SYMBOL,
        string $symbol  = ',',
        string $word = null
    )
    {
        $this->separationType = $separationType;
        $this->symbol = $symbol;
        $this->word = $word;
    }

    public function getSymbolSeparator() : string
    {
        return $this->symbol;
    }

    public function getWordSeparator() : string
    {
        return $this->word;
    }

    public function getSymbolSeparatorExpression() : string
    {
        $expression = preg_replace("/,/", $this->getSymbolSeparator(), self::$symbolSeparator);
        return $expression;
    }

    public function getWordSeparatorExpression() : string
    {
        $expression = preg_replace("/word/", $this->getWordSeparator(), self::$symbolSeparator);
        return $expression;
    }

    public function getSeparationType(): string
    {
        return ($this->separationType == self::STRING) ? 'string' : 'symbol';
    }
}
