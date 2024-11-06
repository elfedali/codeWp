<?php

use CodeWp\Core\BuildHTML;
use CodeWp\Core\HTMLElement;
use Slim\Views\PhpRenderer;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();

$app->add(new Zeuxisoo\Whoops\Slim\WhoopsMiddleware());

$app->get('/', function (Request $request, Response $response, $args) {
    // Usage example:

    $htmlBuilder = new BuildHTML(__DIR__ . '/../page.yml');
    $html =  $htmlBuilder->build();

    $renderer = new PhpRenderer(__DIR__ . '/../templates');
    return $renderer->render($response, 'welcome.php', ['html' => $html]);
});


$app->get('/html', function (Request $request, Response $response, $args) {

    $elm = new HTMLElement('div');
    $elm->setAttribute('class', 'container');
    $elm->setAttribute('id', 'main');
    $elm->setAttribute('style', 'color:red;font-size:20px;');
    $elm->addContent("Hello");

    $renderer = new PhpRenderer(__DIR__ . '/../templates');

    return $renderer->render($response, 'html.php', ['html' => $elm]);
});
$app->run();
