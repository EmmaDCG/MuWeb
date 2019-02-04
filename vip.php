<?php
session_start();
error_reporting(E_ALL^E_NOTICE^E_WARNING);
require_once ("include/SnsNetwork.php");
include 'action.php';
// .-----------------------------------------------------------------------------------
// | The following code is a diamond exchange ingot
// .-----------------------------------------------------------------------------------
//Redeem the ingot
$action = htmlspecialchars($_GET['action']);
if ($action == 'yb') {	
    $openid = $_SESSION['username']; 
    $rmb = round(htmlspecialchars($_POST['change_yb']));
	$server_id = htmlspecialchars($_POST['server_id']);
	$game_id= htmlspecialchars($_POST['game_id']);
	$a = $_SESSION['sscode'];
	$sscode = mt_rand(0,1000000);
	$_SESSION['sscode'] = $sscode;
	
	
	if($_POST['mimacode'] == $a ){
		//Determine whether to choose a game
		if (empty($server_id)){
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('Please select a game');location.href='javascript:history.back()';</script>";
			exit();
		}
		//Determine if the input number is legal
		if (!is_numeric( $rmb )){
				echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
				echo "<script>alert('The input parameter is invalid');location.href='javascript:history.back()';</script>";
				exit();
		}
		//Determine if the input number is legal
		if ($rmb <= 0  ){
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('Please enter a correct amount');location.href='javascript:history.back()';</script>";
			exit();
		}
		$sql="SELECT rmb,id FROM $database.user WHERE username = '$openid' and rmb > 0 and rmb >= $rmb";
		$result=mysql_query($sql,$conn);
		$row=mysql_fetch_array($result);
		if (empty($row)){
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('The amount of diamond to be exchanged can not be greater than the existing amount');location.href='javascript:history.back()';</script>";
			exit();
		}
		
		//Query information about the server table
		$sql_sid = "select pay_api_url,pay_api_key,awardset from $database.game_list where gid = '$game_id'";
		$result_sid = mysql_query($sql_sid,$conn);
		$row_sid    = mysql_fetch_array($result_sid);		
		$pay_api_url = $row_sid['pay_api_url'];
		$pay_api_key = $row_sid['pay_api_key'];
		$awardset = $row_sid['awardset'];
		//Give id为满足某些游戏加入用户ID做识别
		$uid=$row['id']+$pintaimainid;
		
		
		//After judging that the input is correct, start to judge the game.
		if ($row['rmb'] > 0 && $rmb > 0 && $row['rmb'] >= $rmb)
		{
			$payGold = 0;
			$bl = 0;
			$fl = 0;
			$ratioArray = explode("$$",$awardset);			
			for($index=0;$index<count($ratioArray);$index++) 
			{ 
				$al = explode(":",$ratioArray[$index]);
				if($index == 0)
				{
					$bl  = $al[1];
				}	
				else if((int)$rmb >= (int)$al[0]) 
				{
					$fl = $al[1];
				}
			} 
			$awardset=(int)$awardset;
			$payGold = (int)($rmb * $awardset);
				
			$time = time();
			
			//Generate orders
			$sql="INSERT INTO  $database.exchange_log (username, rmb, order_yb, game_id, server_id, STATUS )VALUES('${openid}',${rmb},${payGold},${game_id},${server_id},1 )"; 
			
			mysql_query($sql,$conn);
			$result=mysql_query( "SELECT LAST_INSERT_ID();", $conn);
			$row = mysql_fetch_array($result);
			$order = $row[0]."_".$platform;
			
			$pfopenid = "${openid}${platform}";
			
			$sign = md5("${pfopenid}${time}${uid}${platform}${server_id}${payGold}${order}${pay_api_key}");
			
			$url = "${pay_api_url}?sid=${server_id}&uid=${uid}&gid=${game_id}&pf=${platform}&user=${pfopenid}&time=${time}&gold=${payGold}&order=${order}&sign=${sign}";
			$C['recharge_key'] = "bb74afdf84a84a1c6ff3b5bb05b93eae";
			$data = array(
			'passport'	=> $openid,
			'sid'		=> $server_id,
			'money'		=> $payGold,
			'billno'	=> $order,
			'time'		=> $time,
			'key'		=> $C['recharge_key']
		);
		$data['sign'] = md5(http_build_query($data));
		unset($data['key']);
		$url =  $pay_api_url.'/pay?' . http_build_query($data);
			//die($url);
			//$ret = SnsNetwork::makeRequest($url,array(),'get');
			//$retmsg=htmlspecialchars($ret['msg']);
			$recharge_result  = file_get_contents($url);
			$recharge_result = json_decode($recharge_result, true);
			//exit($recharge_result["status"]."ff".$url);
			if($recharge_result["status"]==1)
			{
				
				// Buckle diamond
				$sql="update $database.user set  rmb = rmb-'$rmb' where username ='$openid'"; 
				mysql_query($sql,$conn);
				
				//Update order status
				$sql="update  $database.exchange_log set STATUS =2 where order_id =$order"; 
				mysql_query($sql,$conn);
				
				
				echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
				echo "<script>alert('Successful Exchange！');location.href='index.php';</script>";
			}
			else
			{
				echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
				echo "<script>alert('Failed Exchange！');location.href='index.php'</script>";
			}
		}
		else
		{
			echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
			echo "<script>alert('Your diamond is not enough, hurry up and recharge it.！');location.href='/home/pay.php';</script>";
		}
	}else{
		echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
		echo "Please do not refresh this page or repeat the submission form！";
	}
}
// .-----------------------------------------------------------------------------------
// | 下面的代码是新手福利元宝$uid   $_SESSION['username']
// .-----------------------------------------------------------------------------------
// Welfare begins
 
if ($action == 'welfare') {	
 
	$server_id = htmlspecialchars($_POST['server_id']);
	$game_id= htmlspecialchars($_POST['game_id']);
	// echo $server_id."hxl".$game_id;
	
	if ( $game_id == "" || $server_id == "") {
		echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
		echo "<script>alert('Dear, you don’t choose between games and district suits. I’m going to pay you so many gifts~_~');window.history.go(-1);</script>"; 
		exit();
	}
 
////////////////////////Check if you have received it	
	$sql="SELECT * FROM $database.welfare WHERE user_name = '".$_SESSION['username']."' and gid=".$game_id." and game_server_id=".$server_id.";";
	$result=mysql_query($sql,$conn);
	$row=mysql_fetch_array($result);
	if (!empty($row)) {
		echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
		echo "<script>alert('Dear, you have already received the novice benefits of this game~_~');window.history.go(-1);</script>"; 
		exit();		
	}
////////////////////////How much is the welfare?
 
	$sql_sid="SELECT welfare,pay_api_url,pay_api_key FROM $database.game_list WHERE gid=".$game_id.";";
	$result_sid=mysql_query($sql_sid,$conn);
	$row_sid=mysql_fetch_array($result_sid);	
	$payGold=$row_sid['welfare'];
	$pay_api_url = $row_sid['pay_api_url'];
	$pay_api_key = $row_sid['pay_api_key'];	
	
	if (empty($payGold)) {
		echo "<meta http-equiv='Content-Type'' content='text/html; charset=utf-8'>";
		echo "<script>alert('Dear, there is no newbie for this game, please contact the platform customer service~_~');window.history.go(-1);</script>"; 
		exit();		
	}	
	
 
//////echo $payGold.$pay_api_key.$pay_api_url;	
//发元宝
 	//Give id为满足某些游戏加入用户ID做识别
	$uid = $uid+$pintaimainid;		
	$username =  $_SESSION['username'];
	$openid =  $_SESSION['username'];
	
	if ($username != "" and $payGold != "" and $server_id != "" and $game_id != "")
	{
 
		$pfopenid = "${username}${platform}";
		
			$time = time();
			$order = $time;
			$sign = md5("${pfopenid}${time}${uid}${platform}${server_id}${payGold}${order}${pay_api_key}");
			
			$url = "${pay_api_url}?sid=${server_id}&uid=${uid}&gid=${game_id}&pf=${platform}&user=${pfopenid}&time=${time}&gold=${payGold}&order=${order}&sign=${sign}";
			
			//die($url);
			// $ret = SnsNetwork::makeRequest($url,array(),'get');
			$C['recharge_key'] = "bb74afdf84a84a1c6ff3b5bb05b93eae";
			$data = array(
			'passport'	=> $openid,
			'sid'		=> $server_id,
			'money'		=> $payGold,
			'billno'	=> $order,
			'time'		=> $time,
			'key'		=> $C['recharge_key']
		);
		$data['sign'] = md5(http_build_query($data));
		unset($data['key']);
		$url =  $pay_api_url.'/pay?' . http_build_query($data);
			//die($url);
			//$ret = SnsNetwork::makeRequest($url,array(),'get');
			//$retmsg=htmlspecialchars($ret['msg']);
			$recharge_result  = file_get_contents($url);
			$recharge_result = json_decode($recharge_result, true);
			//exit($recharge_result["status"]."ff".$url);
			if($recharge_result["status"]==1)
			{
				echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
				echo "<script>alert('Congratulations ".$payGold." for obtaining the game currency, overtaking the game.！');location.href='javascript:history.back()';</script>";
				$sql = "insert into $database.welfare( user_name, gid, game_server_id, status, log_time) values('$username','$game_id','$server_id','',now())";
				//die($sql);
				$result = mysql_query($sql,$conn);					
				
			}
			else 
			{
				echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
				echo "<script>alert('Failed to send');location.href='javascript:history.back()';</script>";
			}
	 	
	 }else{
		echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\">";
		echo "<script>alert('Incomplete information！');location.href='javascript:history.back()';</script>"; 
	 }
			
}
?>
