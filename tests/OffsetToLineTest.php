<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TemplateEngine\OffsetToLine;

final class OffsetToLineTest extends TestCase {
    public function testEmpty() {
        $string = "";
        $otl = new OffsetToLine($string);

        $this->assertEquals(0, $otl->getLine(100));
    }

    public function testFourLines() {
        $string = "a\nb\nc\nd\n";
        $otl = new OffsetToLine($string);

        $this->assertEquals(0, $otl->getLine(0));
        $this->assertEquals(1, $otl->getLine(2));
        $this->assertEquals(2, $otl->getLine(4));
        $this->assertEquals(3, $otl->getLine(6));
        $this->assertEquals(4, $otl->getLine(8));
    }
}
