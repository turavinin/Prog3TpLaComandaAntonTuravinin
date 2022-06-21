<?php

require_once './models/PedidoProducto.php';

class Empleado
{
    public $id;
    public $idTipoEmpleado;
    public $tipoEmpleado;
    public $idEstado;
    public $estado;
    public $usuario;
    public $clave;

    public function CrearEmpleado()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        Empleados (IdTipoEmpleado, IdEstado, Usuario, Clave) 
        VALUES (:idTipoEmpleado, :idEstado, :usuario, :clave)");

        $claveHash = password_hash($this->clave, PASSWORD_DEFAULT);

        $consulta->bindValue(':idTipoEmpleado', $this->idTipoEmpleado, PDO::PARAM_STR);
        $consulta->bindValue(':idEstado', $this->idEstado, PDO::PARAM_STR);
        $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $claveHash);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        E.Id as id,
        E.IdTipoEmpleado as idTipoEmpleado,
        E.IdEstado as idEstado,
        E.Usuario as usuario,
        E.Clave as clave,
        EE.Estado as estado,
        TE.Tipo as tipoEmpleado
        FROM empleados E
        INNER JOIN estadoempleado EE ON EE.Id = E.IdEstado
        INNER JOIN tipoempleado TE ON TE.Id = E.IdTipoEmpleado");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Empleado');
    }

    public static function ObtenerUsuario($usuario)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        E.Id as id,
        E.IdTipoEmpleado as idTipoEmpleado,
        E.IdEstado as idEstado,
        E.Usuario as usuario,
        E.Clave as clave,
        EE.Estado as estado,
        TE.Tipo as tipoEmpleado
        FROM empleados E
        INNER JOIN estadoempleado EE ON EE.Id = E.IdEstado
        INNER JOIN tipoempleado TE ON TE.Id = E.IdTipoEmpleado
        WHERE usuario = :usuario");

        $consulta->bindValue(':usuario', $usuario, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function ObtenerEmpleadosPorTipo($idTipo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        E.Id as id,
        E.IdTipoEmpleado as idTipoEmpleado,
        E.IdEstado as idEstado,
        E.Usuario as usuario,
        E.Clave as clave,
        EE.Estado as estado,
        TE.Tipo as tipoEmpleado
        FROM empleados E
        INNER JOIN estadoempleado EE ON EE.Id = E.IdEstado
        INNER JOIN tipoempleado TE ON TE.Id = E.IdTipoEmpleado
        WHERE E.IdTipoEmpleado = :idTipo");

        $consulta->bindValue(':idTipo', $idTipo, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Empleado');
    }

    public static function ObtenerEmpleadoPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        E.Id as id,
        E.IdTipoEmpleado as idTipoEmpleado,
        E.IdEstado as idEstado,
        E.Usuario as usuario,
        E.Clave as clave,
        EE.Estado as estado,
        TE.Tipo as tipoEmpleado
        FROM empleados E
        INNER JOIN estadoempleado EE ON EE.Id = E.IdEstado
        INNER JOIN tipoempleado TE ON TE.Id = E.IdTipoEmpleado
        WHERE E.Id = :id");

        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Empleado');
    }

    public static function ObtenerEmpleadoDisponible($empleados)
    {
        $listaEmpleadoPedidos = array();

        foreach ($empleados as $key => $empleado) 
        {
            $numeroPedidosEmpleado = PedidoProducto::ContarPedidosDeEmpleado($empleado->id);
            $listaEmpleadoPedidos[$empleado->id] = $numeroPedidosEmpleado;
        }

        $minPedidos = min($listaEmpleadoPedidos);
        $idEmpleadoMinPedidos = array_search($minPedidos, $listaEmpleadoPedidos);

        return self::ObtenerEmpleadoPorId($idEmpleadoMinPedidos);
    }

    public static function ObtenerEmpleadosPorProductos($listaProductos)
    {
        $listaProductoEmpleado = array();

        foreach ($listaProductos as $key => $producto) 
        {
            $productoEmpleado = array();
            $idEmpleadoYaAsginado = self::EncontrarEmpleadoEnListaProducto($listaProductoEmpleado, $producto->id);

            if($idEmpleadoYaAsginado == false)
            {
                $empleados = self::ObtenerEmpleadosPorTipo($producto->idTipoEmpleado);
                $empleadoAsignado = self::ObtenerEmpleadoDisponible($empleados);
                $productoEmpleado[$producto->id] = $empleadoAsignado->id;
            }
            else
            {
                $productoEmpleado[$producto->id] = $idEmpleadoYaAsginado;
            }

            array_push($listaProductoEmpleado, $productoEmpleado);
        }

        return $listaProductoEmpleado;
    }

    private static function EncontrarEmpleadoEnListaProducto($listaProductoEmpleado, $productoIdBuscado)
    {
        foreach ($listaProductoEmpleado as $keyLista => $keyProducto) 
        {
            if(key($listaProductoEmpleado[$keyLista]) == $productoIdBuscado)
            {
                return $keyProducto[$productoIdBuscado];
            }
        }

        return false;
    }
}