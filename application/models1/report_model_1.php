<?php

class Report_Model extends CI_Model {

function member_list($from = '', $to = '') {
$sql = "SELECT * from members ";
if ($from && $to) {
$sql.= " WHERE joiningdate>='$from' AND joiningdate <= '$to'";
}

$sql.=" ORDER BY member_id ASC";

return $this->db->query($sql)->result();
}

function contribution_report($grouping, $from = '', $to = '') {
if($grouping == 1){
$sql = "SELECT PID,
       SUM(CASE WHEN trans_type = 'CR' THEN amount END) AS CR,
       SUM(CASE WHEN trans_type = 'DR' THEN amount END) AS DR
         FROM contribution_transaction";
if ($from && $to) {
$sql.= " WHERE  createdon>='$from 00:00:00' AND createdon <= '$to 23:59:59'";
}

$sql.= '    GROUP BY PID';
}else{
$sql = "SELECT * FROM contribution_transaction";

if ($from && $to) {
$sql.= " WHERE  createdon>='$from 00:00:00' AND createdon <= '$to 23:59:59'";
}
$sql.=" ORDER BY createdon ASC";
}


return $this->db->query($sql)->result();
}


function contribution_statement($PID, $from = '', $to = '') {

$sql = "SELECT * FROM contribution_transaction WHERE PID='$PID'";

if ($from && $to) {
$sql.= " AND  createdon>='$from 00:00:00' AND createdon <= '$to 23:59:59'";
}
$sql.=" ORDER BY createdon ASC";

return $this->db->query($sql)->result();


}

}
