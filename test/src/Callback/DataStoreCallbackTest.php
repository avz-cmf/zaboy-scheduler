<?php

namespace zaboy\test\Callback;

class DataStoreCallbackTest extends \PHPUnit_Framework_TestCase
{
    /** @var  \Zend\ServiceManager\ServiceManager $container */
    protected $container;

    /** @var  \zaboy\scheduler\Callback\Interfaces\CallbackInterface */
    protected $callback;

    protected $itemsArrayDefault = [
        1 => ['id' => 1, 'anotherId' => 10, 'fString' => 'val1', 'fFloat' => 400.0004],
        2 => ['id' => 2, 'anotherId' => 20, 'fString' => 'val2', 'fFloat' => 300.003],
        3 => ['id' => 3, 'anotherId' => 40, 'fString' => 'val2', 'fFloat' => 300.003],
        4 => ['id' => 4, 'anotherId' => 30, 'fString' => 'val2', 'fFloat' => 100.1]
    ];

    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->callback = $this->container->get('test_datastore_type_of_callbacks');
        /** @var \zaboy\rest\DataStore\DataStoreAbstract $dataStore */
        $dataStore = $this->container->get('datastore_for_test_datastore_type_of_callbacks');

        foreach ($this->itemsArrayDefault as $itemData) {
            $dataStore->create($itemData);
        }
    }

    public function test_callWithoutOptions()
    {
        $this->setExpectedExceptionRegExp('zaboy\scheduler\Callback\CallbackException',
            "/Expected necessary key \"method\"/");
        $this->callback->call();
    }

    public function test_callWithNotAllowedMethod()
    {
        $this->setExpectedExceptionRegExp('zaboy\scheduler\Callback\CallbackException',
            "/Specified method must be one of/");
        $this->callback->call([
            'method' => 'some wrong method'
        ]);
    }

    public function test_read()
    {
        // Call read without "item_data"
        try {
            $this->callback->call([
                'method' => 'read',
            ]);
        } catch (\Exception $e) {
            $this->assertEquals(
                get_class($e), 'zaboy\scheduler\Callback\CallbackException'
            );
        }
        // Call without id in "item_data"
        try {
            $this->callback->call([
                'method' => 'read',
                'item_data' => [],
            ]);
        } catch (\Exception $e) {
            $this->assertEquals(
                get_class($e), 'zaboy\scheduler\Callback\CallbackException'
            );
        }

        $id = 1;
        $result = $this->callback->call([
            'method' => 'read',
            'item_data' => [
                'id' => $id
            ],
        ]);
        $this->assertEquals(
            $this->itemsArrayDefault[$id],
            $result
        );
    }

    public function test_create()
    {
        // Checks simple creating with specifying id
        $itemData = [
            'id' => 100,
            'anotherId' => 100,
            'fString' => 'val100',
            'fFloat' => 400.1004
        ];
        $result = $this->callback->call([
            'method' => 'create',
            'item_data' => $itemData
        ]);
        $this->assertEquals(
            $itemData,
            $result
        );

        // Checks simple creating without specifying id
        $itemData = [
            'anotherId' => 101,
            'fString' => 'val101',
            'fFloat' => 400.1014
        ];
        $result = $this->callback->call([
            'method' => 'create',
            'item_data' => $itemData
        ]);
        $itemData['id'] = 101;
        $this->assertEquals(
            $itemData,
            $result
        );

        $itemData = $this->itemsArrayDefault[1];
        // Trying to create entry with existing id with flag rewriteIfExist
        $result = $this->callback->call([
            'method' => 'create',
            'item_data' => $itemData,
            'flags' => [
                'rewriteIfExist' => true
            ],
        ]);
        $this->assertEquals(
            $itemData,
            $result
        );

        // Trying to create entry with existing id without flag rewriteIfExist (by default == false)
        $this->setExpectedExceptionRegExp('zaboy\rest\DataStore\DataStoreException');
        $this->callback->call([
            'method' => 'create',
            'item_data' => $itemData,
        ]);
    }

    public function test_update()
    {
        // Trying to update entry without id
        $itemData = [
            'anotherId' => 500,
            'fString' => 'val500',
            'fFloat' => 500.1014
        ];
        try {
            $this->callback->call([
                'method' => 'update',
                'item_data' => $itemData,
                'flags' => [
                    'createIfAbsent' => true
                ],
            ]);
        } catch (\Exception $e) {
            $this->assertEquals(
                get_class($e), 'zaboy\rest\DataStore\DataStoreException'
            );
        }

        $itemData['id'] = 500;
        // Trying to update an entry with existing id with flag createIfAbsent
        $result = $this->callback->call([
            'method' => 'update',
            'item_data' => $itemData,
            'flags' => [
                'createIfAbsent' => true
            ],
        ]);
        $this->assertEquals(
            $itemData,
            $result
        );

        // Trying to update an entry with existing id without flag createIfAbsent (by default == false)
        $this->setExpectedExceptionRegExp('zaboy\rest\DataStore\DataStoreException');
        $result = $this->callback->call([
            'method' => 'update',
            'item_data' => $itemData,
        ]);
    }

    public function test_query()
    {
        // without "query" part in "item_data"
        try {
            $this->callback->call([
                'method' => 'query',
                'item_data' => [],
            ]);
        } catch (\Exception $e) {
            $this->assertEquals(
                get_class($e), 'zaboy\scheduler\Callback\CallbackException'
            );
        }

        // wrong rql-query
        try {
            $this->callback->call([
                'method' => 'query',
                'item_data' => [
                    'query' => 'qrong rql-query'
                ],
            ]);
        } catch (\Exception $e) {
            $this->assertTrue($e instanceof \Xiag\Rql\Parser\Exception);
//            $this->assertTrue($e instanceof \zaboy\scheduler\Callback\CallbackException);
        }

        $result = $this->callback->call([
            'method' => 'query',
            'item_data' => [
                'query' => 'eq(id,2)',
            ],
        ]);
        $this->assertEquals(1, count($result));
        $this->assertEquals(
            $this->itemsArrayDefault[2], $result[0]
        );
    }

    public function test_delete()
    {
        // Call without id in "item_data"
        try {
            $this->callback->call([
                'method' => 'delete',
                'item_data' => [],
            ]);
        } catch (\Exception $e) {
            $this->assertEquals(
                get_class($e), 'zaboy\scheduler\Callback\CallbackException'
            );
        }

        $id = 4;
        $result = $this->callback->call([
            'method' => 'delete',
            'item_data' => [
                'id' => $id
            ],
        ]);
        $this->assertEquals($this->itemsArrayDefault[$id], $result);
        // call again
        $result = $this->callback->call([
            'method' => 'delete',
            'item_data' => [
                'id' => $id
            ],
        ]);
        $this->assertNull($result);
    }

    public function test_deleteAll()
    {
        $result = $this->callback->call([
            'method' => 'deleteall',
        ]);
        $this->assertEquals(count($this->itemsArrayDefault), $result);
    }
}