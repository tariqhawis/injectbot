<?php

//===================includes======================

define("ERROR", "Unable to make successful injection");

function _print($msg = "", $result = "")
{
    $url = "";
    $target_profile = "";
    $tables = "";
    if (isset($_SESSION["target"]) && $msg != " Profile cleared..") {
        $target = unserialize($_SESSION["target"]);
         if ($target->isVuln) {
            $site_name = $target->get_url($target->url)["host"];
            $url = $target->url[0];
            $tables = $target->tblSchemas;

            $target_profile = <<<ECH
          <div class=form-group" >
          <div class="toast" data-autohide="false">
          <div class="toast-header" style=" background-color: #000;">
          <strong class="mr-auto text-success">$site_name Added</strong>
          <small class="text-muted">Clear Profile</small>
          <button type="submit" class="ml-2 mb-1 close text-success" data-dismiss="toast" name="close">&times;</button>
          </div>
        </div>
        </div>
    <script>
    $(document).ready(function(){
        $('.toast').toast('show');
       $('.close').click(function(){
           var clickBtnValue = $(this).val();
           var ajaxurl = 'index.php',
           data =  {'action': clickBtnValue};
           $.post(ajaxurl, data, function (response) {
             $('.toast').toast('hide');
           });
       });
     });
    </script>
ECH;
     }
    } else {
        $target_profile ="";
    }
    ?>
<!--
GPL-3.0 License 2020 InjectBot - By Tariq Hawis

-Advisory
All the uses of this tool should be used for authorized penetration testing and/or educational purposes only. 
Any misuse of this software will not be the responsibility of the author or of any other collaborator. 
Use it at your own networks and/or with the network owner's permission.

-->
<!-- Body Form -->

<html>

<head>
  <title></title>
  <link rel="shortcut icon" href="inc/favicon.ico">
  <link rel="stylesheet" href="dist/css/bootstrap.min.css"/>
  <link rel="stylesheet" href="dist/css/style.css"/>
  <script src="dist/js/jquery-3.5.1.min.js" ></script>
  <script src="dist/js/bootstrap.bundle.min.js" ></script>

  <!------ Include the above in your HEAD tag ---------->
</head>

<body class="text-dark">
  <!-- Body Form -->
<div style='color:red'>
<noscript>This tool needs JavaScript to run properly!</noscript>
</div>
  <div class="container-md" id="id_over">
  ___          _             _____  ____        _____
 |_ _| _ __   (_)  ___   ___|_   _|| __ )   ___|_   _|
  | | | '_ \  | | / _ \ / __| | |  |  _ \  / _ \ | |  
  | | | | | | | ||  __/| (__  | |  | |_) || (_) || |  
 |___||_| |_|_/ | \___| \___| |_|  |____/  \___/ |_|  
                          |__/                           @TariqHawis             
</div>
<div class="container">
 <div class="row">
  <div class="col-md-12">
<?php
    echo <<<_DONE
        \n\t<form class="form-horizontal" name="injectForm" action="index.php" method="post" role="form" id="form-scan">
        $target_profile
          <div id="textarea" class="form-group rounded" contenteditable>
            <div class="form-group" id="output">
            >_$msg
            </div>
            <table class="table table-borderless table-hover">
              $result
            </table>
          </div>
        <div class="form-group">
          <div class="input-group mb-3">
            <input type="text" name="url" id="url" class="form-control" placeholder="http://site.com/index.php?id=1" value="$url">
_DONE;
?>
            <div class="input-group-append">
              <button class="btn btn-success" type="submit" id="scan" name="scan" value="Scan">Scan</button>
            </div>
          </div>
        </div>
        <div class="form-group">
          <select class="custom-select" name="select_attack" onchange="showDiv(this)">
            <option value="test">Scan only</option>
            <option value="dbinfo">Database fingerprint</option>
            <option value="fschem">Fetch schemas</option>
            <option value="recrd">Retrieve user records</option>
          </select>
        </div>
        <div class="form-group" id="recrd">
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Table name</span>
            </div>
            <select class="custom-select" name="tbl_select" id="tbl_select"></select>
          </div>
          <div class="input-group mb-3">
            <div class="input-group-prepend">
              <span class="input-group-text">Records count</span>
            </div>
            <select class="custom-select" name="rows_no" id="rows_no"></select>
          </div>
        </div>
        <div class="form-group" id="fschem">
         <div class="custom-control custom-switch">
          <input type="checkbox" name="blindOn" class="custom-control-input" id="switch1">
          <label class="custom-control-label text-success" for="switch1">Blind SQLi</label>
         </div>
        </div>
      </form>
<script >
  var min = 1;
  var max = 5;
  var select = document.getElementById("rows_no");

for (var i = min; i <= max; i++) {
  var opt = document.createElement("option");
  opt.value = i;
  opt.innerHTML = i;
  select.appendChild(opt);
}

function showDiv(element) {
  var divId = element.value;
  if (element.value == "recrd") {
    document.getElementById(divId).style.display = "block";
    document.getElementById("fschem").style.display = "block";
    var tbl_select = document.getElementById("tbl_select");
    var tbls = <?php echo json_encode($tables); ?>;

    for (var key in tbls) {
      if (tbls.hasOwnProperty(key)) {
          var opt = document.createElement("option");
          opt.value = key;
          opt.innerHTML = key;
          tbl_select.appendChild(opt);
      }
    }
  } else if (element.value != "fschem" && element.value != "recrd") {
    document.getElementById("recrd").style.display = "none";
    document.getElementById("fschem").style.display = "none";
  }
  if (element.value == "fschem" || element.value == "dbinfo" || element.value == "recrd") {
    document.getElementById("fschem").style.display = "block";
  } else if (element.value != "fschem" && element.value != "recrd" && element.value != "dbinfo") {
    document.getElementById("fschem").style.display = "none";
  }
}
</script>
<footer class="footer">
      <div class="container">
        <span class="text-muted">GPL-3.0 License 2020 InjectBot &trade; - By Tariq Hawis</span>
      </div>
    </footer>
</body>
</html>
<?php
}
//=================== End Functions ========================

?>