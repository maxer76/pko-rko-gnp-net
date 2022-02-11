#!/usr/bin/php
<?php

$tmpl['pko'] = <<<PKO
<?xml version="1.0" encoding="utf-8"?>
<database name="kassa"><table name="pko"><column name="number">%%number%%</column><column name="skladID">%%skladID%%</column><column name="sum">%%sum%%</column><column name="sum10">%%sum10%%</column><column name="nds">%%nds%%</column><column name="nds10">%%nds10%%</column><column name="ot">%%ot%%</column><column name="datetime">%%datetime%%</column><column name="oper">%%oper%%</column><column name="osnov">%%osnov%%</column>%%pril%%<column name="user">%%user_id%%</column></table></database>
PKO;
$tmpl['rko'] = <<<RKO
<?xml version="1.0" encoding="utf-8"?>
<database name="kassa"><table name="rko"><column name="number">%%number%%</column><column name="skladID">%%skladID%%</column><column name="sum">%%sum%%</column><column name="oper">%%oper%%</column><column name="datetime">%%datetime%%</column><column name="vidat">%%vidat%%</column><column name="osnov">%%osnov%%</column><column name="pril">%%pril%%</column><column name="po">%%po%%</column><column name="user">%%user_id%%</column></table></database>
RKO;

$chk = true;

if (empty($argv[1])) {
	echo "Не указано что экспортировать ПКО или РКО\n";
	$chk = false;
}
elseif (!in_array($argv[1],['pko','rko'])) {
	echo "Неверно указан вид экспорта. Укажите pko или rko\n";
	$chk = false;
}
if (empty($argv[2])) {
	echo "Не указан номер АЗС\n";
	$chk = false;
}
elseif (!(int)$argv[2]) {
	echo "Укажите корректный номер АЗС\n";
	$chk = false;
}
if (empty($argv[3])) {
	echo "Не указана дата для экспора в формате 0000-00-00\n";
	$chk = false;
}
if (!$chk) {
	echo "Формат запуска: ".$argv[0]." <pko|rko> <azsNum> <date>\n";
	exit;
}
 
$bd_host = "localhost";
$bd_user = "root";
$bd_pass = "Cwlziqxy1";
$bd_base = "kassa";

$mysqli = new mysqli($bd_host,$bd_user,$bd_pass,$bd_base);
$mysqli->query("SET character_set_database=utf8");
$mysqli->query("SET character_set_client=utf8");
$mysqli->query('SET NAMES "UTF8"');

if (($res = $mysqli->query("SELECT t1.*,".
								($argv[1]=='pko'?"(SELECT t2.`user_id` FROM `users` t2 WHERE t2.`fio`=t1.`ot`) AS `user_id`,":
													"(SELECT t2.`user_id` FROM `users` t2 WHERE t2.`fio`=t1.`vidat`) AS `user_id`,").
								"(SELECT t3.`ink` FROM `azs` t3 WHERE t3.`skladID`=t1.`skladID`) AS `bank`,
								(SELECT t3.`region` FROM `azs` t3 WHERE t3.`skladID`=t1.`skladID`) AS `region`
								FROM `{$argv[1]}` t1
								WHERE `skladID`={$argv[2]} AND `date`='{$argv[3]}'"))
	&& !$mysqli->errno
	&& $res->num_rows) {
	while ($v = $res->fetch_assoc()) {
		$out = $tmpl[$argv[1]];
		$out = @str_replace("%%number%%", $v['number'], $out);
		$out = @str_replace("%%skladID%%", $v['skladID'], $out);
		$out = @str_replace("%%sum%%", $v['sum'], $out);
		$out = @str_replace("%%sum10%%", $v['sum10'], $out);
		$out = @str_replace("%%nds%%", $v['nds'], $out);
		$out = @str_replace("%%nds10%%", $v['nds10'], $out);
		$out = @str_replace("%%ot%%", $v['ot'], $out);
		$out = @str_replace("%%datetime%%", $v['datetime'], $out);
		$out = @str_replace("%%oper%%", $v['oper'], $out);
		switch ($v['oper']) {
			//////////// ПКО ///////////////////
			case '50.02':
				$out = @str_replace("%%osnov%%","Розничная выручка (ККТ №{$v['osnov']})",$out);
				$out = @str_replace("%%pril%%","<column name=\"pril\">Отчет о закрытии смены №{$v['pril']}</column>",$out);
				break;
			case '62.01, 62.02':
				$out = @str_replace("%%osnov%%","Излишняя оплата (подлежащая возврату покупателю)",$out);
				break;		
			case '91.01':
				$out = @str_replace("%%osnov%%","Излишки наличных денежных средств в кассе АЗС №{$v['skladID']}",$out);
				$out = @str_replace("%%pril%%","<column name=\"pril\">{$v['pril']}</column>",$out);
				break;
			////////// РКО ///////////////
			case '57.3':
				$out = @str_replace("%%vidat%%",$v['vidat'],$out);
				if ($v['osnov'] == 'Сдача выручки в банк') {
					$out = @str_replace("%%osnov%%","{$v['osnov']} ({$v['bank']})",$out);
					$d = explode("-",$v['date']);
					$d = "{$d[2]}.{$d[1]}.{$d[0]}";
					$out = @str_replace("%%pril%%","Квитанция к сумке №{$v['pril']} от {$d}",$out);
				}
				else {
					$out = @str_replace("%%osnov%%",$v['osnov'],$out);
					$out = @str_replace("%%pril%%",$v['pril'],$out);
				}
				$out = @str_replace("%%po%%",$v['pasport'],$out);
				break;
			case '62.01,62.02':
				$out = @str_replace("%%vidat%%",$v['vidat'],$out);
				$out = @str_replace("%%osnov%%",$v['osnov'],$out);
				$out = @str_replace("%%pril%%",$v['pril'],$out);
				$out = @str_replace("%%po%%",$v['po'],$out);
				break;
			case '94.05.1':
				$out = @str_replace("%%vidat%%",$v['kassir'],$out);
				$out = @str_replace("%%osnov%%",$v['osnov'],$out);
				$out = @str_replace("%%pril%%",$v['pril'],$out);
				$out = @str_replace("<column name=\"po\">%%po%%</column>","",$out);
				break;
		}
		$out = @str_replace("%%pril%%","",$out);
		$out = @str_replace("%%user_id%%",$v['user_id'],$out);
		$fname = $argv[1].
					'_'.$v['region'].
					'-'.str_pad($v['skladID'],4,'0',STR_PAD_LEFT).
					'-'.str_pad($v['number_int'],5,'0',STR_PAD_LEFT).
					'_'.$v['skladID'].
					'_'.preg_replace("/[-:\s]+/","",$v['datetime']).
					'.xml';
		if ($f = fopen($fname, "wb")) {
			fwrite($f, $out);
			fclose($f);
		}
	}
}
elseif ($mysqli->errno) {
	echo $mysqli->error."\n";
}
else {
	echo "Не найдено ни одной записи для указанных параметров\n";
}
