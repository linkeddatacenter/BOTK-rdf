<?php
/**
 * This scripts just echo a form to call sparqlAgent
 */
require '../vendor/autoload.php';
use BOTK\Core\Representations\Standard;

$endpoint   = @$_REQUEST['endpoint']?:'http://dbpedia.org/sparql';
$query      = @$_REQUEST['query']?:'SELECT * WHERE { ?s ?p ?o } LIMIT 10';
$username   = @$_REQUEST['username']?:'';
$password   = @$_REQUEST['password']?:'';

ob_start(); //start buffering output
?>
    <form method="get" action="sparqlAgent.php">
        <details><summary>Use sparql client identity:</summary>
            <label for="username" id="label_for_username">Username: </label>
            <input type="text" name="username" id="username" value="<?= $username?>" size="40" />
            <label for="password" id="label_for_password">Password: </label>
            <input type="password" name="password" id="password" value="<?= $password?>" size="40" />
        </details>
        <label for="endpoint" id="label_for_endpoint">Endpoint: </label>
        <input type="text" name="endpoint" id="endpoint" value="<?= $endpoint?>" size="80" />
        <details><summary>predefined namespaces</summary>
        <pre><code>
<?php 
        foreach(EasyRdf_Namespace::namespaces() as $prefix => $uri) {
            print "\tPREFIX $prefix: &lt;".htmlspecialchars($uri)."&gt;<br />\n";
        }
?>
        </code></pre>
        </details>
        <label for="query" id="label_for_query">Query: </label>
        <textarea name="query" id="query" cols="80" rows="10"><?= $query?></textarea>
        <input type="reset" value="Reset" /><input type="submit" value="Submit" />
    </form>
<?php
$form=ob_get_contents();
ob_end_clean();

echo Standard::htmlSerializer( 
    $form, 
    Standard::$htmlMetadata, 
    $title='SPARQL query Form',
    "<h1>$title</h1>",
    '',
    true // treat form as an html fragment
);
