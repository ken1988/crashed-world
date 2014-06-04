//======================================================================
// 開発画面用
//======================================================================
function Navi(position, img, title, pos, text, exp) {
  StyElm = document.getElementById("NaviView");
  StyElm.innerHTML = "<div id='NaviTitle'>" + pos + title + "<\/div><img class='NaviImg' src=" + img + "><div class='NaviText'>" + text.replace("\n", "<br>") + "<\/div>";
  if(exp) {
    StyElm.innerHTML += "<div class='NaviText'>" + eval(exp) + "<\/div>";
  }
}
//======================================================================
$(function(){

	$("#accordion").accordion();
	
	$("#sortable").sortable({
		placeholder: 'ui-state-highlight'
	});
	
	$("#sortable").disableSelection();
	
	$("#latestnews").liScroll();
	
	$("textarea.limited").maxlength({
          'feedback': '#charsLeft1'
      });
    
    $("input.limited").maxlength({
          'feedback': '#charsLeft2'
      });
    
 	$("textarea.limited").autoResize({
		onResize : function() {
		$(this).css({opacity:1});
		},
		animateCallback : function() {
		$(this).css({opacity:1});
		},
	    extraSpace : 10,
	    limit : 100
		});
		
	//make some charts
	$('#wstattable').visualize({type: 'line',width: '700px', title: '砲弾生産',colFilter: ':last-child',parseDirection: 'y'});
	$('#wstattable').visualize({type: 'line',width: '700px', title: '産業統計',colFilter: ':gt(3):not(:last-child)',parseDirection: 'y'});
	$('#wstattable').visualize({type: 'line',width: '700px', title: '人口統計',colFilter: ':lt(4)',parseDirection: 'y'});

    $("#regtin").click(function(){
   		var intunit = $('#sel_kind').val();
   		var send_num = $('#sel_val').val();
   		var units = "";
   		
   		switch(intunit){
   			case "71":
   				units = "億Va";
   				break;
   			case "72":
  				units = "万トン";
   				break;
   			case "73":
   				units = "億Va";
   				break;
   			case "74":
	   		   	units = "ガロン";
   				break;
   			case "75":
	   		   	units = "万トン";
   				break;
   			case "76":
	   		   	units = "万トン";
   				break;
   			case "77":
	   		   	units = "万トン";
   				break;
   			case "78":
	   		   	units = "万トン";
   				break;
   			case "79":
	   		   	units = "トン";
   				break;
   			case 80:
	   		   	units = "バレル";
   				break;
   			case 81:
	   		   	units = "ガロン";
   				break;
   			case 82:
	   		   	units = "万トン";
   				break;
   			case 83:
	   		   	units = "メガトン";
   				break;
   			default:
   				break;

   		}
   		
   		if(intunit != "72"){
   		   intunit = 500;
   		}else{
   			intunit = 1000;
   		}
   		
   		var gross_num = intunit * send_num;
    	$("#gross")
		.val($("#gross").text(gross_num+units))
	});
	
	
    $("#islandMap table").mouseover(function(){
    	if($("#NaviView").css("display")!="block"){
		$("#NaviView").show();
		}
	}).mouseout(function(){
    	$("#NaviView").hide();
    }).click(function(){
    	$("#menu").show();
    }).mousemove(function(e){
    	$("#NaviView").css({
    	"top":e.pageY+10+"px",
    	"left":e.pageX+10+"px"
    	});
	});
	
	$("li.IndD a").mouseover(function(){
		tid = $(this).attr("href");
		$("div.tooltip[id="+tid+"]").fadeIn();
		$("div.tooltip[id="+tid+"]").html($(".databox",this).html());
	}).click(function(){
		return false;
	});
});
