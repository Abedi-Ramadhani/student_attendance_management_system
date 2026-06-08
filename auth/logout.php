<?php
// Start the session so we can access it.
session_start();

// Remove all saved session data.
session_unset();

// Destroy the session completely.
session_destroy();

// Send the user back to the login page.
header('Location: login.php?logout=1');
exit();
?>
