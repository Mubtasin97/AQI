<?php
session_start();

// Clear session data
$_SESSION = array();
session_destroy();

// Clear any client-side storage if needed
echo json_encode(["success" => true]);
?>