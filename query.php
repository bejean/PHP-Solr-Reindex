<?php 
include_once 'query-settings.php';

$do_search=isset($_POST['query']) && !empty($_POST['query']);
$do_explain=isset($_POST['explain']) && !empty($_POST['explain']);

$common_params = "";
if (isset($_POST['common-params']) && !empty($_POST['common-params'])) {
	$common_params = $_POST['common-params'];
}
else if (!$do_search) $common_params = $default_common_params;

$fields_params = "";
if (isset($_POST['fields-params']) && !empty($_POST['fields-params'])) {
	$fields_params = $_POST['fields-params'];
	}
else if (!$do_search) $fields_params = $default_fields_params;

$query_params = "";
if (isset($_POST['query-params']) && !empty($_POST['query-params'])) {
	$query_params = $_POST['query-params'];
	}
else if (!$do_search) $query_params = $default_query_params;

$dismax_query_params = "";
if (isset($_POST['query-params-dismax']) && !empty($_POST['query-params-dismax'])) {
	$dismax_query_params = $_POST['query-params-dismax'];
}
else if (!$do_search) $dismax_query_params = $default_dismax_query_params;

$solr_version = 0;
$solr_qt_type = '';


function myUrlEncode($string) {
	$entities = array('%21', '%2A', '%27', '%28', '%29', '%3B', '%3A', '%40', '%26', '%3D', '%2B', '%24', '%2C', '%2F', '%3F', '%25', '%23', '%5B', '%5D');
	$replacements = array('!', '*', "'", "(", ")", ";", ":", "@", "&", "=", "+", "$", ",", "/", "?", "%", "#", "[", "]");
	return str_replace($entities, $replacements, urlencode($string));
}

function SmartUrlEncode($url) {
	if (strpos ( $url, '=' ) === false) {
		return $url;
	} else {
		$startpos = strpos ( $url, "?" );
		$tmpurl = substr ( $url, 0, $startpos + 1 );
		$qryStr = substr ( $url, $startpos + 1 );
		$qryvalues = explode ( "&", $qryStr );
		$qryvalues2 = array();
		foreach ( $qryvalues as $value ) {
			$buffer = explode ("=", $value);
			if ($buffer [0]=='q' || $buffer [0]=='fq' || $buffer [0]=='mm' || $buffer [0]=='qf') 
				$qryvalues2[] = $buffer [0] . '=' . urlencode ($buffer [1]);
			else
				$qryvalues2[] = $buffer [0] . '=' . $buffer [1];
		}
		$finalqrystr = implode ("&", $qryvalues2);
		$finalURL = $tmpurl . $finalqrystr;
		return $finalURL;
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Query</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="pure-min.css">
<!-- http://purecss.io/ -->
<style>
html {
	font-size: 0.8em;
}
label {
	display: block;
	width: 150px;
	float: left;
}
textarea {
	row: 3;
}
td {
	vertical-align: top;
	padding: 5px;
}
.background_criteria {
	background-color: lightgray;
}
.background_info {
	background-color: lightyellow;
}
.background_result {
	background-color: snow;
}
</style>
</head>
<body>

	<div id='criteria' class='background_criteria'>
		<form action="query.php" method='post'>
			<input name="query" type="hidden" value="1" />
			<label for="solr">Solr core</label> <select id="solr" name="solr">
<?php 
		foreach ($solr as $core) {
			$selected='';
			if (isset($_POST['solr']) && $core['url']==$_POST['solr']) {
				$selected='selected';
				$solr_version = $core['version'];
			}
			print ("<option $selected>" . $core['url'] . "</option>");
		}
?>
	</select><br /> <label for="solr_qt">Request Handler</label> <select
				id="solr_qt" name="solr_qt">
<?php 
		foreach ($solr_qt as $qt) {
			$selected='';
			if ($qt['name']==$_POST['solr_qt']) {
				$selected='selected';
				$solr_qt_type = $qt['type'];
			}
			$qt_type = $qt['type'];
			$qt_name = $qt['name'];
			print ("<option $selected value='$qt_name'>$qt_name ($qt_type) </option>");
		}
?>
	</select><br /> <label for="query-params">Standard query parameters</label>
			<textarea id="query-params" name="query-params" cols="100" rows="5"><?php print ($query_params); ?></textarea>
			&nbsp;<a href='https://cwiki.apache.org/confluence/display/solr/The+Standard+Query+Parser' target='help'>Syntax</a>
            <br /> <label for="query-params-dismax">Dismax query parameters</label>
			<textarea id="query-params-dismax" name="query-params-dismax" cols="100" rows="5"><?php print ($dismax_query_params); ?></textarea>
			&nbsp;<a href='https://cwiki.apache.org/confluence/display/solr/The+DisMax+Query+Parser' target='help'>Dismax Syntax</a>			
			&nbsp;<a href='https://cwiki.apache.org/confluence/display/solr/The+Extended+DisMax+Query+Parser' target='help'>EDismax Syntax</a>			
			<br /> <label for="fields-params">Fields parameters</label>
			<textarea id="fields-params" name="fields-params" cols="100" rows="2"><?php print ($fields_params); ?></textarea>
			<br /> <label for="common-params">Common parameters</label>
			<textarea id="common-params" name="common-params" cols="100" rows="2"><?php print ($common_params); ?></textarea>
			<br /> <label for="fields-params">Explain relevance</label><input name="explain" type="checkbox" value="1" />
			<br /> <input type="submit" value="Submit">
		</form>
	</div>
  
<?php 
if ($do_search) {
	if ($solr_qt_type=='dismax') {
		$query_params = $dismax_query_params;
	} 
	if ($solr_version>=3.2) {
		$solr_url = $_POST['solr'] . '/' . $_POST['solr_qt'] . '?' . $query_params . '&' . $fields_params . '&' . $common_params;
	} else {
		$qt = $_POST['solr_qt'];
		if ($qt=='select') $qt = 'standard';
		$solr_url = $_POST['solr'] . '/select/?qt=' . $qt . '&' . $query_params . '&' . $fields_params . '&' . $common_params;
	}
	$solr_url_encoded = SmartUrlEncode($solr_url);
	$ch = curl_init($solr_url_encoded);
	curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,10); # timeout after 10 seconds, you can increase it
	curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);  # Set curl to return the data instead of printing it to the browser.
	$result = curl_exec($ch);
	if(curl_errno($ch))
	{
		$err = curl_error($ch);
	}
	curl_close($ch);
	
	if ($result) {
		$xml = simplexml_load_string($result);
	}	
?>
	<div id='result_info' class='background_info'>
	<table>
		<tr><td>Solr url</td><td><?php print($solr_url); ?></td></tr>
<?php 
	$nodes = $xml->xpath("/response/lst[@name='debug']/str");
	while(list( , $node) = each($nodes)) {
		print ("<tr><td>" . $node['name'] . "</td><td>" . $node . "</td></tr>");
	}
?>
	</table>
	</div>

	<div id='result' class='background_result'>
<?php

$result = $xml->xpath("/response/result");
$rowcount = (string) $result[0]['numFound'];

$explain = $xml->xpath("/response/lst[@name='debug']/lst[@name='explain']/str");
print ('------------------------------<br />');
print ("Row Count = $rowcount <br />");

$count=0;
foreach ($xml->result->doc as $doc) {
	print ('------------------------------<br />');
	print ('<table>');
	print ("<tr><td>count</td><td>$count</td></tr>");
	foreach($doc as $field) {
		//$x = print_r ( $field , true );
		$name = (string) $field['name'];
		$value = '';
		if ($field->getName()=='arr') {
			foreach ($field->str as $str) {
				$value .= $str . ', ';
			}
		} else {
			$value = (string) $field;
		}
		print ("<tr><td>$name</td><td>$value</td></tr>");
	}
	if ($do_explain) print ("<tr><td>explain</td><td><pre>" . str_replace("\n)", ")", $explain[$count]) . "</pre></td></tr>");
	print ('</table>');
	$count++;
}
?>
	</div>

<?php
}
?>	
  </body>
</html>