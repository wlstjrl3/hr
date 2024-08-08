<?php
    include "../dbconn.php";
    if($_REQUEST["control"]=="R"){
        $query = "
        SELECT * FROM BIBLE_PASS
            WHERE idx = ".$_REQUEST["idx"]."
        ";
        $result = mysqli_query($conn,$query); //로그 테이블 정보 끌어오기
        $row = mysqli_fetch_array($result);
    }else if($_REQUEST["control"]=="U"){
        if($_REQUEST["idx"]>0){ //idx가 0보다 큰경우 수정
            $query = "
            UPDATE
                BIBLE_PASS
            SET
                teamNm = '".$_REQUEST["teamNm"]."'
                ,leader = '".$_REQUEST["leader"]."'
                ,phoneNum = '".$_REQUEST["phoneNum"]."'
                ,userId = '".$_REQUEST["userId"]."'
                ,userPass = '".$_REQUEST["userPass"]."'
                ,etc = '".$_REQUEST["etc"]."'
                ,regDt = NOW()
            WHERE
                idx = ".$_REQUEST["idx"]."
            LIMIT 1
            ";
            mysqli_query($conn,$query);
        }else{ //idx가 0이거나 작은 경우 신규인서트
            $query = "
            INSERT
                BIBLE_PASS
            SET
                teamNm = '".$_REQUEST["teamNm"]."'
                ,leader = '".$_REQUEST["leader"]."'
                ,phoneNum = '".$_REQUEST["phoneNum"]."'
                ,userId = '".$_REQUEST["userId"]."'
                ,userPass = '".$_REQUEST["userPass"]."'
                ,etc = '".$_REQUEST["etc"]."'
                ,regDt = NOW()
            ";
            mysqli_query($conn,$query);
        }
    }else if($_REQUEST["control"]=="D"){
        $query = "
        DELETE FROM BIBLE_PASS
            WHERE idx = ".$_REQUEST["idx"]."
        ";
        mysqli_query($conn,$query);
    }else if($_REQUEST["control"]=="DALL"){
        $query = "
        DELETE FROM BIBLE_PASS
        ";
        mysqli_query($conn,$query);
    }
    mysqli_close($conn);
    echo json_encode($row, JSON_UNESCAPED_UNICODE);
?>