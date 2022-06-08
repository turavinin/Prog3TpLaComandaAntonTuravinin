<?php
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class MesasController extends Mesa implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $estadoId = $parametros['estadoId'];

        $mesa = new Mesa();
        $mesa->estadoId = $estadoId;

        $codigo = $mesa->CrearMesa();

        $payload = json_encode(array("mensaje" => "Mesa creada con exito", "codigo" => $codigo));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');

    }

    public function TraerUno($request, $response, $args)
    {
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::ObtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}