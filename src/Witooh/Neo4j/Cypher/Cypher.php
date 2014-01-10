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

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function query()
    {
        return App::make('neo4j.cypher.query');
    }
} 