<?php
$pubPath = realpath(dirname(__FILE__));
$appPath = realpath($pubPath . '/../app');
$n2nPath = realpath($pubPath . '/../lib');
$varPath = realpath($pubPath . '/../var');

set_include_path(implode(PATH_SEPARATOR, 
	array($appPath, $n2nPath, get_include_path())));

if (isset($_SERVER['N2N_STAGE'])) {
	define('N2N_STAGE', $_SERVER['N2N_STAGE']);
}

require_once 'n2n/core/TypeLoader.php';

n2n\core\TypeLoader::register();

n2n\core\N2N::initialize($pubPath, $varPath, new n2n\core\FileN2nCache());
n2n\core\N2N::autoInvokeBatchJobs();
n2n\core\N2N::autoInvokeControllers();
n2n\core\N2N::finalize();

function test($value) {
	if (n2n\N2N::isLiveStageOn()) return;
	echo "\r\n<pre>\r\n";
	if (is_object($value)) {
		echo 'object(' . get_class($value) . ')';
	} else {
		var_dump($value);
	}
	if (is_scalar($value)) echo "\r\n";
	echo "</pre>\r\n";
}

// n2n\N2N::getPdoPool()->getPdo()->getLogger()->dump();