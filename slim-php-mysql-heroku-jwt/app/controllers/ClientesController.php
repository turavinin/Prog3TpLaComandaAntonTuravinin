<?php
require_once './models/Cliente.php';
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';


class ClientesController extends Cliente
{

    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $nombre = $parametros['nombre'];
        $payload = null;

        try 
        {
            $mensajesError = Cliente::ValidarCliente($nombre);
            if(count($mensajesError) > 0)
            {
                throw new Exception(json_encode($mensajesError), 800);
            }
            
            $cliente = new Cliente();
            $cliente->nombre = $nombre;
            $id = $cliente->CrearCliente();

            $payload = json_encode(array("mensaje" => "Se registró el cliente con éxito.", "Id" => $id));
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

    public function PedidoTiempoPreparacion($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $codigoPedido = $parametros['codigoPedido'];
        $codigoMesa = $parametros['codigoMesa'];

        try 
        {
            $pedido = Pedido::ObtenerPorCodigo($codigoPedido);
            $mesa = Mesa::ObtenerPorCodigo($codigoMesa);
            if($pedido == null || $mesa == null)
            {
                throw new Exception("No se encontró pedido buscado.");
            }

            $payload = json_encode(array('Tiempo de preparacion' => $pedido->minutosTotalesPreparacion . " minutos"));
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