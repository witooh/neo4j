<?php
namespace Witooh\Neo4j;

use Illuminate\Support\ServiceProvider;
use Witooh\Neo4j\Cypher\Cypher;
use Witooh\Neo4j\Cypher\Mapper;
use Witooh\Neo4j\Cypher\Query;
use Witooh\Neo4j\Cypher\Transaction;
use Witooh\Neo4j\Index\Index;
use Guzzle\Http\Client;

class Neo4jServiceProvider extends ServiceProvider
{

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('neo4j.curl', function($app){
            $curl = new Client();
            $curl->setBaseUrl($app['config']->get('neo4j.base_url'));
            return $curl;
        });

        $this->registerCypher();

        $this->registerIndex();

        $this->app->singleton('neo4j', function($app){
            return new Neo4jClient(
                $app['neo4j.cypher'],
                $app['neo4j.index']
            );
        });
    }

    public function registerCypher()
    {
        $this->app->singleton('neo4j.cypher', function($app){
            return new Cypher($app['neo4j.cypher.query']);
        });

        $this->app->bind('neo4j.cypher.query', function($app){
            return new Query($app['neo4j.curl']);
        });

        $this->app->singleton('neo4j.cypher.mapper', function(){
            return new Mapper();
        });

        $this->app->bind('neo4j.cypher.transaction', function(){
            return new Transaction();
        });
    }

    public function registerIndex()
    {
        $this->app->singleton('neo4j.index', function($app){
            return new Index($app['neo4j.cypher.query']);
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            'neo4j.curl',
            'neo4j.cypher.query',
            'neo4j.cypher.mapper',
            'neo4j.index',
            'neo4j.cypher.transaction',
            'neo4j',
        ];
    }

}