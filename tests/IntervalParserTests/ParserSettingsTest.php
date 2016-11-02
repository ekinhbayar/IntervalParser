<?php declare(strict_types = 1);

namespace IntervalParserTests;

use IntervalParser\ParserSettings;
use PHPUnit\Framework\TestCase;

class ParserSettingsTest extends TestCase
{
    public function testGetLeadingSeparatorWhenManuallySet()
    {
        $parserSettings = new ParserSettings('foo');

        $this->assertSame('foo', $parserSettings->getLeadingSeparator());
    }

    public function testGetLeadingSeparatorWhenUsingTheDefault()
    {
        $parserSettings = new ParserSettings();

        $this->assertSame('in', $parserSettings->getLeadingSeparator());
    }

    public function testGetSymbolSeparatorWhenManuallySet()
    {
        $parserSettings = new ParserSettings('foo', false, ParserSettings::SYMBOL, ';');

        $this->assertSame(';', $parserSettings->getSymbolSeparator());
    }

    public function testGetSymbolSeparatorWhenUsingTheDefault()
    {
        $parserSettings = new ParserSettings();

        $this->assertSame(',', $parserSettings->getSymbolSeparator());
    }

    public function testGetWordSeparatorWhenManuallySet()
    {
        $parserSettings = new ParserSettings('foo', false, ParserSettings::SYMBOL, ';', 'bar');

        $this->assertSame('bar', $parserSettings->getWordSeparator());
    }

    public function testGetWordSeparatorWhenUsingTheDefault()
    {
        $parserSettings = new ParserSettings();

        $this->assertSame(',', $parserSettings->getWordSeparator());
    }

    public function testKeepLeadingSeparatorWhenManuallySetToTrue()
    {
        $parserSettings = new ParserSettings('foo', true);

        $this->assertTrue($parserSettings->keepLeadingSeparator());
    }

    public function testKeepLeadingSeparatorWhenManuallySetToFalse()
    {
        $parserSettings = new ParserSettings('foo', false);

        $this->assertFalse($parserSettings->keepLeadingSeparator());
    }

    public function testKeepLeadingSeparatorWhenUsingTheDefault()
    {
        $parserSettings = new ParserSettings();

        $this->assertFalse($parserSettings->keepLeadingSeparator());
    }

    public function testGetSeparationTypeWhenManuallySetToSymbol()
    {
        $parserSettings = new ParserSettings('foo', true, ParserSettings::SYMBOL);

        $this->assertSame('symbol', $parserSettings->getSeparationType());
    }

    public function testGetSeparationTypeWhenManuallySetToString()
    {
        $parserSettings = new ParserSettings('foo', false, ParserSettings::STRING);

        $this->assertSame('string', $parserSettings->getSeparationType());
    }

    public function testGetSeparationTypeWhenUsingTheDefault()
    {
        $parserSettings = new ParserSettings();

        $this->assertSame('symbol', $parserSettings->getSeparationType());
    }

    public function testGetLeadingSeparatorExpressionWhenManuallySet()
    {
        $parserSettings = new ParserSettings('foo');

        $this->assertSame('/(.*)\s+(?:foo)\s+(.*)/ui', $parserSettings->getLeadingSeparatorExpression());
    }

    public function testGetLeadingSeparatorExpressionWhenUsingTheDefault()
    {
        $parserSettings = new ParserSettings();

        $this->assertSame('/(.*)\s+(?:in)\s+(.*)/ui', $parserSettings->getLeadingSeparatorExpression());
    }

    public function testGetSymbolSeparatorExpressionWhenManuallySet()
    {
        $parserSettings = new ParserSettings('foo', false, ParserSettings::SYMBOL, ';');

        $this->assertSame('/(?<first>[^;]*)\s?;\s?(?<next>.*)$/ui', $parserSettings->getSymbolSeparatorExpression());
    }

    public function testGetSymbolSeparatorExpressionWhenUsingTheDefault()
    {
        $parserSettings = new ParserSettings();

        $this->assertSame('/(?<first>[^,]*)\s?,\s?(?<next>.*)$/ui', $parserSettings->getSymbolSeparatorExpression());
    }

    public function testGetWordSeparatorExpressionWhenManuallySet()
    {
        $parserSettings = new ParserSettings('in', false, ParserSettings::SYMBOL, ',', 'bar');

        $this->assertSame('/^(?<first>.*?)\s?bar\s?(?<next>.*)$/ui', $parserSettings->getWordSeparatorExpression());
    }

    public function testGetWordSeparatorExpressionWhenUsingTheDefault()
    {
        $parserSettings = new ParserSettings();

        $this->assertSame('/^(?<first>.*?)\s?word\s?(?<next>.*)$/ui', $parserSettings->getWordSeparatorExpression());
    }
}
