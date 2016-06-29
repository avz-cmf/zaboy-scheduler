<?php

namespace zaboy\test\FileSystem;

class FileManagerCsvTest extends \PHPUnit_Framework_TestCase
{
    protected $filename = 'file_manager_csv_test.log';

    protected $fileConfig = [
        'id',
        'rql',
        'callback',
        'active'
    ];

    /** @var  \zaboy\scheduler\FileSystem\FileManagerCsv $fileManager */
    protected $fileManager;

    protected function setUp()
    {
        $container = include './config/container.php';
        $this->fileManager = $container->get('file_manager_csv');

    }

    public function test_has()
    {
        $this->assertFalse(
            $this->fileManager->has($this->filename)
        );
    }

    public function test_create()
    {
        $this->assertTrue(
            $this->fileManager->create($this->filename, $this->fileConfig)
        );

        $this->setExpectedExceptionRegExp('Exception', '/The file \".+\" already exists/');
        $this->fileManager->create($this->filename, $this->fileConfig);
    }

    public function test_rewrite()
    {
        $this->assertTrue(
            $this->fileManager->rewrite($this->filename, $this->fileConfig)
        );
    }

    public function test_delete()
    {
        $this->assertTrue(
            $this->fileManager->delete($this->filename)
        );
    }
}