<?php
namespace BOTK\RDF\Representations;

use BOTK\Core\Representations\AbstractContentNegotiationPolicy,
    BOTK\Core\Representations\Standard;
use EasyRdf_Format as Format,
    EasyRdf_Graph as Graph;


/*
 * Supports the default BOTK\Core content management behaviour for RDF resource defining a
 * set of renderers for main RDF serializations  
 */
class RDF extends AbstractContentNegotiationPolicy
{
    protected static $renderers = array(
        'text/turtle'                       => 'turtleRenderer',
        'text/n3'                           => 'turtleRenderer',
        'application/rdf+xml'               => 'rdfxmlRenderer',
        'application/x-php'                 => 'serialphpRenderer',
        'text/plain'                        => 'ntriplesRenderer',
        'text/ntriples'                     => 'ntriplesRenderer',
        'application/json'                  => 'jsonRenderer',
        'application/rdf+json'              => 'jsonRenderer',
        'text/html'                         => 'htmlRenderer',
    );
    
    protected static $parsers = array(
        //tbd
    ); 
 

    /*************************************************************************
     * RDF Renderers
     *************************************************************************/
    
    public static function rdfRenderer(Graph $graph, $formatName)
    {
        $format = Format::getFormat($formatName);
        static::setContentType($format->getDefaultMimeType());
        
        return $format->newSerialiser()->serialise($graph,$formatName);
    } 
   
    
    public static function turtleRenderer(Graph $graph)
    {
        return static::rdfRenderer($graph,'turtle');
    }


    public static function rdfxmlRenderer(Graph $graph)
    {
        return static::rdfRenderer($graph, 'rdfxml');   
    }


    public static function n3Renderer(Graph $graph)
    {
        return static::rdfRenderer($graph, 'n3');      
    }
     

    public static function jsonRenderer(Graph $graph)
    {
        return static::rdfRenderer($graph, 'json' );       
    } 


    public static function ntriplesRenderer(Graph $graph)
    {
        return static::rdfRenderer($graph, 'ntriples');           
    } 
    
          
    public static function htmlRenderer(Graph $graph)
    {
        static::setContentType('text/html');
        return static::htmlSerializer( $graph->dump('html'), Standard::$htmlMetadata, $graph->getUri(), null,null,true);
    }

    
    public static function serialphpRenderer(Graph $graph)
    {
        static::setContentType('application/x-php');
        return serialize($graph);  
    }
}
