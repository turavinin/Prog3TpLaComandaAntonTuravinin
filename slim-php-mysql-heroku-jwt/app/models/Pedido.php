<?php

require_once './models/Mesa.php';
require_once './models/Producto.php';

class Pedido
{
    public $id;
    public $idMesa;
    public $codigo;
    public $minutosTotalesPreparacion;
    public $fechaAlta;
    public $fechaFin;
    public $foto;
    public $listaProductosPedidos;

    public static function ValidarPedidos($idMesa, $idCliente, $listaProductosIds, $imagen)
    {
        $mensajesError = array();

        $mesa = Mesa::ObtenerPorId($idMesa);

        if(!is_numeric($idMesa) || $mesa == null)
        {
            array_push($mensajesError, "La mesa es invalida o no existe.");
        }

        if($mesa != null && $mesa->estadoId != 4)
        {
            array_push($mensajesError, "La mesa no esta disponible.");
        }

        if(!is_numeric($idCliente) || Cliente::ObtenerClientePorId($idCliente) == null)
        {
            array_push($mensajesError, "El cliente con id ". $idCliente . " es invalido o no existe.");
        }

        if($imagen == null)
        {
            array_push($mensajesError, "Imagen invalida.");
        }

        foreach ($listaProductosIds as $key => $idProducto) 
        {
            if(!is_numeric($idProducto) || Producto::ObtenerPorId($idProducto) == null)
            {
                array_push($mensajesError, "El producto ". $idProducto . " es invalido o no existe.");
            }
        }

        return $mensajesError;
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdMesa as idMesa,
        P.Codigo as codigo,
        P.MinutosTotalesPreparacion as minutosTotalesPreparacion,
        P.Foto as foto,
        P.FechaAlta as fechaAlta,
        P.FechaFin as fechaFin
        FROM pedidos P");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function ObtenerPorCodigo($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdMesa as idMesa,
        P.Codigo as codigo,
        P.MinutosTotalesPreparacion as minutosTotalesPreparacion,
        P.Foto as foto,
        P.FechaAlta as fechaAlta,
        P.FechaFin as fechaFin
        FROM pedidos P
        WHERE P.Codigo = :codigoPedido");

        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function ObtenerPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdMesa as idMesa,
        P.Codigo as codigo,
        P.MinutosTotalesPreparacion as minutosTotalesPreparacion,
        P.Foto as foto,
        P.FechaAlta as fechaAlta,
        P.FechaFin as fechaFin
        FROM pedidos P
        WHERE P.Id = :id");

        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public function CrearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        pedidos (IdMesa, Codigo, MinutosTotalesPreparacion, FechaAlta, Foto) 
        VALUES (:idMesa, :codigo, :minutosTotalesPreparacion, :fechaAlta, :foto)");

        $codigo = Mesa::GenerarCodigo(5);
        $this->codigo = $codigo;
        
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':minutosTotalesPreparacion', $this->minutosTotalesPreparacion, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $this->fechaAlta, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function GenerarCodigo($largo)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $largo); 
    }

    public static function PutPedido($pedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET
        IdMesa = :idMesa, MinutosTotalesPreparacion = :minutosTotalesPreparacion, FechaAlta = :fechaAlta, 
        FechaFin = :fechaFin, Foto = :foto
        WHERE Id = :id");
        
        $consulta->bindValue(':id', $pedido->id, PDO::PARAM_INT);
        $consulta->bindValue(':idMesa', $pedido->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':minutosTotalesPreparacion', $pedido->minutosTotalesPreparacion, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $pedido->fechaAlta, PDO::PARAM_STR);
        $consulta->bindValue(':fechaFin', $pedido->fechaFin, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $pedido->foto, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function ObtenerMasUsadas()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdMesa as idMesa,
        P.Codigo as codigo,
        P.MinutosTotalesPreparacion as minutosTotalesPreparacion,
        P.Foto as foto,
        P.FechaAlta as fechaAlta,
        P.FechaFin as fechaFin,
        COUNT(P.IdMesa) as cantidadUso
        FROM pedidos P
        GROUP BY P.IdMesa
        ORDER BY cantidadUso DESC
        LIMIT 1");

        $consulta->execute();
        return $consulta->fetchObject('Pedido');
    }
}