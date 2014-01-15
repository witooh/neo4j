<?php
namespace Witooh\Neo4j\Cypher;

use App;
use Illuminate\Support\Contracts\ArrayableInterface;

class Data implements \Iterator, \Countable, \ArrayAccess, ArrayableInterface {

    protected $client = null;
    protected $raw = null;
    protected $data = null;
    protected $columns = null;
    protected $position = 0;
    /**
     * @var \Witooh\Neo4j\Cypher\Mapper
     */
    protected $mapper;

    /**
     * @param $columns
     * @param $rowData
     */
    public function __construct($columns, $rowData)
    {
        $this->raw = $rowData;
        $this->data = array();
        $this->columns = $columns;
        $this->mapper = App::make('neo4j.cypher.mapper');
    }

    // ArrayAccess API

    public function offsetExists($offset)
    {
        $raw = $this->mapper->mapStructure($this->raw);
        if (!is_integer($offset)) {

            $rawOffset = array_search($offset, $this->columns);

            if ($rawOffset === false) {
                return false;
            }

            return isset($raw[$rawOffset]);
        }

        return isset($raw[$offset]);
    }

    public function offsetGet($offset)
    {
        if (!is_integer($offset)) {
            $offset = array_search($offset, $this->columns);
        }

        if (!isset($this->data[$offset])) {
            $raw = $this->mapper->mapStructure($this->raw);
            $data = $this->mapper->mapData($raw[$offset]);
//            if (is_array($data)) {
//                $data = new Data(array_keys($raw), array_values($raw));
//            }
            $this->data[$offset] = $data;
        }

        return $this->data[$offset];
    }

    public function offsetSet($offset, $value)
    {
        throw new \BadMethodCallException("You cannot modify a result row.");
    }

    public function offsetUnset($offset)
    {
        throw new \BadMethodCallException("You cannot modify a result row.");
    }


    // Countable API

    public function count()
    {
        return count($this->raw);
    }


    // Iterator API

    public function rewind()
    {
        $this->position = 0;
    }

    public function current()
    {
        return $this[$this->position];
    }

    public function key()
    {
        return $this->columns[$this->position];
    }

    public function next()
    {
        ++$this->position;
    }

    public function valid()
    {
        return $this->position < count($this->raw);
    }

    public function toArray()
    {
        $result = [];
        foreach($this as $key=>$data){
            if($data instanceof ArrayableInterface){
                $result[$key] = $data->toArray();
            }else{
                $result[$key] = $data;
            }
        }

        return $result;
    }

    /**
     * @param string|int $key
     * @param null|mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if($this->offsetExists($key)){
            return $this->offsetGet($key);
        }

        return $default;
    }

    public function put($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function push($value)
    {
        $this->data[] = $value;
    }
}