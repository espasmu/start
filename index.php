<?php

session_start();
$sources = array_filter($_ENV, function($key) {return strpos($key, 'SOURCE_CODE') === 0;}, ARRAY_FILTER_USE_KEY);
ksort($sources);
foreach($sources as $source) {
	$httpcode = shell_exec('curl -I --output /dev/null -w "%{http_code}" '.escapeshellarg($source));
	$ext = pathinfo($source, PATHINFO_EXTENSION);
	if($httpcode == 200) {
		switch($ext) {
			case 'gz': shell_exec('curl -o master.tar.gz '.escapeshellarg($source).'; tar xfz master.tar.gz --overwrite; rm -f master.tar.gz;'); break;
			case 'tar': shell_exec('curl -o master.tar.gz '.escapeshellarg($source).'; tar xf master.tar --overwrite; rm -f master.tar;'); break;
			case 'zip': shell_exec('curl -o master.zip '.escapeshellarg($source).'; unzip -o master.zip; rm -f master.zip;'); break;
		}
		if(empty(shell_exec('cat index.php | grep _ENV'))) { //index.php changed
			header('Location: '.$_SERVER['REQUEST_URI']);
			exit;
			break;
		}
	}
}

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 300');

echo '<h1>Service Temporarily Unavailable</h1>';
echo '<p>Please reload this page after a while or contact the site administrator.</p>';
