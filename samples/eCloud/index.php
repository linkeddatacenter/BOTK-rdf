<?php
require '../../vendor/autoload.php'; 


/***********************************************************************************
 * Inline endpoint classes implementation
 **********************************************************************************/

use BOTK\Core\EndPoint,
    BOTK\Core\Caching;
use BOTK\RDF\Representations\RDF;
use BOTK\RDF\SLDPS\SparqlLDPController;


class MyRouter extends EndPoint
{  
    protected function setRoutes()
    {
        $this->get('/', 'DatasetController');
        $this->get('/*','DataDumpController');
        
        $this->always('Accept', RDF::renderers() );
        $this->always('Through', $this->representationCachingProcessor(Caching::SHORT));
    }
}



class DataDumpController extends SparqlLDPController
{
    protected $endpoint = 'http://lod.openlinksw.com/sparql';
    
    protected $constructor  = '
        PREFIX ld: <http://e-artspace.com/s/eCloud#>
        CONSTRUCT {
          ?ldpr ?p ?o . 
        } WHERE { 
            GRAPH <http://e-artspace.com/s/eCloud/> {
                ?ldpr ?p ?o
                FILTER (?ldpr = ld:{resourceId} )
            }
        } LIMIT {limit} OFFSET {offset}
    ';
    
    protected $metadata = '     
        @base <{pagedResourceUri}> .
        @prefix foaf: <http://xmlns.com/foaf/0.1/> .
        @prefix void: <http://rdfs.org/ns/void#> .
        @prefix prov: <http://www.w3.org/ns/prov#> .
        @prefix ldp:  <http://www.w3.org/ns/ldp#> .
        @prefix rdfs: <http://www.w3.org/2000/01/rdf-schema#> .
        @prefix container:  <{containerUri}#> .
        @prefix ld: <http://e-artspace.com/s/eCloud#> .
        <> a ldp:Resource, foaf:Document, prov:Entity ;
            foaf:primaryTopic ld:{resourceId} ;
            void:inDataset container:dataset ;
            rdfs:seeAlso <{endpoint}?query=DESCRIBE%20%3Chttp%3A%2F%2Fe-artspace.com%2Fs%2FeCloud%23{encodedResourceId}%3E> ;
            prov:wasGeneratedBy [
                a prov:Activity ;
                prov:used <{queryResource}> ;
                prov:wasAssociatedWith container:sparqlServerUser ;
           ]
        .    
     ';    
}    


class DatasetController extends SparqlLDPController
{
    protected $type     = 'DirectContainer';
    protected $endpoint = 'http://lod.openlinksw.com/sparql';
    
    protected $constructor  = '
        PREFIX void: <http://rdfs.org/ns/void#>
        PREFIX ldp:  <http://www.w3.org/ns/ldp#>
        CONSTRUCT {
          <{pagedResourceUri}#dataset> void:dataDump ?ldpr .
        } WHERE {
            GRAPH <http://e-artspace.com/s/eCloud/> {
                ?resource a ?type
                FILTER(!isBlank(?resource))
            }        
            BIND( IRI(REPLACE(STR(?resource), "http://e-artspace.com/s/eCloud#","{strippedUri}")) AS ?ldpr)
        }
    ';
    
    protected $metadata = '     
        @base <{pagedResourceUri}> .
        @prefix foaf: <http://xmlns.com/foaf/0.1/> .
        @prefix void: <http://rdfs.org/ns/void#> .
        @prefix prov: <http://www.w3.org/ns/prov#> .
        @prefix ldp:  <http://www.w3.org/ns/ldp#> .
        @prefix : <#> .
        <>  a ldp:DirectContainer, foaf:Document ;
            foaf:primaryTopic :dataset ;
            ldp:membershipResource :dataset ;
            ldp:hasMemberRelation void:dataDump ;
            ldp:insertedContentRelation foaf:primaryTopic ;
            
        .
        :dataset a void:Dataset, prov:Entity ;
            prov:wasGeneratedBy [
                a prov:Activity ;
                prov:used <{queryResource}> ;
                prov:wasAssociatedWith :sparqlServerUser
            ]
        .
        :sparqlServerUser a foaf:Agent;
            foaf:account [
                a foaf:OnlineAccount ;
                foaf:accountServiceHomepage <{endpoint}> ;
                foaf:accountname "{username}" ;
            ]
        .
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
