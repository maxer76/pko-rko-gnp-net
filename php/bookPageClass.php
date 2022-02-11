<?php
require_once('functions.php');

class Page
{
/***************************************** Установить кассира для страницы ********************************/
	public static function setKassir($kassir, $id, $arch='')					// Входящие параметры - кассир и ID страницы
	{
		global $mysqli;
		$q = "
			UPDATE 
				`book{$arch}` 
			SET 
				`kassir` = '{$kassir}'
			WHERE
				`id` = '{$id}'				
		";		
		if($result = $mysqli->query($q))
		{
			if($mysqli->affected_rows > 0)									// Проверям изменилось ли ФИО кассира
			{
				if(Page::setStatus(3, $id, $arch))							// Устанавливаем статус
					return true;
				$result->close;
			}
		}
		return false;
	}

/**********************************************************************************************************/

/***************************************** Установить статус для страницы *********************************/

	public static function setStatus($status, $id, $arch='')					// Входящие параметры - статус и ID страницы
	{
		global $mysqli;
		
		$q = "
			UPDATE 
				`book{$arch}` 
			SET 
				`status` = '{$status}'
			WHERE
				`id` = '{$id}'				
		";		
		
		if($result = $mysqli->query($q))
		{
			return true;
		}
		return false;
	}

/**********************************************************************************************************/


/************************************** Читаем данные страницы кассовой книги *****************************/

	public static function getData($q) 								// $q - запрос к кассовой книге
	{
		global $mysqli;
		
		if($result = $mysqli->query($q))
		{
			if($result->num_rows > 0)								// Если есть такой лист
			{
				if($obj = $result->fetch_object()){
					return $obj; 									// Возвращаем данные листа в виде объекта
				}
				$result->close;
			}
			
		}

		return false; 
	}
	
/**********************************************************************************************************/

/********************************** Записываем данные страницы в кассовую книгу ***************************/

	public static function saveData($q)
	{
		global $mysqli;
		
		if($result = $mysqli->query($q))
		{
			return true;
		}
		
		return false;
	}

/**********************************************************************************************************/




/************************************* Ищем РКО и ПКО в указанную дату для АЗС ****************************/	

	public static function getRkoPko($azs_id, $date, $arch='') 		// Входящие параметры Номер АЗС и дата
	{
		global $mysqli;
	
		$q = "
			SELECT 
				`pko{$arch}`.`number` as number,
				`pko{$arch}`.`oper` as oper,
				`pko{$arch}`.`sum` as sum,
				`pko{$arch}`.`sum10` as sum10,
				`pko{$arch}`.`type` as type,
				`pko{$arch}`.`kassir` as kassir,
				`pko{$arch}`.`ot` as vidat,
				`pko{$arch}`.`datetime` as datetime,
				`pko{$arch}`.`date` as date,
				`pko{$arch}`.`azs` as azs,
				`pko{$arch}`.`number_int` as number_int,
				`pko{$arch}`.`flag` as flag
			FROM 
				`pko{$arch}` 
			WHERE 
				`pko{$arch}`.`skladID`='".$azs_id."'
				AND
				`pko{$arch}`.`date` = '".$date."'
				AND
				`pko{$arch}`.`status` = '1'		
			UNION			

			SELECT 
				`rko{$arch}`.`number` as number,
				`rko{$arch}`.`oper` as oper,
				`rko{$arch}`.`sum` as sum,
				`rko{$arch}`.`sum` as sum10,
				`rko{$arch}`.`type` as type,
				`rko{$arch}`.`kassir` as kassir,
				`rko{$arch}`.`vidat` as vidat,
				`rko{$arch}`.`datetime` as datetime,
				`rko{$arch}`.`date` as date,
				`rko{$arch}`.`azs` as azs,
				`rko{$arch}`.`number_int` as number_int,
				`rko{$arch}`.`flag` as flag
			FROM 
				`rko{$arch}`
			WHERE 
				`rko{$arch}`.`skladID`='".$azs_id."'
				AND
				`rko{$arch}`.`date` = '".$date."'
				AND
				`rko{$arch}`.`status` = '1'
			ORDER BY
				`datetime` DESC, `number_int` DESC
			";	
			
			if ($result = $mysqli->query($q))
			{
				$mass = array();
				$mass['itog_p'] = 0;
				$mass['itog_r'] = 0;
				$mass['rkopko'] = '';
				$mass['end']	= 0;
				
				if($result->num_rows > 0)														// Если есть документы в указанную дату
				{	
					while ($row = $result->fetch_assoc())
					{
						if($row['type'] == 0)													// Если это ПКО
						{
							$mass['itog_p'] = $mass['itog_p'] + $row['sum'] + $row['sum10']; 	// Суммируем все ПКО
						}
						if($row['type'] == 1)													// Если это РКО
						{
							$mass['itog_r'] = $mass['itog_r'] + $row['sum'];					// Суммируем все РКО
						}							
							
						$rows[]=$row;															// Сохраняем все РКО и ПКО в один массив
					}
					$mass['rkopko'] = base64_encode(serialize($rows));
					$result->close();
				}
				else
					return false;
				
				
				return $mass;
			}
			
	}

/**********************************************************************************************************/
}


class Book
{
	private $azs;
	
	function __construct($azs)
	{
		$this->azs = $azs;
	}
		
	public function showPage($page)
	{
		echo '<pre>';
		print_r($page);
		echo '</pre>';
	}
	
		

/************************************** Поиск последней страницы кассовой книги ***************************/
	
	public function getLastPageData($arch='')
	{
		
		$q = "
			SELECT 
				*
			FROM 
				`book{$arch}`
			WHERE 
				date=(
					SELECT 
						MAX(`date`) 
					FROM 
						`book{$arch}`
					WHERE 
						`skladID` = '{$this->azs}'
				)
			AND
				`skladID` = '{$this->azs}'
			LIMIT 
				1
		";		
		
		return Page::getData($q);
	}
	
/**********************************************************************************************************/

/************************************* Получить данные листа за конкретную дату ***************************/
	
	public function getPageData($date, $arch='')
	{
		
		$q = "
			SELECT 
				*
			FROM 
				`book{$arch}`
			WHERE 
				`date` = '{$date}'
			AND
				`skladID` = '{$this->azs}'
			LIMIT 
				1
		";		
		
		return Page::getData($q);
	}
	
/**********************************************************************************************************/

/********************************** Получить данные листа для отображения в PDF ***************************/
	
	public function getPageDataPDF($id, $arch='')
	{
		
		
		// Пояснение: полностью данные страницы кассовой книги, несколько полей из azs, замещение берётся на основании значения `azs`.`replacement` и так же проверяются даты замещения
		//// fix-0.0.10 `azs`.`removal_date`,
		$q = "
			SELECT 
				`book{$arch}`.*,
				`azs`.`ragion_name`,
				`azs`.`name`,
				`azs`.`position`,
				`azs`.`title_date1`,
				`azs`.`title_date2`,
				`azs`.`removal_date`,
				`replacements`.`fio` as replacement_fio,
				`replacements`.`position` as replacement_position,
				`lastworkday`.`date` as lastworkdate
			FROM
				`book{$arch}`
			INNER JOIN `azs` ON `book{$arch}`.`skladID` = `azs`.`skladID`			
			LEFT JOIN `replacements` ON `replacements`.`skladID` = `azs`.`skladID` AND `replacements`.`from` <= `book{$arch}`.`date` AND `book{$arch}`.`date` <= `replacements`.`to`
			LEFT JOIN `lastworkday` ON `lastworkday`.`date` = `book{$arch}`.`date` AND `lastworkday`.`azs_id` = `book{$arch}`.`skladID`
			WHERE
				`book{$arch}`.`id` = '{$id}'
			LIMIT 
				1
		";				
		return Page::getData($q);
	}
	
/**********************************************************************************************************/
	
/*************************** Формируем данные для новой страницы кассовой книги  **************************/	
	
	public function newPageData($pageData, $kassir, $buh, $newDate, $arch='')
	{
		$oldDate = new DateTime($pageData->date);													// Создаём объект DateTime, что бы сохранить дату предыдущего документа
		$curDate = new DateTime($newDate);															// Создаём объект DateTime, что бы сохранить дату текущего документа
		
		unset($pageData->id);
		$pageData->date					= $newDate;													// Дата для новой страницы
		$pageData->start				= $pageData->end;											// Переносим остатки с конца прошлого дня на начало нового дня
		if($mass = Page::getRkoPko($this->azs, $newDate, $arch))									// Вызываем статический метод класса Page который ищет все ПКО и РКО за определённую дату для указанной АЗС: возвращает массив с данными или false если данные не обнаружены
		{
			$pageData->rkopko			= $mass['rkopko'];											// Сериализованные данные РКО и ПКО на указанную дату
			$pageData->itog_p			= $mass['itog_p'];											// Сумма ПКО				
			$pageData->itog_r			= $mass['itog_r'];											// Сумма РКО
			$pageData->end 				= $pageData->start + $pageData->itog_p - $pageData->itog_r;	// Итог на конец дня
			$pageData->end 				= round($pageData->end, 2);			
			$pageData->list++;																		// Увеличиваем на один счётчик страниц
			if($oldDate->format('m') == $curDate->format('m'))										// Проверяем сменился ли месяц
				$pageData->list_cnt_m++;															// Увеличиваем на один счётчик страниц за месяц
			else 
				$pageData->list_cnt_m 	= 1; 														// Обнуляем счётчик страниц за месяц если произошла смена месяца
			$pageData->list_cnt_y++;																// Увеличиваем на один счётчик страниц за год
			$pageData->status			= 1;														// Устанавливаем статус 1 - готовая к распечатки страница кассовой книги
		}
		else
		{
			$pageData->rkopko			= '';														// За указанную дату нет РКо и ПКО
			$pageData->itog_p			= 0;
			$pageData->itog_r			= 0;
			$pageData->end				= $pageData->start;											// Переносим остатки с начала дня на конец дня без изменений
			if($oldDate->format('m') != $curDate->format('m'))										// Проверяем сменился ли месяц
				$pageData->list_cnt_m 	= 1; 														// Обнуляем счётчик страниц за месяц если произошла смена месяца
			$pageData->status			= 11;														// Устанавливаем статус 11 - пустая страница кассовой книги
		}
		$pageData->kassir				= $kassir;													// Кассир
		$pageData->buh					= $buh;														// Бухгалтер
		$pageData->edit_time			= time();													
		
		
		return $pageData;
	}
	
/**********************************************************************************************************/	

/***************************** Сохранение новой страницы с указанными данными *****************************/	

	public function savePage($pageData, $arch='') 													//Входящий параметр - объект с данными для сохранения, где данные представлены в виде свойство - значение
	{
		$mass = get_object_vars($pageData); 														// Получаем все имена и значения свойств
		$mass_keys_separated	= implode("`, `", array_keys($mass));								// Составляем строку из ключей
		$mass_value_separated	= implode("', '", array_values($mass));								// Составляем строку из значений
		
		$q = "																
			INSERT INTO 
				`book{$arch}`(`".$mass_keys_separated."`)
			VALUES 
				('".$mass_value_separated."')
		";

		if(Page::saveData($q))
			return true;

		return false;
		
	}

/**********************************************************************************************************/	

/************************ Формируем данные для обновления страницы кассовой книги  ************************/	
	
	public function updatePageData($pagePrevDayData, $pageData, $arch='')							// Объект с данными предыдущей и нужной страницы из метода getPageData
	{
		$oldDate = new DateTime($pagePrevDayData->date);											// Создаём объект DateTime, что бы сохранить дату предыдущего документа
		$curDate = new DateTime($pageData->date);													// Создаём объект DateTime, что бы сохранить дату текущего документа
		
		
		$pageData->start				= $pagePrevDayData->end;									// Переносим остатки с конца прошлого дня на начало нового дня
		if($mass = Page::getRkoPko($this->azs, $pageData->date, $arch))									// Вызываем статический метод класса Page который ищет все ПКО и РКО за определённую дату для указанной АЗС: возвращает массив с данными или false если данные не обнаружены
		{
			$pageData->rkopko			= $mass['rkopko'];											// Сериализованные данные РКО и ПКО на указанную дату
			$pageData->itog_p			= $mass['itog_p'];											// Сумма ПКО				
			$pageData->itog_r			= $mass['itog_r'];											// Сумма РКО
			$pageData->end				= $pageData->start + $pageData->itog_p - $pageData->itog_r;	// Итог на конец дня
			$pageData->end				= round($pageData->end, 2);			
			$pageData->list				= $pagePrevDayData->list + 1;								// Увеличиваем на один счётчик страниц
			if($oldDate->format('m') == $curDate->format('m'))										// Проверяем сменился ли месяц
				$pageData->list_cnt_m	= $pagePrevDayData->list_cnt_m + 1;							// Увеличиваем на один счётчик страниц за месяц
			else 
				$pageData->list_cnt_m 	= 1; 														// Обнуляем счётчик страниц за месяц если произошла смена месяца
			$pageData->list_cnt_y		= $pagePrevDayData->list_cnt_y + 1;							// Увеличиваем на один счётчик страниц за год
			
			$pageData->status			= 1;														// Устанавливаем статус 1 - готовая к распечатки страница кассовой книги
		}
		else
		{
			$pageData->rkopko			= '';														// За указанную дату нет РКо и ПКО
			$pageData->itog_p			= 0;
			$pageData->itog_r			= 0;
			$pageData->end				= $pageData->start;											// Переносим остатки с начала дня на конец дня без изменений
			if($oldDate->format('m') != $curDate->format('m'))										// Проверяем сменился ли месяц
				$pageData->list_cnt_m 	= 2; 														// Обнуляем счётчик страниц за месяц если произошла смена месяца

			
			$pageData->status			= 11;														// Устанавливаем статус 11 - пустая страница кассовой книги
		}	
		$pageData->edit_time			= time();													
		
		
		return $pageData;
	}
	
/**********************************************************************************************************/	


/********************************** Обновление страницы с указанными данными ******************************/	

	public function updatePage($pageData, $arch='') 																		// Входящий параметр - объект с данными для сохранения из метода getPageData
	{
		$mass = get_object_vars($pageData); 																	// Получаем все имена и значения свойств
		
		$q = "
			UPDATE
				`book{$arch}`
			SET
		";
		$q .= implode(",", array_map(function($k, $v) { return "`{$k}`='{$v}'"; }, array_keys($mass), $mass)); // Создаём строку вида `key` = 'value' из ассоциативного массива
		$q .= "
			WHERE
				`id` = '".$mass['id']."'
		";
		
		if(Page::saveData($q))
			return true;

		return false;
	}

/**********************************************************************************************************/
	

/********************************** Формирование PDF-файла для страницы книги *****************************/	

	public function createPagePDF($pageDataPDF, $arch='')
	{

		$buh 		= (empty($pageDataPDF->replacement_fio))?$pageDataPDF->buh:$pageDataPDF->replacement_fio;						// Если есть замещение, то будем выводить ФИО замещающего
		$dol 		= (empty($pageDataPDF->replacement_position))?$pageDataPDF->position:$pageDataPDF->replacement_position;		// Если есть замещение, то будем выводить должность замещающего
		$list_date	= new DateTime($pageDataPDF->date);																							// Дата страницы
		
		$rko_cnt = 0;																						// Всего РКО за день
		$pko_cnt = 0;																						// Всего ПКО за день
		$rko_pko_cnt_line1 = 17;																			// Начальная позиция вывода строк с РКО и ПКО на первом листе																		
		$rko_pko_cnt_line2 = 41;																			// Начальная позиция вывода строк с РКО и ПКО на втором листе	
			
			
		include 'PHPExcel/IOFactory.php';
		$inputFileName = 'xls/kassa.xls';
		$objPHPExcel = PHPExcel_IOFactory::load($inputFileName);			
		$rendererName = PHPExcel_Settings::PDF_RENDERER_MPDF;												//
		$rendererLibrary = 'mpdf60';																		// Подлючаем библиотеку 
		$rendererLibraryPath = ROOTPATH.$rendererLibrary;											//
		if (!PHPExcel_Settings::setPdfRenderer($rendererName, $rendererLibraryPath)) {						
			die(
				'Пожалуйста, укажите имя библиотеки $rendererName и путь к ней $rendererLibraryPath' .
				PHP_EOL .
				' в зависимости от конкретной структуры каталогов'
			);
		}
		
		//$start	= round(delete_start_end($pageDataPDF->start,'.'),2);										// !!!НАДО ВЫЯСНИТЬ ЗАЧЕМ ЭТО!!! Округляем и подготавливаем остатки на начало дня
		//$end	= round(delete_start_end($pageDataPDF->end,'.'),2);												// !!!НАДО ВЫЯСНИТЬ ЗАЧЕМ ЭТО!!! Округляем и подготавливаем остатки на конец дня
		
		
		//////////////////////////////// ЗАПОЛНЯЕМ ЯЧЕЙКИ ТАБЛИЦЫ //////////////////////////////
		
		$objWriter = new PHPExcel_Writer_PDF($objPHPExcel);		
		$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 9, $pageDataPDF->ragion_name.' '.$pageDataPDF->name);
		$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 10, $pageDataPDF->ragion_name.' '.$pageDataPDF->name);

		// fix-0.0.15 до 02.12.2020 ООО «ГЭС розница», а с 03.12.2020 новое ООО «ГНП сеть».
		if ($pageDataPDF->date <= '2020-12-02') {
			$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 7, 'Общество с ограниченной ответственностью "Газэнергосеть розница"');
			$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 8, 'Общество с ограниченной ответственностью "Газэнергосеть розница"');
		}
		else {
			$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 7, 'ООО «ГНП сеть»');
			$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 8, 'ООО «ГНП сеть»');
		}
		// end fix-0.0.15

		$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow(4, 17, book_list_cnt($arch, $pageDataPDF->date));
		$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow(4, 21, $dol);
		$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow(10, 21, $buh);
		$objPHPExcel->setActiveSheetIndex(3)->setCellValueByColumnAndRow(10, 23, $buh);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 3, $pageDataPDF->azs);
		if($pageDataPDF->date < '2017-12-08'){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 2, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\616401001');
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 26, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\616401001');
		}
		elseif($pageDataPDF->date >= '2017-12-08' && $pageDataPDF->date < '2018-03-06'){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 2, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\615250001');
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 26, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\615250001');
		}
		elseif($pageDataPDF->date >= '2018-03-06' && $pageDataPDF->date < '2020-12-03'){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 2, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\997350001');
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 26, 'Общество с ограниченной ответственностью "Газэнергосеть розница", 6164317329\997350001');					
		}
		// fix-0.0.15
		elseif ($pageDataPDF->date >= '2020-12-03') {
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 2, 'ООО «ГНП сеть», 6164317329\997350001');
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 26, 'ООО «ГНП сеть», 6164317329\997350001');					
		}
		//end fix-0.0.15

		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 27, $pageDataPDF->azs);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 4, 'КАССА за '.russian_date($pageDataPDF->date));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 28, 'КАССА за '.russian_date($pageDataPDF->date));
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 6, 'Лист '.$pageDataPDF->list_cnt_y);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 30, 'Лист '.$pageDataPDF->list_cnt_y);
		
		if($pageDataPDF->start == 0){
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 9, '');
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 33, '');
		}
		else{
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 9, number_format($pageDataPDF->start, 2, '=', ' '));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 33, number_format($pageDataPDF->start, 2, '=', ' '));
		}
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 34, number_format($pageDataPDF->itog_p, 2, '=', ' '));				
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 10, number_format($pageDataPDF->itog_p, 2, '=', ' '));				
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 10, number_format($pageDataPDF->itog_r, 2, '=', ' '));				
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(6, 34, number_format($pageDataPDF->itog_r, 2, '=', ' '));

		if($pageDataPDF->end == 0){				
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 11, '');				
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 35, '');				
		}
		else{
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 11, number_format($pageDataPDF->end, 2, '=', ' '));				
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 35, number_format($pageDataPDF->end, 2, '=', ' '));				
		}
		
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, 14, $pageDataPDF->kassir);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, 38, $pageDataPDF->kassir);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, 19, $buh);
		$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(3, 43, $buh);
		
		if(!empty($pageDataPDF->lastworkdate))				// Если последний рабочий день месяца, то выводим фразу "Количество листов кассовой книги за месяц"
		{
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 22, 'Количество листов кассовой книги за месяц: '.get_kassa_cnt($pageDataPDF->date,$arch));
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 46, 'Количество листов кассовой книги за месяц: '.get_kassa_cnt($pageDataPDF->date,$arch));
		}
		if('06-30' == substr($pageDataPDF->date,5)) // Если конец первого полугодия --- $pageDataPDF->title_date1
		{ 
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 23, 'Количество листов кассовой книги за 1 полугодие: '.$pageDataPDF->list_cnt_y);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 47, 'Количество листов кассовой книги за 1 полугодие: '.$pageDataPDF->list_cnt_y);
			$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 17, 'на 1 полугодие '.$list_date->format('Y').' года');
			$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 16, 'на 1 полугодие '.$list_date->format('Y').' года');			
		} 
		
		elseif('12-31' == substr($pageDataPDF->date,5) // --- $pageDataPDF->title_date2
			|| $pageDataPDF->title_date2 == $pageDataPDF->date) // Если конец года или ликвидация АЗС
		{ 
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 23, 'Количество листов кассовой книги за год: '.$pageDataPDF->list_cnt_y);	
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 47, 'Количество листов кассовой книги за год: '.$pageDataPDF->list_cnt_y);	
//			if ('12-31' == substr($pageDataPDF->date,5)) { // ...только если конец года ---- $pageDataPDF->title_date2
				$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 17, 'на 2 полугодие '.$list_date->format('Y').' года');
				$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 16, 'на 2 полугодие '.$list_date->format('Y').' года');
				if ($list_date->format('Y') == '2020') {
					$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 8, 'Общество с ограниченной ответственностью "Газэнергосеть розница" по 02.12.2020, ООО "ГНП сеть" с 03.12.2020');
					$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 7, 'Общество с ограниченной ответственностью "Газэнергосеть розница" по 02.12.2020, ООО "ГНП сеть" с 03.12.2020');
				}
//			}
		}
		

		elseif ($pageDataPDF->removal_date == $pageDataPDF->date)
		{ 
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 23, 'Количество листов кассовой книги за год: '.$pageDataPDF->list_cnt_y);	
			$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(0, 47, 'Количество листов кассовой книги за год: '.$pageDataPDF->list_cnt_y);	
			if ('06-30' <= $list_date->format('m-d')) { // ...только если конец года ---- $pageDataPDF->title_date2
				$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 17, 'на 2 полугодие '.$list_date->format('Y').' года');
				$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 16, 'на 2 полугодие '.$list_date->format('Y').' года');
				if ($list_date->format('Y') == '2020') {
					$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 8, 'Общество с ограниченной ответственностью "Газэнергосеть розница" по 02.12.2020, ООО "ГНП сеть" с 03.12.2020');
					$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 7, 'Общество с ограниченной ответственностью "Газэнергосеть розница" по 02.12.2020, ООО "ГНП сеть" с 03.12.2020');
				}
			}
			else {
				$objPHPExcel->setActiveSheetIndex(1)->setCellValueByColumnAndRow(1, 17, 'на 1 полугодие '.$list_date->format('Y').' года');
				$objPHPExcel->setActiveSheetIndex(2)->setCellValueByColumnAndRow(1, 16, 'на 1 полугодие '.$list_date->format('Y').' года');			
			}
		} 
		
		$mass = unserialize(base64_decode($pageDataPDF->rkopko));	// Получаем массив РКО и ПКО из ячейки book.rkopko
		
		
				

		foreach($mass as $e){								// Добавляем в таблицу все РКО за указанную дату
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
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 34, 'Выдано '.$e['vidat']);	
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, 34, $e['oper'].' ');
			}
			else{
				$pko_cnt++;
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(5, 34, number_format($e['sum'] + $e['sum10'], 2, '=', ' '));				
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 34, 'Принято от '.$e['vidat']);	
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(4, 34, $e['oper'].' ');
			}
		}
		
		foreach($mass as $e){								// Добавляем в таблицу все ПКО за указанную дату
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
				$objPHPExcel->setActiveSheetIndex(0)->setCellValueByColumnAndRow(1, 10, 'Выдано '.$e['vidat']);	
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
		
		if('06-30' == substr($pageDataPDF->date, 5) || '12-31' == substr($pageDataPDF->date,5) || $pageDataPDF->title_date2 == $pageDataPDF->date || ($pageDataPDF->removal_date == $pageDataPDF->date)) 						// Если конец полугодия выводим все листы
		{ 
			$objWriter->writeAllSheets();
		} 
		else 
		{ 
			$objWriter->setSheetIndex(0);
		} 

		$ret = "pdf/kassa-{$pageDataPDF->date}-".$pageDataPDF->edit_time.".pdf";	// Путь к файлу PDF
		$objWriter->save($ret);														// Сохраняем
		
		
		Page::setStatus(2, $pageDataPDF->id, $arch);										// Устанавливаем статус "Распечатан"
		
		return $ret;																// Возвращаем путь к файлу PDF, что бы открыть его в браузере
	}

/**********************************************************************************************************/	
	
}








?>