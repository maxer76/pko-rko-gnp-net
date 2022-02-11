<?php
//echo '<img src="/img/works.jpg" alt="Технические работы" />';exit;
require_once('template/head.php');
// fix 0.0.0.2
// fix 0.0.0.3

define ('ROOTPATH', substr(__DIR__,0,strrpos(__DIR__,'/')+1));


$page_ = $_SERVER["PHP_SELF"];

 
$bd_host = "localhost";
$bd_user = "root";
$bd_pass = "Cwlziqxy1";
$bd_base = "kassa";

$mysqli = new mysqli($bd_host,$bd_user,$bd_pass,$bd_base);
$mysqli->query("SET character_set_database=utf8");
$mysqli->query("SET character_set_client=utf8");
$mysqli->query('SET NAMES "UTF8"');


    function my_ucfirst($string, $e ='utf-8') { 
        if (function_exists('mb_strtoupper') && function_exists('mb_substr') && !empty($string)) { 
            $string = mb_strtolower($string, $e); 
            $upper = mb_strtoupper($string, $e); 
            preg_match('#(.)#us', $upper, $matches); 
            $string = $matches[1] . mb_substr($string, 1, mb_strlen($string, $e), $e); 
        } else { 
            $string = ucfirst($string); 
        } 
        return $string; 
    } 
	
	
function generateCode($length=6) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPRQSTUVWXYZ0123456789";
    $code = "";
    $clen = strlen($chars) - 1;  
    while (strlen($code) < $length) {
            $code .= $chars[mt_rand(0,$clen)];  
    }
    return $code;
}

function display_xml_error($error, $xml)
{
    $return  = $xml[$error->line - 1] . "<br>";
    $return .= str_repeat('-', $error->column) . "<br>";

    switch ($error->level) {
        case LIBXML_ERR_WARNING:
            $return .= "Warning $error->code: <br>";
            break;
         case LIBXML_ERR_ERROR:
            $return .= "Error $error->code: <br>";
            break;
        case LIBXML_ERR_FATAL:
            $return .= "Fatal Error $error->code: <br>";
            break;
    }

    $return .= trim($error->message) .
               "<br>  Line: $error->line" .
               "<br>  Column: $error->column";

    if ($error->file) {
        $return .= "<br>  File: $error->file";
    }

    return "$return<br><br>--------------------------------------------<br><br>";
}

function passchk($login, $pass){
	global $mysqli;
	$q = "
		SELECT 
			`users`.`user_login`, 
			`users`.`user_id`, 
			`users`.`user_password`,
			`users`.`fio`,
			`users`.`pasport`,
			`azs`.`name` as azs_name,
			`azs`.`ink`,
			`azs`.`ragion_name`,
			`azs`.`address`,
			`azs`.`region`,
			`azs`.`buh`,
			`azs`.`position`,
			`azs`.`skladID`
		FROM 
			`users` 			
		INNER JOIN 
			`azs`
		ON
			`users`.`azs_id` = `azs`.`skladID`
		WHERE 
			user_login=? 
		LIMIT 1
	";
	$result = $mysqli->stmt_init();
	if ($result->prepare($q))
	{
		$result->bind_param("s",$mysqli->real_escape_string($login));
		$result->execute();
		$obj = $result->get_result()->fetch_object();
		$result->close();
		
		if($obj->user_password === md5(md5($pass))){
			return $obj;
		}
		
		/*if($obj->user_password === md5(md5($pass))){
			if($login != 'azs171zaloznay'){
				$_SESSION['error'] = 'Ведутся технические работы';
				header("Location: index.php"); 
				exit();
			}
			else 
				return $obj;
		}*/
		
		
			//return $false;
	}
	
	return false;
}

function admchk($login, $pass){
	global $mysqli;
	$q = "
		SELECT 
			`it`.`user_id`,
			`it`.`user_login`,
			`it`.`user_password`,
			`it`.`region_id`
		FROM 
			`it` 			
		WHERE 
			user_login=? 
		LIMIT 1
	";
	$result = $mysqli->stmt_init();
	if ($result->prepare($q))
	{
		$result->bind_param("s",$mysqli->real_escape_string($login));
		$result->execute();
		$obj = $result->get_result()->fetch_object();
		$result->close();
		
		if($obj->user_password === md5(md5($pass)))
			return $obj;
	}
	return false;
}

function buhchk($login, $pass){
	global $mysqli;
	$q = "
		SELECT 
			`buh`.`user_id`,
			`buh`.`user_password`,
			`buh`.`region_id`
		FROM 
			`buh` 			
		WHERE 
			user_login=? 
		LIMIT 1
	";
	$result = $mysqli->stmt_init();
	if ($result->prepare($q))
	{
		$result->bind_param("s",$mysqli->real_escape_string($login));
		$result->execute();
		$obj = $result->get_result()->fetch_object();
		$result->close();
		
		if($obj->user_password === md5(md5($pass)))
			return $obj;
	}
	return false;
}

function usrchk($id,$hash){
	global $mysqli;
	$q = "SELECT * FROM `users` WHERE user_id=? LIMIT 1";
	$result = $mysqli->stmt_init();
	if ($result->prepare($q))
	{
		$result->bind_param("i",$id);
		$result->execute();
		$obj = $result->get_result()->fetch_object();
		$result->close();
		if(($obj->user_hash !== $hash) or ($obj->user_id !== $id)){
			unset($_SESSION['user_id']);
			unset($_SESSION['user_hash']);		
			return false;
		}
		else
			return true;
			//return false;
	}
	return false;
}
function usradmchk($id,$hash){
	global $mysqli;
	$q = "SELECT * FROM `it` WHERE user_id=? LIMIT 1";
	$result = $mysqli->stmt_init();
	if ($result->prepare($q))
	{
		$result->bind_param("i",$id);
		$result->execute();
		$obj = $result->get_result()->fetch_object();
		$result->close();
		if(($obj->user_hash !== $hash) or ($obj->user_id !== $id)){
			unset($_SESSION['user_id']);
			unset($_SESSION['user_hash']);		
			return false;
		}
		else
			return true;
	}
	return false;
}

function buh_chk($id,$hash){
	global $mysqli;
	$q = "SELECT * FROM `buh` WHERE user_id=? LIMIT 1";
	$result = $mysqli->stmt_init();
	if ($result->prepare($q))
	{
		$result->bind_param("i",$id);
		$result->execute();
		$obj = $result->get_result()->fetch_object();
		$result->close();
		if(($obj->user_hash !== $hash) or ($obj->user_id !== $id)){
			unset($_SESSION['user_id']);
			unset($_SESSION['user_hash']);		
			return false;
		}
		else
			return true;
	}
	return false;
}

function chkadm($id){
	global $mysqli;
	$q = "
		SELECT roles.id, roles.name FROM `users` 
		INNER JOIN roles ON users.role_id = roles.id
	WHERE users.user_id=? LIMIT 1";
	$result = $mysqli->stmt_init();
	if ($result->prepare($q)){
		$result->bind_param("i",$id);
		$result->execute();
		$obj = $result->get_result()->fetch_object();
		$result->close();
		$_SESSION['role_id'] = $obj->id;
		$_SESSION['role_name'] = $obj->name;
		return true;
	}
	return false;
}


	
function setlog($user_id, $err, $page){
	global $mysqli;
	$q = "INSERT INTO `log` (`user_id`,`err`,`page`) VALUES (?,?,?)";
	$result = $mysqli->stmt_init();
	if ($result = $mysqli->prepare($q))
	{
		$result->bind_param("iss",$user_id,$err,$page);
		$result->execute();
		
		$result->close();
	}
}


function num2str($num) {
    $nul='ноль';
    $ten=array(
        array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
        array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
    );
    $a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
    $tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
    $hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
    $unit=array( // Units
        array('копейка' ,'копейки' ,'копеек',	 1),
        array('рубль'   ,'рубля'   ,'рублей'    ,0),
        array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
        array('миллион' ,'миллиона','миллионов' ,0),
        array('миллиард','милиарда','миллиардов',0),
    );
    //
    list($rub,$kop) = explode('.',sprintf("%015.2f", floatval($num)));
    $out = array();
    if (intval($rub)>0) {
        foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
            if (!intval($v)) continue;
            $uk = sizeof($unit)-$uk-1; // unit key
            $gender = $unit[$uk][3];
            list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
            // mega-logic
            $out[] = $hundred[$i1]; # 1xx-9xx
            if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
            else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
            // units without rub & kop
            if ($uk>1) $out[]= morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
        } //foreach
    }
    else $out[] = $nul;
    $out[] = morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
    $out[] = $kop.' '.morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
    return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
}

/**
 * Склоняем словоформу
 * @ author runcore
 */
function morph($n, $f1, $f2, $f5) {
    $n = abs(intval($n)) % 100;
    if ($n>10 && $n<20) return $f5;
    $n = $n % 10;
    if ($n>1 && $n<5) return $f2;
    if ($n==1) return $f1;
    return $f5;
}

function set_number($table){
	global $mysqli;
	global $_SESSION;
	
	$q = "
		SELECT 
			MAX(`number_int`) as number_int
		FROM 
			{$table}
		WHERE 
			`date`=(
                SELECT 
                  	MAX(`date`) 
        		FROM 
					{$table}
				WHERE 
					`skladID` = '{$_SESSION["skladID"]}'					
            )
		AND
			`skladID` = '{$_SESSION["skladID"]}'
		LIMIT 1
	";

	$result = $mysqli->stmt_init();
	if ($result->prepare($q)){
		$result->execute();
		$obj = $result->get_result()->fetch_object();
		$result->close();
		$number_int = $obj->number_int;
		$number_int++;
		$_SESSION["number_int"] = $number_int;
		$number = str_pad($_SESSION["region"], 2, '0', STR_PAD_LEFT).'-'.str_pad($_SESSION["skladID"], 4, '0', STR_PAD_LEFT).'-'.str_pad($number_int, 5, '0', STR_PAD_LEFT);
		return $number;
	}
	else
		return false;
}
function russian_date($date){
	$date=explode("-", $date);
	switch ($date[1]){
		case 1: $m='января'; break;
		case 2: $m='февраля'; break;
		case 3: $m='марта'; break;
		case 4: $m='апреля'; break;
		case 5: $m='мая'; break;
		case 6: $m='июня'; break;
		case 7: $m='июля'; break;
		case 8: $m='августа'; break;
		case 9: $m='сентября'; break;
		case 10: $m='октября'; break;
		case 11: $m='ноября'; break;
		case 12: $m='декабря'; break;
	}
	return $date[2].' '.$m.' '.$date[0].'г.';
}

function create_PDF($type, $number){
	global $_SESSION;
	global $mysqli;
				global $page_;	
				
	
	$q = "
		SELECT 
			*
		FROM 
			`{$type}` 
		WHERE 
			`number`='{$number}' 
		AND 
			`{$type}`.`skladID`='{$_SESSION["skladID"]}' 
		LIMIT 
			1";
			
			
			
	if ($result = $mysqli->query($q))
	{
		if($result->num_rows > 0){
			$obj = $result->fetch_object();

			
			switch (substr($type,0,3)){
					case 'pko':
/* ====== fix-0.0.8					
					case 'pko_2015':
					case 'pko_2016':
					case 'pko_2017':
					case 'pko_2018':
					case 'pko_2019':
*/					
						include 'PHPExcel/IOFactory.php';
						
						$date = new DateTime($obj->date);						
						
						$inputFileName = 'xls/pko.xls';
						$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);	

						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 11, $obj->number);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, 5, $obj->number);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(4, 17, $obj->oper.' ');
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(7, 11, $date->format('d.m.Y'));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, 6, russian_date($obj->date));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, 27, russian_date($obj->date));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 8, $obj->azs);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 17, number_format($obj->sum + $obj->sum10, 2, ',', ' '));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(13, 17, number_format($obj->sum + $obj->sum10, 2, ' руб. ', ' ').' коп.');
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 25, my_ucfirst(num2str($obj->sum + $obj->sum10)));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 19, my_ucfirst(num2str($obj->sum + $obj->sum10)));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 29, 'В том числе:');
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 22, 'В том числе:');
						
						// fix-0.0.15 до 02.12.2020 ООО «ГЭС розница», а с 03.12.2020 новое ООО «ГНП сеть».
						if ($obj->date <= '2020-12-02') {
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 6, 'Общество с ограниченной ответственностью "Газэнергосеть розница"');
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 2, 'Общество с ограниченной ответственностью "Газэнергосеть розница"');
						}
						else {
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 6, 'ООО «ГНП сеть»');
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 2, 'ООО «ГНП сеть»');
						}
						// end fix-0.0.15
						
						$oper = $obj->oper.' ';
						if($oper == '91.01 ') {
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 29, 'В том числе:');
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 29, 'НДС (без налога) 0-00 руб.');
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 22, 'В том числе:');
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 23, 'НДС (без налога) 0-00 руб.');
						}	
						elseif($obj->oper == '62.01, 62.02' && $obj->number == '61-0394-00274'){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 29, 'НДС (18%) '.number_format($obj->nds, 2, '-', ' ').' руб.');
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 23, 'НДС (18%) '.number_format($obj->nds, 2, '-', ' ').' руб.');
						}						
						else{
							if($obj->date < '2015-08-01'){
								
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 29, 'НДС (18%) '.number_format($obj->nds, 2, '-', ' ').' руб.');
								if(!empty($obj->nds10))
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 30, 'НДС (10%) '.number_format($obj->nds10, 2, '-', ' ').' руб.');
									
								
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 23, 'НДС (18%) '.number_format($obj->nds, 2, '-', ' ').' руб.');
								if(!empty($obj->nds10))
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 24, 'НДС (10%) '.number_format($obj->nds10, 2, '-', ' ').' руб.');
							}
						}
							
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 19, $obj->ot.' (АЗС '.$_SESSION["skladID"].')');
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 9, $obj->ot.' (АЗС'.$_SESSION["skladID"].')');
						if($obj->oper == '50.02'){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 22, 'Розничная выручка (ККТ №'.$obj->osnov.')');
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 12, 'Розничная выручка (ККТ №'.$obj->osnov.')');
						}
						if($obj->oper == '62.01, 62.02'){
							if($obj->number == '61-0394-00274'){
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 22, 'Излишняя оплата (подлежащая возврату покупателю)');
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 12, 'Излишняя оплата (подлежащая возврату покупателю)');								
							}
							else{
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 22, 'Розничная выручка (ККТ №'.$obj->osnov.')');
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 12, 'Розничная выручка (ККТ №'.$obj->osnov.')');
							}
						}
						if($obj->oper == '91.01' or $obj->oper == '62.02'){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 22, $obj->osnov);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(11, 12, $obj->osnov);							
						}
						if($obj->oper == '50.02' or $obj->oper == '62.01, 62.02' or $obj->oper == '62.02') {
							if($obj->number == '52-0327-00278')
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 32, $obj->pril);
							else
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 32, 'Отчет о закрытии смены №'.$obj->pril);
						}
						
						if($obj->oper == '91.01')	
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 32, $obj->pril);
							
						if(!empty(trim($obj->buh))){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 34, $obj->buh);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, 34, $obj->buh);
						}
						else {
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 34, $_SESSION["buh"]);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, 34, $_SESSION["buh"]);
						}
						
						if(isset($obj->creator) && !empty($obj->creator)){
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 37, $obj->creator);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, 37, $obj->creator);							
						}
						else{
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 37, $obj->ot);
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(14, 37, $obj->ot);
						}
						
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'mpdf60';
						$rendererLibraryPath = ROOTPATH.$rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die(
								'Пожалуйста, укажите имя библиотеки $rendererName и путь к ней $rendererLibraryPath' .
								PHP_EOL .
								' в зависимости от конкретной структуры каталогов'
							);
						}
						
						$objWriter = new PHPExcel_Writer_PDF($objPHPExcel);	
						$objWriter->save("pdf/p-{$number}-{$obj->edit_time}.pdf");
						return "pdf/p-{$number}-{$obj->edit_time}.pdf";
						break;
					case 'rko':
/* ======= fix-0.0.8					
					case 'rko_2015': 
					case 'rko_2016': 
					case 'rko_2017': 
					case 'rko_2018': 
					case 'rko_2019':
*/					
						include 'PHPExcel/IOFactory.php';
						
						$date = new DateTime($obj->date);
						
						$inputFileName = 'xls/rko.xls';
						$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);	

						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 9, $obj->number);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 9, $date->format('d.m.Y'));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 29, $date->format('d.m.Y'));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 5, $obj->azs);
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 13, number_format($obj->sum, 2, ',', ' '));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 17, my_ucfirst(num2str($obj->sum)));
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 13, $obj->oper.' ');
				
						// fix-0.0.15 до 02.12.2020 ООО «ГЭС розница», а с 03.12.2020 новое ООО «ГНП сеть».
						if ($obj->date <= '2020-12-02') {
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 3, 'Общество с ограниченной ответственностью "Газэнергосеть розница"');
						}
						else {
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 3, 'ООО «ГНП сеть»');
						}
						// end fix-0.0.15
						
						// *********************** Инкассация **************************************
							if($obj->oper == '57.3'){																													//	КОРР. СЧЁТ						
								$flag_date = new DateTime('2018-05-01');																	
								if($date < $flag_date){
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 14, $obj->vidat);														//	ВЫДАТЬ			= Банк
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 16, $obj->osnov);														//	ОСНОВАНИЕ		= Сдача выручки в банк 
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 20, 'Квитанция к сумке №'.$obj->pril);								//	ПРИЛОЖЕНИЕ		= Квитанция к сумке №
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 26, '');																//	ПОЛУЧИЛ			= ПУСТО
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 31, $obj->po);														//	ПО				= ПУСТО
								}else{
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 14, $obj->vidat);														//	ВЫДАТЬ			= ФИО Кассира
									if($obj->osnov == 'Сдача выручки в банк'){
										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 16, $obj->osnov.' ('.$_SESSION["ink"].')');						//	ОСНОВАНИЕ		= Сдача выручки в банк (Банк)
										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 20, 'Квитанция к сумке №'.$obj->pril.' от '.$date->format("d.m.Y"));	//	ПРИЛОЖЕНИЕ		= Квитанция к сумке №_____ (от ДАТА)
									}
									elseif (mb_substr($obj->osnov,0,38) == 'Сдача выручки в банк '){
										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 16, $obj->osnov);						//	ОСНОВАНИЕ		= Сдача выручки в банк (Банк)
										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 20, 'Квитанция к сумке №'.$obj->pril.' от '.$date->format("d.m.Y"));	//	ПРИЛОЖЕНИЕ		= Квитанция к сумке №_____ (от ДАТА)
									}
									else{
										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 16, $obj->osnov);													//	ОСНОВАНИЕ		= Пополнение основной кассы офиса из кассы АЗС
										$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 20, $obj->pril);													//	ПРИЛОЖЕНИЕ		= Приложение
									}
									
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 26, my_ucfirst(num2str($obj->sum)));									//	ПОЛУЧИЛ			= ПУСТО
									$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 31, $obj->pasport);													//	ПО				= Паспорт кассира
								}
							}
						
						// *************************************************************************
						
						
						// ***********************  Возврат покупателю *****************************
							if($obj->oper == '62.01,62.02'){																					//	КОРР. СЧЁТ
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 14, $obj->vidat);									//	ВЫДАТЬ			= ФИО клиента
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 16, $obj->osnov);									//	ОСНОВАНИЕ		= Возврат оплаты за товар покупателю
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 20, $obj->pril);									//	ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 26, my_ucfirst(num2str($obj->sum)));				//	ПОЛУЧИЛ			= Сумма прописью
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 31, $obj->po);									//	ПО				= Паспортные данные клиента
							}
						// *************************************************************************
						
						
						// ***********************  Недостача  *************************************
							if($obj->oper == '94.05.1'){																						//	КОРР. СЧЁТ
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 14, $obj->vidat);									//	ВЫДАТЬ			= ФИО кассира
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 16, $obj->osnov);									//	ОСНОВАНИЕ		= Недостача наличных денежных средств в кассе АЗС №___
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 20, $obj->pril);									//	ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 26, '');											//	ПОЛУЧИЛ			= ПУСТО
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(2, 31, '');											//	ПО				= ПУСТО
								$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(1, 29, '');											//	ДАТА			= ПУСТО
							}						
						// *************************************************************************

						
						if(!empty(trim($obj->dir)))
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 22, $obj->dir);
						else
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(8, 22, $_SESSION['buh']);
							
						if(!empty(trim($obj->dol)))
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 22, $obj->dol);
						else
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(3, 22, $obj->position);
						
						if(!empty(trim($obj->buh)))
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 24, $obj->buh);			
						else
							$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(6, 24, $_SESSION['buh']);		
							
										
						$objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow(5, 33, $obj->kassir);
						
						$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
						$rendererLibrary = 'mpdf60';
						$rendererLibraryPath = ROOTPATH.$rendererLibrary;
						if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
							die(
								'Пожалуйста, укажите имя библиотеки '.$rendererName.' и путь к ней '.$rendererLibraryPath.
								PHP_EOL .
								' в зависимости от конкретной структуры каталогов'
							);
						}
						
						$objWriter = new PHPExcel_Writer_PDF($objPHPExcel);	
						$objWriter->save("pdf/r-{$number}-{$obj->edit_time}.pdf");
						return "pdf/r-{$number}-{$obj->edit_time}.pdf";
						break;
					default: $_SESSION["error"] = 'В функцию create_PDF не передан тип таблицы'; setlog($_SESSION['user_id'],$_SESSION["error"],$page_);header("Location: actions.php"); exit();
				}	
		}
		else{
			$_SESSION["error"] = 'Попытка открыть несуществующий документ или нет прав доступа '.$number.' '.$q;
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
			return false;			
		}
		$result->close();
	}
	else{
		$_SESSION["error"] = $mysqli->error;
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
		return false;			
	}	
}

function intMorphy ( $int, $im, $rd, $rdm )
{
	$a = $int % 10;
	$b = $int % 100;

	switch(true) {
		case($a == 0 || $a >= 5 || ($b >= 10 && $b <= 20)):
			$result = $rdm;
			break;
		case($a == 1):
			$result = $im;
			break;
		case($a >= 2 && $a <= 4):
			$result = $rd;
			break;
	}

	return $int . ' ' . $result;
}

function get_kassa_cnt($date, $arch=''){
	global $_SESSION;
	global $mysqli;
	global $page_;	

	$date = new DateTime($date);
// fix-0.0.10 - remove DAYS, work with LASTWORKDAY
//	$days = date("t", strtotime($date->format('y') . "-" . $date->format('m') . "-01"));
	$days = $date->format('d');
	
/* fix-0.0.10
	if(
		$days == $date->format('d')  
		|| ($date->format('Y-m-d') == '2016-05-21' && $_SESSION['skladID'] == 167)
		|| ($date->format('Y-m-d') == '2016-08-01' && $_SESSION['skladID'] == 180)
		|| ($date->format('Y-m-d') == '2016-08-30' && $_SESSION['skladID'] == 230)
		|| ($date->format('Y-m-d') == '2016-10-24' && $_SESSION['skladID'] == 178)
		|| ($date->format('Y-m-d') == '2016-10-27' && $_SESSION['skladID'] == 174)
		|| ($date->format('Y-m-d') == '2016-08-11' && $_SESSION['skladID'] == 328)
		|| ($date->format('Y-m-d') == '2016-08-17' && $_SESSION['skladID'] == 304)  
		|| ($date->format('Y-m-d') == '2018-02-19' && $_SESSION['skladID'] == 230)  
		|| ($date->format('Y-m-d') == '2020-07-26' && $_SESSION['skladID'] == 234)  
		|| ($date->format('Y-m-d') == '2019-09-07' && $_SESSION['skladID'] == 435)  // Добавил Юрко Андрей. Первый костыль.
	){
*/	
		$q = "
			SELECT 
				COUNT(*) as cnt
			FROM
				`book{$arch}`
			WHERE
				`skladID` = '{$_SESSION['skladID']}'
			AND
				`date` >= '".$date->format('y')."-".$date->format('m')."-01' 
			AND
				`date` <= '".$date->format('y')."-".$date->format('m')."-".$days."' 
			AND
				`status` != '11'
			AND 
				`status` != '10'
		";
	
		if ($result = $mysqli->query($q))
			if($result->num_rows > 0)
				while ($cnt = $result->fetch_object()) 
				{

					return $cnt->cnt;
				}
//	}
	
	return false;
}

function create_kassa($date, $arch = ''){
	global $_SESSION;
	global $mysqli;
	global $page_;	
	
	$q = "
		SELECT 
			`book{$arch}`.*,
			`book{$arch}`.skladID as skladID,
			`azs`.skladID as skladID2,
			`azs`.ragion_name,
			`azs`.replacement,
			`azs`.name,
			`azs`.title_date1,
			`azs`.title_date2
		FROM
			book{$arch}
		INNER JOIN 
			`azs`
		ON
			`book{$arch}`.`skladID` = `azs`.`skladID`			
		WHERE
			`book{$arch}`.`skladID` = '{$_SESSION['skladID']}'
		AND
			`date` = '{$date}'
		LIMIT 1
	";
	
	
	
	
	if ($result = $mysqli->query($q))
	{
		if($result->num_rows > 0){
		
			include 'PHPExcel/IOFactory.php';
			$inputFileName = 'xls/kassa.xls';
			$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			

			while ($data = $result->fetch_object()){
			
			//	if($data->replacement){
					$q1 = "
					SELECT * FROM `replacements`
						WHERE `skladID`='{$_SESSION['skladID']}'
							AND `from`<='".$data->date."' AND `to`>='".$data->date."' 
						LIMIT 1
					";
				/*	$q1 = "
						SELECT
							*
						FROM	
							`replacements`
						WHERE
							`replacements`.`id` = {$data->replacement}
						LIMIT
							1
					";
				*/
					if ($result1 = $mysqli->query($q1)){
						if($result1->num_rows > 0){
							$data1 = $result1->fetch_object();
						//	if($data->date >= $data1->from && $data->date <= $data1->to){
								$data->buh = $data1->fio;
								$data->dol = $data1->position;
						//	}
						}
					}
			//	}						
				
		
			
				$rko_cnt = 0;
				$pko_cnt = 0;
				$rko_pko_cnt_line1 = 17;
				$rko_pko_cnt_line2 = 41;
				
			
				$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
				$rendererLibrary = 'mpdf60';
				$rendererLibraryPath = '/var/www/html/'.$rendererLibrary;
				if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
					die(
						'Пожалуйста, укажите имя библиотеки $rendererName и путь к ней $rendererLibraryPath' .
						PHP_EOL .
						' в зависимости от конкретной структуры каталогов'
					);
				}
				
				//return 'КАССА за '.russian_date($date);
				
				$title_date1 = $data->title_date1;
				$title_date2 = $data->title_date2;				
				
				$start = round(delete_start_end($data->start,'.'),2);
				$end = round(delete_start_end($data->end,'.'),2);
				//$fdate = new 
				
				$objWriter = new PHPExcel_Writer_PDF($objPHPExcel);		
				$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 9, $data->ragion_name.' '.$data->name);
				$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 10, $data->ragion_name.' '.$data->name);
				$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow(4, 17, $data->list_cnt_y);
				$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow(10, 21, $data->buh);
				$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow(10, 23, $data->buh);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 3, $data->azs);
				if($date < '2017-12-08'){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 2, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\616401001');
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 26, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\616401001');
				}
				elseif($date >= '2017-12-08' && $date < '2018-03-06'){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 2, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\615250001');
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 26, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\615250001');
				}
				elseif($date >= '2018-03-06'){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 2, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\997350001');
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 26, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\997350001');					
				}

				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 27, $data->azs);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 4, 'КАССА за '.russian_date($date));
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 28, 'КАССА за '.russian_date($date));
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 6, 'Лист '.$data->list_cnt_y);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 30, 'Лист '.$data->list_cnt_y);
				
				if($start == '0'){
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 9, '');
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 33, '');
				}
				else{
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 9, number_format($start, 2, '=', ' '));
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 33, number_format($start, 2, '=', ' '));
				}
				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 34, number_format($data->itog_p, 2, '=', ' '));				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 10, number_format($data->itog_p, 2, '=', ' '));				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 10, number_format($data->itog_r, 2, '=', ' '));				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 34, number_format($data->itog_r, 2, '=', ' '));

				if($end == '0'){				
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 11, '');				
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 35, '');				
				}
				else{
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 11, number_format($end, 2, '=', ' '));				
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 35, number_format($end, 2, '=', ' '));				
				}
				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, 14, $data->kassir);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, 38, $data->kassir);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, 19, $data->buh);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, 43, $data->buh);
				
				
				if($c = get_kassa_cnt($date,$arch)){ 
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 22, 'Количество листов кассовой книги за месяц: '.$c);
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 46, 'Количество листов кассовой книги за месяц: '.$c);				
				} 
				
				if($title_date2 == $date) 
				{ 

					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 23, 'Количество листов кассовой книги за год: '.$data->list_cnt_y);
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 47, 'Количество листов кассовой книги за год: '.$data->list_cnt_y);				
					
				} 
				
				$mass = unserialize(base64_decode($data->rkopko));
				/*echo '<pre>';
				print_r($mass);
				echo '</pre>';
				exit;*/
				$flag_date = new DateTime('2018-05-01');
				$list_date = new DateTime($date);
						

				foreach($mass as $e){
					$objPHPExcel->setActiveSheetIndex(0)->insertNewRowBefore(34, 1);
					$objPHPExcel->setActiveSheetIndex(0)->getStyle('A34:G34')->getFont()->setBold(false);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(0, 34)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B34:D34');
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(1, 34)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(4, 34)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(5, 34)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(6, 34)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 34, $e['number']);								
					if($e['type']){
						$rko_cnt++;
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 34, number_format($e['sum'], 2, '=', ' '));
						
						if($list_date < $flag_date){
							$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 34, 'Выдано '.$e['vidat']);	
						}
						else{
							$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 34, 'Выдано '.$e['kassir']);	
						
						}
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, 34, $e['oper'].' ');
					}
					else{
						$pko_cnt++;
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 34, number_format($e['sum'] + $e['sum10'], 2, '=', ' '));				
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 34, 'Принято от '.$e['vidat']);	
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, 34, $e['oper'].' ');
					}
				}
				
				foreach($mass as $e){
					$objPHPExcel->setActiveSheetIndex(0)->insertNewRowBefore(10, 1);
					$objPHPExcel->setActiveSheetIndex(0)->getStyle('A10:G10')->getFont()->setBold(false);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(0, 10)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->setActiveSheetIndex(0)->mergeCells('B10:D10');
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(1, 10)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(4, 10)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(5, 10)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$objPHPExcel->setActiveSheetIndex(0)->getStyleByColumnAndRow(6, 10)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
					$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 10, $e['number']);							
					if($e['type']){
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 10, number_format($e['sum'], 2, '=', ' '));
						if($list_date < $flag_date){
							$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 10, 'Выдано '.$e['vidat']);	
						}
						else{
							$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 10, 'Выдано '.$e['kassir']);	
						}
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, 10, $e['oper'].' ');	
					}
					else{
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 10, number_format($e['sum'] + $e['sum10'], 2, '=', ' '));				
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 10, 'Принято от '.$e['vidat']);	
						$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, 10, $e['oper'].' ');	

					}
				}
				
				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, $rko_pko_cnt_line1+$rko_cnt+$pko_cnt, intMorphy($pko_cnt, 'приходный', 'приходных', 'приходных').'  и '.intMorphy($rko_cnt, 'расходный', 'расходных', 'расходных').' получил.');
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, $rko_pko_cnt_line2+$rko_cnt+$pko_cnt+$rko_cnt+$pko_cnt, intMorphy($pko_cnt, 'приходный', 'приходных', 'приходных').'  и '.intMorphy($rko_cnt, 'расходный', 'расходных', 'расходных').' получил.');

				if($title_date1 == $date || $title_date2 == $date) 
				{ 
					
					//$objWriter->writeAllSheets();
					$objWriter->setSheetIndex(0);
				} 
				else 
				{ 
					$objWriter->setSheetIndex(0);
					//$objWriter->writeAllSheets();
				} 					
				
				$ret = "pdf/kassa-{$date}-".$data->edit_time.".pdf";
				
				$objWriter->save($ret);		
				
				
			
			}
			
			$q = "
				UPDATE 
					`book{$arch}` 
				SET 
					`status`= '2'
				WHERE
					skladID = '{$_SESSION['skladID']}'
				AND
					`date` = '{$date}'					
			";
			$mysqli->query($q);
			
			return $ret;
		}
		else{
			$_SESSION["error"] = 'Нет РКО и ПКО для кассовой книги'.$q;
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
			return false;			
		}
		$result->close();
	}		
	else{
		$_SESSION["error"] = $mysqli->error;
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);		
		return false;
	}
}

function book_list_cnt($arch = '', $part = NULL){
	global $_SESSION;
	global $mysqli;
	global $page_;	
	
	$date = new DateTime($part);
	
	if((int)$date->format('n') > 6)
		$q = "
			SELECT
				count(*) as cnt
			FROM
				`book{$arch}`
			WHERE
				`date` >= '".$date->format('Y')."-07-01'
			and
				`date` <= '".$date->format('Y')."-12-31'
			and
				`skladID` = '{$_SESSION['skladID']}'
			and
				`status` != '11'
			and
				`status` != '10'
				
		";
		else 
		$q = "
			SELECT
				count(*) as cnt
			FROM
				`book{$arch}`
			WHERE
				`date` >= '".$date->format('Y')."-01-01'
			and
				`date` <= '".$date->format('Y')."-06-30'
			and
				`skladID` = '{$_SESSION['skladID']}'
			and
				`status` != '11'
			and
				`status` != '10'
				
		";			
			
	

	
	
	
	if ($result = $mysqli->query($q)){
		while ($data = $result->fetch_object()){
			return $data->cnt;
		}
	}
	
	return 0;
}

function create_title($arch = ''){
	global $_SESSION;
	global $mysqli;
	global $page_;	
	
	$q = "
		SELECT 
			`book{$arch}`.`list_cnt_y`,
			`book{$arch}`.`buh`,
			`azs`.ragion_name
		FROM
			`book{$arch}`
		INNER JOIN 
			`azs`
		ON
			`book{$arch}`.`skladID` = `azs`.`skladID`			
		WHERE
			`book{$arch}`.`skladID` = '{$_SESSION['skladID']}'
		AND
			`book{$arch}`.`date` = '".($arch?substr($arch,1):date('Y'))."-12-31'
		LIMIT 1	
	";
// ==== /\ 	fix-0.0.8 `book{$arch}`.`date` = '2019-12-31' -> `book{$arch}`.`date` = '".($arch?substr($arch,1):date('Y'))."-12-31'
	if ($result = $mysqli->query($q))
	{
		if($result->num_rows > 0){
		
			include 'PHPExcel/IOFactory.php';
			$inputFileName = 'xls/kassa.xls';
			$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);
			
			while ($data = $result->fetch_object()){

				$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;
				$rendererLibrary = 'mpdf60';
				$rendererLibraryPath = '/var/www/html/'.$rendererLibrary;
				if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {
					die(
						'Пожалуйста, укажите имя библиотеки $rendererName и путь к ней $rendererLibraryPath' .
						PHP_EOL .
						' в зависимости от конкретной структуры каталогов'
					);
				}
				
				$objPHPExcel->removeSheetByIndex(0);
				$objWriter = new PHPExcel_Writer_PDF($objPHPExcel);		
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 10, $data->ragion_name.' АЗС '.$_SESSION['skladID']);
				$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 9, $data->ragion_name.' АЗС '.$_SESSION['skladID']);
				$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(4, 17, book_list_cnt($arch));
				$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(10, 21, $data->buh);
				$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(10, 23, $data->buh);

				
				
				$objWriter->writeAllSheets();
			
// =========== fix-0.0.8 ($arch?substr($arch,1):date('Y')) \/
				$ret = "pdf/kassa-{$_SESSION['skladID']}-title-".($arch?substr($arch,1):date('Y'))."-1.pdf";
				
				$objWriter->save($ret);		
				
				
			
			}
			
			
			return $ret;
		}
		else{
			$_SESSION["error"] = 'Нет РКО и ПКО для кассовой книги'.$q;
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
			return false;			
		}
		$result->close();
	}		
	else{
// =========== fix-0.0.8 ($arch?substr($arch,1):date('Y')) \/
		$_SESSION["error"] = 'Титульный лист можно создать только при наличии листа кассовой книги за 31.12.'.($arch?substr($arch,1):date('Y'));
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);		
		return false;
	}
}


function create_book_list($date, $status, $list, $start, $list_cnt_m, $list_cnt_y, $archive = '', $kassir){
	global $_SESSION;
	global $mysqli;
	global $page_;	

	$azs 		= $_SESSION['azs_name'];
	$rkopko		= '';
	$itog_p		= 0;
	$itog_r		= 0;
	$end		= 0;
	$buh		= $_SESSION['buh'];
	$skladID	= $_SESSION['skladID'];	
	$edit_time	= time();
	
	$empty		= true;




	$q = "
		SELECT 
			`pko{$archive}`.`number` as number,
			`pko{$archive}`.`oper` as oper,
			`pko{$archive}`.`sum` as sum,
			`pko{$archive}`.`sum10` as sum10,
			`pko{$archive}`.`type` as type,
			`pko{$archive}`.`kassir` as kassir,
			`pko{$archive}`.`ot` as vidat,
			`pko{$archive}`.`datetime` as datetime,
			`pko{$archive}`.`date` as date,
			`pko{$archive}`.`azs` as azs,
			`pko{$archive}`.`number_int` as number_int,
			`pko{$archive}`.`flag` as flag
		FROM 
			`pko{$archive}` 
		WHERE 
			`pko{$archive}`.`skladID`='{$skladID}'
			AND
			`pko{$archive}`.`date` = '{$date}'
			AND
			`pko{$archive}`.`status` = '1'		
		UNION			

		SELECT 
			`rko{$archive}`.`number` as number,
			`rko{$archive}`.`oper` as oper,
			`rko{$archive}`.`sum` as sum,
			`rko{$archive}`.`sum` as sum10,
			`rko{$archive}`.`type` as type,
			`rko{$archive}`.`kassir` as kassir,
			`rko{$archive}`.`vidat` as vidat,
			`rko{$archive}`.`datetime` as datetime,
			`rko{$archive}`.`date` as date,
			`rko{$archive}`.`azs` as azs,
			`rko{$archive}`.`number_int` as number_int,
			`rko{$archive}`.`flag` as flag
		FROM 
			`rko{$archive}`
		WHERE 
			`rko{$archive}`.`skladID`='{$skladID}'
			AND
			`rko{$archive}`.`date` = '{$date}'
			AND
			`rko{$archive}`.`status` = '1'
		ORDER BY
			`datetime` DESC, `number_int` DESC
		";	

					
	if ($result = $mysqli->query($q)){
		if($result->num_rows > 0){
			while ($row = $result->fetch_assoc()){
				if($row['type'] == 0){
					$itog_p = $itog_p + $row['sum'] + $row['sum10'];
				}
				if($row['type'] == 1){
					$itog_r = $itog_r + $row['sum'];
				}							
					
				$rows[]=$row;
			}

			$rkopko = base64_encode(serialize($rows));
			
			/*echo '<pre>';
			print_r($rows);
			echo '</pre>';
			echo '<br>';
			echo '<br>';
			echo '<br>';
			echo '<br>';
			
			$mass = unserialize(base64_decode($rkopko));
			echo '<pre>';
			print_r($mass);
			echo '</pre>';			
			exit;*/
			
			$end = $start + $itog_p - $itog_r;
			$end = round($end, 2);
			
			$list++;
			$list_cnt_m++;
			$list_cnt_y++;
		}
		else{
			//$_SESSION["error"] = 'Нет РКО и ПКО для кассовой книги за '.$date.'. Поэтому лист не создан.';
			$status = 11;
			$end = $start;
		}
		$result->close();
	}
	else{
		$_SESSION["error"] = "Ошибка запроса выбора РКО и ПКО. ".$mysqli->error;
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
		header("Location: kassa.php"); 
		exit();					
	}	

	$q = "
		INSERT INTO 
			`book{$archive}`(`azs`, `date`, `list`, `start`, `rkopko`, `itog_r`, `itog_p`, `end`, `kassir`, `buh`, `list_cnt_m`, `list_cnt_y`, `skladID`, `status`, `edit_time`) 
		VALUES 
			('{$azs}','{$date}','{$list}','{$start}','{$rkopko}','{$itog_r}','{$itog_p}','{$end}','{$kassir}','{$buh}','{$list_cnt_m}','{$list_cnt_y}','{$skladID}','{$status}','{$edit_time}')
	";
	
		if ($result = $mysqli->query($q)){

			if(!empty($archive)){	

				if(transport($skladID)){
				
				}
			}
			return true;
		}
		else{
			$_SESSION["error"] = 'Ошибка при занеседии данных кассовой книги в БД. '.$q;
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);		
			header("Location: kassa.php?arch=".$archive); 
			exit();								
		}		

	return false;
}

function insert_pko($number,$number_int,$datetime,$date,$time,$azs,$skladID,$sum,$nds,$sum10,$nds10,$ot,$schet,$creator,$osnov,$buh,$dir,$dol,$pril,$ret,$smena,$goods=0,$arch=''){

				global $_SESSION;
				global $mysqli;
				global $page_;
				global $type;
				
				if (($sum < 0) || ($sum10 < 0) || ($sum + $sum10 <= 0)) {
						$_SESSION["error"] = $_SESSION["alert"] = 'Сумма документа <b>'.($sum<0?$sum." (НДС 20%)":($sum10<0?$sum10." (НДС 10%)":"0 рублей")).'</b> не может быть меньше, либо равна нулю';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						foreach(glob("export/import/{$skladID}/*") as $file){
							if(file_exists($file))
								unlink($file);
						}
						return false;
				}
				$q = "INSERT INTO `{$type}` (`number`,`number_int`,`datetime`,`date`,`time`,`azs`,`skladID`,`sum`,`nds`,`sum10`,`nds10`,`ot`,`oper`,`creator`,`osnov`,`buh`,`dir`,`dol`,`pril`,`ret`,`smena`,`goods`,`upl_type`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				
				$datetime_S = date('Y-m-d H:i:s');
				
				if ($result = $mysqli->prepare($q))
				{
				
					$date_interval_1 = new DateTime( date("Y-m-01 00:00:00"));
					$date_interval_2 = new DateTime( date("Y-m-01 03:00:00"));
					
					// add check month is JAN
					if($datetime >= $date_interval_1 && $datetime <= $date_interval_2){
						$datetime->modify('-1 Day');
						
						$datetime_S = $datetime->format('Y-m-d 23:55:00');
						$date = $datetime->format('Y-m-d');
						$time = $datetime->format('23:55:00');
					}
					else{
						$datetime_S = $datetime->format('Y-m-d H:i:s');
						$date = $datetime->format('Y-m-d');
						$time = $datetime->format('H:i:s');						
					}
					
					
					
/*					$q = "
						SELECT 
							`azs`.`replacement`
						FROM 
							`azs` 
						WHERE 
							`skladID`='{$_SESSION["skladID"]}' 
						LIMIT 
							1";
					if ($result0 = $mysqli->query($q))
					{
						if($result0->num_rows > 0){
							$obj = $result0->fetch_object();
				
*/							
						//	if($obj->replacement){
					$q1 = "
					SELECT * FROM `replacements`
						WHERE `skladID`='{$_SESSION['skladID']}'
							AND `from`<='".$date."' AND `to`>='".$date."' 
						LIMIT 1
					";
/*								$q1 = "
									SELECT
										*
									FROM	
										`replacements`
									WHERE
										`replacements`.`id` = '{$obj->replacement}'
									LIMIT
										1
								";			
*/								if ($result1 = $mysqli->query($q1)){
									if($result1->num_rows > 0){
										$data1 = $result1->fetch_object();
										//	if($date >= $data1->from && $date <= $data1->to){
												$buh = $data1->fio;
												$dir = $data1->fio;
												$dol = $data1->position;
										//	}
									}
								}				
						//	}
				//		}
				//	}
					 

					
					$upl_type = isset($_POST['manual'])?'M':'A';
					$result->bind_param("sssssssssssssssssssssss",$number,$number_int,$datetime_S,$date,$time,$azs,$skladID,$sum,$nds,$sum10,$nds10,$ot,$schet,$creator,$osnov,$buh,$dir,$dol,$pril,$ret,$smena,$goods,$upl_type);
					if($result->execute()) {
						
						$params['number'] = $number;
						$params['skladID'] = $skladID;
						$params['sum'] = $sum;
						$params['sum10'] = $sum10;
						$params['nds'] = $nds;
						$params['nds10'] = $nds10;
						$params['ot'] = $ot;
						$params['datetime'] = $datetime_S;
						$params['oper'] = $schet;
						
						if($schet == '50.02'){
							$params['osnov'] = 'Розничная выручка (ККТ №'.$osnov.')';
						}
						if($schet == '62.01, 62.02' || $schet == '62.02'){
							$params['osnov'] = 'Излишняя оплата (подлежащая возврату покупателю) при совершении розничной реализации на ТЗК';
						}
						
						if($schet == '91.01'){
							$params['osnov'] = 'Излишки наличных денежных средств в кассе АЗС №'.$_SESSION['skladID'];
						}
						if($schet == '50.02')
							$params['pril'] = 'Отчет о закрытии смены №'.$pril;

						
						if($schet == '91.01')	
							$params['pril'] =  $pril;						
						
						toXML($params,$type);

						return true;
					
					}
					else{
						$_SESSION["error"] = 'Проблема '.$mysqli->error.' '.func_num_args();
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					}
					$result->close();
				}
				else {				
					$_SESSION["error"] = 'Проблема 2 '.$mysqli->error.' Значение '.$creator;
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				}
				
				/*if($_pdf = create_PDF($type, $number)){
					header("Location: action.php?type={$type}"); 
				}
				else{
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: actions.php?type={$type}"); 
					exit();				
				}	*/
				
	return false;
}

function insert_rko($number,$number_int,$datetime,$date,$time,$azs,$skladID,$sum,$oper,$vidat,$osnov,$pril,$po,$kassir,$buh,$dir,$dol,$arch='',$pasport=''){

				global $_SESSION;
				global $mysqli;
				global $page_;
				global $type;

				$q = "INSERT INTO `{$type}` (`number`,`number_int`,`datetime`,`date`,`time`,`azs`,`skladID`,`sum`,`oper`,`vidat`,`osnov`,`pril`,`po`,`kassir`, `pasport`, `buh`,`dir`,`dol`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
				
				$pasport = $mysqli->real_escape_string($pasport);
				
				if ($result = $mysqli->prepare($q))
				{
/*				
					$q = "
						SELECT 
							`azs`.`replacement`
						FROM 
							`azs` 
						WHERE 
							`skladID`='{$_SESSION["skladID"]}' 
						LIMIT 
							1";
					if ($result0 = $mysqli->query($q))
					{
						if($result0->num_rows > 0){
							$obj = $result0->fetch_object();
*/							
						//	if($obj->replacement){
					$q1 = "
					SELECT * FROM `replacements`
						WHERE `skladID`='{$_SESSION['skladID']}'
							AND `from`<='".$date."' AND `to`>='".$date."' 
						LIMIT 1
					";
/*								$q1 = "
									SELECT
										*
									FROM	
										`replacements`
									WHERE
										`replacements`.`id` = {$obj->replacement}
									LIMIT
										1
								";			
*/								if ($result1 = $mysqli->query($q1)){
									if($result1->num_rows > 0){
										$data1 = $result1->fetch_object();
										//	if($date >= $data1->from && $date <= $data1->to){
												$buh = $data1->fio;
												$dir = $data1->fio;
												$dol = $data1->position;
										//	}
									}
								}				
						//	}
				//		}
				//	}				
				
					$result->bind_param("ssssssssssssssssss",$number,$number_int,$datetime->format('Y-m-d H:i:s'),$date,$time,$azs,$skladID,$sum,$oper,$vidat,$osnov,$pril,$po,$kassir,$pasport,$buh,$dir,$dol);
					if($result->execute()) {
						
						
						
						$params['number'] = $number;																	// НОМЕР ДОКУМЕНТА
						$params['skladID'] = $skladID;																	// НОМЕР АЗС
						$params['sum'] = $sum;																			// СУММА
						$params['oper'] = $oper;																		// КОРР. СЧЕТ
						$params['datetime'] = $datetime->format('Y-m-d H:i:s');											// ДАТА ДОКУМЕНТА
						
						// ********************************************* ИНКАССАЦИЯ *******************************************
						if($oper == '57.3'){
							$flag_date = new DateTime('2018-05-01');
							if($datetime < $flag_date){
								$params['vidat'] = $_SESSION["ink"];													// ВЫДАТЬ 			= Банк
								$params['osnov'] = $osnov;																// ОСНОВАНИЕ		= Сдача выручки в банк 
								$params['pril']	 = 'Квитанция к сумке №'.$pril;											// ПРИЛОЖЕНИЕ		= Квитанция к сумке №
							}
							else{
								$params['vidat'] = $vidat;																// ВЫДАТЬ 			= Кассир
								if($osnov == 'Сдача выручки в банк'){
									$params['osnov'] = $osnov.' ('.$_SESSION["ink"].')';								// ОСНОВАНИЕ		= Сдача выручки в банк (Банк)
									$params['pril']	 = 'Квитанция к сумке №'.$pril.' от '.$datetime->format("d.m.Y");	// ПРИЛОЖЕНИЕ		= Квитанция к сумке №_____ (от ДАТА)
								}
								elseif (mb_substr($obj->osnov,0,38) == 'Сдача выручки в банк '){
									$params['osnov'] = $osnov;								// ОСНОВАНИЕ		= Сдача выручки в банк (Банк)
									$params['pril']	 = 'Квитанция к сумке №'.$pril.' от '.$datetime->format("d.m.Y");	// ПРИЛОЖЕНИЕ		= Квитанция к сумке №_____ (от ДАТА)
								}
								else{
									$params['osnov'] = $osnov;															// ОСНОВАНИЕ		= Пополнение основной кассы офиса из кассы АЗС
									$params['pril']	 = $pril;															// ПРИЛОЖЕНИЕ		= Приложение
								}
								$params['po']	 = $pasport;															// ПО				= Паспорт кассира						
							}
						}
						// ****************************************************************************************************
						
						// *****************************************  Возврат покупателю **************************************
						if($oper == '62.01,62.02'){
							$params['vidat'] = $vidat;																	// ВЫДАТЬ 			= ФИО клиента
							$params['osnov'] = $osnov;																	// ОСНОВАНИЕ		= Возврат оплаты за товар покупателю
							$params['pril']	 = $pril;																	// ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
							$params['po']	 = $po;																		// ПО				= Паспортные данные клиента							
						}
						// ****************************************************************************************************

						// *********************************************** Недостача ******************************************
						if($oper == '94.05.1'){
							$params['vidat'] = $kassir;																	// ВЫДАТЬ 			= ФИО кассира
							$params['osnov'] = $osnov;																	// ОСНОВАНИЕ		= Недостача наличных денежных средств в кассе АЗС №___
							$params['pril']	 = $pril;																	// ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
						}	
						// ****************************************************************************************************
						

						toXML($params,$type);
						
						return true;
						
					}
					else{
						$_SESSION["error"] = 'Проблема 1 '.$mysqli->error;
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: action.php?type={$type}"); 
						exit();						
					}
					
					$result->close();

					$row_id = $mysqli->insert_id;

				}
				else {				
					$_SESSION["error"] = 'Проблема 2 '.$mysqli->error;
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();	
				}

				/*if($_pdf = create_PDF($type, $number)){
					header("Location: action.php?type={$type}"); 
					exit();		
				}
				else{
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: actions.php?type={$type}"); 
					exit();						
				}*/
		return false;
}


function get_document_date($type,$azs_id,$doc_id = 0){
	global $_SESSION;
	global $mysqli;
	global $page_;
	$ret = array();
	
	if($doc_id == 0)
		$q = "
			SELECT 
				`date`, 
				`time`, 
				`datetime` 
			FROM 
				`{$type}` 
			WHERE
				`skladID` = '{$azs_id}'
			ORDER BY 
				`datetime` DESC 
			LIMIT 1		
		";	
	else
		$q = "
			SELECT 
				`date`, 
				`time`, 
				`datetime` 
			FROM 
				`{$type}` 
			WHERE 
				`id` = '{$doc_id}'
			LIMIT 
				1
		";
	
	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
			while ($data = $result->fetch_object()){
				$ret['date'] = $data->date;
				$ret['time'] = $data->time;
				$ret['datetime'] = $data->datetime;
			}
			return $ret;
		}			
	}			

	return false;
}


function chk_type($type){

// ======== fix-0.0.8	
	$types = ['rko','pko'];//,'rko_2015','pko_2015','rko_2016','pko_2016','rko_2017','pko_2017','rko_2018','pko_2018','rko_2019','pko_2019');
	$y = date('Y') - 1;
	do {
		$types[] = 'rko_'.$y;
		$types[] = 'pko_'.$y;
	}
	while ($y-- > 2015);
	
	if(in_array($type, $types))
		return $type;
	


return false;
	
}

function get_document_id($number,$type){
	global $_SESSION;
	global $mysqli;
	global $page_;
	
	if(chk_type($type)){
	
		if (preg_match("/^[0-9]{2,2}-[0-9]{4,4}-[0-9]{5,5}$/",$number))
		{
			$q = "
				SELECT 
					`id`
				FROM 
					`{$type}` 
				WHERE 
					`number` = '{$number}'
				LIMIT 
					1
			";
			
			if ($result = $mysqli->query($q)){	
				if($result->num_rows > 0){
					while ($data = $result->fetch_object()){
						return $data->id;
					}
					return $ret;
				}			
			}		

		}
	}
	
	
	return false;
	
}





function get_document_number($id,$type){
	global $_SESSION;
	global $mysqli;
	global $page_;
	$ret = array();
	
	
	$q = "
		SELECT 
			`number`, 
			`number_int`
		FROM 
			`{$type}` 
		WHERE 
			`id` = '{$id}'
		LIMIT 
			1
	";
	
	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
			while ($data = $result->fetch_object()){
				$ret['number'] = $data->number;
				$ret['number_int'] = $data->number_int;
			}
			return $ret;
		}			
	}			

	return false;
	
}


function delete_document($id,$type){
	global $_SESSION;
	global $mysqli;
	global $page_;
	
		$q = "
			DELETE FROM 
				`{$type}` 
			WHERE 
				`id` = '{$id}'
		";			
		
		if ($result = $mysqli->query($q)){
			return true;
		}
		else{
			$_SESSION["error"] = 'Ошибка при удалении документа';
			setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
		}	
	
	return false;
}

function document_number_reindex($number_int,$type,$azs_id,$operation){
	global $_SESSION;
	global $mysqli;
	global $page_;
	
	$q = "
		UPDATE `{$type}` 
			SET 
				`number_int` = `number_int` {$operation} 1,
				`number`= CONCAT(LPAD('{$_SESSION["region"]}', 2 , '0'), '-', LPAD(`skladID`, 4 , '0'), '-',  LPAD(`number_int`, 5 , '0'))
			WHERE 
				`number` LIKE CONCAT( LPAD(  '{$_SESSION["region"]}', 2,  '0' ) ,  '-%' ) 
			AND
				`number_int` > '{$number_int}'
			AND
				`skladID` = '{$azs_id}'
	";
	
	
	if ($result = $mysqli->query($q)){
		return true;
	}
	else{
		$_SESSION["error"] = 'Ошибка при переиндексации документов';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	}		

	return false;
}

function document_exist($type,$id){
	global $_SESSION;
	global $mysqli;
	global $page_;

	
	$q = "
		SELECT
			count(*) as cnt
		FROM
			`{$type}`
		WHERE
			`id` = '{$id}'
	";
	if ($result = $mysqli->query($q)){	
		$data = $result->fetch_object();
		if($data->cnt > 0)
			return true;
	}
	

	return false;
}


function azs_exist($id){
	global $_SESSION;
	global $mysqli;
	global $page_;

	if (is_numeric($id) && preg_match("/^[0-9]+$/i",$id)){
	
		$q = "
			SELECT
				count(*) as cnt
			FROM
				`azs`
			WHERE
				`skladID` = '{$id}'
		";
		
		
		
		if ($result = $mysqli->query($q)){	
			$data = $result->fetch_object();
			if($data->cnt > 0)
				return $id;
		}
	}

	return false;
}

function document_move($type,$id,$id_after,$azs_id){
	global $_SESSION;
	global $mysqli;
	global $page_;
	
	if(document_exist($type, $id) && document_exist($type, $id_after)){
		if($number_id = get_document_number($id,$type) && $number_id_after = get_document_number($id_after,$type)){
			if(document_number_reindex($type,$number_id_after,$azs_id,'+')){
				if($after_date = get_document_date($id_after,$type)){
					$new_param['new_datetime'] = new DateTime($after_date['datetime']);
					$new_param['new_date'] = $new_param['new_datetime']->format('Y-m-d');;
					$new_param['new_time'] = $new_param['new_datetime']->modify('+1 second');
					$new_param['new_time'] = $new_param['new_datetime']->format('H:i:s');
					$new_param['new_datetime'] = $new_param['new_datetime']->format('Y-m-d H:i:s');
					
					$new_param['new_number_int'] = $number_id_after['number_int'] + 1;
					$new_param['new_number'] = str_pad($_SESSION["region"], 2, '0', STR_PAD_LEFT).'-'.str_pad($azs_id, 4, '0', STR_PAD_LEFT).'-'.str_pad($new_param['new_number_int'], 5, '0', STR_PAD_LEFT);
					
					$q = "
						UPDATE
							`{$type}` 
						SET 
							`number_int` = '{$new_param['new_number_int']}',
							`number`= '{$new_param['new_number']}',
							`date`= '{$new_param['new_date']}',
							`time`= '{$new_param['new_time']}',
							`datetime`= '{$new_param['new_datetime']}'
						WHERE 
							`id` = '{$id}'						
					";
					echo $mysqli->affected_rows;
					/*if ($result = $mysqli->query($q)){
						
						return $q;
					}
					else{
						echo $mysqli->error;
							
					}*/
				}
			}
		}
		else{
			return 'err';
		}
	}
	else
		return 'err1';

	return false;
}

function toXML($params,$type){

	global $_SESSION;
	global $mysqli;
	global $page_;
	
	if (substr($type,0,3) == 'pko')
		$type = 'pko';
	if (substr($type,0,3) == 'rko')
		$type = 'rko';

/* ====== fix-0.0.8	
	if($type == 'pko_2015')
		$type = 'pko';
	
	if($type == 'pko_2016')
		$type = 'pko';
		
	if($type == 'pko_2017')
		$type = 'pko';
		
	if($type == 'pko_2018')
		$type = 'pko';
		
	if($type == 'pko_2019')
		$type = 'pko';
		
	if($type == 'rko_2015')
		$type = 'rko';		
	
	if($type == 'rko_2016')
		$type = 'rko';		
	
	if($type == 'rko_2017')
		$type = 'rko';		
	
	if($type == 'rko_2018')
		$type = 'rko';		

	if($type == 'rko_2019')
		$type = 'rko';		
*/
	$dom = new domDocument("1.0", "utf-8");
	$database = $dom->createElement("database"); 
	$database->setAttribute("name", "kassa");
	$dom->appendChild($database);
	
	$table = $dom->createElement("table"); 
	$table->setAttribute("name", $type);
	$database->appendChild($table);
	
	foreach ($params as $key => $value) {
		$column = $dom->createElement("column", $value);
		$column->setAttribute("name", $key);
		$table->appendChild($column); 
	}
	
		$column = $dom->createElement("column", $_SESSION['user_id']);
		$column->setAttribute("name", "user");
		$table->appendChild($column); 	
	
	$datetime = new Datetime($params['datetime']);
	$dom->save("export/{$type}_{$params['number']}_{$params['skladID']}_".$datetime->format('YmdHis').".xml"); 	
	return true;
}

function export_period($number_int,$type,$azs_id){

	global $_SESSION;
	global $mysqli;
	global $page_;
	//return true; //!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
	$q = "
		SELECT
			*
		FROM
			`{$type}`
		WHERE
			`number_int` >= '{$number_int}'
		AND
			`status` = '1'
		AND
			`skladID` = '{$azs_id}'
	";	
	
	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
		
			while ($data = $result->fetch_object()){


				if (substr($type,0,3) == 'pko') {// fix-0.0.8 $type == 'pko' || $type == 'pko_2015' || $type == 'pko_2016' || $type == 'pko_2017' || $type == 'pko_2018' || $type == 'pko_2019'){
					$params['number'] = $data->number;
					$params['skladID'] = $data->skladID;
					$params['sum'] = $data->sum;
					$params['sum10'] = $data->sum10;
					$params['nds'] = $data->nds;
					$params['nds10'] = $data->nds10;
					$params['oper'] = $data->oper;
					$params['ot'] = $data->ot;
					if($data->oper == '50.02'){
						$params['osnov'] = 'Розничная выручка (ККТ №'.$data->osnov.')';
						$params['pril'] = 'Отчет о закрытии смены №'.$data->pril;
					}
					if($data->oper == '91.01'){
						$params['osnov'] = $data->osnov;
						$params['pril'] = $data->pril;		
					}			
					$datetime = new DateTime($data->datetime);
					$params['datetime'] = $datetime->format('Y-m-d H:i:s');
				}
				elseif (substr($type,0,3) == 'rko') { // fix-0.0.8 $type == 'rko' || $type == 'rko_2015' || $type == 'rko_2016' || $type == 'rko_2017' || $type == 'rko_2018' || $type == 'rko_2019'){

						$params['number'] = $data->number;																// НОМЕР ДОКУМЕНТА
						$params['skladID'] = $data->skladID;															// НОМЕР АЗС
						$params['sum'] = $data->sum;																	// СУММА
						$params['oper'] = $data->oper;																	// КОРР. СЧЕТ
						$datetime = new DateTime($data->datetime);
						$params['datetime'] = $datetime->format('Y-m-d H:i:s');											// ДАТА ДОКУМЕНТА
						
						// ********************************************* ИНКАССАЦИЯ *******************************************
						if($data->oper == '57.3'){
							$flag_date = new DateTime('2018-05-01');
							if($datetime < $flag_date){
								$params['vidat'] = $_SESSION["ink"];													// ВЫДАТЬ 			= Банк
								$params['osnov'] = $data->osnov;														// ОСНОВАНИЕ		= Сдача выручки в банк 
								$params['pril']	 = 'Квитанция к сумке №'.$data->pril;									// ПРИЛОЖЕНИЕ		= Квитанция к сумке №
							}
							else{
								$params['vidat'] = $data->vidat;														// ВЫДАТЬ 			= Кассир
								$params['osnov'] = $data->osnov.' ('.get_bank($azs_id).')';								// ОСНОВАНИЕ		= Сдача выручки в банк (Банк)
								$params['pril']	 = 'Квитанция к сумке №'.$data->pril.' от '.$datetime->format("d.m.Y");	// ПРИЛОЖЕНИЕ		= Квитанция к сумке №_____ (от ДАТА)
								$params['po']	 = $data->pasport;														// ПО				= Паспорт кассира						
							}
						}
						// ****************************************************************************************************
						
						// *****************************************  Возврат покупателю **************************************
						if($data->oper == '62.01,62.02'){
							$params['vidat'] = $data->vidat;															// ВЫДАТЬ 			= ФИО клиента
							$params['osnov'] = $data->osnov;															// ОСНОВАНИЕ		= Возврат оплаты за товар покупателю
							$params['pril']	 = $data->pril;																// ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
							$params['po']	 = $data->po;																// ПО				= Паспортные данные клиента							
						}
						// ****************************************************************************************************

						// *********************************************** Недостача ******************************************
						if($data->oper == '94.05.1'){
							$params['vidat'] = $data->vidat;															// ВЫДАТЬ 			= ФИО кассира
							$params['osnov'] = $data->osnov;															// ОСНОВАНИЕ		= Недостача наличных денежных средств в кассе АЗС №___
							$params['pril']	 = $data->pril;																// ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
						}	
						// ****************************************************************************************************				
						
				}
				
				
				toXML($params,$type);

			}
			return true;
		}
	}	

	return false;
}


function delete_start_end($str,$symbol='') 
{ 
    return($strpos=mb_strpos($str,$symbol))!==false?mb_substr($str,0,$strpos+4,'utf8'):$str;
} 

function amount_summ($sum, $abs = false) {

	$sum = trim($sum);

	$sum = str_replace(',','.',$sum);
	$sum = str_replace('-','.',$sum);
	$sum = str_replace(' ','.',$sum);
	$sum = str_replace('/','.',$sum);
	
	$sum = round($abs?abs((float)$sum):(float)$sum,2);
	
	return $sum;
}

function delete_book_from_date($date, $azs_id, $arch='', $flag=0){

	global $_SESSION;
	global $mysqli;
	global $page_;

// =============== fix-0.0.14	
//	if($flag){
//		$q = "
//			DELETE FROM 
//				`book` 
//			WHERE 
//				`book`.`skladID` = '{$azs_id}'	
//			AND
//				`book`.`date` >= '{$date}'					
//		";		
//	}
//	else{
		$q = "
			DELETE FROM 
				`book{$arch}` 
			WHERE 
				`book{$arch}`.`date` >= '{$date}'
			AND
				`book{$arch}`.`skladID` = '{$azs_id}'		
		";
//	}
	
/* =============== fix-0.0.14	
	$q_err = "
		INSERT INTO `errors`(
			`id`, 
			`skladID`, 
			`user`, 
			`var_date`, 
			`var_azs_id`, 
			`var_arch`, 
			`var_flag`, 
			`query`
		) 
		VALUES (
			NULL,
			'{$azs_id}',
			'{$_SESSION["user_id"]}',
			'{$date}',
			'{$azs_id}',
			'{$arch}',
			'{$flag}',
			'{$mysqli->real_escape_string($q)}'
		)	
	";
	
	if ($result2 = $mysqli->query($q_err)){
		
	}
	else{
		echo $q_err;
		exit;
	}
========== END fix-0.0.14 */
	
	if ($result1 = $mysqli->query($q))
		return true;
	else
		return false;
}

function insert_number($type, $azs_id, $datetime){

	global $_SESSION;
	global $mysqli;
	global $page_;
	

	$q = "
		SELECT  
			`number_int` ,  
			`id` 
		FROM  
			`{$type}` 
		WHERE  
			`skladID` =  '{$azs_id}'
		AND  
			`datetime` >  '{$datetime}'
		ORDER BY  
			`datetime` 
		LIMIT 
			1
	";
	
	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
		
			while ($data = $result->fetch_object()){

				$_SESSION["number_int"] = $data->number_int;
				$number = str_pad($_SESSION["region"], 2, '0', STR_PAD_LEFT).'-'.str_pad($azs_id, 4, '0', STR_PAD_LEFT).'-'.str_pad($data->number_int, 5, '0', STR_PAD_LEFT);
				return $number;
			
			}
			
		}
	}	
	
return false;
}

function get_bank($azs_id){

	global $_SESSION;
	global $mysqli;
	global $page_;
	
	$q = "
		SELECT 
			`ink`
		FROM 
			`azs` 
		WHERE 
			`skladID` = '{$azs_id}'
		LIMIT
			1
	";

	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
		
			while ($data = $result->fetch_object()){
				return $data->ink;
			}
			
		}
	}

	return false;
	
}



function get_buh_fio($azs_id){

	global $_SESSION;
	global $mysqli;
	global $page_;
	
	$q = "
		SELECT 
			`buh`
		FROM 
			`azs` 
		WHERE 
			`skladID` = '{$azs_id}'
		LIMIT
			1
	";

	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
		
			while ($data = $result->fetch_object()){
				return $data->buh;
			}
			
		}
	}

	return false;
	
}

function get_dir_pos($azs_id){

	global $_SESSION;
	global $mysqli;
	global $page_;
	
	$q = "
		SELECT 
			`position`
		FROM 
			`azs` 
		WHERE 
			`skladID` = '{$azs_id}'
		LIMIT
			1
	";

	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
		
			while ($data = $result->fetch_object()){
				return $data->position;
			}
			
		}
	}

	return false;
	
}

function chk_close_period($doc_date, $azs_id = 0){

	global $_SESSION;
	global $mysqli;
	global $page_;

	$q = "
		SELECT 
			*
		FROM 
			`close_period`
	";	
	
	if($azs_id == 0) $azs_id = $_SESSION["skladID"];
	
	
	$ret = true;
	
	$date = new datetime($doc_date);
	
	$date = $date->format('Y-m-d');

	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
		
			while ($data = $result->fetch_object()){
				if($date >= $data->start && $date <= $data->end)
					$ret = false;
			}
			
		}
		else
			$ret = true;
	}
	
	$q2 = "
		SELECT 
			*
		FROM 
			`open_period`
		WHERE
			`azs_id` = '{$azs_id}'
	";		
	
	if ($result2 = $mysqli->query($q2)){
		if($result2->num_rows > 0){
			while ($data2 = $result2->fetch_object()){
				if($date >= $data2->start && $date <= $data2->end)
					$ret = true;
			}
			
		}
	}
	return $ret;
}

function clear_folder(){

	global $_SESSION;
	global $mysqli;
	global $page_;
	
	


return false;
}

function transport($id){
	
	//return false;

	global $_SESSION;
	global $mysqli;
	global $page_;
	
// ======= fix-0.0.8
	$cy = date('Y');
	$py = date('Y') - 1;
//   \/\/\/\/\/\/\/\/\/\/\/\/
	
	$q = "
		SELECT 
			*
		FROM 
			`book_{$py}`
		WHERE 
			`date`=(
                SELECT 
                  	MAX(`date`) 
        		FROM 
					`book_{$py}`
				WHERE 
					`skladID` = '{$id}'	
				and
					`date` < '{$cy}-01-01'
            )
		AND
			`skladID` = '{$id}'		
	";	

	
	if ($result = $mysqli->query($q)){	
		if($result->num_rows > 0){
		
			while ($data = $result->fetch_object()){

				if(delete_book_from_date($py.'-12-31',$id,'',1)){
					$q1 = "
						INSERT INTO 
							`book`(`azs`, `date`, `list`, `start`, `rkopko`, `itog_r`, `itog_p`, `end`, `kassir`, `buh`, `list_cnt_m`, `list_cnt_y`, `skladID`, `status`, `edit_time`) 
						VALUES 
							('АЗС {$id}','{$py}-12-31','0','0','','0','0','{$data->end}','','','0','0','{$id}','10','')
					";
					

					
						if ($result1 = $mysqli->query($q1)){
							return true;
						}
						else{
							$_SESSION["error"] = 'Ошибка при занеседии данных кассовой книги в БД. '.$q;
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);		
							header("Location: kassa.php"); 
							exit();								
						}						
				}
			
			}
			
		}
	}	


return false;
}

function getPasport($fio){
	global $_SESSION;
	global $mysqli;
	global $page_;

	
		$q = "
			SELECT
				pasport
			FROM
				`users`
			WHERE
				`fio` = '{$fio}'
			AND
				`azs_id` = '{$_SESSION['skladID']}'
			LIMIT 1
		";
		
		
		
		if ($result = $mysqli->query($q)){	
			$data = $result->fetch_object();
			return $data->pasport;
		}
	return false;	
}
	function curl_request($url, $dt = null, $method = "POST", $cookie = '', $headers = '') {
		if (!function_exists('curl_init'))
			throw new Exception('CURL module not installed');
		$ch = curl_init();
		if ($headers) {
			if (!is_array($headers))
				$headers = explode("\r\n",$headers);
			if ($dt)
				$headers[] = 'Content-Length: '.strlen($dt);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_HEADER, 0);
		}
		if ($cookie) {
			if (!is_array($cookie))
				$cookie = explode("\r\n",$cookie);
			curl_setopt($ch, CURLOPT_COOKIE, $cookie);
		}
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ($dt) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $dt);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);
		if (($ret = curl_exec($ch)) === false) {
			$error = curl_error($ch);
		}
		curl_close($ch);
		return isset($error)?$error:(array)json_decode($ret);
	}
