<?php

use okushnirov\core\Library\{DbSQLAnywhere, Enums\SQLAnywhere};

require_once __DIR__.'/../../php/handler_error.php';

$SQL = "SELECT TOP 10 * FROM \"dbo\".\"_Сессии\"";

if (!DbSQLAnywhere::connect()) {
  
  exit("No DBase connection");
}

/**
 * Не змінювати ключ (назви стовпця)
 */
$result = DbSQLAnywhere::query($SQL, SQLAnywhere::FETCH_ALL, flags: SQL_KEY_CASE_ORIGIN);

echo "<pre>
<strong>SQL Query</strong> -> $SQL<br>
<strong>Result:</strong><br>";
print_r($result);
echo "
<strong>Error:</strong> -> ".DbSQLAnywhere::$queryErrorMessage;
echo "
</pre>";

DbSQLAnywhere::disconnect();