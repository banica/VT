<?php
/*
record id =>vtiger_import_1 -> record_id
vtiger_crmentry_seq
INSERT INTO `vtiger_modcomments` (`modcommentsid`, `commentcontent`, `related_to`, `parent_comments`) VALUES
(197, 'comentariu de test', '39', '');

INSERT INTO `vtiger_crmentity` (`crmid`, `smcreatorid`, `smownerid`, `modifiedby`, `setype`, `description`, `createdtime`, `modifiedtime`, `viewedtime`, `status`, `version`, `presence`, `deleted`) VALUES
(197, 1, 1, 1, 'ModComments', NULL, '2013-11-04 20:48:40', '2013-11-04 20:48:40', NULL, NULL, 0, 1, 0),
*/

if (!function_exists('str_getcsv')) { 
    function str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\', $eol = '\n') { 
        if (is_string($input) && !empty($input)) { 
            $output = array(); 
            $tmp    = preg_split("/".$eol."/",$input); 
            if (is_array($tmp) && !empty($tmp)) { 
                while (list($line_num, $line) = each($tmp)) { 
                    if (preg_match("/".$escape.$enclosure."/",$line)) { 
                        while ($strlen = strlen($line)) { 
                            $pos_delimiter       = strpos($line,$delimiter); 
                            $pos_enclosure_start = strpos($line,$enclosure); 
                            if ( 
                                is_int($pos_delimiter) && is_int($pos_enclosure_start) 
                                && ($pos_enclosure_start < $pos_delimiter) 
                                ) { 
                                $enclosed_str = substr($line,1); 
                                $pos_enclosure_end = strpos($enclosed_str,$enclosure); 
                                $enclosed_str = substr($enclosed_str,0,$pos_enclosure_end); 
                                $output[$line_num][] = $enclosed_str; 
                                $offset = $pos_enclosure_end+3; 
                            } else { 
                                if (empty($pos_delimiter) && empty($pos_enclosure_start)) { 
                                    $output[$line_num][] = substr($line,0); 
                                    $offset = strlen($line); 
                                } else { 
                                    $output[$line_num][] = substr($line,0,$pos_delimiter); 
                                    $offset = ( 
                                                !empty($pos_enclosure_start) 
                                                && ($pos_enclosure_start < $pos_delimiter) 
                                                ) 
                                                ?$pos_enclosure_start 
                                                :$pos_delimiter+1; 
                                } 
                            } 
                            $line = substr($line,$offset); 
                        } 
                    } else { 
                        $line = preg_split("/".$delimiter."/",$line); 
    
                        /* 
                         * Validating against pesky extra line breaks creating false rows. 
                         */ 
                        if (is_array($line) && !empty($line[0])) { 
                            $output[$line_num] = $line; 
                        }  
                    } 
                } 
                return $output; 
            } else { 
                return false; 
            } 
        } else { 
            return false; 
        } 
    } 
} 

$conn = mysql_connect("localhost", "root", "") or die ("Error on connection"); //conexiune
mysql_select_db("connect2give"); //baza de date
$res = mysql_query("SELECT id FROM vtiger_crmentity_seq"); //id-ul maxim
if ($row = mysql_fetch_array($res)) $commentId = $row["id"];

echo "<pre>";
$csvrows = str_getcsv(file_get_contents("comments_sfh.csv"), "\n");
foreach ($csvrows as $index => $entry) {
	if ($index == 0) continue;
	$columns = str_getcsv($entry[0], ",");
	$columns = $columns[0]; //cum e in CSV 
	$companyName = mysql_real_escape_string($columns[3], $conn); //in CSV coloana 4 e company
	$comment = mysql_real_escape_string($columns[4] . ' - ' . $columns[5], $conn);
	$date = explode('/',$columns[0]); //coloana 0 este data
	$time = explode(':',$columns[1]); //coloana 1 este ora
	$commentDate = $date[2] . '-' . (($date[0]<10?'0':'').($date[0]*1)) . '-' . (($date[1]<10?'0':'').($date[1]*1)) . ' ' . (($time[0]<10?'0':'').($time[0]*1)) . ':' . (($time[0]<10?'0':'').($time[1]*1)) . ':00';
	
	if (trim($companyName) == '') continue; //empty company
	if (trim($comment) == '-') continue; //empty comment
	
	$authorRes = mysql_query("SELECT recordId FROM vtiger_import_1 WHERE account_id = '$companyName'");
	if ($row = mysql_fetch_assoc($authorRes)) {
		$authorId = $row["recordId"];
	}
	else {
		#echo "Author id not found for name: $companyName \n";
		continue;
	}
	if ($authorId) {
		$commentId++;
		mysql_query("INSERT INTO `vtiger_modcomments` (`modcommentsid`, `commentcontent`, `related_to`, `parent_comments`) VALUES ($commentId, '$comment', '$authorId', '')");
		mysql_query("INSERT INTO `vtiger_modcommentscf` (`modcommentsid`) VALUES ($commentId);");
		mysql_query("INSERT INTO `vtiger_crmentity` (`crmid`, `smcreatorid`, `smownerid`, `modifiedby`, `setype`, `description`, `createdtime`, `modifiedtime`, `viewedtime`, `status`, `version`, `presence`, `deleted`) VALUES ($commentId, 1, 1, 1, 'ModComments', NULL, '$commentDate', '$commentDate', NULL, NULL, 0, 1, 0)");
		echo "Comment with id $commentId inserted for company $companyName\n";
	}
}
mysql_query("UPDATE vtiger_crmentity_seq SET id = $commentId");