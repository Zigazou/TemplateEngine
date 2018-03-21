<?php
namespace TemplateEngine;

use TemplateEngine\Token;

class TokenSequence {
    protected $tokens = array();
    
    public function __construct(array $tokens=array()) {
        $this->tokens = array();
        foreach($tokens as $token) $this->addToken($token);
    }

    public function hasTokens() {
        return count($this->tokens) > 0;
    }

    public function length() {
        return count($this->tokens);
    }

    public function at($index) {
        return $this->tokens[$index];
    }

    public function addToken(Token $token) {
        $this->tokens []= $token;
    }

    public function startsWithTypes(array $types, int $index=0) {
        if($index < 0 or $index >= $this->length()) {
            return FALSE;
        }

        // If the external type sequence's length is too big compared to our
        // token sequence, it cannot start it.
        if(count($types) > $this->length() - $index) {
            return FALSE;
        }

        foreach($types as $offset => $type) {
            // The tokens do not match, this sequence is not good
            if($this->tokens[$index + $offset]->type != $type) {
                return FALSE;
            }
        }

        return TRUE;
    }

    public function slice(int $start, int $length) {
        $tokens = new TokenSequence();

        // If the start is higher than the length, returns an empty sequence
        if($start > $this->length()) return $tokens;

        // Calculates the end
        if($start + $length > $this->length()) {
            $end = $this->length();
        } else {
            $end = $start + $length;
        }

        for($index = $start; $index < $end; $index++) {
            $tokens->addToken($this->tokens[$index]);
        }

        return $tokens;
    }
}
