<?php
// config.php example, copy and modify
// MongoDB access:
$config['db_host']='localhost';
$config['db_login']='viz';
$config['db_password']='';
$config['db_base']='viz';
$config['db_prefix']='viz';

// Redis access:
$config['redis_host']='localhost';
$config['redis_password']='';

// Timezone:
$config['server_timezone']='Etc/GMT';

$site_root=$_SERVER['DOCUMENT_ROOT'];