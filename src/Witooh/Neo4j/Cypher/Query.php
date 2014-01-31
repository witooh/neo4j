<?php
namespace Witooh\Neo4j\Cypher;

use Guzzle\Http\Client;
use Witooh\Neo4j\Neo4jException;

class Query {

    protected $uri = '/db/data/cypher';

    protected $queryStr;

    protected $params;
    /**
     * @var \Guzzle\Http\Client
     */
    protected $curl;

    /**
     * @var bool
     */
    protected $hasWhere;

    public function __construct($curl)
    {
        $this->curl = $curl;
        $this->hasWhere = false;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function create()
    {
        $this->createBlock("CREATE", func_get_args());
        return $this;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function match()
    {
        $this->createBlock("MATCH", func_get_args());
        return $this;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function optMatch()
    {
        $this->createBlock("OPTIONAL MATCH", func_get_args());
        return $this;
    }

    /**
     * @param string $varA
     * @param string $opr
     * @param string $varB
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function where($varA, $opr, $varB)
    {
        $str = " WHERE $varA $opr $varB ";

        $this->queryStr .= $str;
        $this->hasWhere = true;
        return $this;
    }

    /**
     * @param bool $condition
     * @param string $varA
     * @param string $opr
     * @param string $varB
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function whereIf($condition, $varA, $opr, $varB)
    {
        if($condition){
            $this->where($varA, $opr, $varB);
        }

        return $this;
    }

    /**
     * @param bool $condition
     * @param string $varA
     * @param string $opr
     * @param string $varB
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function andWhereIf($condition, $varA, $opr, $varB)
    {
        if($condition){
            $this->andWhere($varA, $opr, $varB);
        }

        return $this;
    }

    /**
     * @param bool $condition
     * @param string $varA
     * @param string $opr
     * @param string $varB
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function orWhereIf($condition, $varA, $opr, $varB)
    {
        if($condition){
            $this->orWhere($varA, $opr, $varB);
        }

        return $this;
    }


    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function with()
    {
        $this->createBlock("WITH", func_get_args());
        return $this;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function set()
    {
        $this->createBlock("SET", func_get_args());
        return $this;
    }

    /**
     * @param string $varA
     * @param string $opr
     * @param string $varB
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function andWhere($varA, $opr, $varB){
        if($this->hasWhere){
            $str = " AND $varA $opr $varB ";
        }
        else{
            $str = " WHERE $varA $opr $varB ";
        }

        $this->queryStr .= $str;
        return $this;
    }

    /**
     * @param string $varA
     * @param string $opr
     * @param string $varB
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function orWhere($varA, $opr, $varB){
        if($this->hasWhere){
            $str = " OR $varA $opr $varB ";
        }else{
            $str = " WHERE $varA $opr $varB ";
        }


        $this->queryStr .= $str;
        return $this;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function get(){
        $this->createBlock("RETURN", func_get_args());
        return $this;
    }

    /**
     * @return $this
     */
    public function copy()
    {
        $q = clone $this;
        return $q;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function createUnique()
    {
        $this->createBlock("CREATE UNIQUE", func_get_args());
        return $this;
    }

    /**
     * @param string $field
     * @param string $order
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function orderBy($field, $order = 'asc')
    {
        $order = strtolower($order);
        if($order == 'asc' || $order == 'desc') $this->queryStr .= " ORDER BY $field $order";
        return $this;
    }

    /**
     * @param int $skip
     * @param int $limit
     * @param string $fieldOrder
     * @param string $order
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function paginate($skip, $limit, $fieldOrder, $order = 'asc')
    {
        $this->orderBy($fieldOrder, $order);
        $this->skip($skip);
        $this->limit($limit);

        return $this;
    }

    /**
     * @param int $limit
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function limit($limit)
    {
        if($limit > 0) $this->queryStr .= " LIMIT $limit";
        return $this;
    }

    /**
     * @param int $offset
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function skip($offset)
    {
        if($offset > 0) $this->queryStr .= " SKIP $offset";
        return $this;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function delete()
    {
        $this->createBlock("DELETE", func_get_args());
        return $this;
    }

    /**
     * @param array $params
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function params(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function addParam($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * (a) - [r] -> (b)
     *
     * @param string $relationName
     * @param array|null $relationProps
     * @param string $fromLabel
     * @param string $fromId
     * @param string $toLabel
     * @param string $toId
     * @param array|null $return
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function addRelationById($relationName, $relationProps, $fromLabel, $fromId, $toLabel, $toId, $return = null)
    {
        $this->match("(a:$fromLabel {id:{aid}})", "(b:$toLabel {id:{bid}})");
        if(empty($relationProps)){
            $this->createUnique("(a) - [r:$relationName] -> (b)");
        }else{
            $this->createUnique("(a) - [r:$relationName {relprops}] -> (b)");
        }
        if(empty($return)){
            $this->get(["count(a) as ca", "count(b) as cb", "a.id", "b.id"]);
        }else{
            $this->get($return);
        }
        $this->params(['aid'=>$fromId, 'bid'=>$toId, 'relprops'=>$relationProps]);

        return $this;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\DataCollection
     */
    public function run()
    {
        $res = $this->curl->post($this->uri,
            [
                'Accept'=>'application/json; charset=UTF-8',
                'Content-Type'=>'application/json',
                'X-Stream'=>'true',
            ],
            json_encode([
                'query'=>$this->queryStr,
                'params'=>$this->getParams(),
            ])
        ,['exceptions'=>false])->send();

        $data = $res->json();

        if($res->getStatusCode() == 200){
            return new DataCollection($data['columns'], $data['data']);
        }else{
            throw new Neo4jException($data['message']);
        }
    }

    protected function createBlock($grammar, array $arg)
    {
        $str = " $grammar ";
        if(is_array($arg[0])){
            $this->queryStr .= $str . implode(',', $arg[0]);
        }else{
            $this->queryStr .= $str . implode(',', $arg);
        }
    }

    public function toString()
    {
        return $this->queryStr;
    }

    /**
     * @param string $str
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function raw($str)
    {
        $this->queryStr = $str;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return empty($this->params) ? null : $this->params;
    }
} 