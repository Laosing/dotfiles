<?php
// Force errors to show
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// global $argv;

// Array of all folders at file directory, filter for only dev sites
$sites = glob('*', GLOB_ONLYDIR);
$dev_sites = array_filter($sites, function($value) {
  $pathinfo = pathinfo($value);
  if(isset($pathinfo['extension']) && $pathinfo['extension'] === 'dev')
    return $pathinfo;
});
// Default file locations and settings
$conf_file = '/etc/apache2/sites-available/000-default.conf';
$hosts_file = '/etc/hosts';
$host = "127.0.0.1";
// $conf_file = '000-default.conf';
// $hosts_file = 'hosts';

function setFile($file) {
  if(!file_exists($file)) touch($file);
}
setFile($conf_file);
setFile($hosts_file);

$php_devhost = "#--- Generated from php devhost.php ---#";

// 000-default file reset
$conf_array = file($conf_file, FILE_IGNORE_NEW_LINES);
$php_devhost_key = array_search($php_devhost, $conf_array);
if(!$php_devhost_key) {
  $virtual_host_key = array_search('</VirtualHost>', $conf_array);
  $directory = "\n
<Directory \"/var/www/html/\">
  AllowOverride All
</Directory>\n
$php_devhost";
  array_splice($conf_array, $virtual_host_key+1);
  file_put_contents($conf_file, implode(PHP_EOL, $conf_array));
  file_put_contents($conf_file, $directory, FILE_APPEND);
} else {
  array_splice($conf_array, $php_devhost_key+1);
  file_put_contents($conf_file, implode(PHP_EOL, $conf_array));
}

// Hosts file reset
$hosts_array = file($hosts_file, FILE_IGNORE_NEW_LINES);
$php_host_devhost_key = array_search($php_devhost, $hosts_array);
$hosts_key = array_search('ff02::2 ip6-allrouters', $hosts_array);
if(!$php_host_devhost_key) {
  array_splice($hosts_array, $hosts_key+1);
  file_put_contents($hosts_file, implode(PHP_EOL, $hosts_array));
  file_put_contents($hosts_file, PHP_EOL.PHP_EOL.$php_devhost.PHP_EOL, FILE_APPEND);
} else {
  array_splice($hosts_array, $php_host_devhost_key+2);
  file_put_contents($hosts_file, implode(PHP_EOL, $hosts_array));
}

// Setup strings for files
$listen = 1010;
$conf_str = "\n";
$hosts_str = "\n";
foreach($dev_sites as $site) {
  $listen++;
  $conf_str .= "
listen *:$listen
<VirtualHost *:80 *:$listen>
ServerName $site
DocumentRoot /var/www/html/$site
</VirtualHost>" . PHP_EOL;

  $hosts_str .= $host . ' ' . $site . PHP_EOL;
}
// For visual purposes to see what is being appended
echo $conf_str;
echo $hosts_str;

// Append strings to files
file_put_contents($conf_file, $conf_str, FILE_APPEND);
file_put_contents($hosts_file, $hosts_str, FILE_APPEND);
?>
