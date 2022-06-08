<?php

class Pedido
{
    public $id;
    public $idEstado;
    public $estado;
    public $idMesa;
    public $codigo;
    public $minutosTotalesPreparacion;
    public $fechaAlta;
    public $fechaFin;
    public $listaProductosPedidos;

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdEstado as idEstado,
        EP.Estado as estado,
        P.IdMesa as idMesa,
        P.Codigo as codigo,
        P.MinutosTotalesPreparacion as minutosTotalesPreparacion,
        P.FechaAlta as fechaAlta,
        P.FechaFin as fechaFin
        FROM pedidos P
        INNER JOIN estadopedidos EP ON EP.Id = P.IdEstado");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPorCodigo($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdEstado as idEstado,
        EP.Estado as estado,
        P.IdMesa as idMesa,
        P.Codigo as codigo,
        P.MinutosTotalesPreparacion as minutosTotalesPreparacion,
        P.FechaAlta as fechaAlta,
        P.FechaFin as fechaFin
        FROM pedidos P
        INNER JOIN estadopedidos EP ON EP.Id = P.IdEstado
        WHERE P.Codigo = :codigoPedido");

        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public function CrearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        pedidos (IdEstado, IdMesa, Codigo, MinutosTotalesPreparacion, FechaAlta) 
        VALUES (:idEstado, :idMesa, :codigo, :minutosTotalesPreparacion, :fechaAlta)");

        $codigo = Mesa::GenerarCodigo(5);

        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':minutosTotalesPreparacion', $this->minutosTotalesPreparacion, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $this->fechaAlta, PDO::PARAM_STR);
        $consulta->execute();

        return $codigo;
    }

    public static function GenerarCodigo($largo)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $largo); 
    }
}