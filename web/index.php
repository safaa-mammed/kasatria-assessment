<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);


$app->get('/', function (Request $request, Response $response) {
    // Redirect to login.php
    return $response->withHeader('Location', '/login.php')->withStatus(302);
});

$app->get('/table', function (Request $request, Response $response, $args) {
    return $response->withHeader('Location', '/doc.html')->withStatus(302);
});

$app->get('/table', function (Request $request, Response $response, $args) {
    return $response->withHeader('Location', '/login.php')->withStatus(302);
});
$app->get('/logout', function (Request $request, Response $response, $args) {
    return $response->withHeader('Location', '/logout.php')->withStatus(302);
});
$app->get('/google-oauth', function (Request $request, Response $response, $args) {
    return $response->withHeader('Location', '/google-oauth.php')->withStatus(302);
});
// @codeCoverageIgnoreStart
if (PHP_SAPI != 'cli') {
    $app->run();
}
// @codeCoverageIgnoreEnd

return $app;
