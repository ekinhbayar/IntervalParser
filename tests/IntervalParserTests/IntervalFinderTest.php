<?php

namespace IntervalParserTests;

use IntervalParser\IntervalFinder;
use IntervalParser\IntervalFlags;
use IntervalParser\Normalizer;
use IntervalParser\ParserSettings;
use IntervalParser\TimeInterval;
use PHPUnit\Framework\TestCase;

class IntervalFinderTest extends TestCase
{
    private $intervalFinder;

    public function setUp()
    {
        $this->intervalFinder = new IntervalFinder(new ParserSettings(), new Normalizer());
    }

    /**
     * Data provider for interval parser with trailing data
     *
     * @return array
     */
    public function trailingDataProvider()
    {
        return require(__DIR__ . '/data_provider/trailing_data_provider.php');
    }

    /**
     * Test interval parsing with trailing data
     *
     * @param string $input
     * @param TimeInterval $expectedTimeInterval
     *
     * @dataProvider trailingDataProvider
     */
    public function testFindWithTrailingData(string $input, TimeInterval $expectedTimeInterval)
    {
        $timeInterval = $this->intervalFinder->find($input, IntervalFlags::REQUIRE_TRAILING);

        $this->assertEquals($expectedTimeInterval, $timeInterval);
    }

    /**
     * Data provider for interval parser with leading data
     *
     * @return array
     */
    public function leadingDataProvider()
    {
        return require(__DIR__ . '/data_provider/leading_data_provider.php');
    }

    /**
     * Test interval parsing with leading data
     *
     * @param string $input
     * @param TimeInterval $expectedTimeInterval
     *
     * @dataProvider leadingDataProvider
     */
    public function testFindWithLeadingData(string $input, TimeInterval $expectedTimeInterval)
    {
        $timeInterval = $this->intervalFinder->find($input, IntervalFlags::REQUIRE_LEADING);

        $this->assertEquals($expectedTimeInterval, $timeInterval);
    }

    /**
     * Data provider for interval parser with leading and trailing data
     *
     * @return array
     */
    public function bothDataProvider()
    {
        return require(__DIR__ . '/data_provider/both_data_provider.php');
    }

    /**
     * Test interval parsing with leading and trailing data
     *
     * @param string $input
     * @param TimeInterval $expectedTimeInterval
     *
     * @dataProvider bothDataProvider
     */
    public function testFindWithLeadingAndTrailingData(string $input, TimeInterval $expectedTimeInterval)
    {
        $timeInterval = $this->intervalFinder->find(
            $input,
            IntervalFlags::REQUIRE_TRAILING|IntervalFlags::REQUIRE_LEADING
        );

        $this->assertEquals($expectedTimeInterval, $timeInterval);
    }
}
