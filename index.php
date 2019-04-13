<?php

// Taxon name data browser

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');


//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysqli');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;


//--------------------------------------------------------------------------------------------------
function default_display()
{
	global $config;
	
	echo '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<base href="' . $config['web_root'] . '" />
    <style type="text/css">
      body { margin: 20px; font-family:sans-serif;}
      input[type="text"] {
    		font-size:14px;
	  }
	  button {font-size:14px;}
    </style>


		<title>' . $config['site_name'] . '</title>
	</head>
	<body>
		<div style="float:right;">
			<form method="get" action="index.php">
			<input type="text"  name="q" id="q" value="" placeholder="Genus"></input>
			<input type="submit" value="Search" ></input>
			</form>
		</div>
		<h1>Taxon Name Browser</h1>
	</body>
</html>';
}


//--------------------------------------------------------------------------------------------------
function display_search($query, $type = 'genus')
{
	global $config;
	global $db;
	
	$found = false;
	
	$query = trim($query);
	
	if (preg_match('/^\w+/', $query))
	{
		switch($type)
		{
			case 'genus':
				$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE species_subspecies LIKE ' . $db->qstr($query . '%') . ' LIMIT 1';
			
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
				
				if ($result->NumRows() == 1)
				{
					$genus = $query;
					display_genus($query);
					$found = true;
				}
				break;

			case 'publication':
				$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE journal = ' . $db->qstr($query) . ' LIMIT 1';
			
				$result = $db->Execute($sql);
				if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
				
				if ($result->NumRows() == 1)
				{
					$genus = $query;
					display_publication($query);
					$found = true;
				}
				break;
		
			default:
				break;
		}
				
				
	}
	
	if (!$found)
	{
		echo '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<base href="' . $config['web_root'] . '" />
<style type="text/css">
      body { margin: 20px; font-family:sans-serif;}
      input[type="text"] {
    		font-size:14px;
	  }
	  button {font-size:14px;}
    </style>

<title>' . $config['site_name'] . '</title>
	</head>
	<body>
		<div style="float:right;">
			<form method="get" action="index.php">
			<input type="text"  name="q" id="q" value="" placeholder="Genus"></input>
			<input type="submit" value="Search" ></input>
			</form>
		</div>
		<p>Sorry, your search for "' . $query . '" didn\'t match any data (note that you can only search for genus names).</p>
	</body>
</html>';

	
	
	
	}

}

//--------------------------------------------------------------------------------------------------
function display_publication($publication)
{
	global $config;
	 
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE journal = "' . $publication . '" ORDER BY CAST(volume AS SIGNED), CAST(page AS SIGNED)';
	display_query($sql, "Names in publication \"" . $publication . "\"");
}

//--------------------------------------------------------------------------------------------------
function display_genus($genus)
{
	global $config;
	
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE species_subspecies LIKE  "' . $genus . '%" ORDER BY species_subspecies';
	display_query($sql, 'Genus "' . $genus . '"');
}


//--------------------------------------------------------------------------------------------------
function display_query($sql, $title = "Results")
{
	global $config;
	global $db;
				
	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$record = new stdclass;
		
		$record->id = $result->fields['peters_line'];
		
		
		if ($result->fields['peters_pageid'])
		{
			$record->pageid = $result->fields['peters_pageid'];
		}		
		
		$record->name = $result->fields['species_subspecies'];
		
		if ($result->fields['basionym'])
		{
			$record->basionym = $result->fields['basionym'];
		}		
		
		$record->html = '<i>' . utf8_encode($record->name) . '</i>';
		
		$record->html .= ' ' .  utf8_encode($result->fields['author']);;
		
		if ($result->fields['journal'] != '')
		{
				
			$record->publication = '<a href="?p=' . trim(utf8_encode($result->fields['journal'])) . '">' . trim(utf8_encode($result->fields['journal'])) . '</a> ';

			if ($result->fields['series'] != '')
			{
				$record->publication .= ' (' . $result->fields['series'] . ')';
			}
			
			$record->publication .= $result->fields['volume'];
				
			if ($result->fields['issue'] != '')
			{
				$record->publication .= '(' . $result->fields['issue'] . ')';
			}
			
			$record->publication .= ':' . $result->fields['page'];
			$record->publication .= ' ' . $result->fields['year'];
	    }
		
		// identifiers
		$identifiers = array('issn', 'doi', 'jstor', 'biostor', 'bhl', 'cinii', 'url', 'pdf', 'handle');
		foreach ($identifiers as $i)
		{
			if ($result->fields[$i] != '')
			{
				$record->{$i} = $result->fields[$i];
			}
		}
		
		if ($result->fields['notes'] != '')
		{
			$record->notes = $result->fields['notes'];
		}		
		
		
		
		$species[] = $record;
		$result->MoveNext();	
	
	}
	
	// Display...
	echo 
'<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<base href="' . $config['web_root'] . '" />
    <style type="text/css">
      body { margin: 20px; font-family:sans-serif;}
      input[type="text"] {
    		font-size:14px;
	  }
	  button {font-size:14px;}
    </style>

		<script type="text/javascript" src="' . $config['web_root'] . 'js/jquery-1.4.4.min.js"></script>
		<title>' . $title . ': ' . $config['site_name'] . '</title>
		
		<script>
		
			function show_doi(doi)
			{
				$("#details").html("");
				$.getJSON("pub.php?doi=" + encodeURIComponent(doi),
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_cinii(cinii)
			{
				$("#details").html("");
				$.getJSON("pub.php?cinii=" + cinii,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_biostor(biostor)
			{
				$("#details").html("");
				$.getJSON("pub.php?biostor=" + biostor,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_bhl(PageID, term)
			{
				$("#details").html("");
				$.getJSON("bhl.php?PageID=" + PageID + "&term=" + term,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
			}
			
			
			
		</script>
	</head>
	<body>
		<div style="float:right;">
			<form  method="get" action="index.php">
			<input type="text"  name="q" id="q" value="" placeholder="Genus"></input>
			<input type="submit" value="Search" ></input>
			</form>
		</div>
		<h1>' . $title . '</h1>';

	echo '<div style="position:relative;">';
	echo '<div style="width:800px;height:400px;overflow:auto;border:1px solid rgb(224,224,224);">';

	echo '<table id="specieslist" cellspacing="0">';
	echo '<tbody style="font-size:12px;">';
	
	echo '<tr>';
	echo '<th>Line</th>';
	echo '<th>PageID</th>';
	echo '<th>Name</th>';
	echo '<th>Basionym</th>';
	echo '<th>Journal</th>';
	echo '<th>ISSN</th>';
	echo '<th>DOI</th>';
	echo '<th>Handle</th>';
	echo '<th>BioStor</th>';
	echo '<th>BHL</th>';
	echo '<th>JSTOR</th>';
	echo '<th>CiNii</th>';
	echo '<th>URL</th>';
	echo '<th>PDF</th>';
	echo '<th>Notes</th>';
	echo '</tr>';
	
	$odd = true;
	
	foreach ($species as $sp)
	{
		echo '<tr';
		
		
		if ($odd)
		{
			echo ' style="background-color:#eef;"';
			$odd = false;
		}
		else
		{
			echo ' style="background-color:#fff;"';
			$odd = true;
		}
		
		
		
		
		echo '>';
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->id . '</span>' . '</td>';
		
		
		echo '<td>';
		if (isset($sp->pageid))
		{
			echo '<span onclick="show_bhl(\'' . $sp->pageid . '\',\'' . $sp->basionym . '\');">';		
			echo $sp->pageid;
			echo '</span>';
		}
		echo '</td>';

		
		
		echo '<td>' . $sp->html . '</td>';
		
		if (isset($sp->basionym))
		{
			$style = '';
			if ($sp->basionym != $sp->name)
			{
				$style = 'color:white;background-color:orange;';
			}
			// str_replace(' ', '&nbsp;', $sp->basionym)
			echo '<td>' . '<span style="' . $style . '">' . '<i>' . $sp->basionym . '</i>' . '</span>' . '</td>';
		}
		else
		{
			echo '<td></td>';
		}
		
		echo '<td>';
		if (isset($sp->publication))
		{
			echo $sp->publication;
		}
		echo '</td>';
		
		echo '<td>';
		if (isset($sp->issn))
		{
			echo str_replace('-', '', $sp->issn);
		}		
		echo '</td>';
		
		
		echo '<td>';
		if (isset($sp->doi))
		{
			echo '<span style="color:white;background-color:rgb(21,117,178);padding:2px;" onclick="show_doi(\'' . $sp->doi . '\');">';
			echo 'DOI:&nbsp;' . $sp->doi;
			echo '</span>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->handle))
		{
			echo '<span style="color:white;background-color:rgb(180,44,45);padding:2px;" onclick="show_doi(\'' . $sp->doi . '\');">';
			echo $sp->handle;
			echo '</span>';
		}		
		echo '</td>';
		

		echo '<td>';
		if (isset($sp->biostor))
		{
			echo '<span onclick="show_biostor(\'' . $sp->biostor . '\');">';
			echo $sp->biostor;
			echo '</span>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->bhl))
		{
			echo '<span onclick="show_bhl(\'' . $sp->bhl . '\',\'' . $sp->name . '\');">';		
			echo $sp->bhl;
			echo '</span>';
		}		
		echo '</td>';
		
		echo '<td>';
		if (isset($sp->jstor))
		{
			echo '<span style="color:white;background-color:red;padding:2px;">';	
			echo 'JSTOR:&nbsp;';	
			echo $sp->jstor;
			echo '</span>';
		}		
		echo '</td>';
		

		echo '<td>';
		if (isset($sp->cinii))
		{
			echo '<span onclick="show_cinii(\'' . $sp->cinii . '\');">';
			echo $sp->cinii;
			echo '</span>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->url))
		{
			echo '<a href="' . $sp->url . '" title="' . $sp->url . '">';
			echo substr($sp->url, 7, 20) . '...';
			echo '</a>';
		}		
		echo '</td>';

		echo '<td>';
		if (isset($sp->pdf))
		{
			echo '<a href="' . $sp->pdf . '" title="' . $sp->pdf . '">';
			echo substr($sp->pdf, 7, 20) . '...';
			echo '</a>';
		}		
		echo '</td>';
		
		echo '<td>';
		if (isset($sp->notes))
		{
			echo substr($sp->notes, 0, 200) . '...';
		}		
		echo '</td>';
		
		
		
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	
	
	//echo '<div style="clear:both"></div>';
	echo '<div style="font-size:10px;width:800px;">' . $config['credits'] . '</div>';
	
	
	echo '<div style="font-size:12px;position:absolute;top:0px;left:800px;width:auto;padding-left:10px;">';
	echo '<p style="padding:0px;margin:0px;" id="details"></p>';
	echo '</div>';
	
	echo '</div>';
	
	echo
'	</body>
</html>';

}




//--------------------------------------------------------------------------------------------------
function main()
{
	global $config;
	global $debug;
	
	$query = '';
		
	// If no query parameters 
	if (count($_GET) == 0)
	{
		default_display();
		exit(0);
	}
	
	if (isset($_GET['q']))
	{
		$query = $_GET['q'];
		display_search($query);
	}
	

	if (isset($_GET['p']))
	{	
		$publication = $_GET['p'];
		display_search($publication, 'publication');
	}

}


main();
		
?>