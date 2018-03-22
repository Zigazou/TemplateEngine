<?php
/**
 * The OffsetToLine class.
 *
 * @author Frédéric BISSON <zigazou@free.fr>
 */
namespace TemplateEngine;

/**
 * The OffsetToLine class converts an offset in string to a line
 * number, allowing for better error message.
 */
class OffsetToLine {
    /**
     * @var array $lineOffsets The calculated (line => offset)
     */
    private $lineOffsets = array();

    /**
     * Builds a OffsetToLine converter.
     * 
     * @param string $string The string to retrieve the line offsets from.
     */
    public function __construct(string $string) {
        $this->findLines($string);
    }

    /**
     * Get the line number given an offset.
     * 
     * @param int $offset the offset for which to retrieve the line number.
     * @return int The line number (starting at 0)
     */
    public function getLine(int $offset) {
        $previousLine = 0;

        foreach($this->lineOffsets as $line => $lineOffset) {
            if($offset >= $this->lineOffsets[$previousLine] and
               $offset < $lineOffset) {
                return $previousLine;
            }

            $previousLine = $line;
        }

        if($offset >= $this->lineOffsets[$previousLine]) return $previousLine;

        return 0;
    }

    /**
     * Find offset for every line in a string.
     * 
     * @param string $string The string to analyze.
     */
    private function findLines(string $string) {
        $this->lineOffsets = array(0 => 0);

        if(preg_match_all("/\n/s", $string, $matches, PREG_OFFSET_CAPTURE)) {
            foreach($matches[0] as $occur) {
                $this->lineOffsets []= $occur[1] + 1;
            }
        }
    }
}