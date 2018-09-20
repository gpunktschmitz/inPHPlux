<?php

function filterTag($s) {
    $s = str_replace(' ', '\ ', $s);
    $s = str_replace('=', '\ ', $s);
    $s = str_replace(',', '\ ', $s);
    return trim($s);
}

function influxDbWrite($post,$database) {
    $url='http://localhost:8086/write?precision=s&db=' . urlencode(trim($database));
    $curlHandle = curl_init();
    curl_setopt($curlHandle,CURLOPT_URL,$url);
    curl_setopt($curlHandle,CURLOPT_POST,true);
    curl_setopt($curlHandle,CURLOPT_POSTFIELDS,$post);
    curl_setopt($curlHandle,CURLOPT_RETURNTRANSFER, 1);
    curl_exec($curlHandle);
    curl_close($curlHandle);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="/css/jquery.datetimepicker.css"/>
	<title>inPHPlux</title>
</head>
<body>
<?php
$formFilePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'forms'.DIRECTORY_SEPARATOR;

$formFilesCount = iterator_count(new FilesystemIterator($formFilePath, FilesystemIterator::SKIP_DOTS));
if($formFilesCount>0) {
    ?>
	<h3>available forms:</h3>
<?php
    $formsArray=Array();
    $dir = new DirectoryIterator($formFilePath);
    foreach ($dir as $fileinfo) {
        if(!$fileinfo->isDot()) {
            $formsArray[] = $fileinfo->getFilename();
        }
    }
    asort($formsArray);
    
    foreach($formsArray as $filename) {
		$link = substr($filename,0,-4);
?>
		<a href="index.php?form=<?php echo $link; ?>"><?php echo $link; ?></a><br/><br/>
<?php
	}
}
?>
<hr/><br/>
<?php
$database=false;
if(isset($_GET['form'])) {
	$formSelected = preg_replace("/[^a-zA-Z0-9_-]/", '', $_GET['form']);
    $formFileName = $formFilePath.$formSelected.'.ini';
	if(file_exists($formFileName)) {
		$formSetup = parse_ini_file($formFileName,true);
		
		if(isset($_POST) && count($_POST)>0) {
		    $postVar = Array();		    
		    $formSetup['datetime'] = '';
		    foreach($formSetup as $key=>$value) {
		        switch($key) {
		            case 'datetime':
		                $postVar[$key] = strtotime($_POST['datetime']);
		                break;
		            case 'tagsets':
		                $tmpArray = Array();
		                foreach($_POST['tagsets'] as $key=>$value) {
		                    $value=filterTag($value);
		                    if(strlen($value)>0) {
    		                    $tmpArray[$key]=filterTag($value);
		                    }
		                }
		                $postVar['tagsets'] = $tmpArray;
		                break;
		            case 'valuesets':
		                $tmpArray = Array();
		                foreach($_POST['valuesets'] as $key=>$value) {
		                    $tmpArray[$key]=floatval($value);
		                }
		                $postVar['valuesets'] = $tmpArray;
		                break;
		            case 'database':
		                $database=preg_replace("/[^a-zA-Z0-9]/", '', $_POST['database']);
		                break;
		            default:
		                exit('unknown data received');
		                break;
		        }
		    }
		    
		    $tagString = '';
		    if(array_key_exists('tagsets', $postVar)) {
		        foreach($postVar['tagsets'] as $key=>$value) {
		            $tagString .= ',' . $key . '=' . $value;
		        }
		    }
		    
		    $valueString = '';
		    foreach($postVar['valuesets'] as $key=>$value) {
		        $valueString .= $key . '=' . $value . ',';
		    }
		    $valueString = substr($valueString,0,-1);
		    
		    $postData = $formSelected . $tagString . ' ' . $valueString . ' ' . $postVar['datetime'];
		    
		    influxDbWrite($postData,$database);
		}
?>
	<form method="post" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>">
		<h3><label for="measurement">measurement</label>&nbsp;<input type="text" name="measurement" id="measurement" value="<?php echo $formSelected; ?>"/><br/><br/></h3>
<?php
foreach($formSetup['tagsets'] as $key=>$defaultValue) {
?>
		<label for="<?php echo $key; ?>"><?php echo $key; ?></label>&nbsp;<input type="text" name="tagsets[<?php echo $key; ?>]" value="<?php echo $defaultValue; ?>"/><br/><br/>
<?php  
}
foreach($formSetup['valuesets'] as $key=>$defaultValue) {
?>
		<label for="<?php echo $key; ?>"><?php echo $key; ?></label>&nbsp;<input type="text" name="valuesets[<?php echo $key; ?>]" id="<?php echo $key ?>" value="<?php echo $defaultValue; ?>"/></label><br/><br/>
<?php  
}
?>
		<label for="datetime">datetime</label>&nbsp;<input type="text" name="datetime" id="datetime" value="<?php echo date('Y/m/d H:m'); ?>"/><br/><br/>
		<label for="database">database</label>&nbsp;<input type="text" name="database" id="database" value="<?php echo $formSetup['database']; ?>"/><br/><br/>
		<button>submit</button>
	</form>
	<script src="/js/jquery.js"></script>
	<script src="/js/jquery.datetimepicker.full.min.js"></script>
	<script>
	$.datetimepicker.setLocale('en');
	
	$('#datetime').datetimepicker({
		dayOfWeekStart : 1,
		lang:'en',
	});
	</script>
<?php		
	} else {
?>
	<h2 class='error'>Form name "<?php echo $_GET['form']?>" not found.</h2>
<?php
	}
}
?>
</body>
</html>
