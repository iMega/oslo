<?php

namespace iMega\Mapper;

use iMega\Teleport\MapperInterface;
use iMega\Teleport\Mapper\Map;

/**
 * Class Mapper
 */
class SqlDump implements MapperInterface
{
    protected $prefix;

    /**
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this->prefix = $options['prefix'];
    }

    /**
     * @param string $data Mysql query.
     */
    public function preExecute($data)
    {
        $this->query($data);
    }

    /**
     * @param int   $key
     * @param array $data
     */
    public function execute($key, array $data)
    {
        if (!array_key_exists($key, Map::getTables())) {
            return;
        }
        $values = $this->getValues($key, $data);
        if ($values) {
            $head = $this->getHead($key);
            $this->query($head . $values);
        }
    }

    /**
     * @param string $data Mysql query.
     */
    public function postExecute($data)
    {
        $this->query($data);
    }

    private function getHead($key)
    {
        $tablename = $this->prefix . Map::getTables()[$key];
        $fields    = implode(',', Map::getMap()[$key]);
        return "insert into $tablename($fields)values";
    }

    /**
     * @param int   $key
     * @param array $values
     *
     * @return string
     */
    private function getValues($key, array $values)
    {
        $data = [];
        foreach ($values as $item) {
            $data[] = $this->getValue($key, $item);
        }
        $result = implode(',', $data);

        return $result;
    }

    /**
     * @param int    $key
     * @param string $value JSON.
     *
     * @return string
     */
    private function getValue($key, $value)
    {
        $data = [];
        $record = json_decode($value, true);
        foreach (Map::getMap()[$key] as $field) {
            if (array_key_exists($field, $record)) {
                $res = $record[$field];
            } else {
                $res = '';
            }
            $data[] = $this->escapeString($res);
        }
        $result = implode(',', $data);

        return "($result)";
    }

    /**
     * @param string $value
     *
     * @return string
     */
    private function escapeString($value)
    {
        $result = $this->mysqli_escape_string($value);

        if (false === $result) {
            $result = "";
        }

        return "'$result'";
    }

    private function mysqli_escape_string($value)
    {
        if(!empty($value) && is_string($value)) {
            return str_replace(
                ['\\', "\0", "\n", "\r", "'", '"', "\x1a"],
                ['\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'],
                $value
            );
        }

        return false;
    }

    /**
     * Run Query
     *
     * @param string $value SQL statement
     *
     * @return string
     */
    public function query($value)
    {
        $result = file_put_contents('gaufrette://teleport/'.uniqid().'.sql', $value.";", FILE_APPEND);
    }
}
