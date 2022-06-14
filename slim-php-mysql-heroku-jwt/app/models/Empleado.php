<?php

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

    // public static function modificarUsuario()
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET usuario = :usuario, clave = :clave WHERE id = :id");
    //     $consulta->bindValue(':usuario', $this->usuario, PDO::PARAM_STR);
    //     $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
    //     $consulta->bindValue(':id', $this->id, PDO::PARAM_INT);
    //     $consulta->execute();
    // }

    // public static function borrarUsuario($usuario)
    // {
    //     $objAccesoDato = AccesoDatos::obtenerInstancia();
    //     $consulta = $objAccesoDato->prepararConsulta("UPDATE usuarios SET fechaBaja = :fechaBaja WHERE id = :id");
    //     $fecha = new DateTime(date("d-m-Y"));
    //     $consulta->bindValue(':id', $usuario, PDO::PARAM_INT);
    //     $consulta->bindValue(':fechaBaja', date_format($fecha, 'Y-m-d H:i:s'));
    //     $consulta->execute();
    // }
}