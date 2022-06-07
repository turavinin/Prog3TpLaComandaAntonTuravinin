<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';

class ProductosController extends Empleado implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idTipoEmpleado = $parametros['idTipoEmpleado'];
        $descripcion = $parametros['descripcion'];
        $precio = $parametros['precio'];
        $minutosPreparacion = $parametros['minutosPreparacion'];

        $producto = new Producto();
        $producto->idTipoEmpleado = $idTipoEmpleado;
        $producto->descripcion = $descripcion;
        $producto->precio = $precio;
        $producto->minutosPreparacion = $minutosPreparacion;

        $nuevoId = $producto->CrearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito", "id" => $nuevoId));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');

    }

    public function TraerUno($request, $response, $args)
    {
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Producto::ObtenerTodos();
        $payload = json_encode(array("listaProductos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}