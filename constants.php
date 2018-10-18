<?php
define('DEBUG', false);
define('DIRECTORY', '/tmp/');

// Table file
define('KEYLENGTH', 6);
define('DELETECODELENGTH', KEYLENGTH + 1);
// Limit on linux is 142 characters. fileName is in format "KEYLENGTH-FILENAMELENGTH"
define('FILENAMELENGTH', 142 - KEYLENGTH - 1);
define('PASSWORDLENGTH', 32);
define('LOCATIONLENGTH', 191);

// Table report
define('LINKLENGTH', 191);
define('NAMELENGTH', 40);

//define('KB', 1024);
//define('MB', 1048576);
//define('GB', 1073741824);
//define('TB', 1099511627776);
//define('MAXFILESIZE', 1*GB);