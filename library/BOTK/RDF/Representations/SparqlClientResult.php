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

	// these vars allow to change default html renderer	behaviour
	public static $tableTitle = 'Sparql solutions';
	public static $tableHeader = null;
	public static $tableFooter = null;
	
	
	    
    protected static $renderers = array(
          'text/html'                         => 'htmlRenderer',
          'text/plain'                        => 'textplainRenderer',
          'application/x-php'                 => 'serialphpRenderer',
          'application/json'   				  => 'jsonRenderer',
          'application/csv'   				  => 'csvRenderer',
    );


    public static function jsonRenderer(Sparql_Result $solutions) {
        static::setContentType('application/json');
		
		$fields = $solutions->getFields();
		$a = array();
		foreach($solutions as $solution){
			$rec = New \stdClass;
			foreach ($fields as $field){
				if(isset($solution->$field)) {
					$rec->$field = (string) @$solution->$field;
				}
			}
			$a[]=$rec;	
		}
		
        return json_encode($a);  
    }


    public static function csvRenderer(Sparql_Result $solutions) {
        static::setContentType('application/csv');
		$fields = $solutions->getFields();
				
		$buffer = fopen('php://temp', 'r+');
		
		// add headers
		fputcsv($buffer, $fields);
		
		foreach($solutions as $solution){
			$rec = array();
			foreach ($fields as $key=>$field){
				$rec[] = isset($solution->$field)?($solution->$field):'';
			}
			fputcsv($buffer, $rec);
		}
		rewind($buffer);
		$csv = stream_get_contents($buffer);
		fclose($buffer);
				
        return $csv;  
    }
	

    public static function textplainRenderer(Sparql_Result $solutions) {
        static::setContentType('text/plain');
        return $solutions->dump('text');
    }
    
          
    public static function htmlRenderer(Sparql_Result $solutions) {
        static::setContentType('text/html');
        return static::htmlSerializer( $solutions->dump('html'), Standard::$htmlMetadata, static::$tableTitle, static::$tableHeader , static::$tableFooter,true);
    }

    
    public static function serialphpRenderer(Sparql_Result $solutions) {
        static::setContentType('application/x-php');
        return serialize($solutions);  
    }
	
}