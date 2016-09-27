<?php
namespace IntervalParser;


class IntervalFlags
{
    const INTERVAL_ONLY      = 0b00000000;
    const REQUIRE_TRAILING   = 0b00000001;
    const REQUIRE_LEADING    = 0b00000010;
    const MULTIPLE_INTERVALS = 0b00000100;
}