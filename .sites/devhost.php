<?php
// Force errors to show
ini_set('display_errors', '1');

// Array of all folders at file directory
$sites = glob('*', GLOB_ONLYDIR);
// Default file locations and settings
$conf_file = '/etc/apache2/sites-available/000-default.conf';
$hosts_file = '/etc/hosts';
$host = "127.0.0.1";

// Enable this for testing purposes
$testing = FALSE;
if($testing) {
  $conf_file = '000-default.conf';
  $hosts_file = 'hosts';
}

// Create files if they don't exist
if(!file_exists($conf_file)) {
  touch($conf_file);
}
if(!file_exists($hosts_file)) {
  touch($hosts_file);
}

// Open the file with each line as an Array
$conf_array = file($conf_file, FILE_SKIP_EMPTY_LINES | FILE_IGNORE_NEW_LINES);
// Enable to use an alternative port, use as IP:PORT
$listener = TRUE;
$listener_default = 1010;

// Get the highest listen port number to use as the starting listener
$listen_array = array();
foreach($conf_array as $num => $line) {
  if(strpos($line, 'listen *:') !== false) {
    $a = explode(':', $line);
    $listen_array[] = $a[1];
  }
}
$listen = !empty($listen_array) ? max($listen_array) : $listener_default;

// String for 000-default.conf
$conf_str = "";
// String for hosts
$hosts_str = "";
foreach($sites as $site) {
  // Use #site_name to check if the site is already in $conf_file
  $site_ref = "#$site";
  if(!in_array($site_ref, $conf_array)) {
    $listen++;
    // Add site to conf_str
    $conf_str .= "\r\n$site_ref\r\n";
    if($listener) {
      $listener = "*:$listen";
      $conf_str .= "listen $listener";
    }
    $conf_str .= "
<VirtualHost *:80 $listener>
  ServerName $site
  DocumentRoot /var/www/html/$site
</VirtualHost>";

    // Add site to host_str
    $hosts_str .= "\r\n$site_ref\r\n";
    $hosts_str .= $host . ' ' . $site;
  }
}

// For visual purposes to see what is being appended
echo "<pre>";
echo "$conf_str";
echo "<pre>";
echo "$hosts_str";

// Append strings to files
file_put_contents($conf_file, $conf_str, FILE_APPEND);
file_put_contents($hosts_file, $hosts_str, FILE_APPEND);
?>