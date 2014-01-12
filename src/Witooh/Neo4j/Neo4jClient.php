<?php
namespace Witooh\Neo4j;

use App;

class Neo4jClient {

    /**
     * @var \Witooh\Neo4j\Cypher\Cypher
     */
    protected $cypher;
    /**
     * @var \Witooh\Neo4j\Index\Index
     */
    protected $index;

    public function __construct($cypher, $index)
    {
        $this->cypher = $cypher;
        $this->index = $index;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Cypher
     */
    public function cypher()
    {
        return $this->cypher;
    }

    /**
     * @return \Witooh\Neo4j\Index\Index
     */
    public function index()
    {
        return $this->index;
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Query
     */
    public function query()
    {
        return $this->cypher->query();
    }

    /**
     * @return \Witooh\Neo4j\Cypher\Transaction
     */
    public function beginTransaction()
    {
        return $this->cypher->beginTransaction();
    }

    /**
     * @param array $queries
     * @return array
     */
    public function beginAndCommit(array $queries){
        return $this->cypher->beginAndCommit($queries);
    }

    /**
     * @param \Witooh\Neo4j\Cypher\Data $row
     * @param array|string $fields
     * @return array
     */
    public function mapData($row, $fields)
    {
        if($row->count() > 0){
            if(is_array($fields)){
                $fieldData = [];
                foreach($fields as $key=>$value){
                    if(isset($row[$value])){
                        $fieldData[$key] = $row[$value];
                    }
                }
                return $fieldData;
            }else{
                return isset($row[$fields]) ? $row[$fields] : null;
            }
        }

        return null;
    }
} 