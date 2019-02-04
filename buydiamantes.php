<?php
require '../check_login.php';
require '../action.php';
?>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8">
	<link href="../css/global.css" rel="stylesheet" type="text/css"/>
	<link href="../css/rxhw_main.css" rel="stylesheet" type="text/css"/>
<!--	<script language="javascript" type="text/javascript" src="../js/jquery-1.8.3.min.js"></script>-->
<!--	<script language="javascript" type="text/javascript" src="../js/index.js"></script>-->

	<script type="text/javascript" src="../js/jquery.js"></script>
<!--	<script type="text/javascript" src="../js/jquery.mir.js"></script>-->
<!--	<script type="text/javascript" src="../js/jquery.home.js"></script>-->
	<script type="text/javascript" src="../js/pay.js"></script>
	<script language="javascript">
	lm=new Array();
	<?php
	$query  = "select gl.gid,server_id,server_name from $database.game_list as gl,$database.server as s where gl.gid = s.gid  and gl.is_recom = '1' order by server_id desc ";
	$result = mysql_query($query,$conn);
	$i = 1;
	$j = mysql_num_rows($result);
	while ($i <= $j) {
		while ($row = mysql_fetch_array($result)){
			echo "lm[{$i}] = new Array('{$row[server_id]}','{$row[server_name]}','{$row[gid]}');";	
			$i++;
		}
	}
	?>
	var lmcount= <?php echo $j ?>;
	var allRmb = <?php echo $rmb_user ?>;
	var gameInfoArray = new Array();
	<?php
	$query  = "select gid,game_name,pay_content,awardset from $database.game_list where is_recom = '1' order by id desc";
	$result = mysql_query($query,$conn);
	$j = mysql_num_rows($result);
	$i = 1;
	while ($i <= $j) {
		while ($row = mysql_fetch_array($result)){
			echo "gameInfoArray[{$row[gid]}] = new Array('{$row[gid]}','{$row[game_name]}','".preg_replace("/[\r\n]+/", "<br />", $row["pay_content"])."','{$row[awardset]}');";	
			$i++;
		}
	}
	?>
	$(document).ready(function() {	
		var html='';
		var arr = new Array("first","", "second", "third") 
		for(var item in gameInfoArray) {
			//document.write(gameInfoArray[item]+"<br />");
			html+="<a href=\"javascript:void(0);\" onclick=\"focusserver('"+gameInfoArray[item][1]+"','"+gameInfoArray[item][0]+"','100')\">"+gameInfoArray[item][1]+"</a>";
		}
		$("#SelectGame .items").html(html);
		$("#SelectGame h3").click(function(e){
			e.stopPropagation();		
			$('#SelectServer .ui-select-list').hide();
			$('#SelectGame .ui-select-list').toggle();
		});
		$("#SelectServer h3").click(function(e){
			e.stopPropagation();
			$('#SelectGame .ui-select-list').hide();
			$('#SelectServer .ui-select-list').toggle();
			if($(this).text() == "Select Server") $('#SelectServer .ui-select-list').show();
		});
	});
	</script>
</head>
<body>
<div class="news_nav">
	<ul>
		<li class="current">
			<a title="Currency Exchange"  target="_parent">Currency Exchange</a><b class="line"></b>
		</li>
	</ul>
</div>              
<div class="article-main3">
<form id="pay_form" name="myform" action="../vip.php?action=yb" method="post">
			<input type="hidden" name="mimacode" value="<?php echo $_SESSION['sscode'] ?>">
			<input type="hidden" name="game_id" id="game_id" value="">
			<input type="hidden" name="server_id" id="server_id" value="">
			<ul class="user-form-ul">
				<li class="user-form-ul2">
					<span>Select game：</span>
					<div class="ui-select" id="SelectGame">
						<h3>Select Game</h3>
						<div class="ui-select-list">
<!--							<p>Select2</p>-->
							<div class="items"></div>
						</div>
					</div>
					<div class="ui-select" id="SelectServer" style="display:none;">
						<h3>Select Server</h3>
						<div class="ui-select-list">
<!--							<p>Select Server</p>-->
							<div class="items">  
							</div>
						</div>
					</div>
					<i id="GameTip"></i>                          
				</li>
				<li id="youid">
					<span>Your account：</span><input disabled="disabled" class="t-input-show" value="<?php echo $_SESSION['username'] ?>" />
				</li>
				<li id="youpintb">
					<span>Available balance：</span><input disabled="disabled" class="t-input-show" value="<?php echo $rmb_user ?> $" />
				</li>
				<li id="czzh2">
					<span>$ Exchange：</span>
					<input class="t-input" name="change_yb" type="text" value="" placeholder="$ to Exchange" oncopy="return false;" onpaste="return false;" oncut="return false;" id="txt_checkingPhone" onkeyup="checkRate(this.id)">
					<i></i>
				</li>
				<li>
					<span>&nbsp;</span>
					<p><i>You will receive</i>：<em id="exChangeNum">0</em> <em id="exChangeTip">[Diamonds]</em></p>           
				</li> 
				<li class="bar">
					<span>&nbsp;</span>
					<button type="button" class="b-submit" onclick="pay();" id="submit_button">Exchange</button>          
				</li> 
			</ul>       
			</form>	                  
</div>
</body>	
</html>
