

<?php
$hash = '2y$10$3g6lwjBRWi.YvvCrtWzgAuQcOMfxWlojdhi54r9QUsAwvS4Bd99P2'; // paste the hash you just generated
var_dump(password_verify('Admin@123', $hash)); // should output: bool(true)






