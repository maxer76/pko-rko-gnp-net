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
			
				
				$_SESSION["buh"] = get_buh_fio($azs_id);
				$_SESSION["position"] = get_dir_pos($azs_id);
				$_SESSION["skladID"] = $azs_id;
			
/************************************************************************************************************/
				if(isset($_POST['manual']) && !empty($_POST['manual'])){
				
				
				  if(chk_close_period($_POST['datetime'])){

						if($type = chk_type($_POST['type'])){
					
							if($type == 'pko'){
							
								if(
									!isset($_POST['datetime']) or
									empty($_POST['datetime']) or	
									!isset($_POST['schet']) or
									empty($_POST['schet'])	or	
									!isset($_POST['sum']) or
									empty($_POST['sum']) or
									!isset($_POST['creator']) or
									empty($_POST['creator']) or
									!isset($_POST['ot']) or
									empty($_POST['ot']) or
									!isset($_POST['osnov']) or
									empty($_POST['osnov']) or
									!isset($_POST['pril']) or
									empty($_POST['pril'])
								){
									$_SESSION["error"] = 'Некоторые поля формы ручного ввода ПКО были заполненны не верно. ';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: buh_console.php?type={$type}"); 
									exit();	
								}else{
									
										
										$datetime = new DateTime($_POST['datetime']);
										
										if ($datetime->format('Y') < date('Y')) {
											$arch = '_'.$datetime->format('Y');
											$type = $type.$arch;
//											$stavka= 18;
										}
										else {
											if ($datetime->format('Y') != date('Y')) {
												$arch = '_'.$datetime->format('Y');
												$type = $type.$arch;
											}
											else
												$arch = '';
//											$stavka= 20;
										}
										if ($datetime->format('Y') < 2019)
											$stavka = 18;
										else
											$stavka = 20;
/* ================= fix-0.0.8										
										if($datetime->format('Y') == '2015'){
											$type = $type.'_2015';
											$arch = '_2015';
											$stavka= 18;
										}
										elseif($datetime->format('Y') == '2016'){
											$type = $type.'_2016';
											$arch = '_2016';
											$stavka= 18;
										}										
										elseif($datetime->format('Y') == '2017'){
											$type = $type.'_2017';
											$arch = '_2017';
											$stavka= 18;
										}										
										elseif($datetime->format('Y') == '2018'){
											$type = $type.'_2018';
											$arch = '_2018';
											$stavka= 18;
										}										
										elseif($datetime->format('Y') == '2019'){
											$type = $type.'_2019';
											$arch = '_2019';
											$stavka= 20;
										}										
										else{
											$arch = '';
											$stavka= 20;
										}
*/


										
										if (!($last_doc_date = get_document_date($type,$azs_id))) {
											$reindex = false;
											$number = set_number($type);
										}
										else {
											$last_doc_date = new DateTime($last_doc_date['datetime']);
											if($datetime > $last_doc_date){
												$reindex = false;
												$number = set_number($type);
											}
											else{
												$reindex = true;
												$number = insert_number($type,$azs_id,$datetime->format('Y-m-d H:i:s'));
											}
										}

										$number_int = $_SESSION["number_int"];
										$date = $datetime->format('Y-m-d');
										$time = $datetime->format('H:i:s');
										$azs = 'АЗС '.$azs_id;
										$skladID = $azs_id;
										$sum = amount_summ($_POST['sum']);
										$nds = round($sum * $stavka / (100 + $stavka), 2);
										$ot = $_POST['ot'];
										$schet = $_POST['schet'];
										$creator = $_POST["creator"];
										$osnov = $_POST['osnov'];
										$pril = $_POST['pril'];	
										$sum10 = 0;
										$nds10 = 0;
										$ret = 1;
										$smena = '';	

										$goods = 5;
										
										$buh = $_SESSION["buh"];
										$dir = $_SESSION["buh"];
										$dol = $_SESSION["position"];


										
										if(trim($number) != ''){
											if($reindex)
											{
												if(document_number_reindex($number_int - 1,$type,$azs_id,'+'))
												{
                                                    if (insert_pko($number, $number_int, $datetime, $date, $time, $azs, $skladID, $sum, $nds, $sum10, $nds10, $ot, $schet, $creator, $osnov, $buh, $dir, $dol, $pril, $ret, $smena, $goods))
                                                    {
                                                        if (export_period($number_int, $type, $azs_id)) {

                                                            if (delete_book_from_date($datetime->format('Y-m-d'), $azs_id, $arch)) {
                                                                if (!empty($arch))
                                                                    if (transport($azs_id)) {
                                                                        header("Location: buh_doc_create.php?azs_id={$azs_id}&suc=true");
                                                                        exit;
                                                                    }
                                                            }

                                                        }
                                                    } else {
                                                        $error = 1;
                                                        $log .= '<p>Ошибка при записи в БД. Функция вернула FALSE</p>';
                                                    }
                                                }
											}
											else{
												if(insert_pko($number,$number_int,$datetime,$date,$time,$azs,$skladID,$sum,$nds,$sum10,$nds10,$ot,$schet,$creator,$osnov,$buh,$dir,$dol,$pril,$ret,$smena)){
													if(export_period($number_int,$type,$azs_id)){
														
														if(delete_book_from_date($datetime->format('Y-m-d'), $azs_id, $arch)){
															if(!empty($arch))
																if(transport($azs_id)){
																	header("Location: buh_doc_create.php?azs_id={$azs_id}&suc=true"); 
																	exit;
																}
														}
														
														
													}																											
												}
												else{
													$error = 1;
													$log .= '<p>Ошибка при записи в БД. Функция вернула FALSE</p>';																		
												}									
											}
										}
										else{
											$_SESSION["error"] = 'Ошибка присвоения номера. Попробуйте еще раз';
											setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
											echo 'error';
											header("Location: buh_console.php"); 
											exit();							
										}											
										

								}	
							}
							
							if($type == 'rko'){
													
								if(
									!isset($_POST['datetime']) or
									empty($_POST['datetime']) or	
									!isset($_POST['creator']) or
									empty($_POST['creator']) or								
									!isset($_POST['sum']) or
									empty($_POST['sum']) or								
									!isset($_POST['oper']) or
									empty($_POST['oper'])	or	
									!isset($_POST['vidat']) or
									empty($_POST['vidat']) or
									!isset($_POST['osnov']) or
									empty($_POST['osnov'])
								){
									$_SESSION["error"] = 'Некоторые поля формы ручного ввода РКО были заполненны не верно. ';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: buh_console.php?type={$type}"); 
									exit();	
								}else{
									
										
										$datetime = new DateTime($_POST['datetime']);

										if ($datetime->format('Y') != date('Y')) {
											$arch = '_'.$datetime->format('Y');
											$type = $type.$arch;
										}
										else
											$arch = '';
/* ====== fix-0.0.8										
										if($datetime->format('Y') == '2015'){
											$type = $type.'_2015';
											$arch = '_2015';
										}
										elseif($datetime->format('Y') == '2016'){
											$type = $type.'_2016';
											$arch = '_2016';
										}										
										elseif($datetime->format('Y') == '2017'){
											$type = $type.'_2017';
											$arch = '_2017';
										}										
										elseif($datetime->format('Y') == '2018'){
											$type = $type.'_2018';
											$arch = '_2018';
										}										
										elseif($datetime->format('Y') == '2019'){
											$type = $type.'_2019';
											$arch = '_2019';
										}										
										else{
											$arch = '';									
										}
*/										
										if (!($last_doc_date = get_document_date($type,$azs_id))) {
											$reindex = false;
											$number = set_number($type);
										}
										else {
											$last_doc_date = new DateTime($last_doc_date['datetime']);
											if($datetime > $last_doc_date){
												$reindex = false;
												$number = set_number($type);
											}
											else{
												$reindex = true;
												$number = insert_number($type,$azs_id,$datetime->format('Y-m-d H:i:s'));
											}
										}
										$number_int = $_SESSION["number_int"];
										$date = $datetime->format('Y-m-d');
										$time = $datetime->format('H:i:s');
										$azs = 'АЗС '.$azs_id;
										$skladID = $azs_id;
										$sum = amount_summ($_POST['sum']);
										$oper = $_POST['oper'];
										$vidat = $_POST['vidat'];
										$osnov = $_POST['osnov'];
										if(!isset($_POST['pril']) and empty($_POST['pril'])) $pril = ''; else $pril = $_POST['pril'];
										$kassir = $_POST["creator"];
										$pasport = '';
										if(!isset($_POST['po']) and empty($_POST['po'])){
											$po = '-';
										}
										else{
											$po = $_POST['po'];
										}	

										$buh = $_SESSION["buh"];
										$dir = $_SESSION["buh"];
										$dol = $_SESSION["position"];	

										$pasport_q = "
											SELECT
												`pasport`
											FROM
												`users`
											WHERE
												`fio` = '{$kassir}'
											AND
												`azs_id` = '{$skladID}'
											LIMIT 1
										";
										
										if($pasport_r = $mysqli->query($pasport_q)){
											while($pasport_o = $pasport_r->fetch_object()){
												$pasport = $pasport_o->pasport;
											}
										}
							
										if(trim($number) != ''){
											if($reindex){
												if(document_number_reindex($number_int - 1,$type,$azs_id,'+'))
													if(insert_rko($number,$number_int,$datetime,$date,$time,$azs,$skladID,$sum,$oper,$vidat,$osnov,$pril,$po,$kassir,$buh,$dir,$dol,'',$pasport)){
														if(export_period($number_int,$type,$azs_id)){
															
															if(delete_book_from_date($datetime->format('Y-m-d'), $azs_id, $arch)){
															  if(!empty($arch))
																if(transport($azs_id)){
																	header("Location: buh_doc_create.php?azs_id={$azs_id}&suc=true"); 
																	exit;
																}
																
															}
															
															
														}																												
													}
													else{
														$error = 1;
														$log .= '<p>Ошибка при записи в БД. Функция вернула FALSE</p>';																		
													}	
											}
											else{
												if(insert_rko($number,$number_int,$datetime,$date,$time,$azs,$skladID,$sum,$oper,$vidat,$osnov,$pril,$po,$kassir,$buh,$dir,$dol,'',$pasport)){
													if(export_period($number_int,$type,$azs_id)){
														
														if(delete_book_from_date($datetime->format('Y-m-d'), $azs_id, $arch)){
															if(!empty($arch))
																if(transport($azs_id)){
																	header("Location: buh_doc_create.php?azs_id={$azs_id}&suc=true"); 
																	exit;
																}
														}
														
														
													}																											
												}
												else{
													$error = 1;
													$log .= '<p>Ошибка при записи в БД. Функция вернула FALSE</p>';																		
												}									
											}
										}
										else{
											$_SESSION["error"] = 'Ошибка присвоения номера. Попробуйте еще раз';
											setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
											echo 'error';
											header("Location: buh_console.php"); 
											exit();							
										}											
										

								}	
							
							
							}

						}
						else{
							$_SESSION["error"] = 'Неверный тип документа';
							setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
							echo 'error';
							header("Location: buh_console.php"); 
							exit();							
						}					
					}
					else{
						$_SESSION["error"] = 'Период закрыт';  
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						echo 'error';
						header("Location: buh_console.php"); 
						exit();							
					}					
					
					header("Location: buh_console.php"); 
					exit;
				}

/************************************************************************************************************/			
			
			
			
			
			
			
				require_once('template/head.php');
		
	?>

				<div class="container">	
					<p><a href="exit_buh.php" role="button">Выйти</a></p>
					<div class="jumbotron ">
						<h3>Регион <?=$_SESSION['region']?> - АЗС <?=$_REQUEST['azs_id']?></h3>
						<?php 
							if(isset($_SESSION['error']))
							{
								echo '<div class="alert alert-danger" role="alert">'.$_SESSION['error'].'</div>';
								unset ($_SESSION['error']);
							}
							if(isset($_GET['suc']))
							{
								echo '<div class="alert alert-success" role="alert">Успешно выполенено</div>';
								unset ($_SESSION['error']);
							}
						?>					

						

<?
				if(!isset($_REQUEST['type']) && empty($_REQUEST['type']))
					echo '
						<a href="buh_console.php">назад</a>
						<br>
						<br>
						<br>					
						<div class="mymenu">
							<p><a class="btn btn-primary" href="/buh_doc_create.php?azs_id='.$_REQUEST['azs_id'].'&type=rko" role="button">Создать РКО</a></p>
							<p><a class="btn btn-primary" href="/buh_doc_create.php?azs_id='.$_REQUEST['azs_id'].'&type=pko" role="button">Создать ПКО</a></p>
						</div>	 					
					';
				else{
					
					if($type = chk_type($_REQUEST['type'])){

						if($type == 'pko'){
							echo '
								<a href="/buh_doc_create.php?azs_id='.$_REQUEST['azs_id'].'">назад</a>
								<br>
								<br>
								<h3>Создать ПКО</h3>
								<br>
							';							
						
						
							echo '
							<form class="forms" action="buh_doc_create.php" method="POST">
							
									<input style="display:none" type="text" name="type" value="pko">
									<input style="display:none" type="text" name="manual" value="manual">
									<input style="display:none" type="text" name="azs_id" value="'.$azs_id.'">
									
									<div class="form-group">
										<label for="datetime">Дата и время (дд.мм.гггг чч:мм)</label>
									  <!-- Элемент HTML с id равным datetimepicker1 -->
									  <div class="input-group date" id="datetimepicker1">
										<input name="datetime" id="datetime"  type="text" class="form-control" />
										<span class="input-group-addon">
										  <span class="glyphicon glyphicon-calendar"></span>
										</span>
									  </div>
									</div>		
								
									
									  <div class="form-group">
										<label for="schet">Счет:</label>
											<select name="schet" class="form-control" required>	
												<option></option>
												<option value="91.01">91.01</option>
												<option value="50.02">50.02</option>';
							if ($azs_id==276)
								echo '<option value="62.02">62.02</option>';
							echo '				
											</select>
									  </div>
									  
									<div class="form-group">	
										<label for="sum">Сумма документа</label>
										<input type="number" step="0.01" class="form-control" id="sum" name="sum" placeholder="Сумма" required>
									</div>									  
									  
							';							

							$q = "
								SELECT 
									`fio`, 
									`user_id`,
									`role_id`
								FROM 
									`users` 
								WHERE 
									`azs_id` = '{$azs_id}'
							";
							if ($result = $mysqli->query($q)){
								if($result->num_rows > 0){
								
									echo '
									  <div class="form-group">
										<label for="azs_id">Принято от:</label>
										<select name="ot" class="form-control" required>	
											<option></option>
									';
									while ($data = $result->fetch_object()){
										if($data->role_id == 2){
											echo '<option value="'.$_SESSION['buh'].'">'.$_SESSION['buh'].'</option>';	
										}
										else
											echo '<option value="'.$data->fio.'">'.$data->fio.'</option>';		
									}
									echo '
										</select>
									  </div>			
									';								
									$result->data_seek(0);
									echo '
									  <div class="form-group">
										<label for="creator">От чьего имени создан документ</label>
										<select name="creator" class="form-control" required>	
											<option></option>
									';
									while ($data = $result->fetch_object()){
										if($data->role_id != 2){
											echo '<option value="'.$data->fio.'">'.$data->fio.'</option>';	
										}
									}
									echo '
										</select>
									  </div>			
									';
									

								}
								else{
									$_SESSION["error"] = 'Для АЗС не созданы пользователи';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: buh_console.php"); 
									exit();		
								}		
							}
							else
							{
								$_SESSION["error"] = 'Ошибка при получении списка пользователей '.$mysqli->error;
								setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
								header("Location: buh_console.php"); 
								exit();	
							}		

							$q = "
								SELECT
									`serial`
								FROM
									`kkm`
								WHERE
									`azs` = '{$azs_id}'
								ORDER BY
									`serial`
							";	
						
							if ($result = $mysqli->query($q)){	
								if($result->num_rows > 0){
									$serials = '';
									$serials.='<div class="form-group">';
									$serials.='<label for="osnov">Основание</label>';
									$serials.='<select class="form-control" name="osnov" required>';
									$serials.='<option></option>';
									if ($azs_id==276)
										$serials .= '<option value="Излишняя оплата (подлежащая возврату покупателю) при совершении розничной реализации на ТЗК">Излишняя оплата (подлежащая возврату покупателю) при совершении розничной реализации на ТЗК</option>';
									$serials.='<option value="Излишки наличных денежных средств в кассе АЗС №'.$azs_id.'">Излишки наличных денежных средств в кассе АЗС №'.$azs_id.'</option>';
									while ($data = $result->fetch_object()){
											$serials.='<option value="'.trim($data->serial).'">Розничная выручка (ККТ №'.trim($data->serial).')</option>';
									}
									$serials.='</select>';
									$serials.='</div>';
									echo $serials;
								}
								else{
									$_SESSION["error"] = 'Для АЗС с №'.$azs_id.' не добавлены ККМ';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: buh_console.php?type={$type}"); 
									exit();		
								}
							}							

							echo '<div class="form-group">
									<label for="pril">Приложение или номер Z-отчета</label>
										<input type="text" class="form-control" id="pril" name="pril" placeholder="" required>
									</div>
									<div class="form-group">
										<input type="submit" class="form-control" value="Сохранить">
									</div>										
						</form>
						';				

							
						}
						
						if($type == 'rko'){
							echo '
								<a href="/buh_doc_create.php?azs_id='.$_REQUEST['azs_id'].'">назад</a>
								<br>
								<br>
								<h3>Создать РКО</h3>
								<br>
							';							
							
							$ink = get_bank($azs_id);
						
							echo '
							<form class="forms" action="buh_doc_create.php" method="POST">
							
									<input style="display:none" type="text" name="type" value="rko">
									<input style="display:none" type="text" name="manual" value="manual">
									<input style="display:none" type="text" name="azs_id" value="'.$azs_id.'">
									
									<div class="form-group">
										<label for="datetime">Дата и время (дд.мм.гггг чч:мм)</label>
									  <!-- Элемент HTML с id равным datetimepicker1 -->
									  <div class="input-group date" id="datetimepicker1">
										<input name="datetime" id="datetime"  type="text" class="form-control" />
										<span class="input-group-addon">
										  <span class="glyphicon glyphicon-calendar"></span>
										</span>
									  </div>
									</div>		
								
									
									  <div class="form-group">
										<label for="oper">Счет:</label>
											<select name="oper" class="form-control" required>	
												<option></option>
												<option value="57.3">57.3</option>
												<option value="94.05.1">94.05.1</option>
												<option value="62.01,62.02">62.01,62.02</option>
											</select>
									  </div>
									  
									<div class="form-group">	
										<label for="sum">Сумма документа</label>
										<input type="number" step="0.01" class="form-control" id="sum" name="sum" required>
									</div>										
									  
							';							


							$q = "
								SELECT 
									`fio`, 
									`user_id`,
									`role_id`
								FROM 
									`users` 
								WHERE 
									`azs_id` = '{$azs_id}'
								ORDER BY
									`fio`
							";
							if ($result = $mysqli->query($q)){
								if($result->num_rows > 0){
									echo '
									  <div class="form-group">
										<label for="vidat">Кому выдать</label>
										<select name="vidat" class="form-control" required>	
											<option></option>
									';
									while ($data = $result->fetch_object()){
										if($data->role_id == 2){
											echo '<option value="'.$_SESSION['buh'].'">'.$_SESSION['buh'].'</option>';	
										}
										else
											echo '<option value="'.$data->fio.'">'.$data->fio.'</option>';		
									}
									echo '
										</select>
									  </div>			
									';
									
									$result->data_seek(0);
									echo '
									  <div class="form-group">
										<label for="creator">От чьего имени создан документ</label>
										<select name="creator" class="form-control" required>	
											<option></option>
									';
									while ($data = $result->fetch_object()){
										if($data->role_id != 2){
											echo '<option value="'.$data->fio.'">'.$data->fio.'</option>';		
										}
									}
									echo '
										</select>
									  </div>			
									';									
								}
								else{
									$_SESSION["error"] = 'Для АЗС не созданы пользователи';
									setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
									header("Location: editkkm.php"); 
									exit();		
								}		
							}
							else
							{
								$_SESSION["error"] = 'Ошибка при получении списка пользователей '.$mysqli->error;
								setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
								header("Location: editkkm.php"); 
								exit();	
							}						

							echo '
									  <div class="form-group">
										<label for="osnov">Основание</label>
											<select name="osnov" class="form-control" required>	
												<option></option>
												<option value="Сдача выручки в банк">Сдача выручки в банк</option>
												<option value="Недостача наличных денежных средств в кассе АЗС №'.$azs_id.'">Недостача наличных денежных средств в кассе АЗС №'.$azs_id.'</option>
												<option value="Возврат оплаты за товар покупателю">Возврат оплаты за товар покупателю</option>
											</select>
									  </div>							

									<div class="form-group">
										<label for="pril">Приложение</label>
										<input type="text" class="form-control" id="pril" name="pril" placeholder="" >
									</div>	
									<div class="form-group">
										<label for="po">Выдать по</label>
										<input type="text" class="form-control" id="po" name="po" placeholder="Выдать по">
									</div>										
									<div class="form-group">
										<input type="submit" class="form-control" value="Сохранить">
									</div>										
						</form>
						';				

							
						}						
						
					}
					else
					{
						$_SESSION["error"] = 'Неверный тип документа';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: buh_console.php"); 
						exit();	
					}					
					
				}

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
