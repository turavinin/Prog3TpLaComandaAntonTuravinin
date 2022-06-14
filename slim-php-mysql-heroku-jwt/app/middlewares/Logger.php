<?php

use GuzzleHttp\Psr7\Response;

require_once './middlewares/AutentificadorJWT.php';

class Logger
{
    private static $idSocio = 5;

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
        finally
        {
            $payload = json_encode(array("error" => $errores));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
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

        }
    }

    private static function VerifcarPost($path, $token)
    {
        switch($path)
        {
            case '/empleados':
                return Logger::UsuarioAutorizado($token, self::$idSocio);
                break;
        }
    }

    private static function UsuarioAutorizado($token, $idTipoEmpleado = null)
    {
        $esValido = true;
        $decodificado = AutentificadorJWT::VerificarToken($token);
        $id = $decodificado->data->idTipoEmpleado;

        if($idTipoEmpleado != null)
        {
            $esValido = $id == $idTipoEmpleado;
        }

        return $esValido;
    }

    // private static function VerifcarPostasd($path, $token)
    // {
    //     $response = new Response();

    //     $body = $request->getParsedBody();
    //     $headers = $request->getHeaders();
    //     $headerAuth = $request->getHeaderLine('Authorization');
    //     $token = trim(explode("Bearer", $headerAuth)[1]);
    //     $error = 'ninguno';
    //     $decodificado = 'ninguno';
        

    //     try 
    //     {
    //         $decodificado = AutentificadorJWT::VerificarToken($token);
    //     } 
    //     catch (Exception $ex) 
    //     {
    //         $error = $ex;
    //     }
        
    //     $payload = json_encode(array("body" => $body, "header" => $headers, "decodificado" => $decodificado, "errores" => $error));

    //     $response->getBody()->write($payload);
    //     return $response
    //       ->withHeader('Content-Type', 'application/json');
    // }
}
