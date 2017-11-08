<?php

require_once (dirname(__FILE__) . "/tequila_client.php");

/* GOAL: decode the Tequila key and identify / update the user from
 * the authoritative info provided by the Tequila server.
 *
 * Since we don't want to deal with access control, we *DO NOT* create users.
 * However, GASPAR login names and email addresses might change over time.
 */

$client = new TequilaClient();
$tequila_data = $client->fetchAttributes($_GET["key"]);

print_r($tequila_data);

?>


