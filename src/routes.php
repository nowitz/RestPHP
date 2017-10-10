<?php

use Slim\Http\Request;
use Slim\Http\Response;


/**
 * Slouzi pro overeni autorizace
 *
 * @param Request $request
 * @param $content
 * @return bool
 */
function verifyAuthorization(Request $request, $content)
{
    return (strcmp($request->getHeaders()["HTTP_AUTHORIZATION"][0], $content->get('settings')['authorization']) == 0);
}

// Login

/**
 * Metoda, ktera zpracovava veskere POST pozadavky
 */
$app->post('/login', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $params = $request->getParsedBody();
    $result = $this->dibi->fetch('SELECT * FROM calendar WHERE name = %s AND password = %s', $params["calendar"], $params["pass"]);
    return $response->withJson($result, 201);
});


// Calendar

/**
 * Metoda, ktera zpracovava veskere GET pozadavky
 */
$app->get('/calendar[/{name}]', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    // Sample log message
    $this->logger->info("Slim-Skeleton '/' route");

    $query = array();
    array_push($query, 'SELECT * FROM calendar');

    if (isset($args["name"])) {
        array_push($query, 'WHERE name = %s', $args["name"]);
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
$app->post('/calendar', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('INSERT INTO calendar', $request->getParsedBody());
    return $response->withJson($result, 201);

});

/**
 * Metoda, ktera zpracovava veskere PUT pozadavky
 */
$app->put('/calendar/{name}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('UPDATE calendar SET ', $request->getParsedBody(), 'WHERE name = %s', $args['name']);
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere DELETE pozadavky
 */
$app->delete('/calendar/{name}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('DELETE FROM calendar WHERE name = %s ', $args['name']);
    return $response->withJson($result, 204);
});


// Flipper

/**
 * Metoda, ktera zpracovava veskere GET pozadavky
 */
$app->get('/flipper', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('SELECT * FROM flipper')->fetchAll();
    return $response->withJson($result, 200);
});

$app->get('/flipper/{name}[/{id}]', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $query = array();
    array_push($query, 'SELECT * FROM flipper WHERE id_name = %s', $args["name"]);

    if (isset($args["id"])) {
        array_push($query, 'and id = %s', $args["id"]);
    }
    $result = $this->dibi->query($query)->fetchAll();

    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere POST pozadavky
 */
$app->post('/flipper', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('INSERT INTO flipper', $request->getParsedBody());
    return $response->withJson($result, 201);

});

/**
 * Metoda, ktera zpracovava veskere PUT pozadavky
 */
$app->put('/flipper/{name}/{id}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('UPDATE flipper SET ', $request->getParsedBody(), 'WHERE id_name = %s AND id = %s', $args['name'], $args['id']);
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere DELETE pozadavky
 */
$app->delete('/flipper/{name}/{id}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('DELETE FROM flipper WHERE id_name = %s AND id = %s', $args['name'], $args['id']);
    return $response->withJson($result, 204);
});



// Warning

/**
 * Metoda, ktera zpracovava veskere GET pozadavky
 */
$app->get('/warning', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('SELECT * FROM warning')->fetchAll();
    return $response->withJson($result, 200);
});

$app->get('/warning/{name}[/{id}]', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $query = array();
    array_push($query, 'SELECT * FROM warning WHERE id_name = %s', $args["name"]);

    if (isset($args["id"])) {
        array_push($query, 'and id = %s', $args["id"]);
    }
    $result = $this->dibi->query($query)->fetchAll();

    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere POST pozadavky
 */
$app->post('/warning', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('INSERT INTO warning', $request->getParsedBody());
    return $response->withJson($result, 201);

});

/**
 * Metoda, ktera zpracovava veskere PUT pozadavky
 */
$app->put('/warning/{name}/{id}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('UPDATE warning SET ', $request->getParsedBody(), 'WHERE id_name = %s AND id = %s', $args['name'], $args['id']);
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere DELETE pozadavky
 */
$app->delete('/warning/{name}/{id}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('DELETE FROM warning WHERE id_name = %s AND id = %s', $args['name'], $args['id']);
    return $response->withJson($result, 204);
});


