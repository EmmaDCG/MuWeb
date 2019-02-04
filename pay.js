var ratioArray = new Array();

function focusserver(name,val,payto){
	$("#game_id").val(val);
	$("#server_id").val("");	
	$("#game_payto").val(payto)
	$("#SelectGame h3").html(name);
	$('#SelectGame .ui-select-list').hide();
	i=1;
	var html='';
	while(i<=lmcount)
	{		
		if(lm[i][2]==val)
		{
			html+="<a href=\"javascript:void(0);\" onclick=\"getserver('"+lm[i][1]+"','"+lm[i][0]+"')\">"+lm[i][1]+"</a>";
		}
		i++;
	}	
	
	$("#SelectServer").show().find(".ui-select-list").show().find(".items").html(html);
	$("#SelectServer h3").html("Select Server");
	if($("#SelectServer .items a").length < 3) $('#SelectServer .ui-select-list').addClass("i1");
	else  $('#SelectServer .ui-select-list').removeClass("i1");
	
	ratioArray = gameInfoArray[val][3].split("$$");
	$("#gameExcInfo").html(gameInfoArray[val][2]);
	$("#exChangeTip").html("[Diamonds]");
	$("#GameTip").removeClass("p-error").text("");
	checkRate(null);
	var othermoneyval=$(".jeother").val()!=""?$(".jeother").val():$('input:radio[name="pay_amount"]:checked').val();
}


function getserver(name,val){
	$("#server_id").val(val);	
    $("#SelectServer h3").html(name);
	$('#SelectServer .ui-select-list').hide();
	
	$("#GameTip").removeClass("p-error").text("");
}

	
function pay() {
	if($("#game_id").val() == ""){
		$("#GameTip").addClass("p-error").text("×Select Game！");
		return false;
	}
	if($("#server_id").val() == ""){
		$("#GameTip").addClass("p-error").text("×Select Server！");
		return false;
	}
	if ($("#txt_checkingPhone").val() == "")
	{
		$("#txt_checkingPhone").next().addClass("p-error").text("×Please enter the number of diamonds to redeem!");
		return false;
	}
	if ( parseInt($("#txt_checkingPhone").val()) < 1)
	{
		$("#txt_checkingPhone").next().addClass("p-error").text("×Please enter the number of diamonds to redeem!");
		return false;
	}	
	else
	{
	  document.myform.submit();
	}
}


function checkRate(input)
{
     var re = /^\d+$/;
    var nubmer = $("#txt_checkingPhone").val();
	var extra = "";   
	
    if (!re.test(nubmer))
    {
		if(input) $("#txt_checkingPhone").val("").next().addClass("p-error").text("×Please enter the correct redemption quantity and only allow integers greater than 0!");
        return false;
     }else{
		if($("#game_id").val() == "") {
			$("#txt_checkingPhone").next().addClass("p-error").text("×Please select the game first！");
			return false;
		}
		if(parseInt(nubmer) > parseInt(allRmb)) $("#txt_checkingPhone").val(allRmb);
		
		nubmer = $("#txt_checkingPhone").val();
		var get = parseInt(nubmer) * ratioArray[0];
		for(var i=1; i<ratioArray.length; i++)
		{
			var al = ratioArray[i].split(":");
			if(parseInt(nubmer) >= parseInt(al[0]))	extra = " + "+ parseInt(get*al[1]/100) + "Rebate";
		}
		$("#txt_checkingPhone").next().removeClass("p-error").text(""); 
		$("#exChangeNum").text(get);
	 }
} 

function welfare() {
if (document.myform.package.value == "")
{
	alert('Please choose a package！');
	return false;
}else{
	document.myform.submit();
}
}
