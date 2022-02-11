<?session_start();
session_destroy();

header("Location: buh_login.php"); 
exit();
?>