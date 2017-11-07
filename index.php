<?php

session_start();
$sources = array_filter($_ENV, function($key) {return strpos($key, 'SOURCE_CODE') === 0;}, ARRAY_FILTER_USE_KEY);
ksort($sources);
$md5 = md5_file(__FILE__);
foreach($sources as $source) {
	$httpcode = shell_exec('curl -I --output /dev/null -w "%{http_code}" '.escapeshellarg($source));
	$ext = pathinfo($source, PATHINFO_EXTENSION);
	if($httpcode == 200) {
		if($ext == 'zip') {
			shell_exec('wget '.escapeshellarg($source).' -O master.zip; unzip -o master.zip; rm -f master.zip;');
		} elseif($ext == 'gz') {
			shell_exec('wget '.escapeshellarg($source).' -O master.tar.gz; tar xfz master.tar.gz --overwrite; rm -f master.tar.gz;');
		} elseif($ext == 'tar') {
			shell_exec('wget '.escapeshellarg($source).' -O master.tar.gz; tar xf master.tar --overwrite; rm -f master.tar;');
		}
		if($md5 !== md5_file(__FILE__)) { //index.php changed
			header('Location: '.$_SERVER['REQUEST_URI']);
			exit;
		}
	}
}

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 300');

echo '<h1>Service Temporarily Unavailable</h1>';
echo '<p>Please reload this page after a while or contact the site administrator.</p>';
