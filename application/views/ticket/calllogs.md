
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
<head> 
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /> 
<title>Follow-Up</title> 

<!-- Jquery  -->  
<script type="text/javascript" src="../jquery.js"></script> 

<!-- clueTip  -->  
<script src="../cluetip/lib/jquery.hoverIntent.js" type="text/javascript"></script>
<script src="../cluetip/lib/jquery.bgiframe.min.js" type="text/javascript"></script>
<script src="../cluetip/jquery.cluetip.js" type="text/javascript"></script>
<script src="../cluetip/demo/demo.js" type="text/javascript"></script>
<link rel="stylesheet" href="../cluetip/jquery.cluetip.css" type="text/css" />
<link rel="stylesheet" href="../cluetip/demo/demo.css" type="text/css" /></head>

<!-- jEditable  -->  
<script type="text/javascript" src="../jquery.color.js"></script> 
<script type="text/javascript" src="../jquery.jeditable.pack.js" charset="utf-8"></script> 

<!--    Modal     --> 

        <title>Thickbox to jqModal example</title> 
        <!--
        .................................................................................
        CSS
        .................................................................................
        --> 
        <style type="text/css"> 
		/* jqModal base Styling courtesy of;
	Brice Burgess <bhb@iceburg.net> */

/* The Window's CSS z-index value is respected (takes priority). If none is supplied,
	the Window's z-index value will be set to 3000 by default (via jqModal.js). */

.jqmWindow {
    display: none;
    position: fixed;
    top: 17%;
    left: 50%;
    margin-left: -300px;
    width: 600px;
    background-color: #EEE;
    color: #333;
    border: 1px solid black;
    padding: 12px;
}

.jqmOverlay {
    background-color: #000;
}

/* Background iframe styling for IE6. Prevents ActiveX bleed-through (<select> form elements, etc.) */
* iframe.jqm {
    position: absolute;
    top: 0;
    left: 0;
    z-index: -1;
    width: expression(this.parentNode.offsetWidth+'px');
    height: expression(this.parentNode.offsetHeight+'px');
}

/* Fixed posistioning emulation for IE6
     Star selector used to hide definition from browsers other than IE6
     For valid CSS, use a conditional include instead */
* html .jqmWindow {
    position: absolute;
    top: expression((document.documentElement.scrollTop || document.body.scrollTop) + Math.round(17 * (document.documentElement.offsetHeight || document.body.clientHeight) / 100) + 'px');
}
		
/*body{
padding:10px 50px;
color: #000;
background: transparent url(_img/main_background.gif) top left repeat-x;
font:12px arial;
}
*/
#page{
max-width:65em;
min-width:640px;
margin:auto;

}

.right{border-bottom:4px solid #6495ED;margin:10px;clear:right;}

        .jqmOverlay { background-color: #FFF; }
            .jqmWindow {
                background: #888888 url(http://www.vallery.com.br/jquery/jqModal/modal_bckgrn.gif) left top repeat-x;
                color: #000;
                border: 1px solid #888888;
                padding: 0 0px 50px;
            }
 
            button.jqmClose {
                background: none;
                border: 0px solid #EAEAEB;
                color: #000;
                clear: right;
                float: right;
                padding: 0;
                margin-top:5px;
                margin-left:5px;
                cursor: pointer;
                font-size: 8px;
 
                letter-spacing: 1px;
            }
 
            button.jqmClose:hover, button.jqmClose:active {
                color: #FFF;
				border: 0px solid #FFF;
            }
 
            #jqmTitle {
                background: transparent;
                color: black;
                text-transform: capitalize;
                height: 50px;
                padding: 0px 5px 0 10px;
 
            }
 
            #jqmContent {
                width: 100%;
                height: 100%;
                display: block;
                clear: both;
                margin: 0;
                margin-top: 0px;
                background: #e8e8e8;
                border: 1px solid #888888;
            }
        </style> 
        <!--
        .................................................................................
        JAVASCRIPT
        .................................................................................
        --> 
    
 <script src="https://pixeline.be/experiments/ThickboxToJqModal/jqModal.js" type="text/javascript"> 
        </script> 
        <script type="text/javascript"> 
            $(document).ready(function(){
                 //thickbox replacement
    var closeModal = function(hash)
    {
        var $modalWindow = $(hash.w);
 
        //$('#jqmContent').attr('src', 'blank.html');
        $modalWindow.fadeOut('2000', function()
        {
            hash.o.remove();
            //refresh parent
            if (hash.refreshAfterClose == true)
            {
                window.location.href = document.location.href;
            }
        });
    };
    var openInFrame = function(hash)
    {
        var $trigger = $(hash.t);
        var $modalWindow = $(hash.w);
        var $modalContainer = $('iframe', $modalWindow);
 
        var myUrl = $trigger.attr('href');
 
        var myTitle = $trigger.attr('title');
        var newWidth = 0, newHeight = 0, newLeft = 0, newTop = 0;
        $modalContainer.html('').attr('src', myUrl);
        $('#jqmTitleText').text(myTitle);
        myUrl = (myUrl.lastIndexOf("#") > -1) ? myUrl.slice(0, myUrl.lastIndexOf("#")) : myUrl;
        var queryString = (myUrl.indexOf("?") > -1) ? myUrl.substr(myUrl.indexOf("?") + 1) : null;
 
        if (queryString != null && typeof queryString != 'undefined')
        {
            var queryVarsArray = queryString.split("&");
            for (var i = 0; i < queryVarsArray.length; i++)
            {
                if (unescape(queryVarsArray[i].split("=")[0]) == 'width')
                {
                    var newWidth = queryVarsArray[i].split("=")[1];
                }
                if (escape(unescape(queryVarsArray[i].split("=")[0])) == 'height')
                {
                    var newHeight = queryVarsArray[i].split("=")[1];
                }
                if (escape(unescape(queryVarsArray[i].split("=")[0])) == 'jqmRefresh')
                {
                    // if true, launches a "refresh parent window" order after the modal is closed.
                    hash.refreshAfterClose = queryVarsArray[i].split("=")[1]
                } else
                {
 
                    hash.refreshAfterClose = false;
                }
            }
            // let's run through all possible values: 90%, nothing or a value in pixel
            if (newHeight != 0)
            {
                if (newHeight.indexOf('%') > -1)
                {
 
                    newHeight = Math.floor(parseInt($(window).height()) * (parseInt(newHeight) / 100));
 
                }
                var newTop = Math.floor(parseInt($(window).height() - newHeight) / 2);
            }
            else
            {
                newHeight = $modalWindow.height();
            }
            if (newWidth != 0)
            {
                if (newWidth.indexOf('%') > -1)
                {
                    newWidth = Math.floor(parseInt($(window).width() / 100) * parseInt(newWidth));
                }
                var newLeft = Math.floor(parseInt($(window).width() / 2) - parseInt(newWidth) / 2);
 
            }
            else
            {
                newWidth = $modalWindow.width();
            }
 
            // do the animation so that the windows stays on center of screen despite resizing
            $modalWindow.css({
                width: newWidth,
                height: newHeight,
                opacity: 0
            }).jqmShow().animate({
                width: newWidth,
                height: newHeight,
                top: newTop,
                left: newLeft,
                marginLeft: 0,
                opacity: 1
            }, 'slow');
        }
        else
        {
            // don't do animations
            $modalWindow.jqmShow();
        }
 
    }
 
    $('#modalWindow').jqm({
        overlay: 70,
        modal: true,
        trigger: 'a.thickbox',
        target: '#jqmContent',
        onHide: closeModal,
        onShow: openInFrame
    });
 
            });
        </script> 

<!--  Fim  Modal     --> 
 
<script type="text/javascript"> 
$(document).ready(function(){
 
	$(".pane:even").addClass("alt");
 
	$(".pane .btn-follow").click(function(){
		//alert("Este log será apagado!");
		$.post("save.php", { id: $(this).parents(".pane").children('#idlog').text() , value: "3" } );			
		$(this).parents(".pane").animate({ backgroundColor: "#fbc7c7" }, "fast")
		//.animate({ opacity: "hide" }, "slow")
		.animate({ backgroundColor: "#ffffff" }, "slow")
		.addClass("follow")

		return false;
	});
 
	$(".pane .btn-unapprove").click(function(){
		$.post("save.php", { id: $(this).parents(".pane").children('#idlog').text() , value: "2" } );	

		$(this).parents(".pane").animate({ backgroundColor: "#fff568" }, "fast")
		.animate({ backgroundColor: "#ffffff" }, "slow")
		.addClass("spam")		
		return false;
	});

	$(".pane .btn-approve").click(function(){
		$.post("save.php", { id: $(this).parents(".pane").children('#idlog').text() , value: "1" } );	
										   
		$(this).parents(".pane").animate({ backgroundColor: "#dafda5" }, "fast")
		.animate({ backgroundColor: "#ffffff" }, "slow")
		.removeClass("spam")
		//.toggleClass("pane")
		return false;
	});
 
	$(".pane .btn-spam").click(function(){		
		$.post("save.php", { id: $(this).parents(".pane").children('#idlog').text() , value: "0" } );	
										
		$(this).parents(".pane").animate({ backgroundColor: "#fbc7c7" }, "fast")
		.animate({ opacity: "hide" }, "slow")
		return false;
	});
 
});
</script> 
 
 
<style type="text/css"> 
body {
	margin: 10px auto;
	width: 870px;
}
a, a:visited {
	color: #000099;
}
a:hover {
	text-decoration: none;
}
table {
	margin: 0;
	padding: 0 0 .3em;
	font-size: 11px;
}
h2 {
	margin: 0;
	padding: 0 0 .3em;
	color: #999;
	background-color: #FFF;
}
h3 {
	margin: 0;
	padding: 0 0 .3em;
}

h5 {
	margin: 0;
	padding: 0 0 .3em;
	color: #999;
}
h6 {
	margin: 7px;
	padding: 0 0 .3em;
	position: absolute;
	right: 0;
	top: 0;
	color: #999;
}

.pane {
	width: 450px;
	background: #ffffff;
	padding: 10px 20px 10px;
	position: relative;
	border-top: solid 1px #ccc;
	font-family: serif;
	font-size: 16px;
}

.pane p {
	margin: 0;
	padding: 0 0 1em;
}

.pane a, a:visited {
	color: #99C;
}
.pane a:hover {
	text-decoration: none;
}

.alt {
	background: #f5f4f4; 
}
.spam {
	color: #999999;
}
.follow {
	color: #F60;
}
.factfile {
	color: #F90;
}
</style> 
</head> 
 
<body> 

<div align=left><h2>Logs do Dia (2)</h2></div><br/><div class="pane  "> <div id='idlog' style="font-size:7px;color:#eee" >489712</div><h6 >3 horas atrás</h6><h3>Camila Costa Barbosa disse :</h3><p align=left><a class="factfile" href="save.php"  rel="../../v4/fact_file_client.php?id=722586" title="Detalhes ">Carrao Comercio De Rolamentos E Pecas Para Maquinas Industri</a></p><p>Enviado email pelo sistema da pendência 1-edup-244745a<h5></h5></p><p><a href="#" class="btn-follow">Acompanhar</a> | <a href="#" class="btn-unapprove">Lida</a> | <a href="#" class="btn-approve">Não Lida</a> | <a href="#" class="btn-spam">Desativar</a></p></div><div class="pane  "> <div id='idlog' style="font-size:7px;color:#eee" >489711</div><h6 >3 horas atrás</h6><h3>Camila Costa Barbosa disse :</h3><p align=left><a class="factfile" href="save.php"  rel="../../v4/fact_file_client.php?id=719679" title="Detalhes ">Canal Comercio De Rolamentos Ltda</a></p><p>Enviado email pelo sistema da pendência 1-edup-242728c<h5></h5></p><p><a href="#" class="btn-follow">Acompanhar</a> | <a href="#" class="btn-unapprove">Lida</a> | <a href="#" class="btn-approve">Não Lida</a> | <a href="#" class="btn-spam">Desativar</a></p></div><!--<div class="pane"> 
	<h3>Nick says:</h3> 
	<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi malesuada, ante at feugiat tincidunt, enim massa gravida metus, commodo lacinia massa diam vel eros. Proin eget urna. Nunc fringilla neque vitae odio. Vivamus vitae ligula.</p> 
	<p><a href="#" class="btn-follow">Delete</a> | <a href="#" class="btn-unapprove">Unapprove</a> | <a href="#" class="btn-spam">Spam</a></p> 
</div> 
<div class="pane spam"> 
	<h3>John says:</h3> 
	<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi malesuada, ante at feugiat tincidunt, enim massa gravida metus, commodo lacinia massa diam vel eros. Proin eget urna. Nunc fringilla neque vitae odio. Vivamus vitae ligula.</p> 
	<p><a href="#" class="btn-follow">Delete</a> | <a href="#" class="btn-approve">Approve</a> | <a href="#" class="btn-spam">Spam</a></p> 
</div> 
<div class="pane"> 
	<h3>Smith says:</h3> 
	<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi malesuada, ante at feugiat tincidunt, enim massa gravida metus, commodo lacinia massa diam vel eros. Proin eget urna. Nunc fringilla neque vitae odio. Vivamus vitae ligula.</p> 
	<p><a href="#" class="btn-follow">Delete</a> | <a href="#" class="btn-unapprove">Unapprove</a> | <a href="#" class="btn-spam">Spam</a></p> 
</div> 
<div class="pane"> 
	<h3>Jen says:</h3> 
	<p>Morbi malesuada, ante at feugiat tincidunt, enim massa gravida metus, commodo lacinia massa diam vel eros. Proin eget urna. Nunc fringilla neque vitae odio. Vivamus vitae ligula.</p> 
	<p><a href="#" class="btn-follow">Delete</a> | <a href="#" class="btn-unapprove">Unapprove</a> | <a href="#" class="btn-spam">Spam</a></p> 
</div> 
<div class="pane"> 
	<h3>Jen says:</h3> 
	<p>Morbi malesuada, ante at feugiat tincidunt, enim massa gravida metus, commodo lacinia massa diam vel eros. Proin eget urna. Nunc fringilla neque vitae odio. Vivamus vitae ligula.</p> 
	<p><a href="#" class="btn-follow">Delete</a> | <a href="#" class="btn-unapprove">Unapprove</a> | <a href="#" class="btn-spam">Spam</a></p> 
</div> 
<div class="pane spam"> 
	<h3>John says:</h3> 
	<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi malesuada, ante at feugiat tincidunt, enim massa gravida metus, commodo lacinia massa diam vel eros. Proin eget urna. Nunc fringilla neque vitae odio. Vivamus vitae ligula.</p> 
	<p><a href="#" class="btn-follow">Delete</a> | <a href="#" class="btn-approve">Approve</a> | <a href="#" class="btn-spam">Spam</a></p> 
</div> 
<div class="pane"> 
	<h3>Smith says:</h3> 
	<p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi malesuada, ante at feugiat tincidunt, enim massa gravida metus, commodo lacinia massa diam vel eros. Proin eget urna. Nunc fringilla neque vitae odio. Vivamus vitae ligula.</p> 
	<p><a href="#" class="btn-follow">Delete</a> | <a href="#" class="btn-unapprove">Unapprove</a> | <a href="#" class="btn-spam">Spam</a></p> 
</div> 
-->
    <!-- HEADER -->
    <!-- end HEADER -->
    <!-- end MAIN --> 
    </div> 
    <div id="modalWindow" class="jqmWindow"> 
        <div id="jqmTitle"> 
            <button class="jqmClose"> 
               X
            </button> 
            <span id="jqmTitleText">Title of modal window</span> 
        </div> 
        <iframe id="jqmContent" src=""> 
        </iframe> 
</div> 

</body> 
</html> 


SELECT   c.origin as Origem, 
         c.date as Data, 
         clientes.Nome as Cliente,
         CONCAT(clientes.cidade,'-', clientes.estado) as Cidade,
         lcase(c.subject) as Assunto, 
         c.details as Nota, 
         u.user as Autor,
      c.id AS Id, 
      clientes.id as Idcli
FROM 	call_log c, clientes ,  users u
WHERE 	 u.id=c.userid and 
         clientes.id=c.idcli AND
         c.id >400000 AND
        

       
          if (".$_GET["VENDEDOR"] ."<>0, c.userid = ".$_GET["VENDEDOR"]."      ,1=1) AND

         if ('".$_GET["DIGITE"]."'>'',(  MATCH (details) AGAINST ('".$_GET["DIGITE"]."')  ),1=1)  
         ".Seller('c','userid')."  

ORDER BY   ".$_GET["ORDER"]."