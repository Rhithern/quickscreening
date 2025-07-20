<?php
$input_password = 'Londoner10#';  // Change this to the password you want to test
$stored_hash = '$2y$10$e0MYzXyjpJS2Jr0GaOq2nepQpsKzkPRG9aaVXp1H5T9.ZjFCXMZ3G';  // Your stored hash

if (password_verify($input_password, $stored_hash)) {
    echo "Password verified!";
} else {
    echo "Invalid password!";
}
?>
