<?php


require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/lib.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

$sql = 'SELECT * from avibase WHERE journal="Auk" order by year desc';// LIMIT 100';

$sql = 'SELECT * from avibase WHERE journal="Ann. and Mag. Nat. Hist." order by year desc';// LIMIT 100';

$sql = 'SELECT * from avibase WHERE journal="Journ. f. Orn." order by year desc'; // LIMIT 100';
$sql = 'SELECT * from avibase WHERE journal="Ibis" order by year desc'; // LIMIT 100';
$sql = 'SELECT * from avibase WHERE journal="Journ. Ornith." order by year desc'; 

$journal = 'Journ. f . Orn.';
$journal = 'Emu';
//$journal = 'Ibis';
$journal = 'Mitt. Zool. Mus. Berlin';

$journal = 'Ann. and Mag. Nat. Hist';

$journal = 'Journ. Ornith.';

$sql = 'SELECT * from avibase WHERE journal="' . $journal . '" AND doi is NULL'; 

$sql = 'SELECT * from avibase WHERE issn="0021-8375" AND doi is NULL'; 

// Ostrich

$sql = 'SELECT * from avibase WHERE issn="0030-6525" AND doi is NULL'; 



//$sql .= ' AND series=6';
//$sql = 'SELECT * from avibase WHERE peters_line=71441'; 

//$sql = 'SELECT * from avibase WHERE peters_line=234284';






$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$url = 'http://localhost/~rpage/microcitation/www/index.php?';
	
	$issn = $result->fields['issn'];
	$volume = $result->fields['volume'];
	$page = $result->fields['page'];
	$year = $result->fields['year'];
	$series = '';
	
	if ($result->fields['series'])
	{
		$series = $result->fields['series'];
	}
	
	$url .= "issn=$issn&volume=$volume&page=$page&series=$series";

	//$url .= "issn=$issn&volume=$volume&page=$page&year=$year";
	
	//echo $url . "\n";
	
	$json = get($url);
	
	$obj = json_decode($json);
	
	print_r($obj);
	
	echo '-- ' . $obj->sql . ";\n";
	
	if ($obj->found)
	{
		//echo $obj->doi . "\n";
		
		if (count($obj->results) == 1)
		{
			echo 'UPDATE avibase SET doi="' . $obj->results[0]->doi . '" WHERE peters_line=' . $result->fields['peters_line'] . ';' . "\n";
		}
	}

	$result->MoveNext();	
	
}

?>
