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
require_once './controllers/ClientesController.php';
require_once './controllers/PedidoProductoController.php';
require_once './controllers/EncuestaController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// JWT
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

// API
$app->group('/empleados', function (RouteCollectorProxy $group) {
  $group->get('[/]', \EmpleadosController::class . ':TraerTodos')->add(\Logger::class . ':VerificarCredenciales');
  $group->get('/csv', \EmpleadosController::class . ':ObtenerCSV');
  $group->get('/pdf', \EmpleadosController::class . ':ObtenerPDF');
  $group->get('/{usuario}', \EmpleadosController::class . ':TraerUno')->add(\Logger::class . ':VerificarCredenciales');
  $group->post('[/]', \EmpleadosController::class . ':CargarUno')->add(\Logger::class . ':VerificarCredenciales');
  $group->post('/cargar/csv', \EmpleadosController::class . ':CargarCSV');
});

$app->group('/cliente', function (RouteCollectorProxy $group) {
  $group->post('[/]', \ClientesController::class . ':CargarUno')->add(\Logger::class . ':VerificarCredenciales');;
  $group->get('/pedido', \ClientesController::class . ':PedidoTiempoPreparacion');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesasController::class . ':TraerTodos');
  $group->get('/disponibles', \MesasController::class . ':TraerDisponible');
  $group->get('/estadistica/usadas', \MesasController::class . ':ObtenerMesasMasUsadas');
  $group->post('[/]', \MesasController::class . ':CargarUno');
  $group->post('/estado', \MesasController::class . ':CambiarMesaServida');
})->add(\Logger::class . ':VerificarCredenciales');

$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductosController::class . ':TraerTodos')->add(\Logger::class . ':VerificarCredenciales');
  $group->post('[/]', \ProductosController::class . ':CargarUno')->add(\Logger::class . ':VerificarCredenciales');
  $group->get('/pdf', \ProductosController::class . ':ObtenerPDF');
  $group->get('/csv', \ProductosController::class . ':ObtenerCSV');
  $group->post('/cargar/csv', \ProductosController::class . ':CargarCSV');
});

$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidosController::class . ':TraerTodos');
  $group->get('/{codigo}', \PedidosController::class . ':TraerUno');
  $group->post('[/]', \PedidosController::class . ':CargarUno');
  $group->post('/cobrar', \PedidosController::class . ':Cobrar');
  $group->post('/estado/cerrar', \PedidosController::class . ':CerrarPedidoMesa');
})->add(\Logger::class . ':VerificarCredenciales');

$app->group('/pedidos-productos', function (RouteCollectorProxy $group) {
  $group->get('/estado', \PedidoProductoController::class . ':ObtenerProductosListos');
  $group->get('/estado/empleado', \PedidoProductoController::class . ':ProductosPendientesEmpleado');
  $group->post('/estado/empleado', \PedidoProductoController::class . ':CambiarEstadoEmpleado');
})->add(\Logger::class . ':VerificarCredenciales');

$app->group('/encuesta', function (RouteCollectorProxy $group) {
  $group->post('[/]', \EncuestaController::class . ':AltaEncuesta');
  $group->get('[/]', \EncuestaController::class . ':MejoresEncuestas')->add(\Logger::class . ':VerificarCredenciales');
});

$app->run();
