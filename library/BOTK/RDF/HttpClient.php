<?php
namespace BOTK\RDF;

class HttpClient extends \EasyRdf_Http_Client
{
    public function setAuth($user=null,$password=null)
    {
        $this->setHeaders( 'Authorization', is_null($user)
                ?null
                :('Basic '.base64_encode("$user:$password")
        ));

        return $this;
    }
    
    /**
     * Just an helper to use HttPClient as default EastRdf_default_client)
     */
     public static function useIdentity($username=null,$password=null,$timeout=null)
     {
         $httpClient = \EasyRdf_Http::getDefaultHttpClient();
         
         // if current default http client does not provide setAuth use a new instance of HttpClient
         if (!($httpClient instanceof \Zend_Http_Client or $httpClient instanceof HttpClient)){
             $httpClient = new HttpClient(null,array (
                'maxredirects'    => 5,
                'useragent'       => 'BOTK HttpClient',
                'timeout'         => ini_get('max_execution_time') || 30,        
             ));
         }
         $httpClient->setAuth($username, $password);
         
         return \EasyRdf_Http::setDefaultHttpClient($httpClient);
     }
    
}
