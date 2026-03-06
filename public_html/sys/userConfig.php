<?php
include "sql_safe_helper.php";
verifyApiKey($conn, @$_REQUEST['key']);

$authData = executeQuery($conn, "SELECT USER_AUTH FROM BONDANG_HR.USER_TB WHERE USER_PASS = ? LIMIT 1", "s", [@$_REQUEST['key']]);
$userAuth = $authData[0]['USER_AUTH'] ?? '';

if ($_REQUEST['CRUD'] == 'C') {
    if ($userAuth != 'auth' && $userAuth != 'admin') {
        echo '권한이 없는 사용자 입니다. : ' . $userAuth;
    }
    else {
        if ($_REQUEST['USER_CD'] == "") {
            $regDt = date("Y-m-d h:m:s");
            executeUpdate($conn,
                "INSERT INTO BONDANG_HR.USER_TB(USER_ID,USER_NM,USER_PASS,USER_AUTH,EMAIL,POSITION,ORG_NM,REG_DT,MEMO) VALUES (?,?,?,?,?,?,?,?,?)",
                "sssssssss",
            [$_REQUEST['USER_ID'], $_REQUEST['USER_NM'], MD5($_REQUEST['USER_PASS']), $_REQUEST['USER_AUTH'],
                $_REQUEST['EMAIL'], $_REQUEST['POSITION'], $_REQUEST['ORG_NM'], $regDt, $_REQUEST['MEMO']]
            );
        }
        else {
            $setSql = "USER_ID=?, USER_NM=?";
            $types = "ss";
            $params = [$_REQUEST['USER_ID'], $_REQUEST['USER_NM']];

            if (strlen(@$_REQUEST['USER_PASS']) > 1) {
                $setSql .= ", USER_PASS=?";
                $types .= "s";
                $params[] = MD5($_REQUEST['USER_PASS']);
            }

            $setSql .= ", USER_AUTH=?, EMAIL=?, POSITION=?, ORG_NM=?, MEMO=?";
            $types .= "sssss";
            $params[] = $_REQUEST['USER_AUTH'];
            $params[] = $_REQUEST['EMAIL'];
            $params[] = $_REQUEST['POSITION'];
            $params[] = $_REQUEST['ORG_NM'];
            $params[] = $_REQUEST['MEMO'];

            $types .= "s";
            $params[] = $_REQUEST['USER_CD'];

            executeUpdate($conn, "UPDATE BONDANG_HR.USER_TB SET $setSql WHERE USER_CD = ?", $types, $params);
        }
    }
    mysqli_close($conn);
}
else if ($_REQUEST['CRUD'] == 'R') {
    $params = [];
    $types = "";
    $whereSql = " WHERE 1=1 ";
    if (@$_REQUEST['USER_CD']) {
        $whereSql .= " AND USER_CD = ?";
        $params[] = $_REQUEST['USER_CD'];
        $types .= "s";
    }
    $data = executeQuery($conn, "SELECT * FROM BONDANG_HR.USER_TB" . $whereSql . " LIMIT 1", $types, $params);
    jsonResponse($conn, ["data" => $data ?: null, "date" => "2021-99-99"]);
}
else if ($_REQUEST['CRUD'] == 'D') {
    if ($userAuth != 'auth') {
        echo '권한이 없는 사용자 입니다.';
    }
    executeUpdate($conn, "DELETE FROM BONDANG_HR.USER_TB WHERE USER_CD = ?", "s", [$_REQUEST['USER_CD']]);
    mysqli_close($conn);
}
else {
    echo 'userConfig 잘못된 접근방식입니다.';
}

?>