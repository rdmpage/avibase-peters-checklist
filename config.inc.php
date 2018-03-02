<?php

// $Id: //

/**
 * @file config.php
 *
 * Global configuration variables (may be added to by other modules).
 *
 */

global $config;

// Date timezone
date_default_timezone_set('UTC');

// Server-------------------------------------------------------------------------------------------
$config['web_server']	= 'http://localhost'; 
$config['site_name']	= 'Avibase';

// Files--------------------------------------------------------------------------------------------
$config['web_dir']		= dirname(__FILE__) . '/';
$config['web_root']		= '/~rpage/avibase-peters-checklist/';


// Database-----------------------------------------------------------------------------------------
$config['adodb_dir'] 	= dirname(__FILE__).'/adodb5/adodb.inc.php'; 
$config['db_user'] 	    = 'root';
$config['db_passwd'] 	= '';
$config['db_name'] 	    = 'ion';

$config['db_table'] 	= 'avibase';

// Credits
$config['credits']      = 'D. Lepage, J. Warnier, 2014. The Peters\' Check-list of the Birds of the World (1931-1987) Database. Accessed on 07/05/2015 from Avibase, the World Database: <a href="http://avibase.bsc-eoc.org/peterschecklist.jsp">http://avibase.bsc-eoc.org/peterschecklist.jsp</a>.';



// Proxy settings for connecting to the web---------------------------------------------------------

// Set these if you access the web through a proxy server. This
// is necessary if you are going to use external services such
// as PubMed.
$config['proxy_name'] 	= '';
$config['proxy_port'] 	= '';

//$config['proxy_name'] 	= 'wwwcache.gla.ac.uk';
//$config['proxy_port'] 	= '8080';

?>