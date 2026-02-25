<?php
include __DIR__ . '/header_visibility.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();  // Start the session only if it's not already started
}

include __DIR__ . '/../pages_html/index.html'; 
?>