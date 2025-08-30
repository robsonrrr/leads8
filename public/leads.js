$("#addCart2").livequery("click", function () {
  //alert('break 1');cSCart=33
  //var id	= $(this).parent().parent().attr("id");
  //alert(id);
  var rel = $(this).attr("rel");
  var scart = rel.split("cSCart=");
  var segment = rel.split("cSegment=");
  var cart = scart[1].split("&");
  //		var value =	$(this).parent().prev().children('.vTerms').val().split('-');
  //		var value =	$(this).parent().prev().prev().children('.vProduct').val();
  var qty = 1;
  // var segment =	$(this).parent().prev().children('.cSegment').val();

  var url = rel + "&qProd=" + qty + "&cSegment=" + segment[1];
  //alert(url);
  $.ajax({
    url: url,
    success: function (msg) {
      //alert( "Data Saved: " + msg );
      //$("#leads").html(msg);
      // $("#cartBox").loadJFrame('/leads/icart/'+cart[0]) ;
      //$("#leads").attr("src", '/K3/leads2/icart/33/' );
      // alert(src);
      //src.html('/K3/leads2/icart/33');
      //alert('break 3');
    },
  });
  return false;
});

$(".auto_select_cart2").livequery(function () {
  var cartid = $(this).attr("target");
  var name = $(this).attr("name");

  if ($(this).attr("xtras") !== undefined) {
    // attribute exists
    var xtras = $(this).attr("xtras");
  } else {
    // attribute does not exist
    var xtras = "";
  }

  // $(this).editable("/K/content/InLineEdit", {
  $(this).editable("/K3/global/crud/update/", {
    indicator: '<img src="/K/media/images/indicator.gif">',
    loadurl: "/K3/autocomplete/" + name + "/" + xtras,
    type: "select",
    submit: "OK",
    cancel: "Cancelar",
    //event     : "mouseover",
    style: "inherit",
    submitdata: {
      tagid: $(this).attr("id"),
      xtras: xtras,
      label: "split",
    },
    callback: function (value, settings) {
      //alert('what'+ cartid );
      location.reload();
      //$("#content_lead2").loadJFrame('/K3/leads2/lead/'+cartid ) ;
    },
  });
});
$(".editable_cart2").livequery(function () {
  var cartid = $(this).attr("name");
  $(this).editable("/K3/global/crud/update/", {
    indicator: '<img src="/K/media/images/indicator.gif">',
    submit: "Gravar",
    select: true,
    cancel: "Cancelar",
    tooltip: "Click para editar...",
    submitdata: {
      tagid: $(this).attr("id"),
    },
    callback: function (value, settings) {
      //alert('what');
      $("#cartBox").loadJFrame("/K3/leads2/icart/" + cartid);
    },

    //style   : 'display: inline, padding-left: 20px'
  });
});

$(".del_cart2").livequery("click", function () {
  var currentId = "#" + $(this).parent().attr("id");
  var recordId = $(this).attr("id");
  var cartid = $(this).attr("name");
  $.post(
    "/K3/global/crud/delete",
    {
      id: recordId,
      value: "del",
    },
    function (data) {
      $("#cartBox").loadJFrame("/K3/leads2/icart/" + cartid);
    }
  );
  return false;
});

$("#save_Inquiry2").livequery("click", function () {
  //  type = $('#oportunity').attr('value');
  var myCart = ""; // ids to save
  $(":checkbox").each(function () {
    if ($(this).is(":checked")) {
      myCart = myCart + $(this).attr("id") + "|";
    }
  });
  //alert(myCart);
  //          $('#content_lead').slideUp(5000);
  $.post(
    "/K3/consulta/save/",
    { id: $(this).attr("id"), value: myCart },
    function (data) {
      //  alert(data);

      $("#content_lead2").slideUp(100).slideDown(2000).html(data);
      return false;
    }
  );

  //		$(':checkbox').each( function() {
  //			  if($(this).is(':checked')){
  //					currentId = '#tr_'+$(this).attr('id');
  //					runEffect(currentId,'explode');
  //			  }
  //		});

  return false;
});

$("#save_order2").livequery("click", function () {
  //  type = $('#oportunity').attr('value');
  //        var myCart = ''; // ids to save
  //        $(':checkbox').each(function () {
  //            if ($(this).is(':checked')) {
  //                myCart = myCart + $(this).attr('id') + '|';
  //            }
  //        });
  //alert(myCart);
  //          $('#content_lead').slideUp(5000);
  $.post(
    "/K3/consulta/save/",
    { id: $(this).attr("id"), value: myCart },
    function (data) {
      //  alert(data);

      $("#content_lead2").slideUp(100).slideDown(2000).html(data);
      return false;
    }
  );

  //		$(':checkbox').each( function() {
  //			  if($(this).is(':checked')){
  //					currentId = '#tr_'+$(this).attr('id');
  //					runEffect(currentId,'explode');
  //			  }
  //		});

  return false;
});

$(function () {
  $(".closer2").livequery(function () {
    $(this).button();
  });
  // a workaround for a flaw in the demo system (http://dev.jqueryui.com/ticket/4375), ignore!
  $("#dialog:ui-dialog").dialog("destroy");

  $("#dialog-confirm2").livequery(function () {
    $(this).dialog({
      autoOpen: false,
      resizable: false,
      height: 140,
      modal: true,
      buttons: {
        Confirmar: function () {
          $(this).dialog("close");
          var url = $("#closer2").attr("name");
          var target = "#" + $(this).attr("target");
          $.ajax({
            url: url,
            beforeSend: function () {
              $(target).fadeOut(200);
            },
            success: function (msg) {
              //alert( "Data Saved: " + msg );
              $(target).html(msg);
            },
            complete: function () {
              $(target).fadeIn(800);
            },
          });
        },
        Cancelar: function () {
          //alert('Cancelado');
          $(this).dialog("close");
          return false;
        },
      },
    });
  });

  $("#closer2").livequery("click", function () {
    //alert('closer');
    $("#dialog-confirm2").dialog("open");
    //return false;
  });
});
$(".jauto_cart2").livequery(function () {
  var name = $(this).attr("name");
  var vid = $(this).attr("id");
  var cartid = $(this).attr("target");
  $(this).autocomplete({
    source: "/K3/autocomplete/auto/" + name + "/",
    minLength: 2,
    select: function (event, ui) {
      $("#" + name + "_id").val(ui.item.id);
      $("#" + name + "_target").html(ui.item.txt);
      $.ajax({
        url: "/K3/global/crud/update/",
        data: "id=" + vid + "&value=" + ui.item.id,
        success: function (msg) {
          // alert( "Data Saved: " + msg );
        },
      });
    },
    close: function (event, ui) {
      $(".jauto").val("");
      $("#content_lead2").loadJFrame("/K3/leads2/lead/" + cartid);
    },
  });
});
