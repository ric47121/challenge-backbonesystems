<?php

function file_get_contents_utf8($fn) {
    $content = file_get_contents($fn);
    return mb_convert_encoding($content, 'UTF-8',
        mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}


function procesar_int($s){
   return	($s)? intval($s):null;
}

function procesar_str($cadena){
    $cadena = eliminar_acentos($cadena);
    $cadena = strtoupper($cadena);
    $cadena = utf8_encode($cadena);

    return ($cadena);
}

function procesar_str_sin_uppercase($cadena){
    // $cadena = eliminar_acentos($cadena);
    // $cadena = strtoupper($cadena);
    // $cadena = utf8_encode($cadena);
    // $cadena = utf8_encode($cadena);

    return ($cadena);
}

function eliminar_acentos($cadena){
   
   //Reemplazamos la A y a
   $cadena = str_replace(
   array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
   array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
   $cadena
   );

   //Reemplazamos la E y e
   $cadena = str_replace(
   array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
   array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
   $cadena );

   //Reemplazamos la I y i
   $cadena = str_replace(
   array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
   array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
   $cadena );

   //Reemplazamos la O y o
   $cadena = str_replace(
   array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
   array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
   $cadena );

   //Reemplazamos la U y u
   $cadena = str_replace(
   array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
   array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
   $cadena );

   //Reemplazamos la N, n, C y c
   $cadena = str_replace(
   array('Ñ', 'ñ', 'Ç', 'ç'),
   array('N', 'n', 'C', 'c'),
   $cadena
   );
   
   return $cadena;
}