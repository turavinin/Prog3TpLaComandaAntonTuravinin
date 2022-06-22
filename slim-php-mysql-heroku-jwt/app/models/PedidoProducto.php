<?php

class PedidoProducto
{
    public $id;
    public $codigoPedido;
    public $idProducto;
    public $producto;
    public $idEstado;
    public $estado;
    public $idEmpleado;
    public $empleado;
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
        PP.IdEstado as idEstado,
        EPP.Estado as estado,
        PP.IdEmpleado as idEmpleado,
        E.Usuario as empleado,
        PR.MinutosPreparacion as minutosPreparacion,
        PP.FechaAlta as fechaAlta,
        PP.FechaFin as fechaFin
        FROM pedidosproductos PP
        INNER JOIN productos PR ON PR.Id = PP.IdProducto
        INNER JOIN estadopedidosproductos EPP ON EPP.Id = PP.IdEstado
        INNER JOIN empleados E ON E.Id = PP.IdEmpleado");
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
        PP.IdEstado as idEstado,
        EPP.Estado as estado,
        PP.IdEmpleado as idEmpleado,
        E.Usuario as empleado,
        PR.MinutosPreparacion as minutosPreparacion,
        PP.FechaAlta as fechaAlta,
        PP.FechaFin as fechaFin
        FROM pedidosproductos PP
        INNER JOIN productos PR ON PR.Id = PP.IdProducto
        INNER JOIN estadopedidosproductos EPP ON EPP.Id = PP.IdEstado
        INNER JOIN empleados E ON E.Id = PP.IdEmpleado
        WHERE PP.CodigoPedido = :codigoPedido");

        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'PedidoProducto');
    }

    public function CrearPedidoProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        pedidosproductos (CodigoPedido, IdProducto, IdEmpleado, IdEstado, FechaAlta) 
        VALUES (:codigoPedido, :idProducto, :idEmpleado, :idEstado, :fechaAlta)");

        $consulta->bindValue(':codigoPedido', $this->codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idProducto', $this->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idEmpleado', $this->idEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', 1, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $this->fechaAlta, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function CrearPedidosPorCodigoConProductos($codigoPedido, $listaProductoEmpleado)
    {
        foreach ($listaProductoEmpleado as $keyLista => $arrProductos) 
        {
            foreach ($arrProductos as $key => $value) 
            {
                $pedidoProducto = new PedidoProducto();
                $pedidoProducto->codigoPedido = $codigoPedido;
                $pedidoProducto->idProducto = $key;
                $pedidoProducto->idEmpleado = $value;
                $pedidoProducto->fechaAlta = date('Y-m-d H:i:s');
                $pedidoProducto->CrearPedidoProducto();
            }
        }
    }

    public static function ContarPedidosDeEmpleado($idEmpleado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT COUNT(*)
        FROM pedidosproductos PP
        WHERE PP.IdEmpleado = :idEmpleado");

        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchColumn();
    }
}