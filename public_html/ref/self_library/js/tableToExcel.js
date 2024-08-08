function tableToExcel(id, title) {
    //var data_type = 'data:application/vnd.ms-excel;charset=utf-8';
    var data_type = 'data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;charset=utf-8';
    var table_html = encodeURIComponent(document.getElementById(id).outerHTML);
 
    var a = document.createElement('a');
    a.href = data_type + ',%EF%BB%BF' + table_html;
    a.download = title+'.xlsx';
    a.click();
}