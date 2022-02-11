<?php
session_start();
$heading = 'Меню';
require_once('php/functions.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash']))
{   
    if(buh_chk($_SESSION['user_id'],$_SESSION['user_hash']))
    {
		if(isset($_REQUEST['azs_id']) && !empty($_REQUEST['azs_id'])){
			
			if($azs_id = azs_exist($_REQUEST['azs_id'])) {
			
				
				if(isset($_REQUEST['number']) && !empty($_REQUEST['number']) && isset($_REQUEST['type']) && !empty($_REQUEST['type'])){
				
					$number = $_REQUEST['number'];
					$type = $_REQUEST['type'];
					
					$doc_id = get_document_id($number,$type);
					$doc_date = get_document_date($type,$azs_id,$doc_id);
					
					if($doc_id){
						if (preg_match("/(pko|rko)(_\d{4})?/", $type, $matches)) {
							if (!empty($matches[2]))
								$y = (int)substr($matches[2],1);
							else
								$y = 0;
							$arch = (!$y || ($y == date('Y')))?'':'_'.$y;
						}
						else
							$arch = '';
						
/* ======= fix-0.0.8						
						if($type == 'rko_2016' || $type == 'pko_2016'){
							$arch = '_2016';
						}
						elseif($type == 'rko_2017' || $type == 'pko_2017'){
							$arch = '_2017';
						}
						elseif($type == 'rko_2018' || $type == 'pko_2018'){
							$arch = '_2018';
						}
						elseif($type == 'rko_2019' || $type == 'pko_2019'){
							$arch = '_2019';
						}
						else { 
							$arch = '';
						}
*/
	/**************************************************************************************************************************************************************/				
						$number = get_document_number($doc_id,$type);
						
						if(chk_close_period($doc_date['date'], $azs_id)){
							if(delete_document($doc_id,$type)){
								
								if(document_number_reindex($number['number_int'],$type,$azs_id,'-')){
									
									if(export_period($number['number_int'],$type,$azs_id)){
										
										if(delete_book_from_date($doc_date['date'], $azs_id, $arch)){
											if(!empty($arch))
												if(transport($azs_id)){
												}
										}
										else{
											$_SESSION["error"] = 'Ошибка удаления листа(-ов) кассовой книги начиная с даты '.$doc_date['date'].'. Обратитесь к бухгалтеру, для удаления листа(-ов) кассовой книги вручную';
											setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
											header("Location: buh_doc_del.php?azs_id={$azs_id}"); 
											exit();										
										}
										
									}
								}
								else{
									$_SESSION["error"] = 'Ошибка реиндекса';  
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: buh_doc_del.php?azs_id={$azs_id}"); 
									exit();										
								}
								
								header("Location: buh_doc_del.php?azs_id={$azs_id}&suc=true"); 
								

								
								exit;
								
							}
						}
						else{
							$_SESSION["error"] = 'Период закрыт';  
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
							header("Location: buh_doc_del.php?azs_id={$azs_id}"); 
							exit();							
						}	
								
						//echo document_number_reindex($doc_id,$type,$azs_id,'-');
	
									
					
					
					
					
					
					
					
					
					
					
					
	/**************************************************************************************************************************************************************/				
		
						
					}
					else{
						$_SESSION["error"] = 'Документ не найден. Проверте номер и тип документа.';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: buh_doc_del.php?azs_id={$azs_id}"); 
						exit();				
					}	
				
				}
			
				
				
				
			
				require_once('template/head.php');
		
	?>

				<div class="container">	
					<p><a href="exit_buh.php" role="button">Выйти</a></p>
					<div class="jumbotron ">
						<h3>Регион <?=$_SESSION['region']?></h3>
						<?php 
							if(isset($_SESSION['error']))
							{
								echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
								unset ($_SESSION['error']);
							}
							if(isset($_GET['suc']))
							{
								echo '<div class="alert alert-success" role="alert">Документ удален</div>';
								unset ($_SESSION['error']);
							}
						?>					
						<a href="buh_console.php">назад</a>
						<br>
						<br>
						<br>
						
						
							<form style="width:400px; margin:0 auto" action="buh_doc_del.php" method="POST">

								<input style="display:none" name="azs_id" value="<?=$azs_id?>">
							
								<div class="form-group">	
									<label for="number">Введите номер документа</label>
									<input type="text" class="form-control" id="number" name="number" required>
								</div>		
								
								<div class="form-group">	
									<label for="type">Тип документа</label>
									<select name="type" class="form-control" required>
										<option></option>
										<?php
											$y = (int)date('Y');
											do {
												echo '<option value="rko'.($y!=date('Y')?'_'.$y:'').'">РКО '.$y.'</option>';
											}
											while ($y-- > 2016);
											$y = (int)date('Y');
											do {
												echo '<option value="pko'.($y!=date('Y')?'_'.$y:'').'">ПКО '.$y.'</option>';
											}
											while ($y-- > 2016);
										?>
<!-- ========== fix-0.0.8										
										<option value="rko">РКО 2020</option>
										<option value="rko_2019">РКО 2019</option>
										<option value="rko_2018">РКО 2018</option>
										<option value="rko_2017">РКО 2017</option>
										<option value="rko_2016">РКО 2016</option>
										<option value="pko">ПКО 2020</option>
										<option value="pko_2019">ПКО 2019</option>
										<option value="pko_2018">ПКО 2018</option>
										<option value="pko_2017">ПКО 2017</option>
										<option value="pko_2016">ПКО 2016</option>
-->										
									</select>	
								</div>	
								
								<div class="form-group">
									<input type="submit" class="form-control" value="Удалить">
								</div>	
								
							</form>
<?
					$q = "SELECT * FROM `pko` WHERE skladID=? AND `pko`.`date` > (CURRENT_TIMESTAMP - INTERVAL 10 DAY) AND `status` = '1' ORDER BY number_int DESC";
					$result = $mysqli->stmt_init();
					if ($result->prepare($q))
					{
						$result->bind_param("i",$azs_id);
						$result->execute();
						$obj = $result->get_result();
			?>
			<div class="col-md-6">
				<h3>ПКО</h3>
				<table class="table table-hover">
					  <thead>
						<tr>
						  <th>Номер</th>
						  <th>Дата</th>
						  <th>ККМ</th>
						  <th>Сумма</th>
						</tr>
					  </thead>		
					  <tbody>
			<?php			
						while ($data = $obj->fetch_object()){
							$date = new DateTime($data->datetime);
			?>
						<tr>
						  <th><?=$data->number?></th>
						  <td><?=$date->format('d.m.Y')?></td>
						  <td><?=$data->osnov?></td>
						  <td><?=$data->sum + $data->sum10?></td>

						</tr>
			<?php							
						}						
						$result->close();
			?>
					  </tbody>
					</table>
			</div>
			<?php						
					}	
			
					$q = "SELECT * FROM `rko` WHERE skladID=? AND `rko`.`date` > (CURRENT_TIMESTAMP - INTERVAL 10 DAY) AND `status` = '1' ORDER BY datetime DESC";
					$result = $mysqli->stmt_init();
					if ($result->prepare($q))
					{
						$result->bind_param("i",$azs_id);
						$result->execute();
						$obj = $result->get_result();
			?>
			<div class="col-md-6">
				<h3>РКО</h3>
				<table class="table table-hover">
					  <thead>
						<tr>
						  <th>Номер</th>
						  <th>Дата</th>
						  <th>Время</th>
						  <th>Сумма</th>
						</tr>
					  </thead>		
					  <tbody>
			<?php			
						while ($data = $obj->fetch_object()){
							$date = new DateTime($data->datetime);
			?>
						<tr>
						  <th><?=$data->number?></th>
						  <td><?=$date->format('d.m.Y')?></td>
						  <td><?=$date->format('H:i:s')?></td>
						  <td><?=$data->sum?></td>

						</tr>
			<?php							
						}						
						$result->close();
			?>
					  </tbody>
					</table>	
				</div>
			<?php						
					}				
							
			?>				
							
							
					</div>
					
		 
				  </div>

			<?php

				require_once('template/bottom.php');
			}
			else{
				$_SESSION["error"] = 'АЗС не найдена';
				setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
				header("Location: buh_console.php"); 
				exit();				
			}
		}
    }
    else
    {
		$_SESSION["error"] = 'Неверный логин или пароль';
		setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
        header("Location: buh_login.php"); 
		exit();	
    }
}
else
{
	$_SESSION["error"] = 'Неверный логин или пароль1';
	setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
	header("Location: buh_login.php"); 
	exit();
}

?>
