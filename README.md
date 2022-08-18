# challenge-backbonesystems


primero leo un txt con todos los zip codes.

luego recorro linea por linea comparando el codgigo buscado.

cuando lo encuentro proceso los datos para generar el json.

cuando pasa a otro codigo corto el proceso para ahorrar recursos, ya que el archivo viene ordenado por zip-code de menor a mayor.

## actualizacion
guardo los resultados en redis la primera vez. luego las consultas son mas rapidas

## actualizacion 2
add cors middleware

## actualizacion 3
creo archivo de indices y luego sobre ese hago una busqueda binaria

## actualizacion 4
el indice lo grabo en redis