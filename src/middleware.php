<?php
// Application middleware

//use Slim\Http\Request;
//use Slim\Http\Response;

// e.g: $app->add(new \Slim\Csrf\Guard);

//$app->add(function (Request $request, Response $response, $next) {
//    $response->getBody()->write('BEFORE');
//    $response = $next($request, $response);
//    $response->getBody()->write('AFTER');

//    $this->logger->info("BEFORE");
//    $response = $next($request, $response);
//    $this->logger->info("AFTER");
//    return $response;
//});

//https://github.com/tuupola/slim-basic-auth
$app->add(new \Slim\Middleware\HttpBasicAuthentication(array(
        "realm" => "Protected",
        "authenticator" => function ($arguments) {
            $pdo = new \PDO('mysql:host=localhost;dbname=happy_windows', "root", "root");

            $sql = "SELECT * FROM calendar WHERE name = '".$arguments["user"]."'"."AND password = '".$arguments["password"]."'";
            $dotaz = $pdo->query($sql);
            $dotaz = $dotaz->fetchColumn();
            return $dotaz;
        },
        "error" => function ($request, $response, $arguments) {
            return $response->withJson("Bad authorization!", 401);
        }
    )
));

//"callback" => function ($arguments) {
//
//},