<?php

class PedidoProducto
{
    public $id;
    public $codigoPedido;
    public $idProducto;
    public $producto;
    public $minutosPreparacion;
    public $fechaAlta;
    public $fechaFin;

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        PP.Id as id,
        PP.CodigoPedido as codigoPedido,
        PP.IdProducto as idProducto,
        PR.Descripcion as producto,
        PR.MinutosPreparacion as minutosPreparacion,
        PP.FechaAlta as fechaAlta,
        PP.FechaFin as fechaFin
        FROM pedidosproductos PP
        INNER JOIN productos PR ON PR.Id = PP.IdProducto");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'PedidoProducto');
    }

    public static function ObtenerPorCodigoPedido($codigoPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        PP.Id as id,
        PP.CodigoPedido as codigoPedido,
        PP.IdProducto as idProducto,
        PR.Descripcion as producto,
        PR.MinutosPreparacion as minutosPreparacion,
        PP.FechaAlta as fechaAlta,
        PP.FechaFin as fechaFin
        FROM pedidosproductos PP
        INNER JOIN productos PR ON PR.Id = PP.IdProducto
        WHERE PP.CodigoPedido = :codigoPedido");

        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'PedidoProducto');
    }

    public function CrearPedidoProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        pedidosproductos (CodigoPedido, IdProducto, FechaAlta) 
        VALUES (:codigoPedido, :idProducto, :fechaAlta)");

        $codigo = Mesa::GenerarCodigo(5);

        $consulta->bindValue(':codigoPedido', $this->codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idProducto', $this->idProducto, PDO::PARAM_STR);
        $consulta->bindValue(':fechaAlta', $this->fechaAlta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function CrearPedidosPorCodigoConProductos($codigoPedido, $listaProductos)
    {
        foreach ($listaProductos as $key => $producto) 
        {
            $pedidoProducto = new PedidoProducto();
            $pedidoProducto->codigoPedido = $codigoPedido;
            $pedidoProducto->idProducto = $producto->id;
            $pedidoProducto->fechaAlta = date('Y-m-d H:i:s');
            $pedidoProducto->CrearPedidoProducto();
        }
    }
}