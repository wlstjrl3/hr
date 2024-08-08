<?php
    error_reporting( E_ALL );
    ini_set( "display_errors", 1 );

    include "./dbconn/dbconn.php";
    $sql = "SELECT * FROM BONDANG_HR.USER_TB WHERE 1=1 ";

    if(@$_REQUEST['user_id']){
        $sql=$sql." AND USER_ID LIKE '%".$_REQUEST['user_id']."%'";
    }

    $result = mysqli_query($conn,$sql);
    mysqli_close($conn);

    while($row = mysqli_fetch_array($result)){
        $data[] = $row;
    }
    $datas = array(
       "data" => @$data
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>