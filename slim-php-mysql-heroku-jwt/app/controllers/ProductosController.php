<?php
require_once './models/Producto.php';
require_once './interfaces/IApiUsable.php';
require_once './helpers/fpdf/fpdf.php';

class ProductosController extends Producto
{
    public function TraerTodos($request, $response, $args)
    {
      $payload = null;

      try 
      {
        $lista = Producto::ObtenerTodos();

        if(count($lista) < 1)
        {
          throw new Exception("No se encontraron productos.");
        }

        $payload = json_encode(array("productos" => $lista));
      } 
      catch (Exception $ex) 
      {
        $mensaje = $ex->getMessage();

        if($ex->getCode() == 800)
        {
            $mensaje = json_decode($ex->getMessage());
        }
      
        $payload = json_encode(array('Error' => $mensaje));
      }
      finally
      {
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
      }
    }

    public function CargarUno($request, $response, $args)
    {
      $parametros = $request->getParsedBody();

      $idTipoEmpleado = $parametros['idTipoEmpleado'];
      $descripcion = $parametros['descripcion'];
      $precio = $parametros['precio'];
      $minutosPreparacion = $parametros['minutosPreparacion'];

      $payload = null;

      try 
      {
        $erroresValidacion = Producto::Validar($descripcion);

        if(count($erroresValidacion) > 0)
        {
          throw new Exception(json_encode($erroresValidacion), 800);
        }

        $producto = new Producto();
        $producto->idTipoEmpleado = $idTipoEmpleado;
        $producto->descripcion = $descripcion;
        $producto->precio = $precio;
        $producto->minutosPreparacion = $minutosPreparacion;
        $nuevoId = $producto->CrearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito", "id" => $nuevoId));
      } 
      catch (Exception $ex) 
      {
        $mensaje = $ex->getMessage();

        if($ex->getCode() == 800)
        {
            $mensaje = json_decode($ex->getMessage());
        }

        $payload = json_encode(array('Error' => $mensaje));
      }
      finally
      {
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
      }
    }

    public function ObtenerPDF($request, $response, $args)
    {
      $productos = Producto::ObtenerTodos();

      if(count($productos) > 0)
      {
        
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',9);

        foreach ($productos as $key => $producto) 
        {
          $stringProducto = Producto::ToString($producto);
          $pdf->Cell(140,10, $stringProducto, 0, 1);
        }

        $pdf->Output('D', 'productosResto.pdf', true);

        return $response->withHeader('Content-Type', 'application/pdf');
      }
    }

    public function ObtenerCSV($request, $response, $args)
    {
      $productos = Producto::ObtenerTodos();

      Producto::GuardarProductoToCSV($productos, "./productos.csv");

      $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=productos.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen("./productos.csv", 'rb'))));
        return $response;
    }

    public function CargarCSV($request, $response, $args)
    {
      $payload = null;

      try 
      {
        $csv = $_FILES['csv'];
        $listaProductos = Producto::CargarArchivoCSV($csv["tmp_name"]);
        $errores = null;
  
        foreach ($listaProductos as $key => $producto) 
        {
          $errores = Producto::Validar($producto->descripcion);
        }
  
        if(count($errores) > 0)
        {
          throw new Exception(json_encode($errores), 800);
        }
  
        foreach ($listaProductos as $key => $producto) 
        {
          $nuevoId = $producto->CrearProducto();
          $producto->id = $nuevoId;
        }
  
        $payload = json_encode(array("listaProductos" => $listaProductos));
      } 
      catch (Exception $ex) 
      {
          $mensaje = $ex->getMessage();

          if($ex->getCode() == 800)
          {
              $mensaje = json_decode($ex->getMessage());
          }

          $payload = json_encode(array('Error' => $mensaje));
      }
      finally
      {
          $response->getBody()->write($payload);
          return $response->withHeader('Content-Type', 'application/json');
      }
    }
}