<?php
//CODIGO
//Ejercicio 1
$comentarioPHP1 = "/(\<?php)/";
$comentarioPHP2 = "/(\?\>)/";
$comentario = "/^(\/\/.*)|^(\/\.*)|^(\/\/.*)|^(<!--.*)|^(#)|^( )|^(\t)/"; //Variable universal que define todo lo que es un comentario
$directorio = fopen("./Directories.conf","r") or exit("Error al abrir e archivo Directories.conf"); //Variable que nos permite abrir el archivo Directories.conf
$cont1 = -2;//Contador de elementos analizados para el ejercicio 1
$errores1 = 0;//Contador de errores para el ejercicio 1
//$arrayuno[] será el array donde se almacenará todo el texto de salida del ejercicio 1
$arrayuno[] = array(" 1 Existen los directorios especificados en el fichero Directories.conf y no hay ningún fichero mas en el directorio principal que el index.php:"," ");
//Este while nos permite recorrer el archivo Directories.conf y comprobar si las carpetas y ficheros expuestos en el existen en su correspondiente direccion
while($linea = trim(fgets($directorio))) {
	if (feof($directorio)) break;//Si se llega al final del directorio, finalizará el bucle
		if(!is_file($linea) && !is_dir($linea)){//si no es un directorio o no es un fichero significaría que ese archivo/directorio no existe, por lo tanto lanzaremos un error para esa linea
			$errores1++;
			$arrayuno[] = array("$linea","ERROR, No existe");
	    }else{//Si existe lanzamos un OK
			$arrayuno[] = array("$linea","OK");
			$cont1++;
	}
}

//Con este if comprobaremos si existe el fichero "index.php" dentro del directorio CodigoAExaminar
if(!is_file("./index.php")){
	$arrayuno[] = array("<p>FICHERO index.php","ERROR, NO EXISTE</p>");
	$errores1++;
}else{
	$arrayuno[] = array("<p>FICHERO index.php","OK</p>");
	$cont1++;
}

//Con este trozo de código comprobaremos si ademas del index.php hay algun otro archivo que no esté especificado en Directories.conf
$carpeta = opendir("./") or exit("Error a abrir el directorio $direccion");
$files = array();
$aux = FALSE;
while($current = readdir($carpeta)){
	if(is_file("./$current") && !preg_match("/(index\.php)/", $current) && !preg_match("/^(\.)/", $current) && !preg_match("/(Directories\.conf)/",$current) && !preg_match("/(Files\.conf)/",$current)){
		if(!comprobarDirectories($current)){
			$arrayuno[] = array("FICHERO $current","ERROR, NO DEBERIA EXISTIR (no pertenece al fichero Directories.conf)");
			$errores1++;
		}
	}
}

function comprobarDirectories($valor){//Esta función simplemente comprobará si $valor está dentro de las líneas del fichero Directories.conf
	global $directorio;
	$toret = FALSE;
	while($linea = trim(fgets($directorio))) {
		if (feof($directorio)) break;//Si se llega al final del directorio, finalizará el bucle
		if($linea == "./$valor" && !preg_match("/(index\.php)/",$valor)){
			$toret = TRUE;
			break;
		}
	}
	return $toret;
}
$cont1 = $cont1 + $errores1;//calculamos el número total real de elementos analizados


//Ejercicio 2
$files = fopen("./Files.conf","r") or exit("Error al abrir el archivo Directories.conf");//abrimos Files.conf
$arraydos[] = array(" 2 Los ficheros tienen el nombre indicado en la especificación en el fichero Files.conf"," ");//En este array se guardará todo el texto a mostrar
$cont2 = 0;//Contador de elementos analizados para el ejercicio 2
$errores2 = 0;//Contador errores ejercicio 2

while($file=fgets($files,100)){
	$arraydos[] = array("&emsp;&emsp;$file","");
	$file=trim($file);
	if(!preg_match("/%_/",$file)){
		if(file_exists($file)){
			$arraydos[] = array("&emsp;&emsp;&emsp;&emsp;$file","OK");
			$errores2++;
		}
		else{
			$arraydos[] = array("&emsp;&emsp;&emsp;&emsp;$file","ERROR");
			$cont2++;
			$errores2++;
		}
	}
	else{
		$cadena=explode("/",$file);
		$cadena1=array_reverse($cadena);
		$cadena2=str_replace("%","([[:alnum:]]+)",$cadena1[0]);
		$cadena2=str_replace("_","(_)",$cadena2);
		array_pop($cadena);
		$ruta = implode("/",$cadena);
		$aux = scandir($ruta);

		foreach($aux as $i => $value){
			if($aux[$i] != "." && $aux[$i] != ".." && !is_dir("$ruta/$aux[$i]")){
				if(preg_match("/$cadena2/",$aux[$i])){
					$arraydos[] = array("&emsp;&emsp;&emsp;&emsp;$ruta/$aux[$i]","OK");
					$cont2++;
				}else{
					$arraydos[] = array("&emsp;&emsp;&emsp;&emsp;$ruta/$aux[$i]","ERROR");
					$errores2++;
				}
			}
		}
	}
}

//3
$errores3 = 0;//Contador de errores ejercicio 3
$cont3 = 0;//Contador de elementos analizados ejercicio 3
$arraytres[] = array(" 3 Los ficheros del directorio CodigoAExaminar tiene todos al principio del fichero comentada su función, autor y fecha"," ");//Array de texto de respuesta 

function recorrerCarpeta($direccion){//Función que ejecuta el analisis del ejercicio 3
//Llamamos a los valores con global ya que esta función puede ser recursiva
	global $comentario;
	global $arraytres;
	global $errores3;
	global $cont3;
	global $comentarioPHP1;	
	global $comentarioPHP2;
	$carpeta = opendir($direccion) or exit("Error a abrir el directorio $direccion");
	$files = array();
	$aux = FALSE;
	while($current = readdir($carpeta)){//Recorremos la carpeta CodigoAExaminar
		if($current != "." && $current != ".."){//En caso de no ser "." o ".." seguimos analizando
			if(is_dir($direccion."/".$current)){//Si es un directorio, hacemos una llamada recursiva
				recorrerCarpeta($direccion.$current.'/');
			}else if(comprobarPropietario($current)){//Si es un fichero comenzamos el análisis
				$boolCom = FALSE;
				$boolBienCom = FALSE;
				$linea = file($direccion.$current);
				for($i = 0; $i < sizeof($linea); $i++){
					if(preg_match("$comentario","$linea[$i]")){
						$boolCom = TRUE;
						if(comprobarComen($linea[$i])==TRUE){
							$boolBienCom = TRUE;
							break;
						}else{
							break;
						}
					}else if(!ctype_space($linea[$i]) && !preg_match("$comentarioPHP1",$linea[$i]) && !preg_match("$comentarioPHP2",$linea[$i])){
						break;
					}
				}
				if($boolCom && $boolBienCom){
					$arraytres[] = array("$direccion$current","OK");
					$cont3++;
				}else if($boolCom){
					$errores3++;
					$arraytres[] = array("$direccion$current","ERROR: COMENTARIOS DE CABECERA INCOMPLETOS");
				}else if(!$boolCom && !$boolBienCom){
					$arraytres[] = array("$direccion$current","ERROR: NO TIENE COMENTARIOS DE CABECERA");
					$errores3++;
				}
			}
		}
	}
}

function comprobarPropietario($objetoAExaminar){//Con esta función comprobamos los archivos para evitar que analice tambien archivos como imagenes que no nos interesa que analice
    $toret = explode('.',$objetoAExaminar);//para conseguirlo, dividimos el nombre utilizando el "." y cogiendo la última parte de esto
	$aux = sizeof($toret)-1;
	$resultado = FALSE;
	if(preg_match("/(php)|(css)|(html)|(js)|(mysql)|(c)|(inic)/" , "$toret[$aux]")){//en caso de ser el archivo que queremos devolvemos TRUE, en caso contrario FALSE
		$resultado = TRUE;
	}
	return $resultado;
}


recorrerCarpeta("./CodigoAExaminar/");
$cont3 = $cont3 + $errores3;
function comprobarComen($linea){//Simplemente comprueba si está bien comentado
	$toret = FALSE;
	if(preg_match("/(autor)|(AUTOR)|(author)|(AUTHOR)|(Author)|(Autor)/",$linea) && preg_match("/(function)|(Function)|(función)|(Función)|(FUNCTION)|(FUNCIÓN)|(Funcion)|(funcion)|(FUNCION)/",$linea) && preg_match("/(fecha)|(Fecha)|(FECHA)|(date)|(Date)|(DATE)/",$linea)){
		$toret = TRUE;
		return $toret;
	}else{
		return $toret;
	}
}


//4
$cont4 = 0;
$errores4 = 0;
$arraycuatro[] = array("4 Las funciones y métodos en el código de directorio CodigoAExaminar tienen comentarios con una descripción antes de su comienzo"," ");

function recorrerFunciones($dir){
	global $comentario;
	global $arraycuatro;
	global $errores4;
	global $cont4;
	$car = opendir($dir) or exit("Error a abrir el directorio $dir");
	$aux = FALSE;
	while($cur = readdir($car)){
		if($cur != "." && $cur != ".."){
			if(is_dir($dir."/".$cur)){
				recorrerFunciones($dir.$cur.'/');
			}else if(comprobarPropietario($cur)){
				$l = file($dir.$cur);
				$aux = 0;
				$numRep = FALSE;
				$contador = 0;	
				for($i = 0; $i < sizeof($l); $i++){
					if(preg_match("/^(function)/",trim($l[$i]))){
						if(!estaComentado($i,$l)){
							if(!$numRep){
								$aux++;
								$cont4++;
								$numRep = TRUE;
							}
                            $numLineas = $i + 1;
							$nombreError = extraerNombre($l[$i]);
							if($contador == 0){
								$contador++;
								$arraycuatro[] = array("$dir$cur</br>&emsp;&emsp;&emsp;&emsp;<span style='color:rgb(255,0,0)'>Function $nombreError sin comentario de descripción en la línea $numLineas","ERROR </span>");
								$errores4++;
							}else{
								$arraycuatro[] = array("&emsp;&emsp;&emsp;&emsp;<span style='color:rgb(255,0,0)'>Function $nombreError sin comentario de descripción en la línea $numLineas","  </span>");
								$errores4++;
							}
						}else{
						}
					}
				}
				if($aux == 0){
					$arraycuatro[] = array("$dir$cur","OK");
					$cont4++;
				}
			}
		}
	}
}
function extraerNombre($string){
	$toret1 = explode("{",$string);
	$toret = explode("function",$toret1[0]);
	return $toret[1];
}

function estaComentado($i,$array){
	global $comentario;
	$toret = FALSE;
	for($j=$i-1;$j >= 0;$j--){
		if(preg_match("/(\/\/.*)|(\/\.*)|(\/\/.*)|(<!--.*)|(#)/",trim($array[$i]))){
			$toret = TRUE;
			break;
		}else if(preg_match("/^(\/\/.*)|^(\/\.*)|^(\/\/.*)|(<!--.*)|(#)/",trim($array[$j]))){
			$toret = TRUE;			
			break;
		}else if(!ctype_space($array[$j])){
			$toret = FALSE;
			break;
		}
	}
	return $toret;
}
recorrerFunciones("./CodigoAExaminar/");



//5
$cont5 = 0;
$errores5 = 0;
$arraycinco[] = array("\n\n\n 5 En el código están todas las variables definidas antes de su uso y tienen un comentario antes o en la misma linea\n"," ");

function recorrerVariables($dir){
	global $comentario;
	global $arraycinco;
	global $errores5;
	global $cont5;
	$car = opendir($dir) or exit("Error a abrir el directorio $dir");
	$aux = FALSE;
	while($cur = readdir($car)){
		if($cur != "." && $cur != ".."){
			if(is_dir($dir."/".$cur)){
				recorrerVariables($dir.$cur.'/');
			}else if(comprobarPropietario($cur)){
				$l = file($dir.$cur);
				$aux = 0;
				$contador = 0;
				$numRep = FALSE;
				for($i = 0; $i < sizeof($l); $i++){
					$numLinea = $i + 1;
					if(preg_match("/(^[\$])/",trim($l[$i])) && preg_match("/([=])/",trim($l[$i])) && !preg_match("/([\.][=])|([+][=])|([-][=])/",trim($l[$i]))){
						$nombreError = extraerNombreVar($l[$i]);
						if(empty($nombreError)){
							if(!$numRep){
								$aux++;
								$cont5++;
								$numRep = TRUE;
							}
							if($contador == 0){
								$contador++;
								$arraycinco[] = array("$dir$cur</br>&emsp;&emsp;&emsp;&emsp;<span style='color:rgb(255,0,0)'>Variable $nombreError en la línea $numLinea no está definido antes de su inicio","ERROR </span>");
								$errores5++;
							}else{
								$arraycinco[] = array("&emsp;&emsp;&emsp;&emsp;<span style='color:rgb(255,0,0)'>Variable $nombreError en la línea $numLinea no está definido antes de su inicio"," </span>");
								$errores5++;
							}

						}else if(!estaComentado($i,$l)){
							if(!$numRep){
								$aux++;
								$cont5++;
								$numRep = TRUE;
							}
							if($contador == 0){
								$contador++;
								$arraycinco[] = array("$dir$cur</br>&emsp;&emsp;&emsp;&emsp;<span style='color:rgb(255,0,0)'>Variable $nombreError en la línea $numLinea no está definido antes de su inicio","ERROR </span>");
								$errores5++;
							}else{
								$arraycinco[] = array("&emsp;&emsp;&emsp;&emsp;<span style='color:rgb(255,0,0)'>Variable $nombreError en la línea $numLinea no está definido antes de su inicio"," </span>");
								$errores5++;
							}
						}
					}
				}
				if($aux == 0){
					if($contador == 0){
						$arraycinco[] = array("$dir$cur","OK\n");
						$cont5++;
					}
				}
			}
		}
	}
}

function extraerNombreVar($string){
	$toret = explode("=",$string);
	$cadena = str_replace("$","",$toret[0]);
	return "$$cadena";
}


recorrerVariables("./CodigoAExaminar/");


//Ejercicio 6
$cont6 = 0;
$errores6 = 0;
$arrayseis[] = array("\n\n\n 6 En el código están comentadas todas las estructuras de control antes de uso o en la misma línea\n"," ");

function recorrerEstructuras($dir){
	global $comentario;
	global $arrayseis;
	global $errores6;
	global $cont6;
	$car = opendir($dir) or exit("Error a abrir el directorio $dir");
	$aux = FALSE;
	while($cur = readdir($car)){
		if($cur != "." && $cur != ".."){
			if(is_dir($dir."/".$cur)){
				recorrerEstructuras($dir.$cur.'/');
			}else if(comprobarPropietario($cur)){
				$linea = file($dir.$cur);
				$aux = 0;
				$contador = 0;
				$numRep = FALSE;
				for($i = 0; $i < sizeof($linea); $i++){
					$test = $i + 1;
					if(preg_match("/\b(if)|\b(while)|\b(else)|\b(do)|\b(for)|\b(foreach)|\b(switch)|\b(goto)/i",$linea[$i]) && (preg_match("/(\{)/",trim($linea[$i])) || preg_match("/^(\{)/",trim($linea[$test])))){
						if(!estaComentado($i,$linea)){
							if(!$numRep){
								$aux++;
								$cont6++;
								$numRep = TRUE;
							}
							$nombreError = extraerNombreEst($linea[$i]);
							$numLinea = $i + 1;
							if($contador == 0){
								$contador++;
								$arrayseis[] = array("$dir$cur</br>&emsp;&emsp;&emsp;&emsp;<span style='color:rgb(255,0,0)'>$nombreError sin comentario de descripción en la línea $numLinea","ERROR </span>");
                        				        $errores6++;
							}else{
								$arrayseis[] = array("&emsp;&emsp;&emsp;&emsp;<span style='color:rgb(255,0,0)'>$nombreError sin comentario de descripción en la línea $numLinea"," </span>");
                        				        $errores6++;
							}
						}
					}
				}
				if($aux == 0){
					$arrayseis[] = array("$dir$cur","OK");
					$cont6++;
				}
			}
		}
	}
}
function extraerNombreEST($string){
	$toret = explode("(",trim($string));
	return $toret[0];
}
recorrerEstructuras("./CodigoAExaminar/");


function comprobarPHP($objetoAExaminar){//Esta función fue creada para comprobar que el archivo pasado como parámetro sea un .php para los ejercicios 7, 8 y 9
    $toret = explode('.',$objetoAExaminar);
	$aux = sizeof($toret)-1;
	$resultado = FALSE;
	if(preg_match("/(php)/" , "$toret[$aux]")){
		$resultado = TRUE;
	}
	return $resultado;
}

//7
$cont7 = 0;
$errores7 = 0;
$arraysiete[] = array("\n\n\n 7 Todos los ficheros dentro del directorio Model son definiciones de clases\n"," ");


function recorrerModel($dir){
	global $comentario;
	global $arraysiete;
	global $errores7;
	global $cont7;
	$car = opendir($dir) or exit("Error a abrir el directorio $dir");
	$aux = 0;
	while($cur = readdir($car)){
		if($cur != "." && $cur != ".." && comprobarPHP($cur)){
			$lineas = file($dir.$cur);
			$int = apartado79($lineas);
			if($int == 1){
				$arraysiete[] = array("$dir$cur","ERROR: No es una clase");
				$errores7++;
			}else if($int == 2){
				$arraysiete[] = array("$dir$cur","OK");
				$cont7++;
			}
			
		}
	}
}

function apartado79($lineas){
	$toret = 1;
	$array = comprobarInicio($lineas,0);
	if($array[2] == 2){
		$toret = 1;
	}else if($array[2] == 3){
		$toret = 2;
	}else if($array[2] == 1){
		$i = $array[1];
		$cont = sizeof($lineas);
		$k = 0;
		$llaves = 1;
		$break = FALSE;
		while($i < $cont && $llaves != 0){
			$str = str_split($lineas[$i]);
			for($x = 0; $x < count($str); $x++){
				if($str[$x] == '{'){
					$llaves++;
				}else if($str[$x] == '}'){
					if($k == 0){
						$k++;
						$llaves--;
					}
					$llaves--;
				}
			}
			$lineas[$i] = str_replace($lineas[$i], " ", $lineas[$i]);
			$i++;
		}
		if($llaves != 0){
			$toret = 1;
		}else{
			$toret = apartado79($lineas);
		}
	}
	return $toret;
}


function comprobarInicio($linea,$punto){
	global $comentario;
	$comentarioPHP1 = "/(\<\?php)/";
	$comentarioPHP2 = "/(\?\>)/";
	$aux = array();
	$aux[2] = 3;
	for($i = $punto; $i < sizeof($linea); $i++){
		if(preg_match("/^(class)/",trim($linea[$i]))){
			$aux[0] = TRUE;
			$aux[1] = $i;
			$aux[2] = 1;
			break;
		}else if(!preg_match("$comentario",trim($linea[$i])) && !ctype_space($linea[$i]) && !preg_match("$comentarioPHP1",$linea[$i]) && !preg_match("$comentarioPHP2",$linea[$i])){
			$aux[0] = FALSE;
			$aux[2] = 2;
			break;
		}
	}
	return $aux;
}

recorrerModel("./CodigoAExaminar/Model/");
$cont7 = $cont7 + $errores7;



//8
$cont8 = 0;
$errores8 = 0;
$arrayocho[] = array("\n\n\n 8 Todos los ficheros dentro del directorio Controller son scripts\n"," ");
$llaves = 0;

function recorrerController($dir){
	global $comentario;
	global $arrayocho;
	global $errores8;
	global $cont8;
	$car = opendir($dir) or exit("Error a abrir el directorio $dir");
	$aux = 0;
	while($cur = readdir($car)){
		if($cur != "." && $cur != ".." && comprobarPHP($cur)){
			$lineas = file($dir.$cur);
			$int = apartado79($lineas);
			if($int == 2){
				$arrayocho[] = array("$dir$cur","ERROR: No es un script");
				$errores8++;
			}else if($int == 1){
				$arrayocho[] = array("$dir$cur","OK");
				$cont8++;
			}
			
		}
	}
}

recorrerController("./CodigoAExaminar/Controller/");
$cont8 = $cont8 + $errores8;



//9
$cont9 = 0;
$errores9 = 0;
$arraynueve[] = array("\n\n\n 9 Todos los ficheros dentro del directorio View son definiciones de clases\n"," ");
$llaves = 0;
function recorrerView($dir){
	global $comentario;
	global $arraynueve;
	global $errores9;
	global $cont9;
	$car = opendir($dir) or exit("Error a abrir el directorio $dir");
	$aux = 0;
	while($cur = readdir($car)){
		if($cur != "." && $cur != ".." && comprobarPHP($cur)){
			$lineas = file($dir.$cur);
			$int = apartado79($lineas);
			if($int == 1){
				$arraynueve[] = array("$dir$cur","ERROR: No es una clase");
				$errores9++;
			}else if($int == 2){
				$arraynueve[] = array("$dir$cur","OK");
				$cont9++;
			}
			
		}
	}
}


recorrerView("./CodigoAExaminar/View/");
$cont9 = $cont9 + $errores9;

fclose($directorio);
fclose($files);
?>
<!DOCTYPE html>
<html>
<head>
<title>Proyecto Junio, Interfaces de Usuario</title>
</head>
<body>

<h1>Analizando Código de la carpeta CodigoAExaminar</h1>

<!-- RESUMEN NO DETALLADO -->
<p>RESUMEN:</p>

<p style="text-align:left;"> 1 Existen los directorios especificados en el fichero Directories.conf y no hay ningún fichero mas en el directorio principal que el index.php </p><p style="text-align:left;">&emsp;&emsp; <?php echo "$cont1 Elementos analizados / Número de errores : $errores1" ?> </p>

<p style="text-align:left;"> 2 Los ficheros de vista, controlador y modelo tienen el nombre indicado en la especificación en el fichero Files.conf</p><p style="text-align:left;">&emsp;&emsp; <?php echo "$cont2 Elementos analizados / Número de errores : $errores2" ?> </p>

<p style="text-align:left;"> 3 Los ficheros del directorio CodigoAExaminar tiene todos al principio del fichero comentada su función, autor, y fecha</p><p style="text-align:left;">&emsp;&emsp; <?php echo "$cont3 Elementos analizados / Número de errores : $errores3" ?> </p>

<p style="text-align:left;"> 4 Las funciones y métodos en el código del directorio CodigoAExaminar tienen comentarios con una descripción antes de su comienzo</p><p style="text-align:left;"> &emsp;&emsp;<?php echo "$cont4 Elementos analizados / Número de errores : $errores4" ?> </p>

<p style="text-align:left;"> 5 En el código están todas las variables definidas antes de su uso y tienen un comentario en la línea anterior o en la misma línea </p><p style="text-align:left;"> &emsp;&emsp;<?php echo "$cont5 Elementos analizados / Número de errores : $errores5" ?> </p>

<p style="text-align:left;"> 6 En el código están comentadas todas las estructuras de control en la línea anterior a su uso o en la línea misma </p><p style="text-align:left;"> &emsp;&emsp;<?php echo "$cont6 Elementos analizados / Número de errores : $errores6" ?> </p>

<p style="text-align:left;"> 7 Todos los ficheros dentro del directorio Model son definiciones de clases </p><p style="text-align:left;"> &emsp;&emsp;<?php echo "$cont7 Elementos analizados / Número de errores : $errores7" ?> </p>

<p style="text-align:left;"> 8 Todos los ficheros dentro del directorio Controller son scripts php </p><p style="text-align:left;"> &emsp;&emsp;<?php echo "$cont8 Elementos analizados / Número de errores : $errores8" ?> </p>

<p style="text-align:left;"> 9 Todos los ficheros dentro del directorio View son definiciones de clases </p><p style="text-align:left;"> &emsp;&emsp;<?php echo "$cont9 Elementos analizados / Número de errores : $errores9" ?> </p>


<!-- RESUMEN DETALLADO -->

<!-- Ejercicio 1 -->
<br/><p>DETALLE:</p>
<br/><table> <?php
foreach($arrayuno as $value1){
	if(preg_match("/(ERROR)/","$value1[1]")){
		?> <tr style="color:rgb(255,0,0);">	<td> <?php echo $value1[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value1[1];?></td></tr> <?php
	}else if(!preg_match("/(especificados)/","$value1[0]")){
		?> <tr>	<td> <?php echo $value1[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value1[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value1[0];?><?php echo $value1[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p> &emsp;&emsp;<?php echo "$cont1 Elementos analizados / Número de errores : $errores1" ?> </p>


<!-- Ejercicio 2 -->
<br/><table> <?php
foreach($arraydos as $value2){
	if(preg_match("/(ERROR)/","$value2[1]")){
		?> <tr style="color:rgb(255,0,0);">	<td> <?php echo $value2[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value2[1];?></td></tr> <?php
	}else if(!preg_match("/(ficheros)/","$value2[0]")){
		?> <tr>	<td> <?php echo $value2[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value2[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value2[0];?><?php echo $value2[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p>&emsp;&emsp; <?php echo "$cont2 Elementos analizados / Número de errores : $errores2" ?> </p>


<!-- Ejercicio 3 -->
<br/><table> <?php
foreach($arraytres as $value3){
	if(preg_match("/(ERROR)/","$value3[1]")){
		?> <tr style="color:rgb(255,0,0);">	<td> <?php echo $value3[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value3[1];?></td></tr> <?php
	}else if(!preg_match("/(ficheros)/","$value3[0]")){
		?> <tr>	<td> <?php echo $value3[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value3[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value3[0];?><?php echo $value3[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p>&emsp;&emsp; <?php echo "$cont3 Elementos analizados / Número de errores : $errores3" ?> </p>



<!-- Ejercicio 4 -->
<br/><table> <?php
foreach($arraycuatro as $value4){
	if(preg_match("/(ERROR)/","$value4[1]")){
		?> <tr >	<td> <?php echo $value4[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value4[1];?></td></tr> <?php
	}else if(!preg_match("/(comentarios)/","$value4[0]")){
		?> <tr>	<td> <?php echo $value4[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value4[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value4[0];?><?php echo $value4[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p>&emsp;&emsp; <?php echo "$cont4 Elementos analizados / Número de errores : $errores4" ?> </p>



<!-- Ejercicio 5 -->
<br/><table> <?php
foreach($arraycinco as $value5){
	if(preg_match("/(ERROR)/","$value5[1]")){
		?> <tr>	<td> <?php echo $value5[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value5[1];?></td></tr> <?php
	}else if(!preg_match("/(definidas)/","$value5[0]")){
		?> <tr>	<td> <?php echo $value5[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value5[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value5[0];?><?php echo $value5[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p>&emsp;&emsp; <?php echo "$cont5 Elementos analizados / Número de errores : $errores5" ?> </p>



<!-- Ejercicio 6 -->
<br/><table> <?php
foreach($arrayseis as $value6){
	if(preg_match("/(ERROR)/","$value6[1]")){
		?> <tr>	<td> <?php echo $value6[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value6[1];?></td></tr> <?php
	}else if(!preg_match("/(comentadas)/","$value6[0]")){
		?> <tr>	<td> <?php echo $value6[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value6[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value6[0];?><?php echo $value6[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p>&emsp;&emsp; <?php echo "$cont6 Elementos analizados / Número de errores : $errores6" ?> </p>



<!-- Ejercicio 7 -->
<br/><table> <?php
foreach($arraysiete as $value7){
	if(preg_match("/(ERROR)/","$value7[1]")){
		?> <tr style="color:rgb(255,0,0);">	<td> <?php echo $value7[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value7[1];?></td></tr> <?php
	}else if(!preg_match("/(ficheros)/","$value7[0]")){
		?> <tr>	<td> <?php echo $value7[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value7[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value7[0];?><?php echo $value7[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p>&emsp;&emsp; <?php echo "$cont7 Elementos analizados / Número de errores : $errores7" ?> </p>



<!-- Ejercicio 8 -->
<br/><table> <?php
foreach($arrayocho as $value8){
	if(preg_match("/(ERROR)/","$value8[1]")){
		?> <tr style="color:rgb(255,0,0);">	<td> <?php echo $value8[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value8[1];?></td></tr> <?php
	}else if(!preg_match("/(ficheros)/","$value8[0]")){
		?> <tr>	<td> <?php echo $value8[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value8[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value8[0];?><?php echo $value8[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p>&emsp;&emsp; <?php echo "$cont8 Elementos analizados / Número de errores : $errores8" ?> </p>




<!-- Ejercicio 9 -->
<br/><table> <?php
foreach($arraynueve as $value9){
	if(preg_match("/(ERROR)/","$value9[1]")){
		?> <tr style="color:rgb(255,0,0);">	<td> <?php echo $value9[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value9[1];?></td></tr> <?php
	}else if(!preg_match("/(ficheros)/","$value9[0]")){
		?> <tr>	<td> <?php echo $value9[0];?> </td><td>&emsp;&emsp;</td>
													<td><?php echo $value9[1];?></td></tr> <?php
	}else{
		?> <p><?php echo $value9[0];?><?php echo $value9[1];?> </p><?php
	}
}?></table>
<p>&emsp;&emsp;RESUMEN: </p><p>&emsp;&emsp; <?php echo "$cont9 Elementos analizados / Número de errores : $errores9" ?> </p>


</body>
</html>
