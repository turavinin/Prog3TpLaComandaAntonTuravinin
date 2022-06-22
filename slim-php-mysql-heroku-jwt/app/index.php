<?php
error_reporting(-1);
ini_set('display_errors', 1);
date_default_timezone_set('America/Argentina/Buenos_Aires');

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';
require_once './middlewares/AutentificadorJWT.php';
require_once './middlewares/Logger.php';

require_once './controllers/EmpleadosController.php';
require_once './controllers/ProductosController.php';
require_once './controllers/MesasController.php';
require_once './controllers/PedidosController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);


// Routes
$app->group('/empleados', function (RouteCollectorProxy $group) {
  $group->get('[/]', \EmpleadosController::class . ':TraerTodos');
  $group->get('/{usuario}', \EmpleadosController::class . ':TraerUno');
  $group->post('[/]', \EmpleadosController::class . ':CargarUno');
})->add(\Logger::class . ':VerificarCredenciales');

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductosController::class . ':TraerTodos');
  $group->post('[/]', \ProductosController::class . ':CargarUno');
})->add(\Logger::class . ':VerificarCredenciales');

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesasController::class . ':TraerTodos');
  $group->post('[/]', \MesasController::class . ':CargarUno');
})->add(\Logger::class . ':VerificarCredenciales');

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidosController::class . ':TraerTodos');
  $group->get('/{codigo}', \PedidosController::class . ':TraerUno');
  $group->get('/csv/descarga', \PedidosController::class . ':DescargarCSV');
  $group->post('[/]', \PedidosController::class . ':CargarUno');
})->add(\Logger::class . ':VerificarCredenciales');

// JWT PROPIO
$app->group('/autentificacion', function (RouteCollectorProxy $group) {

  $group->post('/crearToken', function (Request $request, Response $response) {    
    $parametros = $request->getParsedBody();

    $usuario = $parametros['usuario'];
    $contraseña = $parametros['contraseña'];

    $datos = array('usuario' => $usuario, 'contraseña' => $contraseña);

    try 
    {
      $token = AutentificadorJWT::CrearTokenEmpleado($datos);
      $payload = json_encode(array('usuario' => $usuario, 'jwt' => $token));
    } 
    catch (Exception $e) 
    {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });
});

// JWT
$app->group('/jwt', function (RouteCollectorProxy $group) {

  // $group->post('/crearToken', function (Request $request, Response $response) {    
  //   $parametros = $request->getParsedBody();

  //   $usuario = $parametros['usuario'];
  //   $perfil = $parametros['perfil'];
  //   $alias = $parametros['alias'];

  //   $datos = array('usuario' => $usuario, 'perfil' => $perfil, 'alias' => $alias);

  //   // $token = AutentificadorJWT::CrearToken($datos);
  //   $payload = json_encode(array('jwt' => $token));

  //   $response->getBody()->write($payload);
  //   return $response
  //     ->withHeader('Content-Type', 'application/json');
  // });

  $group->get('/devolverPayLoad', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
      $payload = json_encode(array('payload' => AutentificadorJWT::ObtenerPayLoad($token)));
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

  $group->get('/devolverDatos', function (Request $request, Response $response) {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);

    try {
      $payload = json_encode(array('datos' => AutentificadorJWT::ObtenerData($token)));
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });

  $group->get('/verificarToken', function (Request $request, Response $response) 
  {
    $header = $request->getHeaderLine('Authorization');
    $token = trim(explode("Bearer", $header)[1]);
    $esValido = false;

    try 
    {
      AutentificadorJWT::verificarToken($token);
      $esValido = true;
    } catch (Exception $e) {
      $payload = json_encode(array('error' => $e->getMessage()));
    }

    if ($esValido) 
    {
      $payload = json_encode(array('valid' => $esValido));
    }

    $response->getBody()->write($payload);
    return $response
      ->withHeader('Content-Type', 'application/json');
  });
});

$app->run();
