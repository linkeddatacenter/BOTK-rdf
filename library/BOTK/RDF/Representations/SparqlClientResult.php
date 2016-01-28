<?php
namespace BOTK\RDF\Representations;

use BOTK\Core\Representations\AbstractContentNegotiationPolicy,
    BOTK\Core\Representations\Standard;
use EasyRdf_Sparql_Result as Sparql_Result;

/*
 * Supports html and text rendering  
 */

class SparqlClientResult extends AbstractContentNegotiationPolicy
{    
    protected static $renderers = array(
          'text/html'                         => 'htmlRenderer',
          'text/plain'                        => 'textplainRenderer',
          'application/x-php'                 => 'serialphpRenderer',
    );

    public static function textplainRenderer(Sparql_Result $data)
    {
        static::setContentType('text/plain');
        return $data->dump('text');
    }
    
          
    public static function htmlRenderer(Sparql_Result $graph)
    {
        static::setContentType('text/html');
        return Standard::htmlSerializer( $graph->dump('html'), 
            Standard::$htmlMetadata, get_class($graph), null,null,true);
    }

    
    public static function serialphpRenderer(Sparql_Result $graph)
    {
        static::setContentType('application/x-php');
        return serialize($graph);  
    }
}