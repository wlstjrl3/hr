<script src="./js/dist/xlsx.js"></script>
<script>
    window.onload = function() {};

    function readExcel() {
        // [input 태그에 파일이 선텍 된 경우 로직 수행]
        let input = event.target;
        let reader = new FileReader();

        reader.onload = function () {

            let data = reader.result;
            let workBook = XLSX.read(data, { type: 'binary' });

            workBook.SheetNames.forEach(function (sheetName) {

                let rows = XLSX.utils.sheet_to_json(workBook.Sheets[sheetName]);
                //console.log(rows);
                var xhr = new XMLHttpRequest();//XMLHttpRequest 객체 생성
                xhr.open('POST', '/parking/dbconn/reserv/batchInsert.php', true);//요청을 보낼 방식, 주소, 비동기여부 설정
                xhr.setRequestHeader('Content-type', 'application/json;charset=UTF-8');//HTTP 요청 헤더 설정
                xhr.send(JSON.stringify(rows));  //JSON,stringify를 이용하여 json으로 변환해야만 php상에서 엑셀 텍스트가 json으로 인식됨
                xhr.onload = () => {//통신후 작업
                    if (xhr.status == 200) {//통신 성공
                        console.log(xhr.response); 
                    }else{
                        console.log("통신 실패 type1");//통신 실패
                    }
                }
                xhr.onloadend = () => {}


            })
        };

        // [input 태그 파일 읽음]
        reader.readAsBinaryString(input.files[0]);
    };
</script>

<body>
    <!-- [input file 생성] -->
    <input type="file" onchange="readExcel()">
</body>