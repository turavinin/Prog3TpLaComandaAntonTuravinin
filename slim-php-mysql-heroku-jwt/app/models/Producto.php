<?php

class Producto
{
    public $id;
    public $idTipoEmpleado;
    public $tipoEmpleado;
    public $descripcion;
    public $precio;
    public $minutosPreparacion;

    public static function ToString($producto)
    {
        return "|Id: " . $producto->id . "| Descripcion: " . $producto->descripcion . "| Precio: " . $producto->precio;
    }

    public static function Validar($descripcion)
    {
        $mensajesError = array();

        if(empty(str_replace(' ', '', $descripcion)))
        {
            array_push($mensajesError, "Descripcion invalida.");
        }

        if(Producto::ObtenerPorDescripcion($descripcion) != null && count($mensajesError) < 1)
        {
            array_push($mensajesError, "El producto". $descripcion . " ya existe.");
        }

        return $mensajesError;
    }

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

    public static function ObtenerPorDescripcion($descripcion)
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
        WHERE P.Descripcion = :descripcion");
        $consulta->bindValue(':descripcion', $descripcion, PDO::PARAM_STR);
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

    private static function ProductoToCSV($producto)
    {
        return "$producto->id".","."$producto->idTipoEmpleado".","."$producto->descripcion".","."$producto->precio".","."$producto->minutosPreparacion";
    }

    public static function GuardarProductoToCSV($productos, $path)
    {
        $productosArr = array();

        foreach ($productos as $key => $producto) 
        {
            array_push($productosArr, array($producto->id, $producto->idTipoEmpleado, $producto->descripcion, $producto->precio, $producto->minutosPreparacion));
        }

        $total = count($productosArr);

        if($total > 0)
        {
            $file = fopen($path, "wb");

            foreach($productosArr as $key=>$stringArr)
            {
                fputcsv($file, $stringArr);
            }
        
            fclose($file);
        }
    }

    public static function CargarArchivoCSV($pathCSV)
    {
        $listaProductos = array();
        $fila = 1;
        if (($gestor = fopen($pathCSV, "r")) !== FALSE) 
        {
            while (($datos = fgetcsv($gestor, 1000, ",")) !== FALSE) 
            {
                $producto = new Producto();
                $producto->id = $datos[0];
                $producto->idTipoEmpleado = $datos[1];
                $producto->descripcion = $datos[2];
                $producto->precio = $datos[3];
                $producto->minutosPreparacion = $datos[4];
                array_push($listaProductos, $producto);
            }
            fclose($gestor);
        }

        return $listaProductos;
    }
}