<?php
function open_cashdrawer($address="10.0.0.206", $service_port=30998)
{
  $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
  if ($socket === false) {
    echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
  } else {
    if (php_sapi_name() == "cli") {
      echo "$address OK.\n";
    }
  }
  $result = socket_connect($socket, $address, $service_port);
  if ($result === false) {
    if (php_sapi_name() == "cli") {
      echo "socket_connect() failed.\nReason: ($result) " . socket_strerror(socket_last_error($socket)) . "\n";
    }
  } else {
    if (php_sapi_name() == "cli") {
      echo "OK.\n";
    }
  }
  $in = "opendrawer\n";
  socket_write($socket, $in, strlen($in));
  socket_close($socket);
}
# main
$clients_to_cashdrawers = [
  '192.168.112.1' => '127.0.0.1',
  '192.168.112.2' => '10.0.0.207',
];

if (php_sapi_name() == "cli") {
  $client_ip = $argv[1];
  $filename = sys_get_temp_dir() . '/myout.log';
  #log_message('debug', var_export($client_ip, TRUE));
  #write_file($filename, $client_ip);
  if (array_key_exists($client_ip, $clients_to_cashdrawers)) {
    open_cashdrawer($clients_to_cashdrawers[$client_ip]);
  }
}
?>
