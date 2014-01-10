<?php
namespace Witooh\Neo4j\Cypher;

class Mapper {

    /**
     * @param $data
     * @return mixed
     */
    public function mapData($data)
    {
        if(is_array($data)){
            if(array_key_exists('data',$data)){
                return $data['data'];
            }
        }

        return $data;
    }

    public function mapStructure($data)
    {
        if(isset($data['row']))
        {
            return $data['row'];
        }

        return $data;
    }
} 