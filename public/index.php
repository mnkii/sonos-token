<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use GuzzleHttp\Client as Client;
use DI\Container;

require __DIR__ . '/../vendor/autoload.php';

$config = [
    'client_key' => '{ your client key goes here }',
    'client_secret' => '{ your secret goes here }',
];

$container = new Container();
AppFactory::setContainer($container);
$container->set('config', $config);

$app = AppFactory::create();

$app->get('/', function (Request $request, Response $response, $args) {

    $query = http_build_query([
        'client_id' => $this->get('config')['client_key'],
        'response_type' => 'code',
        'state' => 'test',
        'scope' => 'playback-control-all',
        'redirect_uri' => sprintf('https://%s/redirect', $_SERVER['HTTP_HOST'])
    ]);

    $response->getBody()->write(sprintf('<a href="https://api.sonos.com/login/v3/oauth?%s">Sign in with Sonos</a>',
        $query));
    return $response;

});

$app->get('/redirect', function (Request $request, Response $response, $args) {

    $client = new Client();

    $auth = base64_encode(sprintf('%s:%s', $this->get('config')['client_key'], $this->get('config')['client_secret']));

    $tokenResponse = $client->request('POST', 'https://api.sonos.com/login/v3/oauth/access', [
        'headers' => [
            'Authorization' => 'Basic ' . $auth
        ],
        'form_params' => [
            'grant_type' => 'authorization_code',
            'code' => $request->getQueryParams()['code'],
            'redirect_uri' => sprintf('https://%s/redirect', $_SERVER['HTTP_HOST'])
        ],
    ]);

    $tokenData = json_decode($tokenResponse->getBody(), true);

    $response->getBody()->write("<h1>'Create Token' Response</h1>");
    $response->getBody()->write('<dl>');
    foreach ($tokenData as $k => $v) {
        $response->getBody()->write(sprintf('<dt>%s</dt><dd>%s</dd>', $k, $v));
    }
    $response->getBody()->write('</dl>');

    $response->getBody()->write("<h1>Example curl request</h1>");
    $response->getBody()->write("<h2><a href='https://developer.sonos.com/reference/control-api/households/' target='_blank'>Get household</a></h2>");
    $response->getBody()->write(
        sprintf(
            '<pre><code>curl -H "Content-Type: application/json" -H "Authorization: Bearer %s" https://api.ws.sonos.com/control/api/v1/households</code></pre>',
            $tokenData['access_token']
        )
    );

    $response->getBody()->write(
        '<small>Warning: Do not share these keys / curl commands, otherwise people will be able to control your Sonos system.</small>'
    );
    return $response;
});

$app->run();
