<?php

switch ($_GET['test'])
{
	case 1:
		$file = 'schemas/AttendeesRQ_1.xml';
		break;
	case 2:
		$file = 'schemas/SessionsRQ_1.xml';
		break;
	case 3:
		$file = 'schemas/CustomersRQ_1.xml';
		break;
	case 4:
		$file = 'schemas/SessionsRS_1.xml';
		break;
}

if (!file_exists($file))
{
	exit('error no file: ' . $file);
}

$xml_builder = file_get_contents($file);

$ch = curl_init('http://localhost/jl25/index.php?option=com_redeventsync');
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_builder);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
// curl_setopt($ch, CURLOPT_REFERER, '');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

if(! $ch_result = curl_exec($ch))
{
	trigger_error(curl_error($ch));
}

curl_close($ch);
// Print CURL result.
echo $ch_result;
