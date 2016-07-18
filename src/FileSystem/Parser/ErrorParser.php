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

    /**
     * Parses the log file and return a status of a finish of a process and content of its log files
     *
     * @param $filename
     * @return array
     * @throws \Exception
     */
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
            unlink($filename);
            return $return;
        } catch (\Exception $e) {
            throw new \Exception("Something went wrong", null, $e);
        }
    }
}