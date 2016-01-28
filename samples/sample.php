<?php
/**
 * This example shows how to use content management policies for a RDF source.
 * It reads sample.ttl turtle file and renders it in all RDF serialization + html.
 */
require '../vendor/autoload.php';

use BOTK\Core\EndPoint,
    BOTK\Core\Controller,
    BOTK\Core\EndPointFactory,
    BOTK\Core\ErrorManager;
use BOTK\RDF\Representations\RDF;
use EasyRdf_Graph as Graph;


class Router extends EndPoint
{
    protected function setRoutes()
    {
        $this->get('/', 'sampleManager')->accept(RDF::renderers());
    }
}


class sampleManager extends Controller
{
    public function get()
    {
        $graph = new Graph();
        $graph->parseFile('sample.ttl');
        
        return $graph;        
    }    
}

$errorManager = ErrorManager::getInstance()->registerErrorHandler(); 
try {                                                      
    echo EndPointFactory::make('Router')->run();
} catch ( Exception $e) {
    echo $errorManager->render($e); 
}
