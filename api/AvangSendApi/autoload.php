<?php

foreach(scandir(__DIR__) as $file){
	$f = __DIR__. DIRECTORY_SEPARATOR .$file;
	if(is_file($f) && substr($f,-4) == '.php' ){
		require_once($f);
		
	}
	
	
}