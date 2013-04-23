<?php
require ('../config_frank.php');
require ('../pdo.class.php');

$db=new dbPdo();

$db->connect();
$db->setDebugMode();

//count
print_r($db->getTablesName());
print_r($db->getFields('i_categories'));
print_r($db->fetRow('i_categories',$condition = ''));
print_r($db->getRow('select count(*) from i_categories'));
//$db->close();

//fetAll array
//print_r($db->fetAll('i_categories',array('title'=>'电影'),$field = '*'));
//print_r( $db->getAll('select id , vid , pid , name , ecount , xcount , editdate , pub , del from dwselection_vote_item where del = 0 and recomm = 1 and vid = 29 and pid = 0 and pub = 1 order by ecount desc , editdate desc'));







	
		$recom = $db->getAll('select id , vid , pid , name , ecount , xcount , editdate , pub , del from dwselection_vote_item where del = 0 and recomm = 1 and vid = 29 and pid = 0 and pub = 1 order by ecount desc , editdate desc');
		$i = 0;
		foreach($recom as $key=>$value) {
			if(($i+3)%3 == 0) {
				$temp .= "<div class=\"tit4\">";
			}
			$temp .= "<div class=\"tp2\"><p><strong><a href=\"javascript:elect.searchKey('".$value['name']."');\"><span id=\"spName_".($value['id'])."\">".$value['name']."</span></a></strong></p><em style=\"color:#ab0500\">票数:<span id=\"spNum_".($value['id'])."\">".number_format($value['ecount']+$value['xcount'])."</span></em></div>";
			if(($i+3)%3 == 2) {
				$temp .= "<div class=\"clear\"></div></div>";
			}
			$i++;
		}
		echo $temp;  

?>
