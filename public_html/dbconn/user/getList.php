<?php
    include "../dbconn.php";
    $query = "SELECT COUNT(*) FROM BIBLE_PASS";
    $records_total = mysqli_query($conn,$query);
    $record_total = mysqli_fetch_array($records_total);
    $totalRowCnt = $record_total[0];

    // Search
    $search_query = "";
    if(strlen($_REQUEST["teamNm"])>0){
        $search_query = $search_query." AND teamNm LIKE '%".$_REQUEST["teamNm"]."%'";
    }
    if(strlen($_REQUEST["leader"])>0){
        $search_query = $search_query." AND leader LIKE '%".$_REQUEST["leader"]."%'";
    }
    if(strlen($_REQUEST["phoneNum"])>0){
        $search_query = $search_query." AND phoneNum LIKE '%".$_REQUEST["phoneNum"]."%'";
    }
    if(strlen($_REQUEST["userId"])>0){
        $search_query = $search_query." AND userId LIKE '%".$_REQUEST["userId"]."%'";
    }
    if($_REQUEST["regDtFrom"]>'1900-01-01' && $_REQUEST["regDtTo"]>'1900-01-01'){
        $search_query =  $search_query." AND (regDtregDt >= '".$_REQUEST["regDtFrom"]."' AND regDtregDt <= '".$_REQUEST["regDtTo"]." 23:59:59')";
    }else if($_REQUEST["regDtFrom"]>'1900-01-01'){
        $search_query =  $search_query." AND regDtregDt >= '".$_REQUEST["regDtFrom"]."'";
    }else if($_REQUEST["regDtTo"]>'1900-01-01'){
        $search_query =  $search_query." AND regDtregDt <= '".$_REQUEST["regDtTo"]." 23:59:59'";
    }
    // Ordering
    $order_field_idx = $_REQUEST["order"][0]["column"];
    $order_field = $_REQUEST["columns"][$order_field_idx]["data"];
    $order_direction = $_REQUEST["order"][0]["dir"];
    $order_query = $order_field." ".$order_direction;

    $query = "SELECT COUNT(*) FROM BIBLE_PASS WHERE 1 ".$search_query;
    $filtered_total = mysqli_query($conn,$query);
    $filter_total = mysqli_fetch_array($filtered_total);
    $filterRowCnt = $filter_total[0];
    $num = $filterRowCnt - $_REQUEST["start"];

    $sql = "SELECT * FROM BIBLE_PASS
        WHERE 1 ".$search_query."
        ";
        if(strlen($order_query)>1){
            $sql = $sql."
                ORDER BY
                ".$order_query."
            ";
        }        
        if(isset($_REQUEST["start"])){
            $sql = $sql."
                LIMIT
                ".$_REQUEST["start"].", ".$_REQUEST["length"]."
            ";
        }

    //echo $sql;
    //die;

    $result = mysqli_query($conn,$sql);
    while($row = mysqli_fetch_array($result)){
        $row["num"] = $num--;
        $data[] = $row;
    }

    mysqli_close($conn);

    $datas = array(
       "recordsTotal" => $totalRowCnt
       ,"recordsFiltered" => $filterRowCnt
       ,"data" => $data
    ); 

    echo json_encode($datas, JSON_UNESCAPED_UNICODE);

?>