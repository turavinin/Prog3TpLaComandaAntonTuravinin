<?php

class Cliente
{
    public $id;
    public $idPedido;
    public $codigoPedido;
    public $idEncuesta;
    public $nombre;

    public static function ValidarCliente($nombre)
    {
        $mensajesError = array();

        if(empty(str_replace(' ', '', $nombre)))
        {
            array_push($mensajesError, "El nombre del cliente no es valido.");
        }

        return $mensajesError;
    }

    public static function ObtenerClientePorId($idCliente)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        CL.Id as id,
        CL.IdPedido as idPedido,
        CL.CodigoPedido as codigoPedido,
        CL.IdEncuesta as idEncuesta,
        CL.Nombre as nombre
        FROM clientes CL
        WHERE CL.Id = :idCliente");

        $consulta->bindValue(':idCliente', $idCliente, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Cliente');
    }

    public function CrearCliente()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO clientes (Nombre) VALUES (:nombre)");
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function EditarConDatosPedido($idCliente, $idPedido, $codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE clientes SET IdPedido = :idPedido, CodigoPedido = :codigo WHERE Id = :idCliente");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':idCliente', $idCliente, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function EditarIdEncuesta($idCliente, $idEncuesta)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE clientes SET IdEncuesta = :idEncuesta WHERE Id = :idCliente");
        $consulta->bindValue(':idEncuesta', $idEncuesta, PDO::PARAM_INT);
        $consulta->bindValue(':idCliente', $idCliente, PDO::PARAM_INT);
        $consulta->execute();
    }
}