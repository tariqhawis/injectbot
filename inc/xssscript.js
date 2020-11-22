function toggleInput() {
var test=document.getElementById("test");
var mysql5=document.getElementById("mysql5");
var accounts=document.getElementById("accounts");
var blind=document.getElementById("blind");
var blind_all=document.getElementById("blind_all");
var blind_select=document.getElementById("blind_select");
var tbl_name=document.getElementById("tbl_name");
var col_name=document.getElementById("col_name");
var rows_no=document.getElementById("rows_no");
var mysql4=document.getElementById("mysql4");
var passwd=document.getElementById("passwd");
var file = document.getElementById("file");
var tbl = document.getElementById("tbl");
var col = document.getElementById("col");
var update = document.getElementById("update");
var updtchk=document.getElementById("updtchk");
var injfile=document.getElementById("injfile");
var tblpre = document.getElementById("tblpre");
var colpre = document.getElementById("colpre");
if (test.checked){
  document.getElementById("info").disabled = false;
  document.getElementById("make").disabled = false;
 }
else{
  document.getElementById("info").disabled = true;
  document.getElementById("make").disabled = true;
}
if(blind.checked) {
  blind_all.disabled = false;
  blind_select.disabled = false;
  rows_no.disabled = false;
  rows_no.style.backgroundColor="#000";
  rows_no.style.color="#00EE00";
  tbl_name.disabled = false;
  tbl_name.style.backgroundColor="#000";
  tbl_name.style.color="#00EE00";
  col_name.disabled = false;
  col_name.style.backgroundColor="#000";
  col_name.style.color="#00EE00";
}
else {
  blind_all.disabled = true;
  blind_all.checked = false;
  blind_select.disabled = true;
  blind_select.checked = false;
  rows_no.disabled = true;
  rows_no.style.backgroundColor="#111";
  rows_no.style.color="#777";
  tbl_name.disabled = true;
  tbl_name.style.backgroundColor="#111";
  tbl_name.style.color="#777";
  col_name.disabled = true;
  col_name.style.backgroundColor="#111";
  col_name.style.color="#777";
}

if(mysql5.checked){
  accounts.disabled = false;
  //spec.disabled = false;
  //schm_id.disabled = false;
  //tbl_id.disabled = false;
  //col_id.disabled = false;
}
else{
  accounts.disabled = true;
  accounts.checked = false;
  //spec.disabled = true;
  //schm_id.disabled = true;
  //tbl_id.disabled = true;
  //col_id.disabled = true;
  //alltbls.disabled = true;
}
/*if(spec.checked){
  document.getElementById("schm_id").disabled = false;
  document.getElementById("tbl_id").disabled = false;
  document.getElementById("col_id").disabled = false;
}
else{
  document.getElementById("schm_id").disabled = true;
  document.getElementById("tbl_id").disabled = true;
  document.getElementById("col_id").disabled = true;
}
if (schm_id.checked && schm_id.disabled==false) {
  document.getElementById("schm_txt").style.backgroundColor="#000";
  document.getElementById("schm_txt").style.color="#00EE00";
  document.getElementById("schm_txt").disabled = false;
}
else{
  document.getElementById("schm_txt").style.backgroundColor="#111";
  document.getElementById("schm_txt").style.color="#777";
  document.getElementById("schm_txt").disabled = true;
  document.getElementById("schm_txt").value="";
}
if (tbl_id.checked && tbl_id.disabled==false) {
  document.getElementById("tbl_txt").style.backgroundColor="#000";
  document.getElementById("tbl_txt").style.color="#00EE00";
  document.getElementById("tbl_txt").disabled = false;
}
else{
  document.getElementById("tbl_txt").style.backgroundColor="#111";
  document.getElementById("tbl_txt").style.color="#777";
  document.getElementById("tbl_txt").disabled = true;
  document.getElementById("tbl_txt").value="";
}
if (col_id.checked && col_id.disabled==false ) {
  document.getElementById("col_txt").style.backgroundColor="#000";
  document.getElementById("col_txt").style.color="#00EE00";
  document.getElementById("col_txt").disabled = false;
}
else{
  document.getElementById("col_txt").style.backgroundColor="#111";
  document.getElementById("col_txt").style.color="#777";
  document.getElementById("col_txt").disabled = true;
  document.getElementById("col_txt").value="";
}*/
if (passwd.checked) {
  file.disabled = false;
  file.style.backgroundColor="#000";
  file.style.color="#00EE00";
}
else{
  file.disabled = true;
  file.style.backgroundColor="#111";
  file.style.color="#777";
}

if (mysql4.checked) {
  tblpre.disabled = false;
  tblpre.style.backgroundColor="#000";
  tblpre.style.color="#00EE00";
  colpre.disabled = false;
  colpre.style.backgroundColor="#000";
  colpre.style.color="#00EE00";
}
else {
  tblpre.disabled = true;
  tblpre.checked = false;
  tblpre.style.backgroundColor="#111";	
  tblpre.style.color="#777";
  colpre.disabled = true;
  colpre.checked = false;
  colpre.style.backgroundColor="#111";	
  colpre.style.color="#777";
 }

if (updtchk.checked){
  tbl.disabled = false;
  col.disabled = false;
  update.disabled = false;
  tbl.style.backgroundColor="#000";
  tbl.style.color="#00EE00";
  col.style.backgroundColor="#000";
  col.style.color="#00EE00";
  update.style.backgroundColor="#000";
  update.style.color="#00EE00";
}
else {
  tbl.disabled = true;
  col.disabled = true;
  update.disabled = true;
  tbl.style.backgroundColor="#111";
  tbl.style.color="#777";
  col.style.backgroundColor="#111";
  col.style.color="#777";
  update.style.backgroundColor="#111";
  update.style.color="#777";
}
/*if (cpanel.checked) {
  document.getElementById("link").disabled = false;
  document.getElementById("link").style.backgroundColor="#000";
      document.getElementById("link").style.color="#00EE00";
  document.getElementById("asphp").disabled = false;
  document.getElementById("asphp").style.backgroundColor="#000";
      document.getElementById("asphp").style.color="#00EE00";
  document.getElementById("url").disabled = /*true;*/ false;
//  document.getElementById("url").value = " ";
/*}
else{
  document.getElementById("link").disabled = true;
  document.getElementById("link").style.backgroundColor="#111";
    document.getElementById("link").style.color="#777";
  document.getElementById("asphp").disabled = true;
    document.getElementById("asphp").style.backgroundColor="#111";
	  document.getElementById("asphp").style.color="#777";
  document.getElementById("url").disabled = false;
}*/
if (injfile.checked) {
  document.getElementById("pathcr").style.backgroundColor="#000";
        document.getElementById("pathcr").style.color="#00EE00";
  document.getElementById("pathcr").disabled = false;
  document.getElementById("code").style.backgroundColor="#000";
        document.getElementById("code").style.color="#00EE00";
  document.getElementById("code").disabled = false;
}
else{
  document.getElementById("pathcr").style.backgroundColor="#111";
      document.getElementById("pathcr").style.color="#777";
  document.getElementById("pathcr").disabled = true;
    document.getElementById("code").style.backgroundColor="#111";
	    document.getElementById("code").style.color="#777";
  document.getElementById("code").disabled = true;
}
}

function color(flag){
var idover=document.getElementById("idover");
if(flag=='1')
idover.style.color="green";
else if(flag=='0')
idover.style.color="#D1FFCB";
}
function back(id,flag){
if(flag=='1')
id.style.backgroundColor="#333";
else if(flag=='0')
id.style.backgroundColor="#000";
}
