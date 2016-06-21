<?php

namespace zaboy\scheduler\Callback;

use zaboy\rest\DataStore\DataStoreAbstract;
use zaboy\rest\RqlParser\RqlParser;
use zaboy\scheduler\Callback\Interfaces\CallbackInterface;

class DataStore implements CallbackInterface
{
    /** @var \zaboy\rest\DataStore\DataStoreAbstract $dataStore */
    protected $dataStore;

    /** @var string $method*/
    private $method;

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function __construct(array $params = [])
    {
        if (!isset($params['data_store']) || !$params['data_store'] instanceof DataStoreAbstract) {
            throw new CallbackException("Expected necessary object of DataStore.");
        }
        $this->dataStore = $params['data_store'];
    }

    /**
     * {@inheritdoc}
     *
     * {@inheritdoc}
     */
    public function call(array $options = [])
    {
        if (!isset($options['method'])) {
            throw new CallbackException("Expected necessary key \"method\" in the array \$options");
        }
        $allowedMethods = [
            'read',
            'create',
            'update',
            'delete',
            'deleteall',
            'query'
        ];
        $this->method = strtolower($options['method']);
        if (!in_array($this->method, $allowedMethods)) {
            throw new CallbackException("Specified method must be one of '" . join("', '", $allowedMethods) . "'.
                \"{$this->method}\" given.");
        }
        if ('deleteall' !== $this->method && !isset($options['item_data'])) {
            throw new CallbackException("Expected necessary part of options - \"item_data\".");
        }
        return $this->{$this->method}($options);
    }

    /**
     * Translates the "read" call to DataStore
     *
     * @see \zaboy\scheduler\Callback\Factory\DataStoreAbstractFactory
     * @see \zaboy\rest\DataStore\Interfaces\DataStoresInterface
     * @param array $options
     * @return array|null
     * @throws CallbackException
     */
    private function read(array $options = [])
    {
        if (!isset($options['item_data']['id'])) {
            throw new CallbackException("Expected necessary parameter \"id\" in the options array");
        }
        return $this->dataStore->read($options['item_data']['id']);
    }

    /**
     * Translates the "create" call to DataStore
     *
     * @see \zaboy\scheduler\Callback\Factory\DataStoreAbstractFactory
     * @see \zaboy\rest\DataStore\Interfaces\DataStoresInterface
     * @param array $options
     * @return array
     */
    private function create(array $options = [])
    {
        $itemData = $options['item_data'];
        $rewriteIfExist = (isset($options['flags']['rewriteIfExist']) ? $options['flags']['rewriteIfExist'] : false);
        return $this->dataStore->create($itemData, $rewriteIfExist);
    }

    /**
     * Translates the "update" call to DataStore
     *
     * @see \zaboy\scheduler\Callback\Factory\DataStoreAbstractFactory
     * @see \zaboy\rest\DataStore\Interfaces\DataStoresInterface
     * @param array $options
     * @return array
     */
    private function update(array $options = [])
    {
        $itemData = $options['item_data'];
        $createIfAbsent = (isset($options['flags']['createIfAbsent']) ? $options['flags']['createIfAbsent'] : false);
        return $this->dataStore->create($itemData, $createIfAbsent);
    }

    /**
     * Translates the "query" call to DataStore
     *
     * @see \zaboy\scheduler\Callback\Factory\DataStoreAbstractFactory
     * @see \zaboy\rest\DataStore\Interfaces\DataStoresInterface
     * @param array $options
     * @return array | \zaboy\rest\DataStore\Interfaces\DataStoresInterface array
     * @throws CallbackException
     */
    private function query(array $options = [])
    {
        $itemData = $options['item_data'];
        if (!isset($itemData['query'])) {
            throw new CallbackException("Expected necessary parameter \"query\" in the options array");
        }
        $parser = new RqlParser();
        $query = $parser->rqlDecoder($itemData['query']);
        return $this->dataStore->query($query);
    }

    /**
     * Translates the "delete" call to DataStore
     *
     * @see \zaboy\scheduler\Callback\Factory\DataStoreAbstractFactory
     * @see \zaboy\rest\DataStore\Interfaces\DataStoresInterface
     * @param array $options
     * @return array
     * @throws CallbackException
     */
    private function delete(array $options = [])
    {
        if (!isset($options['item_data']['id'])) {
            throw new CallbackException("Expected necessary parameter \"id\" in the options array");
        }
        return $this->dataStore->delete($options['item_data']['id']);
    }

    /**
     * Translates the "deleteAll" call to DataStore
     *
     * @see \zaboy\scheduler\Callback\Factory\DataStoreAbstractFactory
     * @see \zaboy\rest\DataStore\Interfaces\DataStoresInterface
     * @param array $options
     * @return int|null
     */
    private function deleteall(array $options = [])
    {
        return $this->dataStore->deleteAll();
    }
}