<?php
namespace BOTK\RDF\SLDPS;

use BOTK\Core\Controller,
    BOTK\Core\Http,
    BOTK\Core\SimpleTemplateEngine,
    BOTK\Core\WebLink;
use BOTK\Context\PagedResourceContext;
use EasyRdf_Graph as Graph;

/*
 * This class implements Linked Data Platform server 
 * 
 * It support paging according LDP-PAGING draft specifications
 */
abstract class LDPController extends Controller
{
    // Redefine paged Resource defaults, if you want...
    protected 
        $pagesize       = 100,
        $plabel         = 'page',
        $pslabel        = 'pagesize';
           
        
    protected $context, $weblinks, $templateEngine, $resultGraph;


    abstract protected function linkData();  
 
    // pseudo abstract methods
    protected function detectIfHasNextPage() { return false; }
    protected function linkMetaData() { return $this; }   
    
    
    public function __construct()
    {
        $this->context = PagedResourceContext::factory( array(
            'plabel'    =>  $this->plabel,
            'pslabel'   =>  $this->pslabel,
            'pagesize'  =>  $this->pagesize
        ));
        $this->weblinks = array();
        $this->resultGraph = new Graph();
        
        // create a new template engine and setup default variables
        $this->templateEngine = SimpleTemplateEngine::factory()
            ->setVars( array(
            'requestedUri'        => $this->context->guessRequestCanonicalUri(),
            'pagedResourceUri'    => $this->context->getPagedResourceUri(),
            'strippedUri'         => $this->context->getQueryStrippedUri(),
        ));
    }

   
    public function get($resourceId=null)
    {
        // call the method that populate resultGraph        
        $this->linkData();
                
        $hasNextPage= $this->detectIfHasNextPage();
            
        // add metadata and weblinks to single-page resource
        if ($this->context->isSinglePageResource()){
            // provide web links as required by LDP specifications
            $this->weblinks[] = WebLink::factory('http://www.w3.org/ns/ldp-paging#Page')->rel('type');
            
            // LDP servers may provide a first page link when responding to requests with any single-page 
            //  resource as the Request-URI.              
            $this->weblinks[] = WebLink::factory($this->context->firstPageUri())->rel('first');            
    
                    
            // LDP servers may provide a last page link in responses to GET requests with any single-page
            //  resource as the Request-URI.
            // LDP servers must provide a next page link in responses to GET requests with any single-page
            //  resource other than the final page as the Request-URI. This is the mechanism by which clients c
            //  an discover the URL of the next page.
            // LDP servers must not provide a next page link in responses to GET requests with the final 
            //  single-page resource as the Request-URI. This is the mechanism by which clients can discover 
            //  the end of the page sequence as currently known by the server.
            if ($hasNextPage){
                $this->weblinks[] = WebLink::factory($this->context->nextPageUri())->rel('next');
            } 
    
            // LDP servers may provide a previous page link in responses to GET requests with any 
            //  single-page resource other than the first page as the Request-URI.
            //  This is one mechanism by which clients can discover the URL of the previous page.
            // LDP servers must not provide a previous page link in responses to GET requests with the 
            //  First single-page resource as the Request-URI. This is one mechanism by which clients 
            //  can discover the beginning of the page sequence as currently known by the server.
            if( $this->context->getPageNum()>0){
                $this->weblinks[] = WebLink::factory($this->context->prevPageUri())->rel('prev'); 
            }
          
            // add metadata about linked Data Single page resource
            $metadata = $this->templateEngine->setTemplate('
                @base <{requestedUri}> .
                @prefix ldp-paging: <http://www.w3.org/ns/ldp-paging#> .
                <> a ldp-paging:Page; ldp-paging:pageOf <{pagedResourceUri}>.
            ')->render();
            $this->resultGraph->parse($metadata, 'turtle');
        } elseif($hasNextPage){                
            // Server side initiated page management:          
            // WARNING THI IMPLEMENTATION USES A LDP SPECIFICATIO FEATURE AT RISK:
            // LDP servers should respond with HTTP status code 333 (Returning Related) to successful
            //   GET requests with any paged resource as the Request-URI, although any appropriate code may be used.
            Http::setHttpResponseCode(333);
            header('Location: '.$this->context->firstPageUri());
            
            // N.B.  333 code is not yet standarized..it is threathed as 302 by many agents
            // In that case you coud return withoud any other payload setup.
            // but if 333 will be standarized the payload is ready to serve first page
        }
        
        // add to result graph the metadata about Linked Data paged resource in first page    
        // Note that this apply both for Single-Page resource and for Paged Resource
        if($this->context->getPageNum()==0) {
            $this->linkMetadata();          
        }   
        
        
        return $this->stateTransfer($this->resultGraph,$this->weblinks);
    } 
}

