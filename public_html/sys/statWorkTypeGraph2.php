<?php
include_once "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$baseDate = @$_REQUEST['BASE_DATE'] ?: date('Y-m-d');
$graphType = @$_REQUEST['GRAPH_TYPE'] ?: 'age'; 
$incDomestic = false; // Always exclude domestic workers
$mode = @$_REQUEST['MODE'] ?: 'chart'; // 'chart' or 'detail'

// Details parameters
$targetKey = @$_REQUEST['TARGET_KEY']; 
$isManagementTarget = @$_REQUEST['TARGET_GROUP'] == 'management'; // office or management

if ($mode == 'detail' && $baseDate && $targetKey) {
    fetchDetailBreakdown2($conn, $baseDate, $targetKey, $graphType, $incDomestic, $isManagementTarget);
    exit;
}

$safeDateStr = str_replace('-', '', $baseDate);
$currentYear = (int)substr($safeDateStr, 0, 4);

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
        'birthYear' => (int)($yearPrefix . $y2)
    ];
}

// 2. Load transfer history
$sqlTrs = "SELECT PSNL_CD, REPLACE(TRS_DT, '-', '') AS TRS_DT, TRS_CD, TRS_TYPE, WORK_TYPE, POSITION 
           FROM PSNL_TRANSFER 
           WHERE REPLACE(TRS_DT, '-', '') <= '{$safeDateStr}'
           ORDER BY TRS_DT ASC, TRS_CD ASC";
$resTrs = mysqli_query($conn, $sqlTrs);
$trsHistory = [];
$minTrsDt = [];
while($row = mysqli_fetch_assoc($resTrs)) {
    $psnlCd = $row['PSNL_CD'];
    $trsHistory[$psnlCd][] = $row;
    if (!isset($minTrsDt[$psnlCd]) || $row['TRS_DT'] < $minTrsDt[$psnlCd]) {
        $minTrsDt[$psnlCd] = $row['TRS_DT'];
    }
}

// 3. Load grade history
$gradeHistory = [];
if ($graphType == 'reg_grade_ratio') {
    $sqlGrd = "SELECT PSNL_CD, GRD_GRADE, GRD_PAY, REPLACE(ADVANCE_DT, '-', '') AS ADVANCE_DT 
               FROM GRADE_HISTORY 
               WHERE REPLACE(ADVANCE_DT, '-', '') <= '{$safeDateStr}'
               ORDER BY ADVANCE_DT ASC, GRD_CD ASC";
    $resGrd = mysqli_query($conn, $sqlGrd);
    if ($resGrd) {
        while($row = mysqli_fetch_assoc($resGrd)) {
            $gradeHistory[$row['PSNL_CD']][] = $row;
        }
    }
}

$datasetKeys = [];
$officeLabels = null;
$managementLabels = null;

if ($graphType == 'age') {
    $datasetKeys = [
        'age_20_24' => '20-24세', 'age_25_29' => '25-29세', 
        'age_30_34' => '30-34세', 'age_35_39' => '35-39세', 
        'age_40_44' => '40-44세', 'age_45_49' => '45-49세', 
        'age_50_54' => '50-54세', 'age_55_59' => '55-59세', 
        'age_60_64' => '60-64세', 'age_65_69' => '65-69세', 
        'age_70' => '70세 이상'
    ];
} else if ($graphType == 'service_years') {
    $datasetKeys = [
        'sy_1' => '1년 미만', 'sy_3' => '1~3년', 'sy_6' => '3~6년', 'sy_10' => '6~10년', 
        'sy_15' => '10~15년', 'sy_20' => '15~20년', 'sy_25' => '20~25년', 'sy_over' => '25년 이상'
    ];
} else if ($graphType == 'reg_cont_ratio') {
    $datasetKeys = [
        'reg' => '정규직', 'cont' => '계약직'
    ];
    $officeLabels = ['정규직', '계약직'];
    $managementLabels = ['기능직', '계약직'];
} else if ($graphType == 'reg_grade_ratio') {
    $officeLabels = ['4급', '5급', '6급', '7급', '8급', '9급', '미분류'];
    $managementLabels = ['Lv 101-120', 'Lv 81-100', 'Lv 61-80', 'Lv 41-60', 'Lv 21-40', 'Lv 1-20'];
    $gradeKeys = ['grade_4', 'grade_5', 'grade_6', 'grade_7', 'grade_8', 'grade_9', 'grade_unknown'];
    $lvKeys = ['lv_101_120', 'lv_81_100', 'lv_61_80', 'lv_41_60', 'lv_21_40', 'lv_1_20'];
}

$countsOffice = [];
$countsManagementRegular = [];
$countsManagementContract = [];

if ($graphType == 'reg_grade_ratio') {
    $countsOffice = array_fill(0, 7, 0); // Left: Regular (Grades)
    $countsManagementContract = array_fill(0, 6, 0); // Right: Contract (Levels)
} else {
    $countsOffice = array_fill_keys(array_keys($datasetKeys), 0);
    $countsManagementRegular = array_fill_keys(array_keys($datasetKeys), 0);
}

foreach ($psnlInfo as $psnlCd => $info) {
    $curT = null;
    if(isset($trsHistory[$psnlCd])) {
        foreach($trsHistory[$psnlCd] as $t) {
            if($t['TRS_DT'] <= $safeDateStr) $curT = $t;
            else break;
        }
    }
    if($curT && $curT['TRS_TYPE'] != '2') {
        $pos = $curT['POSITION'];
        $wkType = $curT['WORK_TYPE'];
        if ($pos == '가사사용인') continue;
        
        $isManagement = (strpos($pos, '관리') !== false);
        
        if ($graphType == 'age' || $graphType == 'service_years' || $graphType == 'reg_cont_ratio') {
            $mKey = '';
            if ($graphType == 'age') {
                $age = $currentYear - $info['birthYear'] + 1;
                if ($age >= 20 && $age < 25) $mKey = 'age_20_24';
                else if ($age >= 25 && $age < 30) $mKey = 'age_25_29';
                else if ($age >= 30 && $age < 35) $mKey = 'age_30_34';
                else if ($age >= 35 && $age < 40) $mKey = 'age_35_39';
                else if ($age >= 40 && $age < 45) $mKey = 'age_40_44';
                else if ($age >= 45 && $age < 50) $mKey = 'age_45_49';
                else if ($age >= 50 && $age < 55) $mKey = 'age_50_54';
                else if ($age >= 55 && $age < 60) $mKey = 'age_55_59';
                else if ($age >= 60 && $age < 65) $mKey = 'age_60_64';
                else if ($age >= 65 && $age < 70) $mKey = 'age_65_69';
                else if ($age >= 70) $mKey = 'age_70';
            } else if ($graphType == 'service_years') {
                $eDtStr = isset($minTrsDt[$psnlCd]) ? $minTrsDt[$psnlCd] : '19000101';
                $enterDt = new DateTime(substr($eDtStr, 0, 4) . '-' . substr($eDtStr, 4, 2) . '-' . substr($eDtStr, 6, 2));
                $targetDt = new DateTime($baseDate);
                $diff = $enterDt->diff($targetDt);
                $years = $diff->y + ($diff->m / 12);
                if ($years < 1) $mKey = 'sy_1';
                else if ($years < 3) $mKey = 'sy_3';
                else if ($years < 6) $mKey = 'sy_6';
                else if ($years < 10) $mKey = 'sy_10';
                else if ($years < 15) $mKey = 'sy_15';
                else if ($years < 20) $mKey = 'sy_20';
                else if ($years < 25) $mKey = 'sy_25';
                else $mKey = 'sy_over';
            } else if ($graphType == 'reg_cont_ratio') {
                if (strpos($wkType, '정규직') !== false || strpos($wkType, '기능직') !== false) $mKey = 'reg';
                else if (strpos($wkType, '계약직') !== false || strpos($wkType, '무기계약직') !== false) $mKey = 'cont';
                else $mKey = 'other';
            }

            if ($mKey) {
                if ($isManagement) {
                    if (isset($countsManagementRegular[$mKey])) $countsManagementRegular[$mKey]++;
                } else {
                    if (isset($countsOffice[$mKey])) $countsOffice[$mKey]++;
                }
            }
        } else if ($graphType == 'reg_grade_ratio') {
            $curG = null;
            if (isset($gradeHistory[$psnlCd])) {
                foreach($gradeHistory[$psnlCd] as $g) {
                    if ($g['ADVANCE_DT'] <= $safeDateStr) $curG = $g;
                    else break;
                }
            }
            $gradeVal = $curG ? (int)$curG['GRD_GRADE'] : 0;
            
            $isFuncMng = (strpos($wkType, '기능직') !== false && (strpos($pos, '관리장') !== false || strpos($pos, '관리원') !== false));

            if ((strpos($wkType, '정규직') !== false) || (strpos($wkType, '기능직') !== false && !$isFuncMng)) {
                $idx = 6;
                if ($gradeVal >= 4 && $gradeVal <= 9) $idx = $gradeVal - 4;
                else if ($gradeVal > 0) $idx = 6;
                $countsOffice[$idx]++; // Use Left Chart for Regular
            } else if (strpos($wkType, '계약직') !== false || strpos($wkType, '무기계약직') !== false || $isFuncMng) {
                $idx = -1;
                if ($gradeVal >= 101 && $gradeVal <= 120) $idx = 0;
                else if ($gradeVal >= 81 && $gradeVal <= 100) $idx = 1;
                else if ($gradeVal >= 61 && $gradeVal <= 80) $idx = 2;
                else if ($gradeVal >= 41 && $gradeVal <= 60) $idx = 3;
                else if ($gradeVal >= 21 && $gradeVal <= 40) $idx = 4;
                else if ($gradeVal >= 1 && $gradeVal <= 20) $idx = 5;
                if ($idx >= 0) $countsManagementContract[$idx]++; // Use Right Chart for Contract
            }
        }
    }
}

$chartData = [
    'labels' => !empty($datasetKeys) ? array_values($datasetKeys) : null,
    'officeLabels' => $officeLabels,
    'managementLabels' => $managementLabels,
    'officeTitle' => ($graphType == 'reg_grade_ratio') ? '정규직' : '사무직',
    'managementTitle' => ($graphType == 'reg_grade_ratio') ? '계약직' : '관리직',
    'office' => [
        'datasets' => [[
            'label' => '인원',
            'data' => array_values($countsOffice),
            'backgroundColor' => ($graphType == 'reg_grade_ratio') ? 'rgba(255, 99, 132, 0.7)' : 'rgba(54, 162, 235, 0.7)',
            'keys' => ($graphType == 'reg_grade_ratio') ? $gradeKeys : array_keys($datasetKeys)
        ]]
    ],
    'management' => [
        'datasets' => []
    ]
];

if ($graphType == 'reg_grade_ratio') {
    $chartData['management']['datasets'][] = [
        'label' => '인원',
        'data' => array_values($countsManagementContract),
        'backgroundColor' => 'rgba(255, 205, 86, 0.7)', // Yellow
        'keys' => $lvKeys
    ];
} else {
    $chartData['management']['datasets'][] = [
        'label' => '인원',
        'data' => array_values($countsManagementRegular),
        'backgroundColor' => 'rgba(255, 99, 132, 0.7)',
        'keys' => array_keys($datasetKeys)
    ];
}

jsonResponse($conn, $chartData);


function fetchDetailBreakdown2($conn, $date, $targetKey, $graphType, $incDomestic, $isManagementTarget) {
    $safeDateStr = str_replace('-', '', $date);
    $currentYear = (int)substr($safeDateStr, 0, 4);
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
            'birthYear' => (int)($yearPrefix . $y2)
        ];
    }
    $sqlTrs = "SELECT PSNL_CD, REPLACE(TRS_DT, '-', '') AS TRS_DT, TRS_CD, TRS_TYPE, WORK_TYPE, POSITION 
               FROM PSNL_TRANSFER 
               WHERE REPLACE(TRS_DT, '-', '') <= '{$safeDateStr}'
               ORDER BY TRS_DT ASC, TRS_CD ASC";
    $resTrs = mysqli_query($conn, $sqlTrs);
    $trsHistory = [];
    $minTrsDt = [];
    while($row = mysqli_fetch_assoc($resTrs)) {
        $psnlCd = $row['PSNL_CD'];
        $trsHistory[$psnlCd][] = $row;
        if (!isset($minTrsDt[$psnlCd]) || $row['TRS_DT'] < $minTrsDt[$psnlCd]) {
            $minTrsDt[$psnlCd] = $row['TRS_DT'];
        }
    }
    $gradeHistory = [];
    if ($graphType == 'reg_grade_ratio') {
        $sqlGrd = "SELECT PSNL_CD, GRD_GRADE, REPLACE(ADVANCE_DT, '-', '') AS ADVANCE_DT 
                   FROM GRADE_HISTORY 
                   WHERE REPLACE(ADVANCE_DT, '-', '') <= '{$safeDateStr}'
                   ORDER BY ADVANCE_DT ASC, GRD_CD ASC";
        $resGrd = mysqli_query($conn, $sqlGrd);
        if ($resGrd) {
            while($row = mysqli_fetch_assoc($resGrd)) {
                $gradeHistory[$row['PSNL_CD']][] = $row;
            }
        }
    }
    $positions = [];
    foreach ($psnlInfo as $psnlCd => $info) {
        $curT = null;
        if(isset($trsHistory[$psnlCd])) {
            foreach($trsHistory[$psnlCd] as $t) {
                if($t['TRS_DT'] <= $safeDateStr) $curT = $t;
                else break;
            }
        }
        if($curT && $curT['TRS_TYPE'] != '2') {
            $pos = $curT['POSITION'];
            $wkType = $curT['WORK_TYPE'];
            if ($pos == '가사사용인') continue;

            if ($graphType == 'reg_grade_ratio') {
                $isFuncMng = (strpos($wkType, '기능직') !== false && (strpos($pos, '관리장') !== false || strpos($pos, '관리원') !== false));
                $isRegular = (strpos($wkType, '정규직') !== false || (strpos($wkType, '기능직') !== false && !$isFuncMng));
                $isContract = (strpos($wkType, '계약직') !== false || strpos($wkType, '무기계약직') !== false || $isFuncMng);
                if ($isManagementTarget) { // Right Chart = Contract
                    if (!$isContract) continue;
                } else { // Left Chart = Regular
                    if (!$isRegular) continue;
                }
            } else {
                $isMng = (strpos($pos, '관리') !== false);
                if ($isManagementTarget && !$isMng) continue;
                if (!$isManagementTarget && $isMng) continue;
            }
            
            $matchKey = '';
            if ($graphType == 'age') {
                $age = $currentYear - $info['birthYear'] + 1;
                     if ($age >= 20 && $age < 25) $matchKey = 'age_20_24';
                else if ($age >= 25 && $age < 30) $matchKey = 'age_25_29';
                else if ($age >= 30 && $age < 35) $matchKey = 'age_30_34';
                else if ($age >= 35 && $age < 40) $matchKey = 'age_35_39';
                else if ($age >= 40 && $age < 45) $matchKey = 'age_40_44';
                else if ($age >= 45 && $age < 50) $matchKey = 'age_45_49';
                else if ($age >= 50 && $age < 55) $matchKey = 'age_50_54';
                else if ($age >= 55 && $age < 60) $matchKey = 'age_55_59';
                else if ($age >= 60 && $age < 65) $matchKey = 'age_60_64';
                else if ($age >= 65 && $age < 70) $matchKey = 'age_65_69';
                else if ($age >= 70) $matchKey = 'age_70';
            } else if ($graphType == 'service_years') {
                $eDtStr = isset($minTrsDt[$psnlCd]) ? $minTrsDt[$psnlCd] : '19000101';
                $enterDt = new DateTime(substr($eDtStr, 0, 4) . '-' . substr($eDtStr, 4, 2) . '-' . substr($eDtStr, 6, 2));
                $targetDt = new DateTime($date);
                $diff = $enterDt->diff($targetDt);
                $years = $diff->y + ($diff->m / 12);
                if ($years < 1) $matchKey = 'sy_1';
                else if ($years < 3) $matchKey = 'sy_3';
                else if ($years < 6) $matchKey = 'sy_6';
                else if ($years < 10) $matchKey = 'sy_10';
                else if ($years < 15) $matchKey = 'sy_15';
                else if ($years < 20) $matchKey = 'sy_20';
                else if ($years < 25) $matchKey = 'sy_25';
                else $matchKey = 'sy_over';
            } else if ($graphType == 'reg_cont_ratio') {
                if (strpos($wkType, '정규직') !== false || strpos($wkType, '기능직') !== false) $matchKey = 'reg';
                else if (strpos($wkType, '계약직') !== false) $matchKey = 'cont';
                else $matchKey = 'other';
            } else if ($graphType == 'reg_grade_ratio') {
                $curG = null;
                if (isset($gradeHistory[$psnlCd])) {
                    foreach($gradeHistory[$psnlCd] as $g) {
                        if ($g['ADVANCE_DT'] <= $safeDateStr) $curG = $g;
                        else break;
                    }
                }
                $gradeVal = $curG ? (int)$curG['GRD_GRADE'] : 0;
                $isFuncMng = (strpos($wkType, '기능직') !== false && (strpos($pos, '관리장') !== false || strpos($pos, '관리원') !== false));
                if ((strpos($wkType, '정규직') !== false) || (strpos($wkType, '기능직') !== false && !$isFuncMng)) {
                    if ($gradeVal >= 4 && $gradeVal <= 9) $matchKey = 'grade_' . $gradeVal;
                    else if ($gradeVal > 0) $matchKey = 'grade_unknown';
                } else if (strpos($wkType, '계약직') !== false || strpos($wkType, '무기계약직') !== false || $isFuncMng) {
                    $lv = $gradeVal;
                         if ($lv >= 101 && $lv <= 120) $matchKey = 'lv_101_120';
                    else if ($lv >= 81 && $lv <= 100) $matchKey = 'lv_81_100';
                    else if ($lv >= 61 && $lv <= 80) $matchKey = 'lv_61_80';
                    else if ($lv >= 41 && $lv <= 60) $matchKey = 'lv_41_60';
                    else if ($lv >= 21 && $lv <= 40) $matchKey = 'lv_21_40';
                    else if ($lv >= 1 && $lv <= 20) $matchKey = 'lv_1_20';
                }
            }
            if ($matchKey == $targetKey) {
                $posName = $pos ?: '미지정';
                $positions[$posName] = ($positions[$posName] ?? 0) + 1;
            }
        }
    }
    arsort($positions);
    echo json_encode(['data' => $positions]);
}
?>
