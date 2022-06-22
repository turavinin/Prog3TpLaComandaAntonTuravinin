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
    public $listaProductosPedidos;

    public static function ObtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT 
        P.Id as id,
        P.IdMesa as idMesa,
        P.Codigo as codigo,
        P.MinutosTotalesPreparacion as minutosTotalesPreparacion,
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
        P.FechaAlta as fechaAlta,
        P.FechaFin as fechaFin
        FROM pedidos P
        WHERE P.Codigo = :codigoPedido");

        $consulta->bindValue(':codigoPedido', $codigoPedido, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public function CrearPedido()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO 
        pedidos (IdMesa, Codigo, MinutosTotalesPreparacion, FechaAlta) 
        VALUES (:idMesa, :codigo, :minutosTotalesPreparacion, :fechaAlta)");

        $codigo = Mesa::GenerarCodigo(5);
        
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->bindValue(':minutosTotalesPreparacion', $this->minutosTotalesPreparacion, PDO::PARAM_INT);
        $consulta->bindValue(':fechaAlta', $this->fechaAlta, PDO::PARAM_STR);
        $consulta->execute();

        return $codigo;
    }

    public static function GenerarCodigo($largo)
    {
        return substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $largo); 
    }

    public static function ValidarPedidos($idMesa, $listaProductosIds)
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

        foreach ($listaProductosIds as $key => $idProducto) 
        {
            if(!is_numeric($idProducto) || Producto::ObtenerPorId($idProducto) == null)
            {
                array_push($mensajesError, "El producto ". $idProducto . " es invalido o no existe.");
            }
        }

        return $mensajesError;
    }

    private static function PedidoToCSV($pedido)
    {
        return "$pedido->id,$pedido->idMesa,$pedido->codigo,$pedido->minutosTotalesPreparacion,$pedido->fechaAlta". PHP_EOL;
    }

    public static function GuardarPedidoToCSV($pedidos, $path)
    {
        
        $pedidosCSV = array();

        foreach ($pedidos as $key => $pedido) 
        {
            array_push($pedidosCSV, Pedido::PedidoToCSV($pedido));
        }

        $totalPedidos = count($pedidosCSV);

        if($totalPedidos > 0)
        {
            $file = fopen($path, "wb");
    
            foreach($pedidosCSV as $key=>$pedidoString)
            {
                fwrite($file, $pedidoString);
                
                if($key != $totalPedidos - 1) 
                {
                    fwrite($file, "". PHP_EOL);
                }
            }
        
            fclose($file);
        }
    }
}