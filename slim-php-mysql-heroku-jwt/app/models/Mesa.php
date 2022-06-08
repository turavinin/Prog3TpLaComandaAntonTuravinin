<?php

class Mesa
{
    public $id;
    public $codigo;
    public $estadoId;
    public $estadoMesa;

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        M.Id as id,
        M.Codigo as codigo,
        M.EstadoId as estadoId,
        EM.Estado as estadoMesa
        FROM mesas M
        INNER JOIN estadomesas EM ON EM.Id = M.EstadoId");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Mesa');
    }

    public function CrearMesa()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        mesas (EstadoId, Codigo) 
        VALUES (:estadoId, :codigo)");

        $codigo = Mesa::GenerarCodigo(5);

        $consulta->bindValue(':estadoId', $this->estadoId, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();

        return $codigo;
    }

    public static function ActualizarEstadoMesa($idMesa, $estadoId)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE 
        mesas
        SET
        EstadoId = :estadoId
        WHERE
        Id = :idMesa");

        $consulta->bindValue(':estadoId', $estadoId, PDO::PARAM_INT);
        $consulta->bindValue(':idMesa', $idMesa, PDO::PARAM_INT);
        $consulta->execute();
    }

    public static function GenerarCodigo($largo)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $largo); 
    }
}