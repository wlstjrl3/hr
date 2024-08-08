const xhr = new XMLHttpRequest();
xhr.open("GET", "/user");
xhr.send();
xhr.onload = () => {
  if (xhr.status === 200) {
    let tableData = JSON.parse(xhr.response)['data'];
    console.log(tableData);

    let table = document.querySelector('#table-body')
    let str="";
    tableData.forEach((row,key)=>{
    str += `<tr>
        <td>`+row.USER_CD+`</td>
        <td>`+row.USER_ID+`</td>
        <td>`+row.USER_NM+`</td>
        <td>`+row.USER_PASS+`</td>
    </tr>`
    });
    table.innerHTML=str;

  } else {
    console.error(xhr.status, xhr.statusText);
  }
};