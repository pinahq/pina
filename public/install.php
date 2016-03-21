<?php

namespace Pina;

set_time_limit(3600);

include "../bootstrap/autoload.php";

$db_error = false;
$site_error = false;
if (file_exists('../config/config.server.php') && is_readable('../config/config.server.php'))
{
    $siteConfig = include "../config/site.php";
	$dbConfig = include "../config/db.php";

	$rc = mysql_pconnect($dbConfig['host'].':'.$dbConfig['port'], $dbConfig['user'], $dbConfig['pass']);//, true);
	if (empty($rc) || mysql_errno($rc)) $db_error = true;

	mysql_select_db($dbConfig['base'], $rc);
	if (empty($rc) || mysql_errno($rc)) $db_error = true;

	if (!$db_error)
	{
		$db_filled_error = false;
		$r = mysql_query("SELECT * FROM cody_string", $rc);
		if (empty($r) || mysql_errno($rc)) $db_filled_error = true;
	}

	if (empty($siteConfig['default']['domain'])) { 
        $site_error = true;
    }
	if (!empty($siteConfig['default']['domain']) && $siteConfig['default']['domain'] != $_SERVER["HTTP_HOST"]) {
        $site_error = true;
    }
}

if (!defined('SITE_CHARSET')) define('SITE_CHARSET', 'utf-8');

if (!function_exists("_lng"))
{
	function _lng($key)
	{
		static $data = array();
		if (empty($data))
		{
			
			$file = fopen(__DIR__."/../app/default/Modules/Language/data/strings.csv", "rt");
			while ($line = fgetcsv($file, 0, ';'))
			{
				if ($line[0] == 'en')
				{
					$data[$line[1]] = $line[2];
				}
			}
			fclose($file);
			
		}
		if (!empty($data[$key])) return $data[$key];
		return $key;
	}
}

header('Content-Type: text/html; charset='.SITE_CHARSET);

function printHeader()
{
echo '<html>
<head>
<style>
	body {text-align:center;font:1em Arial,sans-serif; line-height: 18px;}
	table th {padding-top: 10px;}
</style>
</head>
<body>
	<img src="/static/default/images/logo.png" />';
}

function printFooter()
{
	echo '<p>© 2010-'.@date('Y').' «Dobrosite Ltd». All rights reserved.</p>
</body>
</html>';
}

function printSubTitle($key)
{
	echo '<strong>'._lng($key).'</strong><br />';
}

function printTitle($key)
{
	echo '<h3>'._lng($key).'</h3>';
}

function printEnvironmentTitle($titleKey)
{
	echo '<tr><th colspan="3">'._lng($titleKey).'</th></tr>';
}

function printEnvironmentTest($title, $test)
{
	$errors = 0;
	if (!empty($test["warning_type"]))
	{
		echo '<tr><td>';
		echo '<strong style="">'.$title.' '.$test["expected"].'</strong>';
		echo '</td><td>';
		echo $test["value"];
		echo '</td><td>';
		#echo ' (';;
		#echo $test['expected'];
		#echo ')';
		if ($test['warning_type'] == 'ok')
		{
			echo '<strong style="color:green;">'.$test['warning_type'].'</strong>';
		}
		else
		{
			echo '<strong style="color:red">'.$test['warning_type'].'</strong>';
		}
		echo '</td></tr>';

		if ($test['warning_type'] == "error")
		{
			$errors += 1;
		}
	}
	return $errors;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" && empty($_GET["mode"]))
{
	printHeader();
	printTitle('environment_testing');

	$errors = 0;

	echo '<table style="margin:auto;">';
	printEnvironmentTitle('php_version');
	$data = Environments::checkPhpVersion();
	$errors += printEnvironmentTest(_lng('php_version'), $data);

	printEnvironmentTitle('required_php_extensions');
	$data = Environments::checkPhpExtensions();
	foreach ($data as $key => $item)
	{
		$item["expected"] = '';
		$item['value'] = $item["loaded"]?"enabled":"disabled";
		$item['warning_type'] = $item["loaded"]?"ok":"warning";
		$errors += printEnvironmentTest($item['title'], $item);
	}

	printEnvironmentTitle('php_settings_values');
	$data = Environments::checkPhpDirectives();
	foreach ($data as $key => $item)
	{
		$errors += printEnvironmentTest($key, $item);
	}

	printEnvironmentTitle('permissions');

	$extra = array();
	if ($db_error)
	{
		$extra = array("config/config.server.php" => array("expected" => "writable"));
	}
	$permissions = Environments::checkPermissions($extra);
	foreach ($permissions as $file => $item)
	{
		$errors += printEnvironmentTest("./".$file, $item);
	}

	echo '</table>';


	if ($errors)
	{
		echo '<p>Please fix errors and <a href="#" onclick="location.reload(); return false;">Reload page</a></p>';
	}
	elseif ($db_error || $site_error)
	{
		echo '<p><a href="install.php?mode=config">Configure</a></p>';
	}
	else
	{
		echo '<p><a href="install.php?mode=install">Install</a></p>';
	}

	printFooter();
}
elseif (!empty($_GET["mode"]) && $_GET["mode"] == "config" && ($db_error || $site_error))
{
	printHeader();

	echo '<form action="install.php" method="POST">';

	echo '<table style="margin:auto;">';

	if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
	if (!defined('DB_PORT')) define('DB_PORT', '3306');
	if (!defined('DB_USER')) define('DB_USER', 'root');
	if (!defined('DB_PASS')) define('DB_PASS', '');
	if (!defined('DB_BASE')) define('DB_BASE', '');

	if (!defined('SITE_HOST')) define('SITE_HOST', $_SERVER["HTTP_HOST"]);
	if (!defined('SITE_PATH'))
	{
		$info = pathinfo($_SERVER["REQUEST_URI"]);
		define('SITE_PATH', trim($info["dirname"], '\\\/').'/');
	}

	if ($db_error)
	{
		printEnvironmentTitle('database connection');
		echo '<tr><td>Server</td><td></td><td><input type="text" name="db_host" value="'.DB_HOST.'" /></td></tr>';
		echo '<tr><td>Port</td><td></td><td><input type="text" name="db_port" value="'.DB_PORT.'" /></td></tr>';
		echo '<tr><td>User</td><td></td><td><input type="text" name="db_user" value="'.DB_USER.'" /></td></tr>';
		echo '<tr><td>Password</td><td></td><td><input type="text" name="db_pass" value="'.DB_PASS.'" /></td></tr>';
		echo '<tr><td>Base</td><td></td><td><input type="text" name="db_base" value="'.DB_BASE.'" /></td></tr>';
	}

	if ($site_error)
	{
		printEnvironmentTitle('site location');
		echo '<tr><td>Host</td><td></td><td><input type="text" name="site_host" value="'.SITE_HOST.'" /></td></tr>';
		echo '<tr><td>Path</td><td></td><td><input type="text" name="site_path" value="'.SITE_PATH.'" /></td></tr>';
	}

	echo '</table>';

	echo '<input type="hidden" name="mode" value="save" />';
	echo '<p><input type="submit" value="Save configuration and install" /></p>';

	echo '</form>';

	printFooter();
}
elseif (!empty($_GET["mode"]) && $_GET["mode"] == "config" && !$db_error && !$site_error)
{
	header('Location: install.php?mode=install');
	exit;
}
elseif (!empty($_POST["mode"]) && $_POST["mode"] == "save" && ($db_error || $site_error))
{
	function replaceConfig($file, $def, $val)
	{
		$val = str_replace("'", "\'", $val);
		$file = preg_replace("/(define\(\'".$def."\'\,\s+\')[^\']*(\'\);)/si", '${1}'.$val.'$2', $file);
		return $file;
	}

	$file = file_get_contents(PATH.'config/config.server.php');

	if ($db_error)
	{
		if (isset($_POST["db_host"]))
		{
			$file = replaceConfig($file, 'DB_HOST', $_POST['db_host']);
		}
		if (isset($_POST["db_port"]))
		{
			$file = replaceConfig($file, 'DB_POST', $_POST['db_port']);
		}
		if (isset($_POST["db_user"]))
		{
			$file = replaceConfig($file, 'DB_USER', $_POST['db_user']);
		}
		if (isset($_POST["db_pass"]))
		{
			$file = replaceConfig($file, 'DB_PASS', $_POST['db_pass']);
		}
		if (isset($_POST["db_base"]))
		{
			$file = replaceConfig($file, 'DB_BASE', $_POST['db_base']);
		}
	}

	if ($site_error)
	{
		if (isset($_POST["site_host"]))
		{
			$file = replaceConfig($file, 'SITE_HOST', $_POST['site_host']);
		}
		if (isset($_POST["site_path"]))
		{
			$file = replaceConfig($file, 'SITE_PATH', $_POST['site_path']);
		}
	}

	//file_put_contents(PATH.'config/config.server.php', $file);

	header('Location: install.php?mode=install');
	exit;
}
elseif (!empty($_POST["mode"]) && $_POST["mode"] == "save" && !$db_error && !$site_error)
{
	header('Location: install.php?mode=install');
	exit;
}
elseif (!empty($_GET["mode"]) && $_GET["mode"] == "install" && ($db_error || $site_error))
{
	header('Location: install.php?mode=config');
	exit;
}
elseif (!empty($_GET["mode"]) && $_GET["mode"] == "install" && !$db_error && !$site_error)
{
	printHeader();
	echo '<p>Installation is in progress. Please wait...</p>';
	printFooter();
	str_repeat(' ', 4096);
	flush();

	$dbUpdateDomain = new Modules\Core\DBUpdateDomain();
	$dbUpdateDomain->update();

	$dir = __DIR__."/../app/default/Modules/";
	$modules = array();

	if (is_dir($dir)) {
		if ($dh = opendir($dir))
		{
			while (($file = readdir($dh)) !== false)
			{
				if (strpos($file, '.') === false && is_dir($dir.$file))
				{
					$modules [] = $file;
				}
			}
			closedir($dh);
		}
	}

	foreach ($modules as $m)
	{
		if (file_exists($dir.$m."/install.php"))
		{
			include $dir.$m."/install.php";
			str_repeat(' ', 4096);
			flush();
		}
	}
	
	foreach ($modules as $m)
	{
		if (file_exists($dir.$m."/demo.php"))
		{
			include_once $dir.$m."/demo.php";
			str_repeat(' ', 4096);
			flush();
		}
	}

	$import = new Modules\Language\StringDataLoader();
	$import->setInputCoding('UTF-8');
	$import->setOutputCoding('UTF-8');
	$import->import();

    echo "<META http-equiv=\"Refresh\" content=\"0;URL=install.php?mode=complete\">";
}
elseif (!empty($_GET["mode"]) && $_GET["mode"] == "complete")
{
	printHeader();
	echo '<p>
	Installation has been completed; Please remove this file.
	<a href="pina.php">Go to site</a>
	</p>';
	printFooter();
}
