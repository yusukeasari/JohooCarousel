<?php

$FOUND=false;

if(!empty($_GET['id'])){
	$json = file("../swfData/pitcomdb.json");
	$line=json_decode($json[0],true);
	foreach($line as $k=>$v){
		if(substr($v["img"],0,1) == "9"){
			$v["img"] = substr($v["img"],1);
		}

		if($v["id"] == $_GET['id'] && strlen($_GET['id'])==6){
			$v["result"]="FOUND";
			echo "[[".json_encode($v)."],".'[{"ERROR":""},{"TOTAL":1}]]';
			$FOUND = true;
			break;
		}
	}

	if(!$FOUND){
		echo '[[],[{"ERROR":"NOTFOUND"}]]';
	}
}else if(!empty($_GET['n'])&&empty($_GET['request'])){
	$json = file("../swfData/pitcomdb.json");
	$line=json_decode($json[0],true);
	foreach($line as $k=>$v){
		if(substr($v["img"],0,1) == "9"){
			$v["img"] = substr($v["img"],1);
		}

		if($v["num"] == $_GET['n']){
			$v["result"]="FOUND";
			echo "[".json_encode($v)."]";
			$FOUND = true;
			break;
		}
	}

	if(!$FOUND){
		echo '[{"ERROR":"NOTFOUND"}]';
	}
}else if(!empty($_GET['n'])&&!empty($_GET['request'])&&!empty($_GET['base'])){
	$result = [];
	$json = file("../swfData/pitcomdb.json");
	$line=json_decode($json[0],true);

	// $lineをnumでソートする
	$ns = array_column($line,'num');
	array_multisort($ns, SORT_ASC, $line);

	$line_c = $line;
	//リクエストされたキーからnumとbidを算出
	$array = explode(",",$_GET['request']);

	//bidからnumが下限または上限を超えていないかチェックする
	sort($array);


	$last_key = array_last($array);
	$ary = array_last($line);
	$last_num = $ary['num'];

	//下限値
	if($_GET['n']+(($_GET['base']*-1)+$array[0]) < 1){
		//下限値より小さい場合
		$ret = array_slice($line_c,$last_num-10,10);
		$line = array_merge($ret,$line);
	}
	//上限値
	if($_GET['n']+(($_GET['base']*-1)+$last_key) > $last_num){
		$ret = array_slice($line_c,0,10);
		$line = array_merge($line,$ret);
	}

	foreach($array as $k => $v){
		//
		$array[$k] = [];
		if($_GET['n']+(($_GET['base']*-1)+$v) < 1){
			$array[$k]["num"] = $last_num + ($_GET['n']+(($_GET['base']*-1)+$v));
		}else if($_GET['n']+(($_GET['base']*-1)+$v) > $last_num){
			$array[$k]["num"] = $_GET['n']+(($_GET['base']*-1)+$v) - $last_num;
		}else{
			$array[$k]["num"] = $_GET['n']+(($_GET['base']*-1)+$v);
		}
		$array[$k]["bid"] = $v;
	}

	sort($array);
	//配列の最後を取得
#	$last_key = array_last($array);

#	$last_num = array_last($line);

	//完全一致を取得
#	$nums = array_column($line,'num');
#	$ret = in_array($nums,$_GET['base']);

	$result = array();
	foreach($line as $k=>$v){
		if(substr($v["img"],0,1) == "9"){
			$v["img"] = substr($v["img"],1);
		}
		foreach($array as $k2 => $v2){
			if($v2["num"] == $v["num"]){
				$v["bid"] = $v2["bid"];
				array_push($result,$v);
			}
		}
	}
	echo json_encode($result);

#	error_log($_GET['request']);
}else{
	$result = array();
	$json = file("../swfData/pitcomdb.json");
	$line=json_decode($json[0],true);
	foreach($line as $k => $v){
		$b1 = '';
		$b2 = '';
		foreach($_GET as $k2 => $v2){
			if($k2 == 'b1'){
				$b1 = $v2;
			}
			if($k2 == 'b2'){
				$b2 = $v2;
			}
		}
		if(substr($v["img"],0,1) == "9"){
			$v["img"] = substr($v["img"],1);
		}
		if(!empty($b1) && !empty($b2) && preg_match("/".$b1."/", $v['b1']) && preg_match("/".$b2."/", $v['b2']) && $v['flg'] == 1){
			//print "パターン1:"."{$b1}/{$b2}:{$v['b1']}/{$v['b2']}/{$v['flg']}<br />";
			array_push($result,$v);
		}else if(!empty($b1) && empty($b2) && preg_match("/".$b1."/", $v['b1']) && $v['flg'] == 1){
			//print "パターン2:"."{$b1}/{$v['b1']}/{$v['flg']}<br />";
			array_push($result,$v);
		}else if(!empty($b2) && empty($b1) && preg_match("/".$b2."/", $v['b2']) && $v['flg'] == 1){
			//print "パターン3:"."{$b2}/{$v['b2']}/{$v['flg']}<br />";
			array_push($result,$v);
		}
	}
	$count = count($result);

	if (count($result) > 300){
		echo '[[],[{"ERROR":"TOOMUCHRESULT"},{"TOTAL":'.$count.'}]]';
		exit;
	}
	if(!empty($_GET['page'])){
		$result = array_splice($result, ($_GET['page']-1)*10,10);
	}else{
		$result = array_splice($result, 0,10);
	}

	if(count($result) > 0){
		echo '['.json_encode($result).',[{"ERROR":""},{"TOTAL":'.$count.'}]]';
	}else if($_GET['id'] == '' && @$_GET['b1'] == '' && @$_GET['b2'] == ''){
		echo '[[],[{"ERROR":"NOWORD"},{"TOTAL":0}]]';
	}else{
		echo '[[],[{"ERROR":"NOTFOUND"},{"TOTAL":0}]]';
	}
}
function array_last(array $array)
{
    return end($array);
}