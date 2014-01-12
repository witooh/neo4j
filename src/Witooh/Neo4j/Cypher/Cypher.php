<?php
namespace Witooh\Neo4j\Cypher;

use App;

class Cypher {

    /**
     * @var Query
     */
    protected $query;
    /**
     * @var Transaction
     */
    protected $transction;

    public function __construct()
    {
        $this->query = App::make('neo4j.cypher.query');
        $this->transction = App::make('neo4j.cypher.transaction');
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Transaction
     */
    public function beginTransaction()
    {
        $t = App::make('neo4j.cypher.transaction');
        return $t->beginTransaction();
    }

    public function beginAndCommit(array $queries){
        $t = App::make('neo4j.cypher.transaction');
        return $t->beginAndCommit($queries);
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function query()
    {
        return App::make('neo4j.cypher.query');
    }

    /**
     * @param string $from
     * @param string $to
     * @param string $name
     * @param array $props
     * @param bool $unique
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function addRelation($from, $to, $name, $props, $unique = false)
    {
        $varFrom = $this->getVarformQuery($from);
        $varTo = $this->getVarformQuery($to);
        $q = $this->query()
            ->match($from, $to);
        $create = "$varFrom - [r:$name {data}] -> $varTo";
        if($unique){
            $q->createUnique($create);
        }else{
            $q->create($create);
        }
        $q->get('r')->params(['data'=>$props]);

        return $q;
    }

    /**
     * @param string $str
     * @return null|string
     */
    protected function getVarformQuery($str)
    {
        preg_match('/([a-zA-Z0-9_]+):/', $str, $matches);
        return empty($matches) ? null : $matches[0];
    }
} 