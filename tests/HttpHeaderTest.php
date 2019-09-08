<?php declare(strict_types=1);

namespace Dewep\Tests;

use Dewep\Client\HttpHeader;
use PHPUnit\Framework\TestCase;

class HttpHeaderTest extends TestCase
{
    public function testParse()
    {
        $header = new HttpHeader();

        $header->parse(
            '
        test: ttt
        test-test: ttt
        test-test1
        '
        );

        self::assertArrayNotHasKey('Test-Test1', $header->map());
        self::assertArrayHasKey('Test', $header->map());
        self::assertEquals('ttt', $header->map()['Test']);
    }
}
