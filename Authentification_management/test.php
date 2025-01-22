<?php

    $storedPassword = '$2y$10$87IpdleFa7NWTv2r.NcZDO35gxPKQnruz3P6MhjRe18j1yA64Ra5i'; // Replace with the hashed password from the DB
    $inputPassword = 'user'; // Replace with the password you're testing

    if (password_verify($inputPassword, $storedPassword)) {
        echo "Password matches!";
    } else {
        echo "Password does not match=>";
    }
    $newPassword = 'user'; // Replace with the new plain text password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    echo $hashedPassword; // Copy and update this hash in your database


?>
