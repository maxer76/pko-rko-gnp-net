#!/usr/bin/php
<?php


exit;



echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
 
$bd_host = "localhost";
$bd_user = "root";
$bd_pass = "Cwlziqxy1";
$bd_base = "kassa";

$mysqli = new mysqli($bd_host,$bd_user,$bd_pass,$bd_base);
$mysqli->query("SET character_set_database=utf8");
$mysqli->query("SET character_set_client=utf8");
$mysqli->query('SET NAMES "UTF8"');


// Выбрать таблицу rko или pko
$table = 'pko';
// Номер АЗС
$azs_number = '218';
// Регион АЗС
$azs_region = '23';
// Время с которого сдвинуть документы
$timestamp = '2021-09-02 00:00:00';
// На сколько сдвигать
$shift = 1;


if(!empty($table) && !empty($azs_number) && !empty($azs_region) && !empty($timestamp) && !empty($shift))
{
	$q = "
		UPDATE `{$table}` 
			SET 
				`number_int` = `number_int` + {$shift},
				`number`= CONCAT(LPAD('{$azs_region}', 2 , '0'), '-', LPAD(`skladID`, 4 , '0'), '-',  LPAD(`number_int`, 5 , '0'))
			WHERE 
				`skladID` = '{$azs_number}'
			AND
				`datetime` >= '{$timestamp}'
	";


	if($result = $mysqli->query($q))
	{
		echo '<p>Выполнено ...</p>';
	}
	else
	{
		echo 'Чт-то пошло не так: '.$mysqli->error;
	}	
}
else
{
	echo 'Чт-то пошло не так: не заполнены параметры';
}