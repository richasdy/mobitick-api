<?php
	//note to myself :
	// $_SERVER["REQUEST_METHOD"] used to determined the method
	// $_SERVER["REQUEST_URI"] used to determined the URI
	// well, it's obvious, but i need to remember it
	include "jsonHelper.php";
	foreach (glob("BTPRestApi/*.php") as $filename)
	{
		require_once $filename;
	}
	
	$mappingArray = array();
	$classList = array(	
						'user'=>array('get'=>array(),'post'=>array(2=>'postLogin')),
						
						'absen'=>array('get'=>array(),'post'=>array(2=>'postMasuk',3=>'postPulang')),
						
						'util'=>array('get'=>array(0=>'getStatus'),'post'=>array()),
						);
	foreach($classList as $class=>$valueList)
	{
		$mappingArray[$class] = array();
		$mappingArray[$class]['get'] = $valueList['get'];
		$mappingArray[$class]['post'] = $valueList['post'];
	}
	
	function mappingQuery($classList, $action, $request, $postData = NULL)
	{
		$namespaceName = 'BTPRestAPI\\';
		$splittedRequest = explode("/",$request);
		$tableName = $splittedRequest[0];
		$remap = array();
		$numArg;
		$flag = TRUE;
		$error = array('error' => 'invalid entry');
		if(!isset($postData) || $postData == NULL)
		{
			$numArg = count($splittedRequest);
			for($i = 0;$i<($numArg-1) / 2;$i++)
			{
				if(isset($splittedRequest[$i*2 + 1]) && isset($splittedRequest[($i+1) * 2]))
					$remap[$splittedRequest[$i*2 + 1]] = $splittedRequest[($i+1) * 2];
				else
				{
					$flag = FALSE;
					echo json_encode($error);
					die();
				}
			}
		}
		else
		{
			$numArg = count($postData);
			$remap = $postData;
		}
		$fullClassName = $namespaceName.ucfirst($tableName);	
		$clazz = new $fullClassName;
		$tableArray;
		$actionTable;
		$remapAction;
		$remappedSuccess = FALSE;
		if(isset($classList[$tableName]))
		{
			$tableArray = $classList[$tableName];
			if(isset($tableArray[strtolower($action)]))
			{
				$actionTable = $tableArray[strtolower($action)];
				if(isset($actionTable[count($remap)]))
				{
					$remapAction = $actionTable[count($remap)];
					$remappedSuccess = TRUE;
				}
			}
		}
		
		if(!$remappedSuccess)
		{
			echo indent(json_encode($error));
		}
		else
		{
			$getTableActionRemap = $classList[$tableName][strtolower($action)][count($remap)];
			$result = call_user_func_array(array($clazz, $getTableActionRemap), array($remap));
			echo indent(json_encode($result));
		}
	}
	
	function indent($json) {

		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = '  ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ($i=0; $i<=$strLen; $i++) {

			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;

			// If this character is the end of an element,
			// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}

			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element,
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}

				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}

			$prevChar = $char;
		}

		return $result;
	}
	
	//var_dump($_POST);
	$request = $_SERVER["REQUEST_METHOD"];
	$result = "";
	if(strtolower($request) == "get")
		$result = mappingQuery($classList, $request,$_GET['_url']);
	else if(strtolower($request) == "post") 
		$result = mappingQuery($classList, $request,$_GET['_url'],$_POST);
	
	echo $result;
	
	
?>