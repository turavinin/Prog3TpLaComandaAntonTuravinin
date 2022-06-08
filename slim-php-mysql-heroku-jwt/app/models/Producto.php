<?php

class Producto
{
    public $id;
    public $idTipoEmpleado;
    public $tipoEmpleado;
    public $descripcion;
    public $precio;
    public $minutosPreparacion;

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdTipoEmpleado as idTipoEmpleado,
        TE.Tipo as tipoEmpleado,
        P.Descripcion as descripcion,
        P.Precio as precio,
        P.MinutosPreparacion as minutosPreparacion
        FROM productos P
        INNER JOIN tipoempleado TE ON TE.Id = P.IdTipoEmpleado");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Producto');
    }

    public static function ObtenerPorId($idProducto)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdTipoEmpleado as idTipoEmpleado,
        TE.Tipo as tipoEmpleado,
        P.Descripcion as descripcion,
        P.Precio as precio,
        P.MinutosPreparacion as minutosPreparacion
        FROM productos P
        INNER JOIN tipoempleado TE ON TE.Id = P.IdTipoEmpleado
        WHERE P.Id = :idProducto");
        $consulta->bindValue(':idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Producto');
    }

    public static function ObtenerProductosPorIds($arrayIds)
    {
        $listaProductos = array();
        
        foreach ($arrayIds as $key => $idProducto) 
        {
            $producto = Producto::ObtenerPorId($idProducto);
            array_push($listaProductos, $producto);
        }

        return $listaProductos;
    }

    public function CrearProducto()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        productos (IdTipoEmpleado, Descripcion, Precio, MinutosPreparacion) 
        VALUES (:idTipoEmpleado, :descripcion, :precio, :minutosPreparacion)");

        $consulta->bindValue(':idTipoEmpleado', $this->idTipoEmpleado, PDO::PARAM_INT);
        $consulta->bindValue(':descripcion', $this->descripcion, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(':minutosPreparacion', $this->minutosPreparacion, PDO::PARAM_STR);
        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }

    public static function CalcularMinutosTotalesPreparacion($listaProductos)
    {
        $minutosTotales = 0;

        foreach ($listaProductos as $key => $producto) 
        {
            $minutosTotales = $producto->minutosPreparacion > $minutosTotales ? $producto->minutosPreparacion : $minutosTotales;
        }

        return $minutosTotales;
    }
}