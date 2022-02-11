<?php
echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
 
$bd_host = "localhost";
$bd_user = "root";
$bd_pass = "Cwlziqxy1";
$bd_base = "kassa";

$mysqli = new mysqli($bd_host,$bd_user,$bd_pass,$bd_base);
$mysqli->query("SET character_set_database=utf8");
$mysqli->query("SET character_set_client=utf8");
$mysqli->query('SET NAMES "UTF8"');

// Дата с которой выгрузить документы РКО
$date = '';

// Регион для выгрузки
$region = '';

// Запрос к БД на получение всех АЗС региона
$getAzsQuery = "
	SELECT 
		* 
	FROM  
		`azs` 
	where
		`region` = '{$region}'
	";


	if ($azsCollectionResults = $mysqli->query($getAzsQuery))
	{	
		if($azsCollectionResults->num_rows > 0)
		{
			while ($azs = $azsCollectionResults->fetch_object())
			{

				$getRKOQuery = "
					SELECT 
						* 
					FROM  
						`rko` 
					WHERE
						`skladID` = '{$azs->skladID}'
					AND
						`date` >= '{$date}'		
				";			
			
			
				if ($RKOCollectionResults = $mysqli->query($getRKOQuery))
				{	
					if($RKOCollectionResults->num_rows > 0)
					{					
						while ($rko = $RKOCollectionResults->fetch_object())
						{
							$dom = new domDocument("1.0", "utf-8");
							$database = $dom->createElement("database"); 
							$database->setAttribute("name", "kassa");
							$dom->appendChild($database);
							
							$table = $dom->createElement("table"); 
							$table->setAttribute("name", "rko");
							$database->appendChild($table);
							
							$datetime = new DateTime($rko->datetime);
							
							$params['number'] = $rko->number;																		// НОМЕР ДОКУМЕНТА
							$params['skladID'] = $rko->skladID;																		// НОМЕР АЗС
							$params['sum'] = $rko->sum;																				// СУММА
							$params['oper'] = $rko->oper;																			// КОРР. СЧЕТ
							$params['datetime'] = $datetime->format('Y-m-d H:i:s');													// ДАТА ДОКУМЕНТА
							
							// ********************************************* ИНКАССАЦИЯ *******************************************
							if($rko->oper == '57.3'){
								$flag_date = new DateTime('2018-05-01');
								if($datetime < $flag_date){
									$params['vidat'] = get_bank($rko->skladID);														// ВЫДАТЬ 			= Банк
									$params['osnov'] = $rko->osnov;																	// ОСНОВАНИЕ		= Сдача выручки в банк 
									$params['pril']	 = 'Квитанция к сумке №'.$rko->pril;											// ПРИЛОЖЕНИЕ		= Квитанция к сумке №
								}
								else{
									$params['vidat'] = $rko->vidat;																	// ВЫДАТЬ 			= Кассир
									if($rko->osnov == 'Сдача выручки в банк'){
										$params['osnov'] = $rko->osnov.' ('.get_bank($rko->skladID).')';							// ОСНОВАНИЕ		= Сдача выручки в банк (Банк)
										$params['pril']	 = 'Квитанция к сумке №'.$rko->pril.' от '.$datetime->format("d.m.Y");		// ПРИЛОЖЕНИЕ		= Квитанция к сумке №_____ (от ДАТА)
									}
									else{
										$params['osnov'] = $rko->osnov;																// ОСНОВАНИЕ		= Пополнение основной кассы офиса из кассы АЗС
										$params['pril']	 = $rko->pril;																// ПРИЛОЖЕНИЕ		= Приложение
									}
									$params['po']	 = $rko->pasport;																// ПО				= Паспорт кассира						
								}
							}
							// ****************************************************************************************************
							
							// *****************************************  Возврат покупателю **************************************
							if($rko->oper == '62.01,62.02'){
								$params['vidat'] = $rko->vidat;																		// ВЫДАТЬ 			= ФИО клиента
								$params['osnov'] = $rko->osnov;																		// ОСНОВАНИЕ		= Возврат оплаты за товар покупателю
								$params['pril']	 = $rko->pril;																		// ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
								$params['po']	 = $rko->po;																		// ПО				= Паспортные данные клиента							
							}
							// ****************************************************************************************************

							// *********************************************** Недостача ******************************************
							if($rko->oper == '94.05.1'){
								$params['vidat'] = $rko->kassir;																	// ВЫДАТЬ 			= ФИО кассира
								$params['osnov'] = $rko->osnov;																		// ОСНОВАНИЕ		= Недостача наличных денежных средств в кассе АЗС №___
								$params['pril']	 = $rko->pril;																		// ПРИЛОЖЕНИЕ		= Поле приложение заполняется вручную
							}	
							// ****************************************************************************************************
							
							foreach ($params as $key => $value) {
							
								$column = $dom->createElement("column", $value);
								$column->setAttribute("name", $key);
								$table->appendChild($column); 
							}
							
							$dom->save("export/rko_{$rko->skladID}_".$datetime->format('YmdHis').".xml"); 
										
						}
					}
					else
					{
						echo '<p>Не обнаружено ни одного документа для АЗС '.$azs->skladID.'</p>';
					}					
				}	
				else
				{
					echo '<p>Что-то пошло не так: '.$mysqli->error.'</p>';
				}
			}
		}
		else
		{
			echo '<p>Не обнаружено ни одной АЗС</p>';
		}
	}
	else
	{
		echo '<p>Что-то пошло не так: '.$mysqli->error.'</p>';
	}