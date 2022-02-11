<?php
session_start();
$heading = 'Меню';
require_once('php/functions.php');

if (isset($_SESSION['user_id']) and isset($_SESSION['user_hash']))
{   
    if(buh_chk($_SESSION['user_id'],$_SESSION['user_hash']))
    {

        if(isset($_GET['transport']) && !empty($_GET['transport'])){
            transport($_GET['transport']);
            header("Location: buh_console.php?azs_id=".$_GET['transport']."&suc=1");
        }

	
		require_once('template/head.php');
		
		if(isset($_GET['azs_id']) && !empty($_GET['azs_id'])) $azs_id = $_GET['azs_id']; else $azs_id = 0;
		
		

		
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
			 ?>					

			<?
				$q = "
					SELECT 
						`name`, 
						`skladID`
					FROM 
						`azs` 
					WHERE 
						`region` = '{$_SESSION['region']}'
				";			
			
				if ($result = $mysqli->query($q)){
					if($result->num_rows > 0){
						echo '
						<form method="GET">
						  <div class="form-group">
							<label for="azs_id">Выберите АЗС</label>
							<select name="azs_id" class="form-control"  onchange="this.form.submit();">
								<option></option>
						';
						while ($data = $result->fetch_object()){
							$sel = ($azs_id == $data->skladID) ? 'selected' : '';
							echo '<option '.$sel.' value="'.$data->skladID.'">'.$data->name.'</option>';		
						}
						echo '
							</select>
						  </div>			
						</form>			
						';
					}
					else{
						$_SESSION["error"] = 'В регионе '.$_SESSION['region'].' не найдены АЗС';
						setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
						header("Location: editkkm.php"); 
						exit();		
					}		
				}
				else
				{
					$_SESSION["error"] = 'Ошибка при получении списка АЗС '.$mysqli->error;
					setlog($_SESSION['user_id'],$_SESSION["error"],$page_);
					header("Location: editkkm.php"); 
					exit();	
				}
				
				
/**************************************************************************************************************************************************************/				
				

				if(isset($_GET['azs_id']) && !empty($_GET['azs_id'])){

                    if(isset($_GET['suc']))
                    {
                        echo '<div class="alert alert-success" role="alert">Остатки перенесены</div>';
                    }
				
					echo '
						<div class="mymenu">
							<p><a class="btn btn-primary" href="/buh_doc_del.php?azs_id='.$_GET['azs_id'].'" role="button">Удаление документа</a></p>
							<p><a class="btn btn-primary" href="/buh_doc_create.php?azs_id='.$_GET['azs_id'].'" role="button">Создание документа</a></p>
							<p><a class="btn btn-primary" href="/buh_book.php?azs_id='.$_GET['azs_id'].'" role="button">Переформировать книгу '.date('Y').'</a></p>
							<p><a class="btn btn-primary" href="/buh_console.php?transport='.$_GET['azs_id'].'" role="button">Перенести остатки с '.(date('Y') - 1).' года</a></p>
							
						</div>	 					
					';
					
				
				}				
				//<p><a class="btn btn-primary" href="/buh_book.php?azs_id='.$_GET['azs_id'].'" role="button">Переформирование книги 2016</a></p>
				
				
				
				
				
				
				
				
				
				
/**************************************************************************************************************************************************************/				
			?>
			



			
		 </div> 
	  </div>

<?php

		require_once('template/bottom.php');
	
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
