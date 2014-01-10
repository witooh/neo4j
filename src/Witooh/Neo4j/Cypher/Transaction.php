<?php
namespace Witooh\Neo4j\Cypher;

use App;
use Witooh\Neo4j\Neo4jException;

class Transaction {

    protected $uri='/db/data/transaction';

    protected $id;

    protected $isClose;


    /**
     * @var \Guzzle\Http\Client
     */
    protected $curl;

    public function __construct()
    {
        $this->curl = App::make('neo4j.curl');
        $this->id = null;
        $this->isClose = true;
    }

    /**
     * @param Query $query
     */
    public function addStatement(Query $query)
    {
        $res = $this->send('POST', "{$this->uri}/{$this->id}", json_encode([
            'statements'=>[
                ['statement'=>$query->toString(), 'parameters'=>$query->getParams()]
            ]
        ]));

        $data = $res->json();
        if(count($data['errors']) == 0){
            return new DataCollection($data['results'][0]['columns'], $data['results'][0]['data']);
        }else{
            throw new Neo4jException($res->json()['errors'][0]['message']);
        }
    }

    public function addStatements(array $querys)
    {
        $statements = [];
        foreach($querys as $query){
            $statements[] = ['statement'=>$query->toString(), 'parameters'=>$query->getParams()];
        }

        $res = $this->send('POST', "{$this->uri}/{$this->id}", json_encode([
            'statements'=>$statements
        ]));

        $data = $res->json();

        if(count($data['errors']) == 0){
            $result = [];
            foreach($data['results'] as $d){
                $result[] = new DataCollection($d['columns'], $d['data']);
            }

            return $result;
        }else{
            throw new Neo4jException($data['errors'][0]['message']);
        }
    }

    public function commit()
    {
        $res = $this->send('POST', "{$this->uri}/{$this->id}/commit", null);

        if($res->getStatusCode() == 200){
            $this->isClose = true;
        }else{
            throw new Neo4jException($res->json()['errors'][0]['message']);
        }
    }

    public function rollback()
    {
        $res = $this->send('DELETE', "{$this->uri}/{$this->id}", null);

        if($res->getStatusCode() == 200){
            $this->isClose = true;
        }else{
            throw new Neo4jException($res->json()['errors'][0]['message']);
        }
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Transaction
     * @throws \Witooh\Neo4j\Neo4jException
     */
    public function beginTransaction()
    {
        $res = $this->send('POST', $this->uri, null);

        $data = $res->json();
        if($res->getStatusCode() == 201){

            $this->setId($data['commit']);
            $this->isClose = false;
        }else{
            throw new Neo4jException($data['errors'][0]['message']);
        }

        return $this;
    }

    protected function setId($str)
    {
        $str = str_replace($this->curl->getBaseUrl().$this->uri.'/', '', $str);
        $str = str_replace('/commit', '', $str);
        $this->id = $str;
    }

    public function getId()
    {
        return $this->id;
    }

    protected function send($method, $uri, $data = null)
    {
        return $this->curl->createRequest($method, $uri,
            [
                'Accept'=>'application/json; charset=UTF-8',
                'Content-Type'=>'application/json',
            ], $data ,['exceptions'=>false])->send();
    }
} 