<?php

require_once './models/EstadoPedidoProducto.php';

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

    public static function ValidarPedidoProducto($idEstado)
    {
        $mensajesError = array();

        if(!is_numeric($idEstado) || EstadoPedidoProducto::ObtenerPorId($idEstado) == null)
        {
            array_push($mensajesError, "El estado con id ". $idEstado . " es invalido o no existe.");
        }

        return $mensajesError;
    }

    public static function ValidarCambioEstadoEmpleado($idProducto, $idEstado, $empleadoId)
    {
        $mensajesError = self::ValidarPedidoProducto($idEstado);
        $producto = self::ObtenerPorId($idProducto);

        if(!is_numeric($idProducto) || $producto == null)
        {
            array_push($mensajesError, "El producto con id ". $idProducto . " es invalido o no existe.");
        }

        if($producto->idEmpleado != $empleadoId)
        {
            array_push($mensajesError, "El empleado no es el encargado del producto.");
        }

        if(count($mensajesError) < 1)
        {
            $estadoProductoOriginal = $producto->idEstado;

            if($idEstado == 2 && $estadoProductoOriginal != 1)
            {
                array_push($mensajesError, "El estado es invalido para el producto. Actualmente el estado esta en " . $producto->estado);
            }
            else if($idEstado == 3 && $estadoProductoOriginal != 2)
            {
                array_push($mensajesError, "El estado es invalido para el producto. Actualmente el estado esta en " . $producto->estado);
            }
            else if($idEstado == 1 || $idEstado == 4)
            {
                array_push($mensajesError, "Estado invalido.");
            }
        }

        return $mensajesError;
    }

    public static function ValidarTodosServidos($codigoPedido)
    {
        $esValido = false;
        $listaPedidosProducto = self::ObtenerEnPreparacion($codigoPedido);
        
        if(count($listaPedidosProducto) < 1 || $listaPedidosProducto == null)
        {
            $esValido = true;
        }

        return $esValido;
    }

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

    public static function ObtenerEnPreparacion($codigoPedido)
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
        WHERE PP.CodigoPedido = :codigoPedido AND PP.IdEstado = :estado");

        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':estado', 2, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'PedidoProducto');
    }

    public static function ObtenerPedidoListoParaServir($codigoPedido)
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
        WHERE PP.CodigoPedido = :codigoPedido AND PP.IdEstado = :estadoPedido");

        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':estadoPedido', 3, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'PedidoProducto');
    }

    public static function ObtenerPorId($id)
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
        WHERE PP.Id = :id");

        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('PedidoProducto');
    }

    public static function ObtenerPorEstadoEmpleado($idEmpleado, $idEstado)
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
        WHERE PP.idEmpleado = :idEmpleado AND PP.IdEstado = :idEstado");

        $consulta->bindValue(':idEmpleado', $idEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $idEstado, PDO::PARAM_INT);
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

    public static function CambiarEstadoPedidoProducto($id, $estadoProducto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidosproductos SET IdEstado = :idEstado WHERE Id = :id");

        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $estadoProducto, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function PutGeneral($pedidoProducto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidosproductos 
        SET 
        CodigoPedido = :codigoPedido,
        IdProducto = :idProducto,
        IdEstado = :idEstado,
        IdEmpleado = :idEmpleado,
        FechaAlta = :fechaAlta,
        FechaFin = :fechaFin
        WHERE Id = :id");

        $consulta->bindValue(':id', $pedidoProducto->id, PDO::PARAM_INT);
        $consulta->bindValue(':codigoPedido', $pedidoProducto->codigoPedido, PDO::PARAM_STR);
        $consulta->bindValue(':idProducto', $pedidoProducto->idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':idEstado', $pedidoProducto->idEstado, PDO::PARAM_INT);
        $consulta->bindValue(':idEmpleado', $pedidoProducto->idEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $pedidoProducto->fechaAlta, PDO::PARAM_STR);
        $consulta->bindValue(':fechaFin', $pedidoProducto->fechaFin, PDO::PARAM_STR);
        $consulta->execute();
    }
}