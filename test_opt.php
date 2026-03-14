<?php
include 'public_html/sys/sql_safe_helper.php';

$date = '2024-03-31';
$safeDateStr = str_replace('-', '', $date);
$workTypeFilter = 'ALL';

// DB approach
$sql = "SELECT 
            SUM(CASE WHEN SUBSTR(REPLACE(P.PSNL_NUM, '-', ''), 7, 1) IN ('1', '3', '5', '7', '9') THEN 1 ELSE 0 END) AS MALE,
            SUM(CASE WHEN SUBSTR(REPLACE(P.PSNL_NUM, '-', ''), 7, 1) IN ('2', '4', '6', '8', '0') THEN 1 ELSE 0 END) AS FEMALE
        FROM PSNL_INFO P
        JOIN PSNL_TRANSFER T ON T.TRS_CD = (
            SELECT TRS_CD FROM PSNL_TRANSFER T2
            WHERE T2.PSNL_CD = P.PSNL_CD AND REPLACE(T2.TRS_DT, '-', '') <= '{$safeDateStr}'
            ORDER BY REPLACE(T2.TRS_DT, '-', '') DESC, T2.TRS_CD DESC LIMIT 1
        )
        LEFT OUTER JOIN (
            SELECT PSNL_CD, SUBSTRING_INDEX(GROUP_CONCAT(PTT_CD ORDER BY PTT_YEAR DESC, PTT_CD DESC), ',', 1) AS MAX_PTT_CD
            FROM PSNL_PARTTIME
            WHERE PTT_YEAR <= LEFT('{$safeDateStr}', 4)
            GROUP BY PSNL_CD
        ) PTT_SUB ON PTT_SUB.PSNL_CD = P.PSNL_CD
        LEFT OUTER JOIN PSNL_PARTTIME PT ON PT.PTT_CD = PTT_SUB.MAX_PTT_CD
        WHERE T.TRS_TYPE != '2'";
        
$t1 = microtime(true);
$res = mysqli_query($conn, $sql);
$rowDB = mysqli_fetch_assoc($res);
$t2 = microtime(true);
echo "DB Approach: M=" . $rowDB['MALE'] . " F=" . $rowDB['FEMALE'] . " Time: " . ($t2-$t1) . "s\n";

// Memory approach
$t1 = microtime(true);
$sqlPsnl = "SELECT PSNL_CD, SUBSTR(REPLACE(PSNL_NUM, '-', ''), 7, 1) AS G FROM PSNL_INFO";
$resPsnl = mysqli_query($conn, $sqlPsnl);
$psnlInfo = [];
while($row = mysqli_fetch_assoc($resPsnl)) {
    $psnlInfo[$row['PSNL_CD']] = ['male' => in_array($row['G'], ['1','3','5','7','9']) ? 1 : 0];
}

$sqlTrs = "SELECT PSNL_CD, REPLACE(TRS_DT, '-', '') AS TRS_DT, TRS_CD, TRS_TYPE, WORK_TYPE 
           FROM PSNL_TRANSFER 
           WHERE REPLACE(TRS_DT, '-', '') <= '{$safeDateStr}'
           ORDER BY TRS_DT DESC, TRS_CD DESC";
$resTrs = mysqli_query($conn, $sqlTrs);
$trsData = [];
while($row = mysqli_fetch_assoc($resTrs)) {
    if(!isset($trsData[$row['PSNL_CD']])) {
        $trsData[$row['PSNL_CD']] = $row; // Just keep the first one since it's ordered DESC
    }
}

$sqlPtt = "SELECT PSNL_CD, PTT_YEAR, PTT_CD, PTT_HOUR 
           FROM PSNL_PARTTIME 
           WHERE PTT_YEAR <= LEFT('{$safeDateStr}', 4)
           ORDER BY PTT_YEAR DESC, PTT_CD DESC";
$resPtt = mysqli_query($conn, $sqlPtt);
$pttData = [];
while($row = mysqli_fetch_assoc($resPtt)) {
    if(!isset($pttData[$row['PSNL_CD']])) {
        $pttData[$row['PSNL_CD']] = $row;
    }
}

$m = 0; $f = 0;
foreach($psnlInfo as $psnlCd => $p) {
    if(isset($trsData[$psnlCd])) {
        $t = $trsData[$psnlCd];
        if($t['TRS_TYPE'] != '2') {
            $pt = $pttData[$psnlCd] ?? null;
            $ptHours = $pt ? $pt['PTT_HOUR'] : null;
            
            // Apply workType filter check (not used in this test but logic is here)
            // if ALL, pass.
            if($p['male']) $m++; else $f++;
        }
    }
}
$t2 = microtime(true);
echo "Memory Approach: M=" . $m . " F=" . $f . " Time: " . ($t2-$t1) . "s\n";

