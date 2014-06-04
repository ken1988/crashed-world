//======================================================================
// �J����ʗp
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
	$('#wstattable').visualize({type: 'line',width: '700px', title: '�C�e���Y',colFilter: ':last-child',parseDirection: 'y'});
	$('#wstattable').visualize({type: 'line',width: '700px', title: '�Y�Ɠ��v',colFilter: ':gt(3):not(:last-child)',parseDirection: 'y'});
	$('#wstattable').visualize({type: 'line',width: '700px', title: '�l�����v',colFilter: ':lt(4)',parseDirection: 'y'});

    $("#regtin").click(function(){
   		var intunit = $('#sel_kind').val();
   		var send_num = $('#sel_val').val();
   		var units = "";
   		
   		switch(intunit){
   			case "71":
   				units = "��Va";
   				break;
   			case "72":
  				units = "���g��";
   				break;
   			case "73":
   				units = "��Va";
   				break;
   			case "74":
	   		   	units = "�K����";
   				break;
   			case "75":
	   		   	units = "���g��";
   				break;
   			case "76":
	   		   	units = "���g��";
   				break;
   			case "77":
	   		   	units = "���g��";
   				break;
   			case "78":
	   		   	units = "���g��";
   				break;
   			case "79":
	   		   	units = "�g��";
   				break;
   			case 80:
	   		   	units = "�o����";
   				break;
   			case 81:
	   		   	units = "�K����";
   				break;
   			case 82:
	   		   	units = "���g��";
   				break;
   			case 83:
	   		   	units = "���K�g��";
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
