# BOTK\Rdf
[![Build Status](https://img.shields.io/travis/linkeddatacenter/BOTK-rdf.svg?style=flat-square)](http://travis-ci.org/linkeddatacenter/BOTK-rdf)
[![Code Coverage](https://img.shields.io/scrutinizer/coverage/g/linkeddatacenter/BOTK-rdf.svg?style=flat-square)](https://scrutinizer-ci.com/g/linkeddatacenter/BOTK-rdf)
[![Latest Version](https://img.shields.io/packagist/v/botk/rdf.svg?style=flat-square)](https://packagist.org/packages/botk/rdf)
[![Total Downloads](https://img.shields.io/packagist/dt/botk/rdf.svg?style=flat-square)](https://packagist.org/packages/botk/rdf)
[![License](https://img.shields.io/packagist/l/botk/rdf.svg?style=flat-square)](https://packagist.org/packages/botk/rdf)

An EasyRdf extension to support authentication, reasoning, LDP server endpoints and more.

This is a BOTK package. Please refer to http://ontology.it/tools/botk for more info
about BOTK project.

## Quick start

The package is available on [Packagist](https://packagist.org/packages/botk/rdf).
You can install it using [Composer](http://getcomposer.org).

```bash
composer require botk/rdf
```

Some code examples in samples directory.

# RDF package documentation


## Abstract
An EasyRdf extension to support authentication,reasoning, LDP server endpoints and more.

This package is part of [Business Ontology ToolKit (BOTK)](http://ontology.it/tools/botk/).

This package contains a set of libraries to work with RDF data structure. It provides parsers for common RDF
          serializations (RDF/JSON, N-Triples, RDF/XML, Turtle) and methods for loading RDF data from web
          resources and&nbsp; [SPARQL1.1 end-points](http://www.w3.org/TR/sparql11-query/). 
Beside this it contains an implementation of a simple end point to publish linked data supporting many serialization and html according [Linked Data Platform Editor Draft specification](https://dvcs.w3.org/hg/ldpwg/raw-file/default/ldp.html)

This package is implemented as an extension of [EasyRDF](http://www.easyrdf.org/) libraries. Refer to [EasyRDF documentation](http://www.easyrdf.org/docs) for usage info and examples.


# Installation

This package follows[ BOTK guide line for installation](../overview/#installation) and require [composer](http://getcomposer.org/).

Add following dependances to **composer.json** file in your project root:

```
    {
      "require": {
        "botk/rdf": "*",
        "botk/context": "*"
      }
    }
```
Note that botk/context dependency should be added just if you use [SLDPS Classes](#SimpleLinkedDataEndpoint).


# Overview

This library extends the default [http
          client of EasyRDF](http://www.easyrdf.org/docs/api/EasyRdf_Http_Client.html) to support basic authentication and and provides two content negotiation policies to
        manage consistently representations&nbsp; for RDF graphs and for Sparql Results data models. Beside this, it
        implements a simple Linked Data Platform Server to publish linked data through a remote sparql server.

# HttpClient class

This class is a replacement for[EasyRDF http client class](http://www.easyrdf.org/docs/api/EasyRdf_Http_Client.html). It adds the `setAuth(string
          $user, string $password)` method supporting basic http authentication type. HttpClient in turn it is a
        lightware alternative to *Zend_Http_client* or *Guzzle* libraries.


HttpClient is normally used as HTTP protocol wrapper for all EasyRDF specialized clients and for [SparqlClient](#SparqlClient)
        through [`EasyRdf_Http::setDefaultHttpClient()`](http://www.easyrdf.org/docs/api/EasyRdf_Http.html)
        method, but it can be also used as a generic Web Resource client.

For example, to use simple client identity in accessing a remote sparql endpoint execute:

    // define common properties of http client to use to access RDF web resources
    $httpClient = new BOTK\RDF\HttpClient;
    $httpClient->setAuth('username', 'password');
    EasyRdf_Http::setDefaultHttpClient($httpClient);

    // access a private sparql end-point that requires basic authentication
    $sparql = new EasyRdf_Sparql_Client('https://private/sparql');
    $result=$sparql->query('SELECT * WHERE {?s ?p ?o} LIMIT 10');

HttpClient interface is compatible with [ZEND-Http_client
          library](http://framework.zend.com/manual/2.2/en/modules/zend.http.client.html).

HttpClient provides a simple helper to create an authenticated HTTP client and use it in EasyRdf&nbsp; with a
        single call of the static method `HttpClient::useIdentity(string $usename = null, string $password=null, $timeout=null )` . If not specified the client will reuse the timeout of the calling script (if
        available) or 30 sec. otherwise. Using the helper, the previous code can be shorten as:

    BOTK\RDF\HttpClient::useIdentity('username','password');

    $sparql = new EasyRdf_Sparql_Client('https://private/sparql');
    $result=$sparql->query('SELECT * WHERE {?s ?p ?o} LIMIT 10');

.

# Content negotiation policies

### RDF

This content negotiation policy is designed for applications that use&nbsp; EasyRff_Graph data structure as
        Resource Model.

It provide following response and request representations:

      RDF class define following renderer functions: 
<dl><dt>RDF::turtleRenderer(mixed $data, Standard::n3Renderer(mixed
          $data)</dt><dd> Serializes data structure as RDF text/turle </dd><dt>RDF::rdfxmlRenderer(mixed
          $data)</dt><dd> Serializes data structure as RDF application/xml+rdf. </dd><dt>RDF::jsonRenderer(mixed
          $data)</dt><dd> Serializes data structure using json</dd><dt>RDF::ntriplesRenderer(mixed
          $data)</dt><dd> serializes data structure RDF ntriples. </dd><dt>RDF::htmlRenderer(mixed
          $data)</dt><dd> serializes data structure as html. </dd><dt>RDF::serialphpRenderer(mixed
          $data)</dt><dd> serializes data structure as php.</dd></dl>

### SparqlClientResult

This content negotiation policy is designed for applications that use&nbsp; EasyRdf_Sparql_Result data
        structure as Resource Model.

It provide following response and request representations:



# Simple Linked Data Platform Server (SLDPS)

This set of classes allow you to implement a simple endpoint to publish linked data according last [Linked Data Platform Woking Group Draft Specifications](http://www.w3.org/2012/ldp/wiki/Main_Page)
          . The provided classes can be used to base&nbsp; Linked Data Platform Server Implementations.

Here is a simple script that realizes an LDP PAGING server:

```
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

    $errorManager = BOTK\Core\ErrorManager::getInstance()->registerErrorHandler();    
    try {                                                      
        echo BOTK\Core\EndPointFactory::make('MyRouter')->run();
    } catch ( Exception $e) {
        echo $errorManager->render($e); 
    }
```

## LDPController class

This class provides an generic abstract implementation of a LDP paged controller. In order to get a working
          implementation you have to implement three methods:

*   `detectIfHasNextPage()`: that must return true if another linked data page is available. If it
                is not provided, paging features will be disabled.
*   `linkData()`: that populate the protected var `resultGraph` (an EasyRdf_Graph object)
                with linked resource
*   `linkMetaData()`: that optionally add metadata to `resultGraph`

It provides a simple template engine (from Core Package) populated with a set&nbsp; of predefine variables
          placeholders:

*   `{pagedResourceUri}` :&nbsp; the requested uri without page info;
*   `{strippedUri}` : the requested uri without query strings and fragment.
*   `{requestUri}` :&nbsp; the requested uri as written by user (with or partial page info)

The protected `context` variable contains an instance of Core PagedResourceContext.

## SparqlLDPController Class

This class extends LDPController and provides a generic&nbsp; implementation of a Linked Data Platform Server
          that publish as Linked Data Resources some resources contained in a sparql Server.

You need to redefine following variables to override defaults :

<dl><dt>$username</dt><dd>the username required to access sparql update endpoint, default is empty</dd><dt>$password</dt><dd>the password required to access sparql update endpoint. only Basic method supported, default is empty</dd><dt>$endpoint</dt><dd>the sparql endpoint uri. The default is empty.&nbsp; Examples of valid open sparql endpoints are:
            http://dbpedia.org/sparql,&nbsp; http://lod.openlinksw.com/sparql, http://linkedopencommerce.com/sparql </dd><dt>$pagingPolicy</dt><dd>it is a string value that can be `AGGRESSIVE` or `CONSERVATIVE` to drive next page
            detection algorithm. The default is&nbsp; AGGRESSIVE</dd><dt>$constructor</dt><dd>must contain a valid&nbsp; sparq query template that build a graph with linked data. Do not include&nbsp;
            LIMIT/OFFSET clause.The default is empty.</dd><dt>$metadata</dt><dd>optionally contains a turtle template with paged resource metadata. The default is empty</dd></dl>

SparqlLDPController class implements LDPController abstract methods:

*   it&nbsp; provides an implementation of `detectIfHasNextPage()` based on a parametric
                algorithm that use the ` $pagingPolicy` variable. If `$pagingPolicy`=AGGRESSIVE than `detectIfHasNextPage()`
                method returns true when last query to sparqls server resulted in exactly `$pagesize` triples,
                false otherwise. If&nbsp; `$pagingPolicy`=CONSERVATIVE returns true when last query to sparql
                server was not empty.
*   It provides an implementation of `linkData()` methods based on the sparql query in `$selector`
                variable.
*   It provides an implementation of `linkMedtadata()` methods based on the turtle template in in
                `$metadata` variable.

Beside this,&nbsp;SparqlLDPController class implements&nbsp; :

*   `get($resourceId=null)`: a default controller get methods with an optional argument that is set
                by router.

This class add following predefined variables placeholders to the simple template engine:

*   `{endpoint}`: the value of the variable of the same name.
*   `{limit}`: same value of PagedResouceContext::getPageSize()
*   `{offset}`: a calculated value as&nbsp; PagedResouceContext::getPageNum() *&nbsp;
                PagedResouceContext::getPageSize()
*   `{username}`: the value of the variable of the same name.
*   `{containerUri}`: the guessed uri of the container extracted from {strippedUri}
*   `{resourceId}`: the value of the router template var argument. If resouceId is an array, it is
                transformed in string with `implode('/',$resourceId)` php instruction.
*   `{encodedResourceId}`: same as {resourceId} but url encoded.



You can use these variables, plus the ones defined in LDPController, in $constructor and $metadata templates.

        Here is a full example of a contanainer/resource LDP-PAGING implementation using void and prov ontology to
        annotate resources:
```
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

    $errorManager = BOTK\Core\ErrorManager::getInstance()->registerErrorHandler();    
    try {                                                      
        echo BOTK\Core\EndPointFactory::make('MyRouter')->run();
    } catch ( Exception $e) {
        echo $errorManager->render($e); 
    }
```

## License

 Copyright © 2016 by  Enrico Fagnoni at [LinkedData.Center](http://LinkedData.Center/)®

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
