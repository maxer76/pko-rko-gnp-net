<?php
session_start();
set_time_limit(0);
require_once('php/functions.php');

$operation = '';

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash'])){
    if(usrchk($_SESSION['user_id'],$_SESSION['user_hash'])) {

	if (preg_match("/^(pko|rko)(_\d{4})?|kassa$/",$_GET['type'],$matches)
		|| preg_match("/^(pko|rko)(_\d{4})?|kassa$/",$_POST['type'],$matches)) {
		$cy = date('Y');
		if (!empty($matches[2]))
			$y = (int)substr($matches[2],1);
		else
			$y = 0;
		$operation = $matches[1];
		switch ($operation) {
			case 'rko': if (!$y || ($y == $cy)) {
							$heading = 'РКО';
							$type = 'rko';
						}
						else {
							$heading = 'Архив РКО '.$y;
							$type = 'rko_'.$y;
							$arch_year = $y;
						}
						break;
			case 'pko': if (!$y || ($y == $cy)) {
							$heading = 'ПКО';
							$type = 'pko';
						}
						else {
							$heading = 'Архив ПКО '.$y;
							$type = 'pko_'.$y;
							$arch_year = $y;
						}
						break;
			case 'kassa': $heading='Касса';
							$type='kassa';
							break;
		}
	}
	else {
		$_SESSION["error"] = 'Выберите правильное действие'; setlog($_SESSION['user_id'],$_SESSION["error"],$page_);header("Location: actions.php"); exit();
	}
/* ====== fix-0.0.8
		switch ($_POST['type']){
				case 'pko': $heading='ПКО'; $type=$_POST['type']; break;
				case 'rko': $heading='РКО'; $type=$_POST['type']; break;
				case 'pko_2015': $heading='Архив ПКО 2015'; $type=$_POST['type']; break;
				case 'pko_2016': $heading='Архив ПКО 2016'; $type=$_POST['type']; break;
				case 'pko_2017': $heading='Архив ПКО 2017'; $type=$_POST['type']; break;
				case 'pko_2018': $heading='Архив ПКО 2018'; $type=$_POST['type']; break;
				case 'pko_2019': $heading='Архив ПКО 2019'; $type=$_POST['type']; break;
				case 'rko_2015': $heading='Архив РКО 2015'; $type=$_POST['type']; break;
				case 'rko_2016': $heading='Архив РКО 2016'; $type=$_POST['type']; break;
				case 'rko_2017': $heading='Архив РКО 2017'; $type=$_POST['type']; break;
				case 'rko_2018': $heading='Архив РКО 2018'; $type=$_POST['type']; break;
				case 'rko_2019': $heading='Архив РКО 2019'; $type=$_POST['type']; break;
				case 'kassa': $heading='Касса'; $type=$_POST['type']; break;
				default: $_SESSION["error"] = 'Выберите правильное действие'; setlog($_SESSION['user_id'],$_SESSION["error"],$page_);header("Location: action.php"); exit();
			}		
*/			

			
	  if($_POST['form_id'] == $_SESSION['form_id'] ){
	  
		$_SESSION['form_id'] = 0;
	  
		if($operation == 'pko')	
		{
			if(!isset($_POST['manual'])){
				
				$q = "
					SELECT
						`serial`
					FROM
						`kkm`
					WHERE
						`azs` = '{$_SESSION["skladID"]}'
					ORDER BY
						`serial`
				";	
				
				if ($result = $mysqli->query($q)){	
					if($result->num_rows > 0){
						$kkm_array = array();
						while ($data = $result->fetch_object()){
							if (!array_key_exists('kkm__'.trim($data->serial), $_POST)) {
								$_SESSION["error"] = 'Один из ККМ не найдет в базе';
								setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
								$result->close();
								header("Location: actions.php"); 
								exit();	
							}
							else{
								$kkm_array[trim($data->serial)]['pril'] = $_POST['kkm__'.trim($data->serial)];
							}
						}					
					}
					else{
						$_SESSION["error"] = 'Для АЗС с №'.$_SESSION["skladID"].' не добавлены ККМ';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						$result->close();
						header("Location: actions.php"); 
						exit();		
					}
				}
				else
				{
					$_SESSION["error"] = 'Ошибка при получении списка ККМ';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: actions.php"); 
					exit();	
				}	

				$kassir = '';
				$azs='';	

				$log = '';
				$error = 0;
				
				
				$uploaddir = "export/import/{$_SESSION['skladID']}/";
				$uploadfile = $_POST['filename'];			

				$zip = new ZipArchive;
				$res = $zip->open($uploadfile);
				
				if ($res !== TRUE) 
				{
					$_SESSION["error"] = '<p>Ошибка открытия архива: '.$zip->getStatusString().'</p>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();						
				} 
				
				if($zip->extractTo($uploaddir) !== TRUE){
					$_SESSION["error"] = '<p>Ошибка распаковки файла</p>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();						
				}
				$zip->close();
				unset($zip);

				$GSMARCHIVE = 'GSMARCHIVE_';										
				$GOODSSALE = 'GOODSSALE_';										
				$GOODSSALEATTR = 'GOODSSALEATTR_';
				$SHIFT = 'SHIFT_';										
				$SHIFTHOSTS = 'SHIFTHOSTS_';										
				$GOODS = 'GOODS_';	

				$GSMARCHIVE_file = false;			
				$GOODSSALE_file = false;
				$GOODSSALEATTR_file = false;
				$SHIFT_file = false;
				$SHIFTHOSTS_file = false;
				$GOODS_file = false;	

			
				$filter = $uploaddir.'{'.$GSMARCHIVE.'*.XML,'.$GOODSSALE.'*.XML,'.$GOODSSALEATTR.'*.XML,'.$SHIFT.'*.XML,'.$SHIFTHOSTS.'*.XML,'.$GOODS.'*.XML}';
				
				foreach(glob($filter, GLOB_BRACE) as $file){					
					
					$pos = strripos($file, $GSMARCHIVE);
					if($pos !== false){
						$GSMARCHIVE_file = $file;
					} 

					$pos = strripos($file, $GOODSSALE);
					if($pos !== false){
						$GOODSSALE_file = $file;
					} 

					$pos = strripos($file, $GOODSSALEATTR);
					if($pos !== false){
						$GOODSSALEATTR_file = $file;
					} 

					$pos = strripos($file, $SHIFT);
					if($pos !== false){
						$SHIFT_file = $file;
					} 

					$pos = strripos($file, $SHIFTHOSTS);
					if($pos !== false){
						$SHIFTHOSTS_file = $file;
					} 
					
					$pos = strripos($file, $GOODS);
					if($pos !== false){
						$GOODS_file = $file;
					} 					

				}
										
				if(!$GSMARCHIVE_file || !$GOODSSALE_file || !$GOODSSALEATTR_file || !$SHIFT_file || !$SHIFTHOSTS_file|| !$GOODS_file){
					$_SESSION["error"] = '<p>Ошибка. Некоторые файлы не были найдены в архиве. </p>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();											
				}
				
				
				
				
				libxml_use_internal_errors(true);
				
				$GSMARCHIVE_xml = simplexml_load_file($GSMARCHIVE_file);
				$doc = explode("\n", $GSMARCHIVE_file);

				if (!$GSMARCHIVE_xml) 
				{				
					$errors = libxml_get_errors();
					foreach ($errors as $error) {
						$log .= display_xml_error($error, $doc);
					}
					libxml_clear_errors();
					
					$_SESSION["error"] = '<p>'.$log.'</p>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					$log = '';
					header("Location: action.php?type={$type}"); 					
					exit();							
				}
				
				
				
				$GOODSSALE_xml = simplexml_load_file($GOODSSALE_file);
				$doc = explode("\n", $GOODSSALE_file);

				if (!$GOODSSALE_xml) 
				{				
					$errors = libxml_get_errors();
					foreach ($errors as $error) {
						$log .= display_xml_error($error, $doc);
					}
					libxml_clear_errors();
					
					$_SESSION["error"] = '<p>'.$log.'</p>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					$log = '';					
					header("Location: action.php?type={$type}"); 
					exit();							
				}



				$GOODSSALEATTR_xml = simplexml_load_file($GOODSSALEATTR_file);
				$doc = explode("\n", $GOODSSALEATTR_file);

				if (!$GOODSSALEATTR_xml) 
				{				
					$errors = libxml_get_errors();
					foreach ($errors as $error) {
						$log .= display_xml_error($error, $doc);
					}
					libxml_clear_errors();
					
					$_SESSION["error"] = '<p>'.$log.'</p>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					$log = '';
					header("Location: action.php?type={$type}"); 					
					exit();							
				}



				$SHIFT_xml = simplexml_load_file($SHIFT_file);
				$doc = explode("\n", $SHIFT_file);

				if (!$SHIFT_xml) 
				{				
					$errors = libxml_get_errors();
					foreach ($errors as $error) {
						$log .= display_xml_error($error, $doc);
					}
					libxml_clear_errors();
					
					$_SESSION["error"] = '<p>'.$log.'</p>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					$log = '';					
					header("Location: action.php?type={$type}"); 
					exit();							
				}



				$SHIFTHOSTS_xml = simplexml_load_file($SHIFTHOSTS_file);
				$doc = explode("\n", $SHIFTHOSTS_file);

				if (!$SHIFTHOSTS_xml) 
				{				
					$errors = libxml_get_errors();
					foreach ($errors as $error) {
						$log .= display_xml_error($error, $doc);
					}
					libxml_clear_errors();
					
					$_SESSION["error"] = '<p>'.$log.'</p>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					$log = '';					
					header("Location: action.php?type={$type}"); 
					exit();							
				}


				if($GOODS_file){
					$GOODS_xml = simplexml_load_file($GOODS_file);
					$doc = explode("\n", $GOODS_file);

					if (!$GOODS_xml) 
					{				
						$errors = libxml_get_errors();
						foreach ($errors as $error) {
							$log .= display_xml_error($error, $doc);
						}
						libxml_clear_errors();
						
						$_SESSION["error"] = '<p>'.$log.'</p>';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						$log = '';						
						header("Location: action.php?type={$type}"); 
						exit();							
					}		
				}	
				$temp = 0;
			
				foreach ($kkm_array as $kkm_serial => $kkm_data)
				{			
				
					$sum_20_shop = 0.00;
					$nds_20_shop = 0.00;
					$sum_10_shop = 0.00;
					$nds_10_shop = 0.00;
					$sum_20_gsm = 0.00;
					$nds_20_gsm = 0.00;
					$sum_20 = 0.00;
					$nds_20 = 0.00;
					$sum_10 = 0.00;
					$nds_10 = 0.00;		

					$goods = 0;		
					$goods_cnt = 0;
																				
					$number = set_number('pko');
					$number_int = $_SESSION["number_int"];

					$q = "
						SELECT
							pse
						FROM
							kkm
						WHERE
							serial = '{$kkm_serial}'
						LIMIT 1
					";
					
					if($result = $mysqli->query($q))
					{
						if($result->num_rows > 0){
							while ($data = $result->fetch_object()){
								$pse = $data->pse;
							}
						}
						else{
							$_SESSION["error"] = '<p>Ошибка. ККМ с номером '.$kkm_serial.' в БД не найден.</p>';
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);	
							$result->close();							
							header("Location: action.php?type={$type}"); 
							exit();															
						}
						
					}
					else{
						$_SESSION["error"] = '<p>Ошибка. '.$mysqli->error.'</p>';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);	
						header("Location: action.php?type={$type}"); 
						exit();		
					}	
					
					$gsm_item_20_nds = 0.00;
					$gsm_item = 0.00;
					$gsm_cnt_rows = 0;
					foreach ($GSMARCHIVE_xml->ROWDATA[0]->ROW as $row) 
					{
						if((int)$row['PAYMENTKIND'] === 0 && mb_strtolower($row['HOST'], 'UTF-8') === trim(mb_strtolower($pse, 'UTF-8')))
						{
							$gsm_cnt_rows ++;
							$gsm_item = floatval($row['FACTSUMMA']);
							$gsm_item_20_nds = $gsm_item * 20 / 120;
							
							$sum_20_gsm += $gsm_item;	
							$nds_20_gsm += round($gsm_item_20_nds,2);
							
							// Temporary log for PKO lines
//							$mysqli->query("INSERT INTO `pko_lines` (`skladID`,`number_int`,`amount`,`nds`,`tm`)
//												VALUES(".$_SESSION["skladID"].",".$number_int.",".$gsm_item.",".round($gsm_item_20_nds,2).",'".date('Y-m-d H:i:s',time())."')");
						}
					}
					
					$goods_cnt_rows = 0;
					foreach($GOODSSALEATTR_xml->ROWDATA[0]->ROW as $GOODSSALEATTR_row)
					{
						$products = $GOODSSALE_xml->xpath('//ROWDATA/ROW[@PAYMENTKIND="0" and @CODE="'.(string)$GOODSSALEATTR_row['CODENAKL'].'" and translate(@HOST, "ABCDEFGHIJKLMNOPQRSTUVWXYZЁЙЦУКЕНГШЩЗХЪФЫВАПРОЛДЖЭЯЧСМИТЬБЮ", "abcdefghijklmnopqrstuvwxyzёйцукенгшщзхъфывапролджэячсмитьбю")="'.trim(mb_strtolower($pse, 'UTF-8')).'"]');
						$products_count = count($products);
						if($products_count > 1)
						{
							$_SESSION["error"] = '<p>Ошибка. Для товара '.(string)$GOODSSALEATTR_row['CODENAKL'].' найдено больше одной звписи</p>';
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
							header("Location: action.php?type={$type}"); 
							exit();											
						}
						
						$shop_item_20_sum = 0.00;	
						$shop_item_20_nds = 0.00;
						$shop_item_10_sum = 0.00;	
						$shop_item_10_nds = 0.00;										
						
						if($products_count === 1)
						{
							$goods_cnt_rows ++;
							if(strlen((string)$GOODSSALEATTR_row['GOODCODE']) === 5)
							{
								$nomenclature = $GOODS_xml->xpath('//ROWDATA/ROW[@CODE="'.(string)$GOODSSALEATTR_row['GOODCODE'].'"]');
								$nomenclature_count = count($nomenclature);
								if($nomenclature_count > 1)
								{
									$_SESSION["error"] = '<p>Ошибка. Для товара '.(string)$GOODSSALEATTR_row['CODENAKL'].' найдено больше одной звписи в номенклатурном справочнике</p>';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);	
									header("Location: action.php?type={$type}"); 
									exit();											
								}
								if($nomenclature_count === 0)
								{
									$_SESSION["error"] = '<p>Ошибка. Товар '.(string)$GOODSSALEATTR_row['CODENAKL'].' не найден в номенклатурном справочнике</p>';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);	
									header("Location: action.php?type={$type}"); 
									exit();											
								}									
								if($nomenclature_count === 1)
								{
									if((int)$nomenclature[0]->attributes()->{'TAXINDEX'} === 2 || (int)$nomenclature[0]->attributes()->{'TAXINDEX'} === 4)
									{
										$shop_item_10_sum = floatval($GOODSSALEATTR_row['SUMMA']);
										$shop_item_10_nds = $shop_item_10_sum * 10 / 110;
										$sum_10_shop += $shop_item_10_sum;
										$nds_10_shop += round($shop_item_10_nds,2);

										// Temporary log for PKO lines
//										$mysqli->query("INSERT INTO `pko_lines` (`skladID`,`number_int`,`amount`,`nds`,`nds_type`,`tm`)
//													VALUES(".$_SESSION["skladID"].",".$number_int.",".$shop_item_10_sum.",".round($shop_item_10_nds,2).",'nds10','".date('Y-m-d H:i:s',time())."')");
									}
									else
									{
										$shop_item_20_sum = floatval($GOODSSALEATTR_row['SUMMA']);
										$shop_item_20_nds = $shop_item_20_sum * 20 / 120;
										$sum_20_shop += $shop_item_20_sum;
										$nds_20_shop += round($shop_item_20_nds,2);

										// Temporary log for PKO lines
//										$mysqli->query("INSERT INTO `pko_lines` (`skladID`,`number_int`,`amount`,`nds`,`tm`)
//													VALUES(".$_SESSION["skladID"].",".$number_int.",".$shop_item_20_sum.",".round($shop_item_20_nds,2).",'".date('Y-m-d H:i:s',time())."')");
									}
								}																	
							}
							else
							{
								$shop_item_20_sum = floatval($GOODSSALEATTR_row['SUMMA']);
								$shop_item_20_nds = $shop_item_20_sum * 20 / 120;
								$sum_20_shop = $shop_item_20_sum + $sum_20_shop;		
								$nds_20_shop += round($shop_item_20_nds,2);

								// Temporary log for PKO lines
//								$mysqli->query("INSERT INTO `pko_lines` (`skladID`,`number_int`,`amount`,`nds`,`tm`)
//											VALUES(".$_SESSION["skladID"].",".$number_int.",".$shop_item_20_sum.",".round($shop_item_20_nds,2).",'".date('Y-m-d H:i:s',time())."')");
							}
						}
					}
					
					if (!$goods_cnt_rows && !$gsm_cnt_rows) {
						setlog($_SESSION['user_id'],'Не найдено ни одной записи в out файле. Возможная причина - неверные настройки ККМ №'.$kkm_serial,$page_); 
						continue;											
					}
														
					$sum_20 = $sum_20_gsm + $sum_20_shop;
					$sum_10 = $sum_10_shop;
															
					$nds_20 = $nds_20_gsm + $nds_20_shop;
					$nds_10 = $nds_10_shop;

					$date_string_start	= $SHIFT_xml->ROWDATA[0]->ROW['SHIFTFROM'];
					$date_string_end	= $SHIFT_xml->ROWDATA[0]->ROW['SHIFTTO'];													
																
					foreach ($SHIFTHOSTS_xml->ROWDATA[0]->ROW as $SHIFTHOSTS_row) {
						if(mb_strtolower($SHIFTHOSTS_row['HOST']) == mb_strtolower($pse)){
							$kassir = (string)$SHIFTHOSTS_row['ONDUTY'];
						}
					}
																	
					$smena = '';

					$date_start = substr($date_string_start, 0, 4).'-'.substr($date_string_start, 4, 2).'-'.substr($date_string_start, 6, 2).' '.substr($date_string_start, 9, 2).':'.substr($date_string_start, 12, 2).':'.substr($date_string_start, 15, 2);
					$date_end = substr($date_string_end, 0, 4).'-'.substr($date_string_end, 4, 2).'-'.substr($date_string_end, 6, 2).' '.substr($date_string_end, 9, 2).':'.substr($date_string_end, 12, 2).':'.substr($date_string_end, 15, 2);
																	
/*					$q = "
						SELECT 
							id,
							sum,
							nds,
							nds10,
							sum10
						FROM
							pko
						WHERE
							datetime >= '{$date_start}'
						AND
							datetime <= '{$date_end}'
						AND
							ret = '1'
						AND
							skladID = '{$_SESSION['skladID']}'
						AND
							osnov = '{$kkm_serial}'
					";
*/					$q = "
						SELECT 
							sum
						FROM
							rko
						WHERE
							datetime >= '{$date_start}'
						AND
							datetime <= '{$date_end}'
						AND
							skladID = '{$_SESSION['skladID']}'
						AND
							pril='{$kkm_serial}'
						AND
							osnov = 'Возврат оплаты за товар покупателю'
					";
//						AND
//							oper = '62.01,62.02'
																	
					if ($result = $mysqli->query($q))
					{
						if($result->num_rows > 0)
						{
							while ($data = $result->fetch_object())
							{
								$nds_20 = $nds_20 - floatval($data->sum * 20 / 120);
							//	$nds_10 = $nds_10 - floatval($data->nds10);
								$sum_20 = $sum_20 - floatval($data->sum);
							//	$sum_10 = $sum_10 - floatval($data->sum10);
							}
						}
// fix-0.0.11 null PKO disabled
						if (($itog = $sum_20 + $sum_10) > 0) {
							$itog_nds = $nds_20 + $nds_10;
							
							$datetime = new DateTime(date('Y-m-d H:i:s'));
							$date = $datetime->format('Y-m-d');
							$time = $datetime->format('H:i:s');
							$azs = $_SESSION["azs_name"];
							$skladID = $_SESSION["skladID"];
							$sum = number_format($sum_20,2,'.','');
							$nds = number_format($nds_20,2,'.','');
							$sum10 = number_format($sum_10,2,'.','');
							$nds10 = number_format($nds_10,2,'.','');
							$ot = $_POST['ot__'.$kkm_serial];
							$schet = $_POST['schet'];
							$osnov = $kkm_serial;
							$pril = $kkm_data['pril'];	
							$ret = 0;
							$creator = $_SESSION["fio"];
							$buh = $_SESSION["buh"];
							$dir = $_SESSION["buh"];
							$dol = $_SESSION["position"];

							if(insert_pko($number,$number_int,$datetime,$date,$time,$azs,$skladID,$sum,$nds,$sum10,$nds10,$ot,$schet,$creator,$osnov,$buh,$dir,$dol,$pril,$ret,$smena,$goods)){
								usleep(1100000); // delay 1.1 sec for 1C convenience
							}
							else{
								$_SESSION["error"] = '<p>Ошибка при записи ПКО в БД. '.(!empty($_SESSION['alert'])?$_SESSION['alert']:'').'</p>';
								if (!empty($_SESSION['alert']))
									unset($_SESSION['alert']);
								setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
								header("Location: action.php?type={$type}");
								exit();
							}
						}
						else { // fix-0.0.11 (see above)
							$_SESSION["error"] = '<p>ПКО на сумму '.($sum_20 + $sum_10).' руб. создавать запрещено</p>';
							if (!empty($_SESSION['alert']))
								unset($_SESSION['alert']);
							header("Location: action.php?type={$type}");
							exit();
						}
																
					}
					else{
						$_SESSION["error"] = '<p>Ошибка в запросе к БД при поиске возвратов</p>';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);					
						header("Location: action.php?type={$type}"); 
						exit();	
					}	
				}
										
				foreach(glob($uploaddir.'*.XML') as $file){
					if(file_exists($file))
						unlink($file);
				}

			}
			else
			{
				if(
					!isset($_POST['ot']) or
					empty($_POST['ot']) or	
					!isset($_POST['sum']) or
					empty($_POST['sum']) or	
					(float)$_POST['sum'] <= 0 or
					!isset($_POST['pril']) or
					empty($_POST['pril'])
				){
					$_SESSION["error"] = 'Некоторые поля формы ручного ввода ПКО были заполненны не верно.';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: create.php?type={$type}"); 
					exit();	
				}
				elseif (!preg_match("/^\d+([,.]\d{1,2})?$/",$_POST['sum'])
						|| (strlen($_POST['sum']) > 10)) {
					$_SESSION["error"] = 'Значение суммы имеет неверный формат либо длинее 10 символов. Введите сумму в формате <b>1234.56</b>';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: create.php?type={$type}"); 
					exit();	
				}
				else{
					$number = set_number('pko');
					$number_int = $_SESSION["number_int"];
					$datetime = new DateTime(date('Y-m-d H:i:s'));
					$date = $datetime->format('Y-m-d');
					$time = $datetime->format('H:i:s');
					$azs = $_SESSION["azs_name"];
					$skladID = $_SESSION["skladID"];
					$sum = amount_summ($_POST['sum'],true);
					$schet = $_POST['schet'];

					if($schet == '91.01')
						$nds = 0;
					else
						$nds = round($sum * 20 / 120, 2);
						
					$ot = $_POST['ot'];
					
					$creator = $_SESSION["fio"];
					if($schet == '91.01'){
						$osnov = 'Излишки наличных денежных средств в кассе АЗС №'.$_SESSION['skladID'];
					}	
					else{
						$osnov = $_POST['osnov'];
					}
					$pril = $_POST['pril'];	
					$sum10 = 0;
					$nds10 = 0;
					$ret = (int)$_POST['ret'];
					$smena = '';
					
					$goods = 3;
					
					$buh = $_SESSION["buh"];
					$dir = $_SESSION["buh"];
					$dol = $_SESSION["position"];					
					
					
					if(insert_pko($number,$number_int,$datetime,$date,$time,$azs,$skladID,$sum,$nds,$sum10,$nds10,$ot,$schet,$creator,$osnov,$buh,$dir,$dol,$pril,$ret,$smena, $goods)){
						$_SESSION["success"] = 'Документ создан';
						header("Location: action.php?type={$type}");							
						exit();																						
					}
					else{
						$_SESSION["error"] = 'Ошибка при записи в БД. Функциф вернула FALSE';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: action.php?type={$type}"); 
						exit();																	
					}				
				}
			}	
			
			foreach(glob($uploaddir.'*.zip') as $file){
				if(file_exists($file))
					unlink($file);
			}
			
			$_SESSION["success"] = 'Документ создан';
			header("Location: action.php?type={$type}");
			exit();						
		}
				
		if($operation == 'rko')
		{
			if(
				!isset($_POST['sum']) and
				empty($_POST['sum'])	and	
				!isset($_POST['oper']) and
				empty($_POST['oper'])	and	
				!isset($_POST['vidat']) and
				empty($_POST['vidat'])	and	
				!isset($_POST['osnov']) and
				empty($_POST['osnov'])	and	
				!isset($_POST['pril']) and
				empty($_POST['pril'])
			){
				$_SESSION["error"] = 'Некоторые поля формы ручного ввода РКО были заполненны не верно';
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: create.php?type={$type}"); 
				exit();	
			}
			else{
				$number = set_number('rko');
				$number_int = $_SESSION["number_int"];
				$datetime = new DateTime(date('Y-m-d H:i:s'));
				$date = $datetime->format('Y-m-d');
				$time = $datetime->format('H:i:s');
				$azs = $_SESSION["azs_name"];
				$skladID = $_SESSION["skladID"];
				$sum = amount_summ($_POST['sum']);
				$oper = $_POST['oper'];
				$pasport = getPasport($_POST['vidat']);
				$vidat = $_POST['vidat'];
				$osnov = $_POST['osnov'];
				if(!isset($_POST['pril']) and empty($_POST['pril'])) $pril = ''; else $pril = $_POST['pril'];
				$kassir = $_SESSION["fio"];
				if(!isset($_POST['po']) and empty($_POST['po'])){
					$po = '-';
				}
				else{
					$po = $_POST['po'];
				}
				
				$buh = $_SESSION["buh"];
				$dir = $_SESSION["buh"];
				$dol = $_SESSION["position"];					
				
				if(insert_rko($number,$number_int,$datetime,$date,$time,$azs,$skladID,$sum,$oper,$vidat,$osnov,$pril,$po,$kassir,$buh,$dir,$dol,'',$pasport)){
					$_SESSION["success"] = 'Документ создан';
					header("Location: action.php?type={$type}");
					exit();
				}
				else{
					$_SESSION["error"] = 'Ошибка при записи в БД. Функциф вернула FALSE';
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: action.php?type={$type}"); 
					exit();																	
				}				
				
			}		
		}	
	  }
	  else{
		$_SESSION["error"] = 'Не надо долбить по кнопке!!';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
		header("Location: action.php?type={$type}"); 
		exit();		  
	  }
	}
}
?>