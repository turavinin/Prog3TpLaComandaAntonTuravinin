<?php
require_once './models/Encuesta.php';

class EncuestaController extends Encuesta
{
    public function AltaEncuesta($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $idMesa = $parametros['idMesa'];
        $idPedido = $parametros['idPedido'];
        $puntuacionMesa = $parametros['puntuacionMesa'];
        $puntuacionRestaurante = $parametros['puntuacionRestaurante'];
        $puntuacionMozo = $parametros['puntuacionMozo'];
        $puntuacionCocinero = $parametros['puntuacionCocinero'];
        $comentarios = $parametros['comentarios'];
        $payload = null;

        try 
        {
            $encuesta = new Encuesta();
            $encuesta->idMesa = $idMesa;
            $encuesta->idPedido = $idPedido;
            $encuesta->puntuacionMesa = $puntuacionMesa;
            $encuesta->puntuacionRestaurante = $puntuacionRestaurante;
            $encuesta->puntuacionMozo = $puntuacionMozo;
            $encuesta->puntuacionCocinero = $puntuacionCocinero;
            $encuesta->comentarios = $comentarios;

            $errores = Encuesta::ValidarEncuesta($encuesta);

            if(count($errores) > 0)
            {
                throw new Exception(json_encode($errores), 800);
            }

            $id = Encuesta::CrearEncuesta($encuesta);
            $cliente = Cliente::ObtenerClientePorPedido($idPedido);
            Cliente::EditarIdEncuesta($cliente->id, $id);
            $payload = json_encode(array('Respuesta' => "La encuesta se creo con exito.", 'Id' => $id));
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

    public function MejoresEncuestas($request, $response, $args)
    {
        try 
        {
            $encuestas = Encuesta::ObtenerMejoresEncuestas();

            if(count($encuestas) < 1)
            {
                throw new Exception("No se encontraron buenas encuestas.");
            }

            $payload = json_encode($encuestas);
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