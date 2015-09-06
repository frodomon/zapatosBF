<?php
/* 
 * Oscar Alderete - oscaralderete@hmail.com
 * Cosas Peruanas S.A. - http://www.paginasweb.pe
 * ...
 */
header('Content-Type: text/html; charset=utf-8');

$eval=isset($_POST['action'])&&$_POST['action']=='peubigeo_ajax'&&isset($_POST['id'])&&1*$_POST['id']>0;

if($eval){
		
	require_once('../app/Mage.php');
	umask(0);
	Mage::app();

	error_reporting(E_ALL);

	$db=Mage::getSingleton('core/resource')->getConnection('core_write');
	//utf8 compliance
	$db->query('SET NAMES "utf8"');

	switch($_POST['type']){
		case 'load_cities':
			$result=$db->query('SELECT name FROM paginaswebpe_cities WHERE region_id='.$_POST['id'].' ORDER BY name ASC');
			if(!$result){
				die("ERROR 3002\nUnreadable parameter value..");
			}
			$r=$result->fetchAll(PDO::FETCH_ASSOC);
			$s='<option value="">Ciudad</option>';
			foreach($r as $i){
				$s.='<option value="'.$i['name'].'">'.$i['name'].'</value>';
			}
		break;
		case 'load_districts':
			$result=$db->query('SELECT pd.name FROM paginaswebpe_districts pd,paginaswebpe_cities pc WHERE pc.region_id='.$_POST['id'].' AND pc.name="'.$_POST['name'].'" AND pc.id=pd.city_id  ORDER BY pd.name ASC');
			if(!$result){
				die("ERROR 3002\nUnreadable parameter value..");
			}
			$r=$result->fetchAll(PDO::FETCH_ASSOC);
			$s='<option value="">Distrito</option>';
			foreach($r as $i){
				$s.='<option value="'.$i['name'].'">'.$i['name'].'</value>';
			}
		break;

		default:
			$s="ERROR 3001\nUndefined method..";
		break;
	}
	echo $s;
}

?>