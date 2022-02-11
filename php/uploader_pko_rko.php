#!/usr/bin/php
<?php

$table     = 'pko';
//$azs_id    = [255,256,257,258,259,260,261,262,263,264,265,266,267,268];
//$azs_id    = [112,235,236,237,238,239,240,241,242,243,244,245,246,247,248,249];
//$azs_id    = [103,104,105,106,107,108,109,110,111,371,372,373,374,375,377,378,379,419];
//$azs_id    = [113,114,115,116,117,118,119,120,121,122,123,124,141,142];
//$azs_id    = [96,97,269,270,271,272,273,274,276,277,278,279,306,307,308,309,310,311,312,313,314,315,317,318,319,320,322,329,330,421,440];
$azs_id    = [190];
//$azs_id       = [101,102,135,136,137,138,381,382,383,384,385,386,387,429];
//$azs_id = [51,52,53,56,57,132,133,331,336,434,44,47,50,332,333,334,335,428,436];
$date      = '2022-01-05';//,'2021-06-27'"; //11, 15, 19, 23, 27
$date_from = '2022-01-02';
$pril      = '';

$bd_host = "localhost";
$bd_user = "root";
$bd_pass = "Cwlziqxy1";
$bd_base = "kassa";

$mysqli = new mysqli($bd_host,$bd_user,$bd_pass,$bd_base);
$mysqli->query("SET character_set_database=utf8");
$mysqli->query("SET character_set_client=utf8");
$mysqli->query('SET NAMES "UTF8"');

//$where = "skladId={$azs_id}";
$where = $date_from?"`date`>='{$date_from}' and `date`<='{$date}'":"`date` IN ({$date})";
$where .= $pril?" and `pril`='{$pril}'":"";

echo "SELECT * FROM {$table} WHERE {$where} order by id";

foreach ($azs_id as $v) {
if ($res = $mysqli->query("SELECT * FROM {$table} WHERE skladId={$v} and {$where} order by id")) {
        $azs = $mysqli->query("SELECT * FROM azs WHERE skladid={$v}");
        $azs = $azs->fetch_assoc();
        while ($v = $res->fetch_assoc()) {
                $params['number'] = $v['number'];
                $params['skladID'] = $v['skladID'];
                $params['sum'] = $v['sum'];
                if ($table == 'pko') {
                        $params['sum10'] = $v['sum10'];
                        $params['nds'] = $v['nds'];
                        $params['nds10'] = $v['nds10'];
                        $params['ot'] = $v['ot'];
                }
                $params['datetime'] = $v['datetime'];
                $params['oper'] = $v['oper'];

                if($v['oper'] == '50.02'){
                        $params['osnov'] = 'Розничная выручка (ККТ №'.$v['osnov'].')';
                }
                if($v['oper'] == '62.01, 62.02'){
                        $params['osnov'] = 'Излишняя оплата (подлежащая возврату покупателю)';
                }

                if($v['oper'] == '91.01'){
                        $params['osnov'] = 'Излишки наличных денежных средств в кассе АЗС №'.$_SESSION['skladID'];
                }
                if($v['oper'] == '50.02')
                        $params['pril'] = 'Отчет о закрытии смены №'.$v['pril'];


                if($v['oper'] == '91.01')
                        $params['pril'] =  $v['pril'];
                // ======= RKO
                if ($v['oper'] == '57.3') {
                        $params['vidat'] = $v['vidat'];
                        if($v['osnov'] == 'Сдача выручки в банк'){
                            $params['osnov'] = $v['osnov'].' ('.$azs["ink"].')';
                            $params['pril']  = 'Квитанция к сумке №'.$v['pril'].' от '.preg_replace("/(\d{4})-(\d{2})-(\d{2})/",'${3}.${2}.${1}',$v['date']);
                        }
                                                elseif (mb_substr($v['osnov'],0,38) == 'Сдача выручки в банк '){
                                                        $params['osnov'] = $v['osnov'];                                                         // ОСНОВАНИЕ= Сдача выручки в банк (Банк)
                                                        $params['pril']  = 'Квитанция к сумке №'.$v['pril'].' от '.preg_replace("/(\d{4})-(\d{2})-(\d{2})/",'${3}.${2}.${1}',$v['date']);    // ПРИЛОЖЕНИЕ           = Квитанция к сумке №_____ (от ДАТА)
                                                }
                        else{
                            $params['osnov'] = $v['osnov'];
                            $params['pril']  = $v['pril'];
                        }
                        $params['po']    = $v['pasport'];
                }
                // *****************************************  Возврат покупателю **************************************
                if($v['oper'] == '62.01,62.02' || $v['oper'] == '62.02'){
                    $params['vidat'] = $v['vidat'];
                    $params['osnov'] = $v['osnov'];
                    $params['pril']      = $v['pril'];
                    $params['po']        = $v['po'];
                }
                // *********************************************** Недостача ******************************************
                if($v['oper'] == '94.05.1'){
                    $params['vidat'] = $v['kassir'];
                    $params['osnov'] = $v['osnov'];
                    $params['pril']      = $v['pril'];
                }
                print_r($params);
                toXML($params,$table);
        }
}
}

function toXML($params,$type){

        global $mysqli;

//      if (substr($type,0,3) == 'pko')
//              $type = 'pko';
//      if (substr($type,0,3) == 'rko')
//              $type = 'rko';

        $dom = new domDocument("1.0", "utf-8");
        $database = $dom->createElement("database");
        $database->setAttribute("name", "kassa");
        $dom->appendChild($database);

        $table = $dom->createElement("table");
        $table->setAttribute("name", substr($type,0,3));
        $database->appendChild($table);

        foreach ($params as $key => $value) {
                $column = $dom->createElement("column", $value);
                $column->setAttribute("name", $key);
                $table->appendChild($column);
        }

                $column = $dom->createElement("column", 5000);
                $column->setAttribute("name", "user");
                $table->appendChild($column);

        $datetime = new Datetime($params['datetime']);
        $dom->save(__DIR__."/../export/".substr($type,0,3)."_{$params['number']}_{$params['skladID']}_".$datetime->format('YmdHis').".xml");
        return true;
}
