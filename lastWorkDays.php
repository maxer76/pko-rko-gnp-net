<?php

require_once('php/functions.php');

$year = 2020;

$azs_query = "
	SELECT
		`skladID`
	FROM
		`azs`
	WHERE
		`azs`.`skladID` = 'значение(№АЗС)'
";

if($azs_result = $mysqli->query($azs_query))
{
	while($azs = $azs_result->fetch_object())
	{
		for($i=1;$i<13;$i++)
		{
			$date = new DateTime($year.'-'.$i.'-'.'1');
			$date->format('Y-m-t');
			
			$lastworkday_query = "
				INSERT INTO
					`lastworkday`
						(`azs_id`,`date`)
				VALUES
					('".$azs->skladID."','".$date->format('Y-m-t')."')
			";
			
			if($lastworkday_result = $mysqli->query($lastworkday_query))
			{
				
			}
			
		}
	}
}