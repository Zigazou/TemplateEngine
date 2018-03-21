<?php
namespace TemplateEngine;

class OffsetToLine {
    private $lineOffsets = array();

    public function __construct(string $string) {
        $this->findLines($string);
    }

    public function getLine(int $offset) {
        $previousLine = 0;

        foreach($this->lineOffsets as $line => $lineOffset) {
            if($offset >= $this->lineOffsets[$previousLine] and
               $offset < $lineOffset) {
                return $previousLine;
            }

            $previousLine = $line;
        }

        if($offset >= $this->lineOffsets[$previousLine]) {
            return $previousLine;
        } else {
            return 0;
        }
    }

    private function findLines(string $string) {
        $this->lineOffsets = array(0 => 0);

        if(preg_match_all("/\n/s", $string, $matches, PREG_OFFSET_CAPTURE)) {
            foreach($matches[0] as $occur) {
                $this->lineOffsets []= $occur[1] + 1;
            }
        }
    }
}