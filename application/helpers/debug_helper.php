<?php

function mydump(&$variable)
{
  // read backtrace
  $bt   = debug_backtrace();
  // read file
  $file = file($bt[0]['file']);
  // select exact debug($varname) line
  $src  = $file[$bt[0]['line']-1];
  // search pattern
  $pat = '#(.*)'.__FUNCTION__.' *?\( *?(.*) *?\)(.*)#i';
  // extract $varname from match no 2
  $var  = preg_replace($pat, '$2', $src);
	
  // print to browser
  echo trim($var);
	
	if (!is_cli())
	{
		echo '<pre>';
	}
	else
	{
		echo "\n";
	}
	
	if(is_cli())
	{
			print_r($variable);
	}
	else
	{
		var_dump($variable);
	}
	
	if (!is_cli())
	{
		echo '</pre>';
	}
	else
	{
		echo "\n\n";
	}

}

?>