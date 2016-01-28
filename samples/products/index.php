<?php
require '../../vendor/autoload.php'; 

use BOTK\Core\EndPoint,
    BOTK\Core\Caching;
use BOTK\RDF\Representations\RDF;
use BOTK\RDF\SLDPS\SparqlLDPController;


class MyRouter extends EndPoint
{  
    protected function setRoutes() 
    {
        $this->get('/', 'ProductsController')
            ->accept(RDF::renderers())
            ->through($this->representationCachingProcessor(Caching::SHORT));
    }
}


class ProductsController extends SparqlLDPController
{
    protected 
        $pagesize       = 10,
        $pagingPolicy   = self::CONSERVATIVE,
        $endpoint       = 'http://linkedopencommerce.com/sparql/',
        $constructor    = '
            PREFIX gr:  <http://purl.org/goodrelations/v1#>
            DESCRIBE ?product WHERE {
                ?product a gr:ProductOrServiceModel.  
            }
        ';
}


// fix a bug of Easy RDF
EasyRdf_Format::register(
    'turtle',
    'Turtle Terse RDF Triple Language',
    'http://www.w3.org/TR/turtle/',
    array(
        'text/turtle' => 1.0,
        'application/turtle' => 0.7,
        'application/x-turtle' => 0.7
    ),
    array('ttl')
);


$errorManager = BOTK\Core\ErrorManager::getInstance()->registerErrorHandler();    
try {                                                      
    echo BOTK\Core\EndPointFactory::make('MyRouter')->run();
} catch ( Exception $e) {
    echo $errorManager->render($e); 
}
