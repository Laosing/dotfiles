<?php
// Force errors to show
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

/**
 * Global arguments passed into the script
 *
 * $argv[0] = Name of script
 * $argv[#] = # Argument
 *
 * @var Array
 **/
global $argv;

/**
 * Array of all dev folders
 *
 * @return Array
 *
 **/
function getSites(String $location = '*')
{
  $sites = glob($location, GLOB_ONLYDIR);
  $dev_sites = array_filter($sites, function($value) {
    $pathinfo = pathinfo($value);
    if(isset($pathinfo['extension']) && $pathinfo['extension'] === 'dev')
      return $pathinfo;
  });

  return $dev_sites;
}

/**
 * Creates the file if it doesn't exist
 *
 * @return void
 *
 **/
function createFile(String $file)
{
  if(!file_exists($file)) touch($file);
}

/**
 * Array of the file
 *
 * @return Array
 *
 **/
function buildFile(String $file)
{
  $generated_tag = "#--- Generated from php devhost.php ---#";
  $directory = "<Directory \"/var/www/html/\">
  AllowOverride All
</Directory>";

  $file_array = file($file, FILE_IGNORE_NEW_LINES);
  $php_devhost_key = array_search($generated_tag, $file_array);
  if(!$php_devhost_key) {
    $file_array[] = $generated_tag;
  } else {
    array_splice($file_array, $php_devhost_key+1);
  }

  if(basename($file) === "000-default.conf") {
    $file_array = array_merge($file_array, preg_split('/$\R?^/m', $directory));
  }

  return $file_array;
}

/**
 * Array of created directories
 *
 * @return Array
 *
 **/
function createDirectories(String $file, Array $dev_sites, String $host)
{
  $host = empty($host) ? "127.0.0.1" : $host;
  $listen = 1010;
  $sites_array = [];
  $output = "";
  foreach($dev_sites as $site) {
    if(basename($file) === "000-default.conf") {
      $listen++;
      $output .= "listen *:$listen
<VirtualHost *:80 *:$listen>
  ServerName $site
  DocumentRoot /var/www/html/$site
</VirtualHost>".PHP_EOL;
    } else {
      $sites_array[] = $host . ' ' . $site;
    }
  }

  if(basename($file) === "000-default.conf") {
    $str_array = preg_split('/$\R?^/m', $output);
    $sites_array = array_merge($sites_array, $str_array);
  }

  return $sites_array;
}

/**
 * Initialize development host
 *
 * @return void
 *
 **/
function initialize(String $file, $argv)
{
  $argv = !is_null($argv) ? '' : $argv[1];

  $sites = getSites();
  createFile($file);
  $directory_array = createDirectories($file, $sites, $argv);
  $file_array = buildFile($file);
  $file_array = array_merge($file_array, $directory_array);

  print_r($file_array);

  file_put_contents($file, implode(PHP_EOL, $file_array));
};

$files = [
// $conf_file = '/etc/apache2/sites-available/000-default.conf';
// $hosts_file = '/etc/hosts';
  '000-default.conf',
  'hosts'
];
foreach ($files as $key => $file) {
  initialize($file, $argv);
}

// Restart apache
echo PHP_EOL."Restarting apache2".PHP_EOL;
shell_exec('sudo service apache2 restart');