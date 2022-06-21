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
                return Logger::UsuarioAutorizado($token);
                break;
            case '/productos':
                return Logger::UsuarioAutorizado($token);
                break;
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
