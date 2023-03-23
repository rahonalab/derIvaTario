<?php
/**PHP-CoLFIS 0.3 connect.php.
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
**/
$connection = new mysqli("mariadb","morphologist","derivatario","derivatario");

// Check connection
if ($connection -> connect_errno) {
  echo "Failed to connect to MySQL: " . $connection -> connect_error;
  exit();
}

global $connection;

?>
