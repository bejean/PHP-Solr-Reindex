<?php 
$solr = array (
	array ('url' => "http://inrs:8080/solr14/inrs/", 'version' => 1.4),	
	array ('url' => "http://inrs:8080/solr14/tests/", 'version' => 1.4),	
	array ('url' => "http://inrs:8080/solr14/tests2/", 'version' => 1.4),	
	array ('url' => "http://inrs:8080/solr47/tests/", 'version' => 4.7)	
);
$solr_qt = array (
		array ('name' => "select", 'type' => 'standard'),
		array ('name' => "inrs", 'type' => 'dismax'),
);
$default_common_params = 'start=0&rows=200&sort=score+desc,datefo+desc,typedocument+asc&wt=xml&debugQuery=on';
$default_fields_params = 'fl=titre,groupesource,segmentation,typedocument,score';
$default_query_params = 'q=((titre:"chutes de hauteur"~20^8 OR textimportant:"chutes de hauteur"~30^4 OR text:"chutes de hauteur"~30 OR text:(chutes de hauteur)^0.1 OR text:(chutes de hauteur*)^0.01)) AND (segmentation:(RECHERCHE_SIMPLE))';
$default_dismax_query_params = 'q=chute de hauteur&fq=segmentation:(RECHERCHE_SIMPLE)&qf=titre^8.0 textimportant^4.0 text^1.0';
?>