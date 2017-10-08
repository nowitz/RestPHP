<?php

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Metoda, ktera vrati nazev id sloupce z nazvu tabulky
 * @param $table nazev tabulky
 * @return string nazev id sloupce
 */
function getIdName($table)
{
    $idName = "name";
    if ($table != "calendar") {
        $idName = "id_name";
    }
//    $pom = explode("_",$table);
//
//    for ($i = 0; $i<sizeof($pom); $i++) {
//        if(substr($pom[$i], -1) == "s"){
//            $idName = $idName."_".substr($pom[$i], 0, -1);
//        }else{
//            $idName = $idName."_".$pom[$i];
//        }
//    }
    return $idName;
}


// Routes

/**
 * Metoda, ktera zpracovava veskere GET pozadavky
 */
$app->get('/{table}[/{id}]', function (Request $request, Response $response, array $args) {

    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    $query = array();
    array_push($query, 'SELECT * FROM %n', $args["table"]);

    $idName = getIdName($args["table"]);

    if (isset($args["id"])) {
        array_push($query, 'WHERE %n = %s', $idName, $args["id"]);
    }
    $result = $this->dibi->query($query)->fetchAll();

//    return $response->withStatus(200)
//        ->withHeader('Content-Type', 'application/json')
//        ->withHeader('Access-Control-Allow-Origin', '*')
//        ->withHeader('Access-Control-Allow-Methods', ['OPTIONS', 'GET', 'POST', 'PUT', 'DELETE'])
//        ->withHeader('Access-Control-Allow-Methods', ['OPTIONS', 'GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'TRACE', 'CONNECT'])
//        ->withHeader('set Access-Control-Max-Age', '1000')
//        ->withHeader('Access-Control-Expose-Headers',['X-Error', 'X-Error-Type', 'X-Access-Token', 'X-Error-Original'] )
//        ->withHeader('Access-Control-Allow-Headers', ['x-requested-with', 'Content-Type', 'authorization', 'accept', 'client-security-token'])
//        ->withHeader('Access-Control-Allow-Headers', ['Origin', 'X-Requested-With', 'Content-Type', 'Accept', 'X-Access-Token'])
//        ->write(json_encode($result));

    return $response->withJson($result, 200);

    // Render index view
//    return $this->renderer->render($response, 'index.phtml', $args);
});

/**
 * Metoda, ktera zpracovava veskere POST pozadavky
 */
$app->post('/{table}', function (Request $request, Response $response, array $args) {
    $params = $request->getParsedBody();
    if ((strcmp($args['table'], "calendar") == 0) && (sizeof($params) == 2)) {
        $result = $this->dibi->fetch('SELECT * FROM %n', $args["table"], 'WHERE name = %s AND password = %s', $params["calendar"], $params["pass"]);
        return $response->withJson($result, 201);
    }
    $result = $this->dibi->query('INSERT INTO %n', $args['table'], $request->getParsedBody());
    return $response->withJson($result, 201);

});

/**
 * Metoda, ktera zpracovava veskere PUT pozadavky
 */
$app->put('/{table}/{id}[/{day}]', function (Request $request, Response $response, array $args) {
    $idName = getIdName($args["table"]);
    if (isset($args["day"])) {
        $result = $this->dibi->query('UPDATE %n SET ', $args['table'], $request->getParsedBody(), 'WHERE %n = %s AND front = %s', $idName, $args['id'], $args['day']);
    }else {
        $result = $this->dibi->query('UPDATE %n SET ', $args['table'], $request->getParsedBody(), 'WHERE %n = %s', $idName, $args['id']);
    }
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere DELETE pozadavky
 */
$app->delete('/{table}/{id}', function (Request $request, Response $response, array $args) {
    $idName = getIdName($args["table"]);
    $result = $this->dibi->query('DELETE FROM %n WHERE %n = %s ', $args['table'], $idName, $args['id']);
    return $response->withJson($result, 204);
});


