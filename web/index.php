<?php

/*
 * Copyright 2019 Google LLC.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

// [START appengine_flex_helloworld_index_php]
require_once __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;

// Create App
$app = AppFactory::create();

// Display errors
$app->addErrorMiddleware(true, true, true);

// $app->get('/', function (Request $request, Response $response) {
//     $response->getBody()->write("Hello World");
//     return $response;
// });

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
// [END appengine_flex_helloworld_index_php]
