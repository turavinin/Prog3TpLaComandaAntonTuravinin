<?php

class ImagenesManager
{
    private $directorio;

    public function __construct($path)
    {
        $this->CrearDirectorioSiNoExiste($path);
        $this->SetDirectory($path);
    }

    public function CrearDirectorioSiNoExiste($path)
    {
        if (!file_exists($path)) 
        {
            mkdir($path, 0777, true);
        }
    }

    public function SetDirectory($path)
    {
        $this->directory = $path;
    }

    public function GetDirectory()
    {
        return $this->directory;
    }

    public function GuardarImagen($fileArray, $nuevoNombreImagen)
    {
        $fileName = $nuevoNombreImagen;
        $dir = $this->GetDirectory();

        $path = $dir.$fileName;
        $from = $fileArray['foto']['tmp_name'];

        move_uploaded_file($from, $path);

        return $path;
    }

    public static function GetNombreImagen($arrayTitulos, $extension)
    {
        $fileName = null;

        foreach ($arrayTitulos as $key => $titulo) 
        {
            $tituloImagen = str_replace(':', '_', str_replace(' ', '', $titulo));

            if($fileName == null)
            {
                $fileName = $tituloImagen;
            }
            else
            {
                $fileName .= '_'.$tituloImagen;
            }
        }

        $fileName .= $extension;

        return $fileName;
    }
}