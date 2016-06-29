<?php

namespace zaboy\scheduler\FileSystem;

/**
 * Creates an object of file manager.
 *
 * This manager allows to create, to rewrite and to delete a file by its name.
 * The file config is passed into each method. This config using for writing the first row into file.
 * the first row in csv-file is info about column headers - the CSV-format requires it.
 *
 * File config must be an array with the structure below:
 * <code>
 * $fileConfig = [
 *     'column_name_1',
 *     'column_name_2',
 *     // ...
 *     'column_name_N',
 * ]
 * </code>
 *
 * You can also pass into creating method (or/and rewriting also) the delimiter with which the column headers
 * will be separated. By default it's the symbol ';'.
 *
 * @package zaboy\scheduler\FileSystem
 */
class FileManagerCsv
{
    const PATH_BY_DEFAULT = 'tmp';

    const DELIMITER_BY_DEFAULT = ';';

    /**
     * FileManagerCsv constructor.
     */
    public function __construct()
    {
        $this->path = realpath(getcwd() . self::PATH_BY_DEFAULT);
    }

    /**
     * Builds and returns real path for filename if it's necessary
     *
     * Converts filename only like 'index.php' to pull path to project with directory in project by default
     * And if the filename which contents current directory in its name like './index.php'
     *     it's just built path to project
     *
     * @param $filename
     * @return string
     */
    private function getRealFileName($filename)
    {
        if (!preg_match("/^\.\//", $filename) && dirname($filename) == '.') {
            $filename = getcwd() . DIRECTORY_SEPARATOR . self::PATH_BY_DEFAULT . DIRECTORY_SEPARATOR . $filename;
        } elseif (preg_match("/^\.\//", $filename)) {
            $filename = getcwd() . DIRECTORY_SEPARATOR . $filename;
        }
        return $filename;
    }

    /**
     * Checks if specified file exists
     *
     * @param $filename
     * @return bool
     */
    public function has($filename)
    {
        return is_file($this->getRealFileName($filename));
    }

    /**
     * Tries to create the file.
     *
     * If it already exists - throw an exception.
     * After creating it puts into file row with column headers.
     *
     * @param $filename
     * @param $fileConfig
     * @param null $delimiter
     * @return bool
     * @throws \Exception
     */
    public function create($filename, $fileConfig, $delimiter = null)
    {
        if ($this->has($filename)) {
            throw new \Exception("The file \"{$filename}\" already exists. Use \"rewrite()\" instead.");
        }
        $filename = $this->getRealFileName($filename);
        $delimiter = (!is_null($delimiter)) ?: self::DELIMITER_BY_DEFAULT;

        try {
            $file = new \SplFileObject($filename, 'w');
            $file->fputcsv($fileConfig, $delimiter);
        } catch (\Exception $e) {
            return false;
        } finally {
            // Close file
            unset($file);
            // Make garbage the cycle that the closing file was done immediately.
            gc_collect_cycles();
        }
        return true;
    }

    /**
     * Rewrites the file.
     *
     * Rewrite == at first deletes and than creates new file.
     *
     * @param $filename
     * @param $fileConfig
     * @return bool
     * @throws \Exception
     */
    public function rewrite($filename, $fileConfig)
    {
        $this->delete($filename);
        return $this->create($filename, $fileConfig);
    }

    /**
     * Delete the file if one exists
     *
     * @param $filename
     * @return bool
     */
    public function delete($filename)
    {
        try {
            if ($this->has($filename)) {
                unlink($this->getRealFileName($filename));
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}