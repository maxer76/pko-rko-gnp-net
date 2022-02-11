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

// Дата с которой выгрузить документы ПКО
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

				$getPKOQuery = "
					SELECT 
						* 
					FROM  
						`pko` 
					WHERE
						`skladID` = '{$azs->skladID}'
					AND
						`date` >= '{$date}'		
				";			
			
			
				if ($PKOCollectionResults = $mysqli->query($getPKOQuery))
				{	
					if($PKOCollectionResults->num_rows > 0)
					{					
						while ($pko = $PKOCollectionResults->fetch_object())
						{
							$dom = new domDocument("1.0", "utf-8");
							$database = $dom->createElement("database"); 
							$database->setAttribute("name", "kassa");
							$dom->appendChild($database);
							
							$table = $dom->createElement("table"); 
							$table->setAttribute("name", "pko");
							$database->appendChild($table);
							
							$params['number'] = $pko->number;
							$params['skladID'] = $pko->skladID;
							$params['sum'] = $pko->sum;
							$params['sum10'] = $pko->sum10;
							$params['nds'] = $pko->nds;
							$params['nds10'] = $pko->nds10;
							$params['ot'] = $pko->ot;
							
							$params['oper'] = $pko->oper;
							
							if($pko->oper == '50.02'){
								$params['osnov'] = 'Розничная выручка (ККТ №'.$pko->osnov.')';
								$params['pril'] = 'Отчет о закрытии смены №'.$pko->pril;
							}

							if($pko->oper == '62.01, 62.02'){
								$params['osnov'] = 'Излишняя оплата (подлежащая возврату покупателю)';
							}						
							
							if($pko->oper == '91.01'){
								$params['osnov'] = 'Излишки наличных денежных средств в кассе АЗС №'.$pko->skladID;
								$params['pril'] = $pko->pril;		
							}			
							
							$datetime = new DateTime($pko->datetime);
							$params['datetime'] = $datetime->format('Y-m-d H:i:s');
							
							
							
							foreach ($params as $key => $value) {
							
								$column = $dom->createElement("column", $value);
								$column->setAttribute("name", $key);
								$table->appendChild($column); 
							}
							$dom->save("export/pko_".$pko->number_int."_{$pko->skladID}_".$datetime->format('YmdHis').".xml"); 
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