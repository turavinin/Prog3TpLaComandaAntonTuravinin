<?php

use GuzzleHttp\Psr7\Response;

require_once './middlewares/AutentificadorJWT.php';

class Logger
{
    private static $idSocio = 1;
    private static $idCervecero = 2;
    private static $idCocinero = 3;
    private static $idMozo = 4;
    private static $idBartender = 5;

    public static function VerificarCredenciales($request, $handler)
    {
        $response = new Response();
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        $errores = "";

        
        try 
        {
            $headerAuth = $request->getHeaderLine('Authorization');
            $token = AutentificadorJWT::GetTokenDelHeader($headerAuth);
    
            $usuarioEsValido = Logger::VerificarAccesoEndpoint($method, $path, $token);

            if($usuarioEsValido == true)
            {
                return $handler->handle($request);
            }

            $errores .= "Usuario no autorizado";
        } 
        catch (Exception $ex) 
        {
            $errores .= $ex->getMessage();
        }
        
        $payload = json_encode(array("error" => $errores));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private static function VerificarAccesoEndpoint($method, $path, $token)
    {
        switch($method)
        {
            case 'GET':
                return Logger::VerficarGets($path, $token);
                break;
            case 'POST':
                return Logger::VerifcarPost($path, $token);
                break;
        }
    }

    private static function VerficarGets($path, $token)
    {
        switch($path)
        {
            case '/empleados':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/productos':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/mesas':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/mesas/disponibles':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/pedidos':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio));
                break;
            case '/pedidos/csv/descarga':
                return Logger::UsuarioAutorizado($token);
                break;
            case '/pedidos-productos/estado/empleado':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idCervecero, self::$idCocinero, self::$idBartender));
                break;
            case '/pedidos-productos/estado':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/encuesta':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio));
                break;
            case '/mesas/estadistica/usadas':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio));
                break;
            default:
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                
        }
    }

    private static function VerifcarPost($path, $token)
    {
        switch($path)
        {
            case '/empleados':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio));
                break;
            case '/productos':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/mesas':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio));
                break;
            case '/mesas/estado':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/pedidos':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/pedidos/cobrar':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/pedidos/estado/cerrar':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio));
                break;
            case '/cliente':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
                break;
            case '/pedidos-productos/estado/empleado':
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idCervecero, self::$idCocinero, self::$idBartender));
                break;
            default:
                return Logger::UsuarioAutorizado($token, array(self::$idSocio, self::$idMozo));
        }
    }

    private static function UsuarioAutorizado($token, $arrayIds = null)
    {
        $esValido = true;
        $decodificado = AutentificadorJWT::VerificarToken($token);
        $id = $decodificado->data->idTipoEmpleado;

        if($arrayIds != null && count($arrayIds) > 0)
        {
            $esValido = in_array($id, $arrayIds);
        }

        return $esValido;
    }
}
