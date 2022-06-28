<?php
require_once './models/Empleado.php';
require_once './interfaces/IApiUsable.php';

class EmpleadosController extends Empleado implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $idTipoEmpleado = $parametros['idTipoEmpleado'];
        $idEstado = $parametros['idEstado'];
        $usuario = $parametros['usuario'];
        $clave = $parametros['clave'];

        $empleado = new Empleado();
        $empleado->idTipoEmpleado = $idTipoEmpleado;
        $empleado->idEstado = $idEstado;
        $empleado->usuario = $usuario;
        $empleado->clave = $clave;

        $nuevoId = $empleado->CrearEmpleado();

        $payload = json_encode(array("mensaje" => "Empleado creado con exito", "id" => $nuevoId));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $usuario = $args['usuario'];
        $empleado = Empleado::ObtenerUsuario($usuario);
        $payload = json_encode($empleado);

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Empleado::ObtenerTodos();
        $payload = json_encode(array("listaEmpleado" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function ObtenerPDF($request, $response, $args)
    {
      $lista = Empleado::ObtenerTodos();

      if(count($lista) > 0)
      {
        
        $pdf = new FPDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',9);

        foreach ($lista as $key => $empleado) 
        {
          $stringEmpleado = Empleado::ToString($empleado);
          $pdf->Cell(140,10, $stringEmpleado, 0, 1);
        }

        $pdf->Output('D', 'empleadosResto.pdf', true);

        return $response->withHeader('Content-Type', 'application/pdf');
      }
    }

    public function ObtenerCSV($request, $response, $args)
    {
      $lista = Empleado::ObtenerTodos();

      Empleado::GuardarToCSV($lista, "./empleados.csv");

      $response = $response
            ->withHeader('Content-Type', 'application/octet-stream')
            ->withHeader('Content-Disposition', 'attachment; filename=empleados.csv')
            ->withAddedHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->withHeader('Cache-Control', 'post-check=0, pre-check=0')
            ->withHeader('Pragma', 'no-cache')
            ->withBody((new \Slim\Psr7\Stream(fopen("./empleados.csv", 'rb'))));
        return $response;
    }

    public function CargarCSV($request, $response, $args)
    {
      $payload = null;

      try 
      {
        $csv = $_FILES['csv'];
        $lista = Empleado::CargarArchivoCSV($csv["tmp_name"]);
        $errores = null;
  
        foreach ($lista as $key => $empleado) 
        {
          $errores = Empleado::Validar($empleado->usuario);
        }
  
        if(count($errores) > 0)
        {
          throw new Exception(json_encode($errores), 800);
        }
  
        foreach ($lista as $key => $empleado) 
        {
          $nuevoId = $empleado->CrearEmpleado();
          $empleado->id = $nuevoId;
        }
  
        $payload = json_encode(array("listaEmpleados" => $lista));
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
