<?php
include_once "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$sttDate = @$_REQUEST['STT_DATE'] ?: date('Y-01-01');
$endDate = @$_REQUEST['END_DATE'] ?: date('Y-m-d');
$workTypeFilter = @$_REQUEST['WORK_TYPE'] ?: 'ALL';
$interval = @$_REQUEST['INTERVAL'] ?: 'month';
$groupBy = @$_REQUEST['GROUP_BY'] ?: 'gender';
$mode = @$_REQUEST['MODE'] ?: 'chart'; // 'chart' or 'detail'

// Details parameters
$targetDate = @$_REQUEST['TARGET_DATE'];
$targetKey = @$_REQUEST['TARGET_KEY']; // e.g., 'male', 'age_30'

if ($mode == 'detail' && $targetDate && $targetKey) {
    // Detail logic helper
    fetchDetailBreakdown($conn, $targetDate, $targetKey, $groupBy, $workTypeFilter);
    exit;
}

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
        $dates[] = $current->format('Y-m-t');
        $current->modify('first day of next month');
    }
}

foreach ($dates as $idx => $d) {
    if ($d > $endDate) $dates[$idx] = $endDate;
}
$dates = array_unique($dates);
sort($dates);

// 1. Load basic personal info
$sqlPsnl = "SELECT PSNL_CD, PSNL_NUM FROM PSNL_INFO";
$resPsnl = mysqli_query($conn, $sqlPsnl);
$psnlInfo = [];
while($row = mysqli_fetch_assoc($resPsnl)) {
    $psnlNum = str_replace('-', '', $row['PSNL_NUM']);
    if (strlen($psnlNum) < 7) continue;
    $g = substr($psnlNum, 6, 1);
    $y2 = substr($psnlNum, 0, 2);
    $yearPrefix = in_array($g, ['1', '2', '5', '6', '9', '0']) ? '19' : '20';
    $psnlInfo[$row['PSNL_CD']] = [
        'gender' => in_array($g, ['1', '3', '5', '7', '9']) ? 'M' : 'F',
        'birthYear' => (int)($yearPrefix . $y2)
    ];
}

$endDateStr = str_replace('-', '', $endDate);
$sqlTrs = "SELECT PSNL_CD, REPLACE(TRS_DT, '-', '') AS TRS_DT, TRS_CD, TRS_TYPE, WORK_TYPE, POSITION 
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

$chartData = ['labels' => [], 'datasets' => []];
$datasetKeys = [];
if ($groupBy == 'gender') {
    $datasetKeys = ['male' => '남성', 'female' => '여성'];
} else {
    $datasetKeys = [
        'age_20' => '20대', 'age_30' => '30대', 'age_40' => '40대', 'age_50' => '50대', 'age_60' => '60대 이상'
    ];
}

foreach ($datasetKeys as $key => $label) {
    $chartData['datasets'][$key] = ['label' => $label, 'data' => []];
}

foreach ($dates as $date) {
    $safeDateStr = str_replace('-', '', $date);
    $yearStr = substr($safeDateStr, 0, 4);
    $currentYear = (int)$yearStr;
    $counts = array_fill_keys(array_keys($datasetKeys), 0);
    
    foreach ($psnlInfo as $psnlCd => $info) {
        $curT = null;
        if(isset($trsHistory[$psnlCd])) {
            foreach($trsHistory[$psnlCd] as $t) {
                if($t['TRS_DT'] <= $safeDateStr) $curT = $t;
                else break;
            }
        }
        if($curT && $curT['TRS_TYPE'] != '2') {
            $curPt = null;
            if(isset($pttHistory[$psnlCd])) {
                foreach($pttHistory[$psnlCd] as $pt) {
                    if($pt['PTT_YEAR'] <= $yearStr) $curPt = $pt;
                    else break;
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
            } else $pass = true;
            
            if($pass) {
                if ($groupBy == 'gender') {
                    $counts[($info['gender'] == 'M' ? 'male' : 'female')]++;
                } else {
                    $age = $currentYear - $info['birthYear'] + 1;
                    if ($age >= 20 && $age < 30) $counts['age_20']++;
                    else if ($age >= 30 && $age < 40) $counts['age_30']++;
                    else if ($age >= 40 && $age < 50) $counts['age_40']++;
                    else if ($age >= 50 && $age < 60) $counts['age_50']++;
                    else if ($age >= 60) $counts['age_60']++;
                }
            }
        }
    }
    $chartData['labels'][] = $date;
    foreach ($datasetKeys as $key => $label) $chartData['datasets'][$key]['data'][] = $counts[$key];
}

jsonResponse($conn, $chartData);

function fetchDetailBreakdown($conn, $date, $key, $groupBy, $workTypeFilter) {
    // Re-calculate counts for specific date but grouped by TRS_PSIT_NM
    $safeDateStr = str_replace('-', '', $date);
    $yearStr = substr($safeDateStr, 0, 4);
    $currentYear = (int)$yearStr;

    // We still need the full logic to find who's active on that date
    // (Self-contained for simplicity or we could refactor. Since it's detail, it's fast enough for one date)
    
    $sqlPsnl = "SELECT PSNL_CD, PSNL_NUM FROM PSNL_INFO";
    $resPsnl = mysqli_query($conn, $sqlPsnl);
    $psnlInfo = [];
    while($row = mysqli_fetch_assoc($resPsnl)) {
        $psnlNum = str_replace('-', '', $row['PSNL_NUM']);
        if (strlen($psnlNum) < 7) continue;
        $g = substr($psnlNum, 6, 1);
        $y2 = substr($psnlNum, 0, 2);
        $yearPrefix = in_array($g, ['1', '2', '5', '6', '9', '0']) ? '19' : '20';
        $psnlInfo[$row['PSNL_CD']] = [
            'gender' => in_array($g, ['1', '3', '5', '7', '9']) ? 'M' : 'F',
            'birthYear' => (int)($yearPrefix . $y2)
        ];
    }

    $sqlTrs = "SELECT PSNL_CD, REPLACE(TRS_DT, '-', '') AS TRS_DT, TRS_CD, TRS_TYPE, WORK_TYPE, POSITION 
               FROM PSNL_TRANSFER 
               WHERE REPLACE(TRS_DT, '-', '') <= '{$safeDateStr}'
               ORDER BY TRS_DT ASC, TRS_CD ASC";
    $resTrs = mysqli_query($conn, $sqlTrs);
    $trsHistory = [];
    while($row = mysqli_fetch_assoc($resTrs)) $trsHistory[$row['PSNL_CD']][] = $row;

    $sqlPtt = "SELECT PSNL_CD, PTT_YEAR, PTT_CD, PTT_HOUR 
               FROM PSNL_PARTTIME 
               WHERE PTT_YEAR <= LEFT('{$safeDateStr}', 4)
               ORDER BY PTT_YEAR ASC, PTT_CD ASC";
    $resPtt = mysqli_query($conn, $sqlPtt);
    $pttHistory = [];
    while($row = mysqli_fetch_assoc($resPtt)) $pttHistory[$row['PSNL_CD']][] = $row;

    $positions = [];
    foreach ($psnlInfo as $psnlCd => $info) {
        // Filter by Gender/Age Key
        if ($groupBy == 'gender') {
            $currentGender = ($info['gender'] == 'M' ? 'male' : 'female');
            if ($currentGender != $key) continue;
        } else {
            $age = $currentYear - $info['birthYear'] + 1;
            $currentAgeKey = '';
            if ($age >= 20 && $age < 30) $currentAgeKey = 'age_20';
            else if ($age >= 30 && $age < 40) $currentAgeKey = 'age_30';
            else if ($age >= 40 && $age < 50) $currentAgeKey = 'age_40';
            else if ($age >= 50 && $age < 60) $currentAgeKey = 'age_50';
            else if ($age >= 60) $currentAgeKey = 'age_60';
            if ($currentAgeKey != $key) continue;
        }

        $curT = null;
        if(isset($trsHistory[$psnlCd])) {
            foreach($trsHistory[$psnlCd] as $t) {
                if($t['TRS_DT'] <= $safeDateStr) $curT = $t;
                else break;
            }
        }
        if($curT && $curT['TRS_TYPE'] != '2') {
            $curPt = null;
            if(isset($pttHistory[$psnlCd])) {
                foreach($pttHistory[$psnlCd] as $pt) {
                    if($pt['PTT_YEAR'] <= $yearStr) $curPt = $pt;
                    else break;
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
            } else $pass = true;

            if($pass) {
                $pos = $curT['POSITION'] ?: '미지정';
                $positions[$pos] = ($positions[$pos] ?? 0) + 1;
            }
        }
    }
    arsort($positions);
    echo json_encode(['data' => $positions]);
}
?>
