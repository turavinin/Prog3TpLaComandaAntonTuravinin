<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './models/PedidoProducto.php';
require_once './interfaces/IApiUsable.php';

class MesasController extends Mesa
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

    public function TraerDisponible($request, $response, $args)
    {
      $payload = null;

      try 
      {
        $lista = Mesa::TraerMesasDisponibles();

        if($lista == null || count($lista) < 1)
        {
          throw new Exception("No se encontraron mesas disponibles.");
        }

        $payload = json_encode($lista);
      } 
      catch (Exception $ex) 
      {
        $payload = json_encode(array('Error' => $ex->getMessage()));
      }
      finally
      {
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
      }
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Mesa::ObtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function CambiarMesaServida($request, $response, $args)
    {
      $parametros = $request->getParsedBody();
      $codigoPedido = $parametros['codigoPedido'];
      $payload = null;

      try 
        {
          $pedido = Pedido::ObtenerPorCodigo($codigoPedido);
          $mesa = Mesa::ObtenerPorId($pedido->idMesa);

          if($pedido == null)
          {
            throw new Exception("No existe el pedido.");
          }

          if(PedidoProducto::ValidarTodosServidos($codigoPedido) == false)
          {
            throw new Exception("No todos los productos estan listos para servir.");
          }

          $mesa->estadoId = "2";
          Mesa::PutMesa($mesa);

          $pedidosListos = PedidoProducto::ObtenerPorCodigoPedido($codigoPedido);

          foreach ($pedidosListos as $key => $pedidoProducto) 
          {
            $pedidoProducto->idEstado = 4;
            PedidoProducto::PutGeneral($pedidoProducto);
          }

          $payload = json_encode(array('Respuesta' => "Se realizo la operacion con exito."));
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

    public function ObtenerMesasMasUsadas($request, $response, $args)
    {
      try 
        {
            $mesa = Pedido::ObtenerMasUsadas();

            if($mesa == null)
            {
                throw new Exception("No se encontraron mesas.");
            }

            $payload = json_encode($mesa);
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
}