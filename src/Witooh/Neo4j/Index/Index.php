<?php
namespace Witooh\Neo4j\Index;

class Index {
    /**
     * @var \Witooh\Neo4j\Cypher\Cypher
     */
    protected $cypher;

    public function __construct($cypher)
    {
        $this->cypher = $cypher;
    }

    /**
     * @param $label
     * @param $property
     * @param bool $unique
     */
    public function add($label, $property, $unique = false)
    {
        if($unique){
            $this->cypher->query()
                ->raw("CREATE CONSTRAINT ON (a:$label) ASSERT a.$property IS UNIQUE")
                ->run();
        }else{
            $this->cypher->query()
                ->raw("CREATE INDEX ON :$label($property)")
                ->run();
        }
    }

    /**
     * @param $label
     * @param $property
     * @param bool $unique
     */
    public function drop($label, $property, $unique = false)
    {
        if($unique){
            $this->cypher->query()
                ->raw("DROP CONSTRAINT ON (a:$label) ASSERT a.$property IS UNIQUE")
                ->run();
        }else{
            $this->cypher->query()
                ->raw("DROP INDEX ON :$label($property)")
                ->run();
        }

    }
} 