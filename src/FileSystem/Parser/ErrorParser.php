<?php

namespace zaboy\scheduler\FileSystem\Parser;

class ErrorParser
{
    protected $patterns;

    /**
     * ParserError constructor.
     *
     * @param $patterns
     */
    public function __construct($patterns)
    {
        $this->patterns = $patterns;
    }

    public function parseLog($filename)
    {
        if (!is_file($filename)) {
            throw new \Exception("The file doesn't exist");
        }

        try {
            $fatalStatus = false;
            $fileContent = file($filename, FILE_SKIP_EMPTY_LINES);
            foreach ($fileContent as $row) {
                foreach ($this->patterns as $pattern) {
                    if (preg_match($pattern, $row)) {
                        $fatalStatus = true;
                        break 2;
                    }
                }
            }
            $return = [
                'fatalStatus' => $fatalStatus,
                'message' => join(PHP_EOL, $fileContent),
            ];
            return $return;
        } catch (\Exception $e) {
            throw new \Exception("Something went wrong", null, $e);
        }
    }
}