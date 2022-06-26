<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';
class Encuesta
{
    public $id;
    public $idMesa;
    public $idPedido;
    public $puntuacionMesa;
    public $puntuacionRestaurante;
    public $puntuacionMozo;
    public $puntuacionCocinero;
    public $comentarios;

    public static function ValidarEncuesta($encuesta)
    {
        $mensajesError = array();

        if(!is_numeric($encuesta->idMesa) || Mesa::ObtenerPorId($encuesta->idMesa) == null)
        {
            array_push($mensajesError, "La mesa es invalida o no existe.");
        }

        if(!is_numeric($encuesta->idPedido) || Pedido::ObtenerPorId($encuesta->idPedido) == null)
        {
            array_push($mensajesError, "El pedido es invalida o no existe.");
        }

        if(!is_numeric($encuesta->puntuacionMesa))
        {
            array_push($mensajesError, "Puntuacion Mesa es invalida");
        }

        if(!is_numeric($encuesta->puntuacionRestaurante))
        {
            array_push($mensajesError, "Puntuacion Restaurante es invalida");
        }

        if(!is_numeric($encuesta->puntuacionMozo))
        {
            array_push($mensajesError, "Puntuacion Mozo es invalida");
        }

        if(!is_numeric($encuesta->puntuacionCocinero))
        {
            array_push($mensajesError, "Puntuacion Cocinero es invalida");
        }

        if(empty(str_replace(' ', '', $encuesta->comentarios)) || strlen($encuesta->comentario) > 100)
        {
            array_push($mensajesError, "El comentario es invalido");
        }

        return $mensajesError;
    }

    public static function CrearEncuesta($encuesta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        encuestas (IdMesa, IdPedido, IdEmpleado, PuntuacionMesa, PuntuacionRestaurante, PuntuacionMozo, PuntuacionCocinero, Comentarios) 
        VALUES (:idMesa, :idPedido, :idEmpleado, :puntuacionMesa, :puntuacionRestaurante, :puntuacionMozo, :puntuacionCocinero, :comentarios)");

        $consulta->bindValue(':idMesa', $encuesta->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':idPedido', $encuesta->idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':idEmpleado', $encuesta->puntuacionMesa, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacionMesa', $encuesta->estadoId, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacionRestaurante', $encuesta->puntuacionRestaurante, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacionMozo', $encuesta->puntuacionMozo, PDO::PARAM_INT);
        $consulta->bindValue(':puntuacionCocinero', $encuesta->puntuacionCocinero, PDO::PARAM_INT);
        $consulta->bindValue(':comentarios', $encuesta->comentarios, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ObtenerMejoresEncuestas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdMesa as idMesa,
        P.IdPedido as idPedido,
        P.PuntuacionMesa as puntuacionMesa,
        P.PuntuacionRestaurante as puntuacionRestaurante,
        P.PuntuacionMozo as puntuacionMozo,
        P.PuntuacionCocinero as puntuacionCocinero,
        P.Comentarios as comentarios
        FROM encuestas P
        WHERE P.PuntuacionMesa > :puntacion");

        $consulta->bindValue(':puntacion', 6, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Encuesta');
    }
}