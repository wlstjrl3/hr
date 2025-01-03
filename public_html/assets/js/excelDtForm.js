function excelDateToJSDate(excelDate) {
    // 엑셀 날짜 기준(1900년 1월 1일)
    const excelBaseDate = new Date(1900, 0, 1);
    // 엑셀의 날짜는 1부터 시작하고, 1900년 2월 29일 오류 보정을 위해 -1 처리
    const jsDate = new Date(excelBaseDate.getTime() + (excelDate - 2) * 24 * 60 * 60 * 1000);
    return jsDate.toISOString().split('T')[0]; // 2025-01-01 형식으로 리턴
}
