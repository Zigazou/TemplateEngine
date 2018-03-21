<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use TemplateEngine\TokenSequence;
use TemplateEngine\Token;

final class TokenSequenceTest extends TestCase {
    public function testLengthMethod() {
        $tokens = new TokenSequence();
        $this->assertEquals(0, $tokens->length());

        $token = new Token("test", "test", 0);
        $tokens->addToken($token);
        $this->assertEquals(1, $tokens->length());

        $tokens->addToken($token);
        $this->assertEquals(2, $tokens->length());
    }

    public function testHasTokensMethod() {
        $tokens = new TokenSequence();
        $this->assertFalse($tokens->hasTokens());

        $token = new Token("test", "test", 0);
        $tokens->addToken($token);
        $this->assertTrue($tokens->hasTokens());
    }

    public function testStartsWithTypesMethod() {
        $tokens = new TokenSequence(array(
            new Token("a", "t", 0),
            new Token("b", "t", 1),
            new Token("c", "t", 2),
            new Token("d", "t", 3),
            new Token("e", "t", 4),
            new Token("f", "t", 5),
            new Token("g", "t", 6),
            new Token("h", "t", 7),
        ));

        $types1 = array("d", "e", "f", "g", "h");
        $types2 = array("d", "e", "f", "g", "h", "i");

        $this->assertFalse($tokens->startsWithTypes($types1));
        $this->assertTrue($tokens->startsWithTypes($types1, 3));
        $this->assertFalse($tokens->startsWithTypes($types2, 3));
    }

    public function testSliceMethod() {
        $tokens = new TokenSequence(array(
            new Token("a", "t", 0),
            new Token("b", "t", 1),
            new Token("c", "t", 2),
            new Token("d", "t", 3),
            new Token("e", "t", 4),
            new Token("f", "t", 5),
            new Token("g", "t", 6),
            new Token("h", "t", 7),
        ));

        $this->assertEquals(0, $tokens->slice($tokens->length(), 10)->length());
        $this->assertEquals($tokens, $tokens->slice(0, $tokens->length()));
        $this->assertEquals(
            3, $tokens->slice($tokens->length() - 3, 10)->length()
        );
    }
}
