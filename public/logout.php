<?php
session_starts();
session_destroy();
header('Location: index.php');
exit;
