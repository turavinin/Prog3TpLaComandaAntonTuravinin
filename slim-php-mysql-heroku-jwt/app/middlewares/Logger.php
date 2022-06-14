<?php

use GuzzleHttp\Psr7\Response;

require_once './middlewares/AutentificadorJWT.php';

class Logger
{
    public static function VerificarCredenciales($request, $handler)
    {
        $response = new Response();
        $method = $request->getMethod();

        switch ($method) {
            case 'GET':
                $response = Logger::ManejadorGET($request, $handler);
                break;
            default:
                $response = Logger::ManejadorPOST($request, $handler);
        }

        return $response;
    }

    private static function ManejadorGET($request, $handler)
    {
        return $handler->handle($request);
    }

    private static function ManejadorPOST($request, $handler)
    {
        $response = new Response();

        $body = $request->getParsedBody();
        $headers = $request->getHeaders();
        $headerAuth = $request->getHeaderLine('Authorization');
        $token = trim(explode("Bearer", $headerAuth)[1]);
        $error = 'ninguno';
        $decodificado = 'ninguno';
        

        try 
        {
            $decodificado = AutentificadorJWT::VerificarToken($token);
        } 
        catch (Exception $ex) 
        {
            $error = $ex;
        }
        
        $payload = json_encode(array("body" => $body, "header" => $headers, "decodificado" => $decodificado, "errores" => $error));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
