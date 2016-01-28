<?php
namespace BOTK\RDF\SLDPS;

use BOTK\Core\WebLink;
use BOTK\Context\ContextNameSpace as V;
use EasyRdf_Sparql_Client as SparqlClient,
    EasyRdf_ParsedUri as ParsedUri;

/*
 * This class implements Linked Data Platform server 
 * 
 * It support paging according LDP-PAGING draft specifications
 */
class SparqlLDPController extends LDPController
{
    // Valid values for paging policy:
    const CONSERVATIVE   = 'CONSERVATIVE';
    const AGGRESSIVE     = 'AGGRESSIVE';
      
    // this variables should be redefined  
    protected 
        $username 		= '',
        $password 		= '',
        $endpoint       = '', // i.e. http://lod.openlinksw.com/sparql
        $pagingPolicy   = self::AGGRESSIVE, // self::AGGRESSIVE or self::CONSERVATIVE only
        $type           = 'Resource', // any type in http://www.w3.org/ns/ldp# namespace
        $constructor    = '',
        $metadata       = '';      
                                                        
    protected $hasNextPage = false;
   
    
    public function __construct()
    {
        parent::__construct();
          
        // add variables to template engine
        $this->templateEngine->setVars( array(
            'endpoint'     => $this->endpoint,
            'limit'        => (int)$this->context->getPageSize(),
            'offset'       => (int)$this->context->getPageNum() * (int)$this->context->getPageSize(),
            'username'     => $this->username,
        ));

    }
 
    
    protected function detectIfHasNextPage() 
    {
        return $this->hasNextPage;
    }
   
   
    protected function linkData()
    {
        // use credential to access SPARQL server, if required
        if($this->username) HttpClient::useIdentity($this->username,$this->password);
      
        if(!empty($this->endpoint)&&!empty($this->constructor)){
            // create links to data 
            $sparql = new SparqlClient($this->endpoint);
            $endpointQuery= $this->templateEngine
                ->setTemplate($this->constructor.' LIMIT {limit} OFFSET {offset}')->render();
            $this->resultGraph = $sparql->query($endpointQuery);
            // add queryResource to template vars
            $this->templateEngine->addVar('queryResource', $this->endpoint.'?query='.urlencode($endpointQuery));
            
            // calculate if hasNextPage according pagingPolicy
            $this->hasNextPage = ($this->pagingPolicy == self::AGGRESSIVE ) 
                ?($this->resultGraph->countTriples() == $this->context->getPageSize())
                :(!$this->resultGraph->isEmpty());
        }
        
        return $this;
    }
 
 
    protected function linkMetadata()
    {
        $ldpType = 'http://www.w3.org/ns/ldp#'.$this->type;
        
        $this->weblinks[] = WebLink::factory($ldpType)->rel('type');

        $metadata = $this->templateEngine
            ->setTemplate($this->metadata?$this->metadata:("<{pagedResourceUri}> a <$ldpType> ."))
            ->render();
        $this->resultGraph->parse($metadata, 'turtle'); 

        return $this;
    }
 
 
    private function guessContainerUri()
    {
        $parsedUrl =  new ParsedUri(rtrim($this->context->getQueryStrippedUri(),'/'));
        $parsedUrl->normalise()->setPath( dirname($parsedUrl->getPath()));
        
        return  $parsedUrl->toString();    
    } 
   
    
    public function get($resourceId=null)
    {
        // manage catch all paramethers from routers...
        if (!empty($resourceId) && is_array($resourceId)){
            $resourceId = implode('/',$resourceId);
        }
        // add some variables to be used in metadata template
        $this->templateEngine->setVars( array(
            'containerUri'      => $this->guessContainerUri(),
            'resourceId'        => empty($resourceId)?'':$resourceId,
            'encodedResourceId' => empty($resourceId)?'':urlencode($resourceId)
        ));            
        
        return parent::get();
    }    
}

