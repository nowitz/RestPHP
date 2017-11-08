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

function sendNotification($name)
{
//    $content = array(
//        "en" => 'English Message'
//    );

    $fields = array(
        'app_id' => "5db769bf-d46f-4423-9a58-9d33d0cc6418",
        'filters' => array(array("field" => "tag", "key" => "calendar", "relation" => "=", "value" => $name)),
        'template_id' => "89df93f6-ce13-407f-b672-adf2ece7c51a"
    );

    $fields = json_encode($fields);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
        'Authorization: Basic NzI0ODE3YTYtOTRiNC00MTgyLWFkZGEtYzk5YzdlNWM2Mzdl'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HEADER, FALSE);
    curl_setopt($ch, CURLOPT_POST, TRUE);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

// ----------------------------- Login -----------------------------


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

/**
 * Metoda, ktera zpracovava veskere POST pozadavky
 */
$app->post('/login/admin', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $params = $request->getParsedBody();
    $result = $this->dibi->fetch('SELECT * FROM calendar WHERE name = %s AND password_edit = %s', $params["calendar"], $params["pass"]);
    return $response->withJson($result, 201);
});

// ----------------------------- Check calendar name -----------------------------

/**
 * Metoda, ktera overi nazev kalendare
 */
$app->get('/check/{name}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $result = $this->dibi->query('SELECT * FROM calendar WHERE name = %s', $args['name'])->getRowCount();
    return $response->withJson($result, 200);
});

// ----------------------------- Automaticky notifikace -----------------------------

/**
 * Metoda pro zasilani automatickych notfikaci
 */
$app->get('/notification', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $resultValue = array();
    $today = new DateTime('');
    $result = $this->dibi->query('SELECT id_name FROM flipper WHERE open = 0 AND date >= %t AND date <= %t',
        $today->setTime(0, 0, 0)->format("Y-m-d H:i:s"), $today->setTime(23, 59, 59)->format("Y-m-d H:i:s"))->fetchAll();
    foreach ($result as $value) {
        $rs = sendNotification($value["id_name"]);
        array_push($resultValue, $rs);
    }
    $this->logger->info("nofitication-OK", $resultValue);
    return $response->withJson($resultValue, 200);
});


// ----------------------------- Calendar -----------------------------

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
 * 1 - vytvoreno
 * 2 - duplicita
 */
$app->post('/calendar', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $count = $this->dibi->query('SELECT * FROM calendar WHERE name = %s', $request->getParsedBody()['name'])->getRowCount();

    if ($count == 0) {
        $result = $this->dibi->query('INSERT INTO calendar', $request->getParsedBody());
    } else {
        $result = 2;
    }
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

// ----------------------------- User -----------------------------

/**
 * Metoda, ktera zpracovava veskere GET pozadavky
 */
$app->get('/user[/{name}]', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $query = array();
    array_push($query, 'SELECT * FROM user');

    if (isset($args["name"])) {
        array_push($query, 'WHERE id_name = %s', $args["name"]);
    }
    $result = $this->dibi->query($query)->fetchAll();
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere POST pozadavky
 */
$app->post('/user', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('INSERT INTO user', $request->getParsedBody());
    return $response->withJson($result, 201);

});

/**
 * Metoda, ktera zpracovava veskere PUT pozadavky
 */
$app->put('/user/{name}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('UPDATE user SET ', $request->getParsedBody(), 'WHERE id_name = %s', $args['name']);
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere DELETE pozadavky
 */
$app->delete('/user/{name}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('DELETE FROM user WHERE id_name = %s', $args['name']);
    return $response->withJson($result, 204);
});


// ----------------------------- Flipper -----------------------------

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

    $arrayParam = $request->getParsedBody();

    //pokud pri updatu flipperu je nastavenej datum na vetsi den nez je dnesek, dojde k updatu open = 0 (nastavi se FRONT)
    if (array_key_exists("date", $arrayParam)) {
        $expire_dt = new DateTime($arrayParam["date"]);
        $today = new DateTime('');
        if ($today->format("Y-m-d") < $expire_dt->format("Y-m-d")) {
            $arrayParam['open'] = 0;
        }
    }

    $result = $this->dibi->query('UPDATE flipper SET ', $arrayParam, 'WHERE id_name = %s AND id = %s', $args['name'], $args['id']);
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere PATCH pozadavky
 * Jedna se o kontrolu datumu. Pokud spatnej datum tak return 2
 */
$app->patch('/flipper/{name}/{id}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $body = $request->getParsedBody()["date"];

    $expire_dt = new DateTime($body["date"]);
    $today = new DateTime('');
//    dumpf($today);
//    dumpf($expire_dt);
//    dumpf($today->format("Y-m-d") < $expire_dt->format("Y-m-d"));
    if ($today->format("Y-m-d") < $expire_dt->format("Y-m-d")) {
//      podvod
        $result = 2;
    } else {
//      je to OK, update zaznamu
        if ($request->getParsedBody()["template"] == 0) {
            $result = $this->dibi->query('UPDATE flipper SET ', ['open' => 1], 'WHERE id_name = %s AND id = %s', $args['name'], $args['id']);
        }
    }
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


// ----------------------------- Warning -----------------------------

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


// ----------------------------- Pays -----------------------------

/**
 * Metoda, ktera zpracovava veskere GET pozadavky
 */
$app->get('/pays[/{id}]', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }

    $query = array();
    array_push($query, 'SELECT * FROM pays');

    if (isset($args["id"])) {
        array_push($query, 'WHERE id = %i', $args["id"]);
    }
    $result = $this->dibi->query($query)->fetchAll();
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere POST pozadavky
 */
$app->post('/pays', function (Request $request, Response $response) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('INSERT INTO pays', $request->getParsedBody());
    return $response->withJson($result, 201);

});

/**
 * Metoda, ktera zpracovava veskere PUT pozadavky
 */
$app->put('/pays/{id}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('UPDATE pays SET ', $request->getParsedBody(), 'WHERE id = %i', $args['id']);
    return $response->withJson($result, 200);
});

/**
 * Metoda, ktera zpracovava veskere DELETE pozadavky
 */
$app->delete('/pays/{id}', function (Request $request, Response $response, array $args) {

    if (!verifyAuthorization($request, $this)) {
        return $response->withJson("Bad authorization!", 401);
    }
    $result = $this->dibi->query('DELETE FROM pays WHERE id = %i', $args['id']);
    return $response->withJson($result, 204);
});

