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

    public function __construct($curl)
    {
        $this->curl = $curl;
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
        $str = " AND $varA $opr $varB ";

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
        $str = " OR $varA $opr $varB ";

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
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function createUnique()
    {
        $this->createBlock("CREATE UNIQUE", func_get_args());
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