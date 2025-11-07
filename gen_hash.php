<?php
// Visit: http://localhost/hms/public/gen_hash.php
$pwd = 'Admin@123';
$hash = password_hash($pwd, PASSWORD_DEFAULT);
echo "Password: $pwd<br>Hash:<br><textarea cols='100' rows='3'>".$hash."</textarea>";
