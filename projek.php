<?php

session_start();
$sources = array_filter($_ENV, function($key) {return strpos($key, 'SOURCE_CODE') === 0;}, ARRAY_FILTER_USE_KEY);
ksort($sources);
if(!is_file('index.php')) {
	foreach($sources as $source) {
		$content_type = shell_exec('curl -I -k --output /dev/null -w "%{content_type}" '.escapeshellarg($source));
		if(preg_match('#application/.*(zip|tar)#i', $content_type)) {
			switch(pathinfo($source, PATHINFO_EXTENSION)) {
				case 'gz': shell_exec('curl -k -o source.tar.gz '.escapeshellarg($source).'; tar xfz source.tar.gz --overwrite; rm -f source.tar.gz README.md;'); break;
				case 'tar': shell_exec('curl -k -o source.tar '.escapeshellarg($source).'; tar xf source.tar --overwrite; rm -f source.tar README.md;'); break;
				case 'zip': shell_exec('curl -k -o source.zip '.escapeshellarg($source).'; unzip -o source.zip; rm -f source.zip README.md;'); break;
			}
			if(is_file('index.php')) {
				header('Location: '.$_SERVER['REQUEST_URI']);
				exit;
			}
		}
	}
}

header('HTTP/1.1 503 Service Temporarily Unavailable');
header('Status: 503 Service Temporarily Unavailable');
header('Retry-After: 300');

echo '<h1>Service Temporarily Unavailable</h1>'.
'<p>Please reload this page after a while or contact the site administrator.</p>';
