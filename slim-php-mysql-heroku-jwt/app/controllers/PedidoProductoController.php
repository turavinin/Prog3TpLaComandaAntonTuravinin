<?php

require_once './models/PedidoProducto.php';
require_once './models/Empleado.php';
require_once './middlewares/AutentificadorJWT.php';

class PedidoProductoController extends PedidoProducto
{

    public function ProductosPendientesEmpleado($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $headerAuth = $request->getHeaderLine('Authorization');
        $token = AutentificadorJWT::GetTokenDelHeader($headerAuth);
        $data = AutentificadorJWT::ObtenerData($token);
        $usuario = $data->usuario;
        $idEstado = intval($parametros['idEstado']);
        $payload = null;

        try 
        {
            $erroresValidacion = PedidoProducto::ValidarPedidoProducto($idEstado);

            if(count($erroresValidacion) > 0)
            {
              throw new Exception(json_encode($erroresValidacion), 800);
            }

            $empleado = Empleado::ObtenerUsuario($usuario);
            $pedidoPendientes = PedidoProducto::ObtenerPorEstadoEmpleado($empleado->id, $idEstado);

            if($pedidoPendientes == null)
            {
                throw new Exception("No hay pedidos pendientes para el empleado.");
            }

            $payload = json_encode($pedidoPendientes);
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

    public function CambiarEstadoEmpleado($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $headerAuth = $request->getHeaderLine('Authorization');
        $token = AutentificadorJWT::GetTokenDelHeader($headerAuth);
        $data = AutentificadorJWT::ObtenerData($token);
        $idPedidoProducto = $parametros['idPedidoProducto'];
        $idEstado = $parametros['idEstado'];
        $payload = null;

        try 
        {
            $empleado = Empleado::ObtenerUsuario($data->usuario);
            $erroresValidacion = PedidoProducto::ValidarCambioEstadoEmpleado($idPedidoProducto, $idEstado, $empleado->id);

            if(count($erroresValidacion) > 0)
            {
              throw new Exception(json_encode($erroresValidacion), 800);
            }

            $pedidoProducto = PedidoProducto::ObtenerPorId($idPedidoProducto);
            $pedidoProducto->idEstado = $idEstado;
            
            if($idEstado == 3)
            {
                $pedidoProducto->fechaFin = date('Y-m-d H:i:s');
            }

            PedidoProducto::PutGeneral($pedidoProducto);

            $payload = json_encode(array('Respuesta' => "El cambio de estado se realizo con exito."));
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


    public function ObtenerProductosListos($request, $response, $args)
    {
        $parametros = $request->getQueryParams();
        $codigoPedido = $parametros['codigoPedido'];
        $payload = null;

        try 
        {
            $pedido = Pedido::ObtenerPorCodigo($codigoPedido);

            if($pedido == null)
            {
              throw new Exception("No existe pedido.");
            }

            $pedidosListos = PedidoProducto::ObtenerPedidoListoParaServir($codigoPedido);

            if($pedidosListos == null)
            {
                throw new Exception("No hay pedidos listo para servir.");
            }

            $payload = json_encode($pedidosListos);
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