<?php
/**
/**
 * BOTK agent to do SPARQL queries to a remote SPARQL end point. 
 * It accepts variable content negotiation policies depending from the passed query content.
 *
 * @copyright  Copyright (c) 2013 Enrico Fagnoni 
 * @license    http://unlicense.org/
 */
require '../vendor/autoload.php';

use BOTK\Core\EndPoint,
    BOTK\Core\WebLink,
    BOTK\Core\Controller,
    BOTK\Core\ErrorManager,
    BOTK\Core\EndPointFactory;
use BOTK\RDF\Representations\RDF,
    BOTK\RDF\Representations\SparqlClientResult,
    BOTK\RDF\HttpClient;
use EasyRdf_Graph as Graph,
    EasyRdf_Sparql_Client as SparqlClient;
    

/**
 * two routes depending from query parameter content
 */
class SparqlAgentRouter extends EndPoint
{
    protected function setRoutes()
    {
        $renderers =  preg_match('/(select|ask)/i', filter_input(INPUT_GET, 'query'))
                ? SparqlClientResult::renderers()
                : RDF::renderers();
                
        $this->get('/', 'SparqlController')->accept($renderers);
    }
}

/**
 * Full hypermedia RESTful state transfer controller
 */
class SparqlController extends Controller
{
    public function get()
    {
        // sanitize request input
        $username = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_STRING);
        $password = filter_input(INPUT_GET, 'password', FILTER_SANITIZE_STRING);
        $endpoint = filter_input(INPUT_GET, 'endpoint', FILTER_SANITIZE_URL);
        $query    = filter_input(INPUT_GET, 'query',    FILTER_UNSAFE_RAW);
        
        // prepare request content model 
        if ($endpoint && $query){
            HttpClient::useIdentity($username,$password);
            $sparql = new SparqlClient($endpoint);
            $result = $sparql->query($query); 
        } else {
            $result = new Graph($_SERVER['REQUEST_URI']);
        }
        
        return self::stateTransfer(
            $result,
            WebLink::factory('sparqlForm.php?'.http_build_query($_GET))->rel('edit')
        );
    }
}


$errorManager = ErrorManager::getInstance()->registerErrorHandler(); 
try {                                                      
    echo EndPointFactory::make('SparqlAgentRouter')->run();
} catch ( Exception $e) {
    echo $errorManager->render($e); 
}
//echo memory_get_usage ();
