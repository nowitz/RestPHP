<?php

require 'vendor/autoload.php';
require 'lib/debug.php';
   
// connect to database
dibi::connect(array(
            'driver'   => 'postgre',    //Zkratka pro PostgreSQL (zkratka pro MySQL - mysqli)
            'host'     => 'localhost',  
            'username' => 'nowitz',
            'password' => 'Sikdydydydgshs',
            'port'     => '5432',       // Defaultni port PostgreSQL (defaultní port MySQL - 3306)
            'database' => 'nowitz',     // Nazev databaze
            'charset'  => 'utf8',
        ));


/**
 * Slouzi pro vypis PHP chyb
 */
$configuration = [
    'settings' => [
        'displayErrorDetails' => true,
    ],
];
$c = new \Slim\Container($configuration);
$app = new Slim\App($c);

/**
 * Metoda, ktera vrati nazev id sloupce z nazvu tabulky
 * @param $table nazev tabulky
 * @return string nazev id sloupce
 */
function getIdName($table){
    $idName = "id";
    $pom = explode("_",$table);

    for ($i = 0; $i<sizeof($pom); $i++) {
        if(substr($pom[$i], -1) == "s"){
            $idName = $idName."_".substr($pom[$i], 0, -1);
        }else{
            $idName = $idName."_".$pom[$i];
        }
    }
    return $idName;
}

/**
 * Metoda, ktera zpracovava veskere GET pozadavky
 */
$app->get('/{table}[/{id}]', function($request,$response, $args){  
    $query = array();
    array_push($query, 'SELECT * FROM %n', $args["table"]);   

    $idName = getIdName($args["table"]);
    
    if (isset($args["id"])){
      array_push($query, 'WHERE %n = %i',$idName, $args["id"]);
    }   
    $result = dibi::query($query)->fetchAll();
    
    return  $response->withJson($result,200); 
  
});

/**
 * Metoda, ktera zpracovava veskere POST pozadavky
 */
$app->post('/{table}', function($request, $response, $args){
    $result = dibi::query('INSERT INTO %n',$args['table'], $request->getParsedBody(),'RETURNING *')->fetch(); 
    return  $response->withJson($result,201);
});

/**
 * Metoda, ktera zpracovava veskere PUT pozadavky
 */
$app->put('/{table}/{id}', function($request, $response, $args){
    $idName = getIdName($args["table"]);
    $result = dibi::query('UPDATE %n SET ',$args['table'], $request->getParsedBody(),'WHERE %n = %i RETURNING *', $idName, $args['id'] )->fetch();
    return  $response->withJson($result,200);
});

/**
 * Metoda, ktera zpracovava veskere DELETE pozadavky
 */
$app->delete('/{table}/{id}', function($request, $response, $args){
    $idName = getIdName($args["table"]);
    $result = dibi::query('DELETE FROM %n WHERE %n = %i ',$args['table'], $idName, $args['id']); 
    return  $response->withJson($result,204);
}); 

$app->run();
