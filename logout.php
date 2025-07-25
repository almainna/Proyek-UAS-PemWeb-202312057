<?php
require_once 'config/init.php';

session_destroy();
redirect('index.php');
?>