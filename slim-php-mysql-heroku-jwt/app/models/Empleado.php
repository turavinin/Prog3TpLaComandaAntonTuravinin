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

    public static function Validar($usuario)
    {
        $mensajesError = array();

        if(empty(str_replace(' ', '', $usuario)))
        {
            array_push($mensajesError, "Usuario invalido.");
        }

        if(Empleado::ObtenerUsuario($usuario) != null && count($mensajesError) < 1)
        {
            array_push($mensajesError, "El usuario". $usuario . " ya existe.");
        }

        return $mensajesError;
    }

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

    public static function ToString($empleado)
    {
        return "| Id: " . $empleado->id . " | TipoEmpleado: " . $empleado->tipoEmpleado . " | Estado: " . $empleado->estado . " | Usuario: " . $empleado->usuario;
    }

    public static function GuardarToCSV($lista, $path)
    {
        $array = array();

        foreach ($lista as $key => $empleado) 
        {
            array_push($array, array($empleado->id, $empleado->idTipoEmpleado, $empleado->idEstado, $empleado->usuario, $empleado->clave));
        }

        $total = count($array);

        if($total > 0)
        {
            $file = fopen($path, "wb");

            foreach($array as $key=>$stringArr)
            {
                fputcsv($file, $stringArr);
            }
        
            fclose($file);
        }
    }

    public static function CargarArchivoCSV($pathCSV)
    {
        $lista = array();
        $fila = 1;
        if (($gestor = fopen($pathCSV, "r")) !== FALSE) 
        {
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) 
            {
                $empleado = new Empleado();
                $empleado->id = $datos[0];
                $empleado->idTipoEmpleado = $datos[1];
                $empleado->idEstado = $datos[2];
                $empleado->usuario = $datos[3];
                $empleado->clave = $datos[4];
                array_push($lista, $empleado);
            }
            fclose($gestor);
        }

        return $lista;
    }
}