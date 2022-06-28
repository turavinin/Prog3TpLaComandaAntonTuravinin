<?php
require_once './models/Pedido.php';
require_once './models/PedidoProducto.php';
require_once './models/Producto.php';
require_once './models/Mesa.php';
require_once './helpers/ImagenesManager.php';
require_once './interfaces/IApiUsable.php';

class PedidosController extends Pedido implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idMesa = $parametros['idMesa'];
        $idCliente = $parametros['idCliente'];
        $idsProductos = $parametros['idsProductos'];
        $arrayIdsProductos = explode(",", $idsProductos);
        $foto = $_FILES['foto'];

        $payload = null;

        try 
        {
          $erroresValidacion = Pedido::ValidarPedidos($idMesa, $idCliente, $arrayIdsProductos, $foto);

          if(count($erroresValidacion) > 0)
          {
            throw new Exception(json_encode($erroresValidacion), 800);
          }

          $listaProductosPedidos = Producto::ObtenerProductosPorIds($arrayIdsProductos);
          $listaPedidoEmpleado = Empleado::ObtenerEmpleadosPorProductos($listaProductosPedidos);

          $pedido = new Pedido();
          $pedido->idMesa = $idMesa;
          $pedido->fechaAlta = date('Y-m-d H:i:s');
          $pedido->minutosTotalesPreparacion = Producto::CalcularMinutosTotalesPreparacion($listaProductosPedidos);

          $imagenManager = new ImagenesManager("./FotosPedidos/");
          $nombreImagen = ImagenesManager::GetNombreImagen(array($idCliente, $pedido->idMesa, $pedido->fechaAlta), ".png");
          $imagenManager->GuardarImagen($_FILES, $nombreImagen);

          $pedido->foto = $nombreImagen;
          $idPedido = $pedido->CrearPedido();

          PedidoProducto::CrearPedidosPorCodigoConProductos($pedido->codigo, $listaPedidoEmpleado);
          Mesa::ActualizarEstadoMesa($idMesa, 1);
          Cliente::EditarConDatosPedido($idCliente, $idPedido, $pedido->codigo);

          $payload = json_encode(array("mensaje" => "Pedido creado con exito", "id" => $idPedido, "codigo" => $pedido->codigo));
        } 
        catch (Exception $ex) 
        {
          $mensaje = $ex->getMessage();

          if($ex->getCode() == 800)
          {
              $mensaje = json_decode($ex->getMessage());
          }

          $payload = json_encode(array('Error' => $mensaje));
        }
        finally
        {
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
        }
    }

    public function Cobrar($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $idPedido = $parametros['idPedido'];
      $payload = null;

      try 
        {
          $pedido = Pedido::ObtenerPorId($idPedido);

          if($pedido == null)
          {
            throw new Exception("No existe el pedido buscado.");
          }

          $mesa = Mesa::ObtenerPorId($pedido->idMesa);
          $mesa->estadoId = 3;
          Mesa::PutMesa($mesa);

          $payload = json_encode(array('Respuesta' => "Se cobro con exito."));
        } 
        catch (Exception $ex) 
        {
            $mensaje = $ex->getMessage();

            if($ex->getCode() == 800)
            {
                $mensaje = json_decode($ex->getMessage());
            }

            $payload = json_encode(array('Error' => $mensaje));
        }
        finally
        {
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

    }

    public function CerrarPedidoMesa($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $idPedido = $parametros['idPedido'];
      $payload = null;

      try 
        {
          $pedido = Pedido::ObtenerPorId($idPedido);
          $pedido->fechaFin = date('Y-m-d H:i:s');
          Pedido::PutPedido($pedido);

          if($pedido == null)
          {
            throw new Exception("No existe el pedido buscado.");
          }

          $mesa = Mesa::ObtenerPorId($pedido->idMesa);
          $mesa->estadoId = 4;
          Mesa::PutMesa($mesa);

          $payload = json_encode(array('Respuesta' => "Se cerro con exito la mesa."));
        } 
        catch (Exception $ex) 
        {
            $mensaje = $ex->getMessage();

            if($ex->getCode() == 800)
            {
                $mensaje = json_decode($ex->getMessage());
            }

            $payload = json_encode(array('Error' => $mensaje));
        }
        finally
        {
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }

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

        if(count($lista) > 0)
        {
          foreach ($lista as $key => $pedido) 
          {
            $pedido->listaProductosPedidos = PedidoProducto::ObtenerPorCodigoPedido($pedido->codigo);
          }
        }

        $payload = json_encode(array("listaPedidos" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}