<?php

class EstadoPedidoProducto
{
    public $id;
    public $estado;


    public static function ObtenerPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        EPP.Id as id,
        EPP.Estado as estado
        FROM estadopedidosproductos EPP
        WHERE EPP.Id = :idEstado");

        $consulta->bindValue(':idEstado', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('EstadoPedidoProducto');
    }
}