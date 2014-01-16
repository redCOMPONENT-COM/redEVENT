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
	case 5:
		$file = 'schemas/AttendeesRQ_mae.xml';
		break;
	case 6:
		$file = 'schemas/AttendeesRQ_mae2.xml';
		break;
	case 7:
		$file = 'schemas/AttendeesRQ_mae3.xml';
		break;
	case 8:
		$file = 'schemas/AttendeesRQ_mae4.xml';
		break;
	case 'mae5':
		$file = 'schemas/AttendeesRQ_mae5.xml';
		break;
	case 9:
		$file = 'schemas/Wrongformat.xml';
		break;
}

switch ($_GET['target'])
{
	case 'julmaersk':
		$target = "http://juldevmaersk.com.web14.redhost.dk";
		break;

	default:
		$target = "http://localhost/jl25";
		break;
}

$debug = isset($_GET['debug']) ? 1 : 0;

if (!file_exists($file))
{
	exit('error no file: ' . $file);
}

$xml_builder = file_get_contents($file);

$ch = curl_init($target . '/index.php?option=com_redeventsync&client=maersk' . ($debug ? '&debug=1' : ''));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_builder);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
// curl_setopt($ch, CURLOPT_REFERER, '');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

if(!$ch_result = curl_exec($ch))
{
	trigger_error(curl_error($ch));
}

curl_close($ch);
// Print CURL result.
echo $ch_result;
