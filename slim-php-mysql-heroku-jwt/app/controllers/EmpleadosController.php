<?php
require_once './models/Empleado.php';
require_once './interfaces/IApiUsable.php';

class EmpleadosController extends Empleado implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idTipoEmpleado = $parametros['idTipoEmpleado'];
        $idEstado = $parametros['idEstado'];
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];

        $empleado = new Empleado();
        $empleado->idTipoEmpleado = $idTipoEmpleado;
        $empleado->idEstado = $idEstado;
        $empleado->usuario = $usuario;
        $empleado->clave = $clave;

        $nuevoId = $empleado->CrearEmpleado();

        $payload = json_encode(array("mensaje" => "Empleado creado con exito", "id" => $nuevoId));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $usuario = $args['usuario'];
        $empleado = Empleado::ObtenerUsuario($usuario);
        $payload = json_encode($empleado);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Empleado::ObtenerTodos();
        $payload = json_encode(array("listaEmpleado" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
    
    // public function ModificarUno($request, $response, $args)
    // {
    //     $parametros = $request->getParsedBody();

    //     $nombre = $parametros['nombre'];
    //     Usuario::modificarUsuario($nombre);

    //     $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));

    //     $response->getBody()->write($payload);
    //     return $response
    //       ->withHeader('Content-Type', 'application/json');
    // }

    // public function BorrarUno($request, $response, $args)
    // {
    //     $parametros = $request->getParsedBody();

    //     $usuarioId = $parametros['usuarioId'];
    //     Usuario::borrarUsuario($usuarioId);

    //     $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));

    //     $response->getBody()->write($payload);
    //     return $response
    //       ->withHeader('Content-Type', 'application/json');
    // }
}
