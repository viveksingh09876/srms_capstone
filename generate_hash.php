   <?php
   $password = 'admin123';
   $hash = password_hash($password, PASSWORD_DEFAULT);
   echo "Hash for '$password': " . $hash . "\n";
   ?>
   