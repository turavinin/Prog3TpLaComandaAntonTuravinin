<?php
require_once './models/Pedido.php';
require_once './models/PedidoProducto.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './interfaces/IApiUsable.php';

class PedidosController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idMesa = $parametros['idMesa'];
        $idsProductos = $parametros['idsProductos'];
        $arrayIdsProductos = explode(",", $idsProductos);
        $listaProductosPedidos = Producto::ObtenerProductosPorIds($arrayIdsProductos);

        $pedido = new Pedido();
        $pedido->idMesa = $idMesa;
        $pedido->fechaAlta = date('Y-m-d H:i:s');
        $pedido->idEstado = 1;
        $pedido->minutosTotalesPreparacion = Producto::CalcularMinutosTotalesPreparacion($listaProductosPedidos);

        $codigoPedido = $pedido->CrearPedido();
        PedidoProducto::CrearPedidosPorCodigoConProductos($codigoPedido, $listaProductosPedidos);
        Mesa::ActualizarEstadoMesa($idMesa, 1);

        $payload = json_encode(array("mensaje" => "Pedido creado con exito", "codigo" => $codigoPedido));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');

    }

    public function TraerUno($request, $response, $args)
    {
        $codigoPedido = $args['codigo'];
        $lista = Pedido::ObtenerPorCodigo($codigoPedido);
        $lista->listaProductosPedidos = PedidoProducto::ObtenerPorCodigoPedido($codigoPedido);

        $payload = json_encode(array("pedido" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Pedido::ObtenerTodos();
        $lista->listaProductosPedidos = PedidoProducto::ObtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}