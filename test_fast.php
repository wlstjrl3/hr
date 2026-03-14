<?php
include "public_html/sys/sql_safe_helper.php";

$sttDate = '2021-01-01';
$endDate = '2025-03-14';
$workTypeFilter = 'ALL';
$interval = 'month';

$sttDt = new DateTime($sttDate);
$endDt = new DateTime($endDate);

$dates = [];
$current = clone $sttDt;

if ($interval == 'year') {
    while ($current <= $endDt) {
        $dates[] = $current->format('Y-12-31');
        $current->modify('first day of next year');
    }
} else { // month
    while ($current <= $endDt) {
        $dates[] = $current->format('Y-m-t'); // End of month
        $current->modify('first day of next month');
    }
}

foreach ($dates as $idx => $d) {
    if ($d > $endDate) $dates[$idx] = $endDate;
}
$dates = array_unique($dates);
sort($dates);

$t1 = microtime(true);

$sqlPsnl = "SELECT PSNL_CD, SUBSTR(REPLACE(PSNL_NUM, '-', ''), 7, 1) AS G FROM PSNL_INFO";
$resPsnl = mysqli_query($conn, $sqlPsnl);
$psnlInfo = [];
while($row = mysqli_fetch_assoc($resPsnl)) {
    $g = $row['G'];
    $psnlInfo[$row['PSNL_CD']] = in_array($g, ['1','3','5','7','9']) ? 'M' : 'F';
}

$endDateStr = str_replace('-', '', $endDate);
// Order by ASC is important here so we can overwrite/take the latest one up to the date
$sqlTrs = "SELECT PSNL_CD, REPLACE(TRS_DT, '-', '') AS TRS_DT, TRS_CD, TRS_TYPE, WORK_TYPE 
           FROM PSNL_TRANSFER 
           WHERE REPLACE(TRS_DT, '-', '') <= '{$endDateStr}'
           ORDER BY TRS_DT ASC, TRS_CD ASC";
$resTrs = mysqli_query($conn, $sqlTrs);
$trsHistory = [];
while($row = mysqli_fetch_assoc($resTrs)) {
    $trsHistory[$row['PSNL_CD']][] = $row;
}

$sqlPtt = "SELECT PSNL_CD, PTT_YEAR, PTT_CD, PTT_HOUR 
           FROM PSNL_PARTTIME 
           WHERE PTT_YEAR <= LEFT('{$endDateStr}', 4)
           ORDER BY PTT_YEAR ASC, PTT_CD ASC";
$resPtt = mysqli_query($conn, $sqlPtt);
$pttHistory = [];
while($row = mysqli_fetch_assoc($resPtt)) {
    $pttHistory[$row['PSNL_CD']][] = $row;
}

$chartData = ['labels' => [], 'male' => [], 'female' => []];

foreach ($dates as $date) {
    $safeDateStr = str_replace('-', '', $date);
    $yearStr = substr($safeDateStr, 0, 4);
    
    $m = 0; $f = 0;
    
    foreach ($psnlInfo as $psnlCd => $gender) {
        $curT = null;
        if(isset($trsHistory[$psnlCd])) {
            foreach($trsHistory[$psnlCd] as $t) {
                if($t['TRS_DT'] <= $safeDateStr) {
                    $curT = $t;
                } else {
                    break;
                }
            }
        }
        
        if($curT && $curT['TRS_TYPE'] != '2') {
            $curPt = null;
            if(isset($pttHistory[$psnlCd])) {
                foreach($pttHistory[$psnlCd] as $pt) {
                    if($pt['PTT_YEAR'] <= $yearStr) {
                        $curPt = $pt;
                    } else {
                        break;
                    }
                }
            }
            
            $ptHours = $curPt ? (float)$curPt['PTT_HOUR'] : null;
            $wkType = $curT['WORK_TYPE'];
            
            $pass = false;
            if($workTypeFilter == 'REG') {
                if(($wkType == '정규직' || $wkType == '기능직') && ($ptHours === null || $ptHours >= 40)) $pass = true;
            } else if($workTypeFilter == 'CONT') {
                if((strpos($wkType, '계약직') !== false || $wkType == '무기계약직') && ($ptHours === null || $ptHours >= 40)) $pass = true;
            } else if($workTypeFilter == 'SHORT') {
                if($ptHours !== null && $ptHours < 40) $pass = true;
            } else {
                $pass = true;
            }
            
            if($pass) {
                if($gender == 'M') $m++; else $f++;
            }
        }
    }
    
    $chartData['labels'][] = $date;
    $chartData['male'][] = $m;
    $chartData['female'][] = $f;
}
$t2 = microtime(true);
echo "Time taken: " . ($t2 - $t1) . "s\n";
print_r(count($chartData['labels']));
?>
