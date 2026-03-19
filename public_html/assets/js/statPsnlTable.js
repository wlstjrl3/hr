(function () {
    const tableArea = document.getElementById('statPsnlTableArea');
    const tableBody = document.getElementById('statPsnlTableBody');
    const tableHead = document.querySelector('#statPsnlTable thead');
    const psnlKey = document.getElementById('psnlKey').value;
    const includeDomesticCheck = document.getElementById('includeDomestic');
    const statBaseDateInput = document.getElementById('statBaseDate');
    const sortOrderSelect = document.getElementById('sortOrder');
    const shortenPosCheck = document.getElementById('shortenPos');
    const showExtCheck = document.getElementById('showExt');
    const showTelCheck = document.getElementById('showTel');
    const showBaptCheck = document.getElementById('showBapt');
    const showWorkTypeCheck = document.getElementById('showWorkType');
    const btnPrint = document.getElementById('btnPrint');

    let currentRawData = []; // 인쇄를 위한 데이터 저장용

    function getColspan() {
        let cols = 4; // 기본: 번호, 지구, 본당명, 성명
        if (showExtCheck.checked) cols++;
        if (showTelCheck.checked) cols++;
        if (showBaptCheck.checked) cols++;
        if (showWorkTypeCheck.checked) cols++;
        return cols;
    }

    async function loadData() {
        const colspan = getColspan();
        tableBody.innerHTML = `<tr><td colspan="${colspan}" class="txtCenter pddS">데이터를 불러오는 중입니다...</td></tr>`;

        try {
            const baseUrl = `${DIR_ROOT}/sys/statPsnlTable.php?key=${psnlKey}`;
            const includeDomestic = includeDomesticCheck.checked;
            const statBaseDate = statBaseDateInput.value;
            const sortOrder = sortOrderSelect.value;

            const url = `${baseUrl}&INCLUDE_DOMESTIC=${includeDomestic}&STAT_BASE_DATE=${statBaseDate}&SORT_ORDER=${sortOrder}`;

            const response = await fetch(url);
            const result = await response.json();
            currentRawData = result.data || [];

            if (currentRawData.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="${colspan}" class="txtCenter pddS">데이터가 없습니다.</td></tr>`;
                return;
            }

            renderTable(currentRawData);

        } catch (error) {
            console.error('Data fetch error:', error);
            tableBody.innerHTML = `<tr><td colspan="${colspan}" class="txtCenter pddS">데이터를 불러오는 중 오류가 발생했습니다.</td></tr>`;
        }
    }

    // 초기 로드
    loadData();

    // 필터 변경 시 새로고침
    const filters = [includeDomesticCheck, statBaseDateInput, sortOrderSelect, shortenPosCheck, showExtCheck, showTelCheck, showBaptCheck, showWorkTypeCheck];
    filters.forEach(f => f.addEventListener('change', loadData));

    // 인쇄 버튼 이벤트
    btnPrint.addEventListener('click', function () {
        if (!currentRawData || currentRawData.length === 0) {
            alert('인쇄할 데이터가 없습니다.');
            return;
        }
        printTable(currentRawData);
    });

    function renderTable(rawData) {
        const showExt = showExtCheck.checked;
        const showTel = showTelCheck.checked;
        const showBapt = showBaptCheck.checked;
        const showWorkType = showWorkTypeCheck.checked;
        const useShortenPos = shortenPosCheck.checked;

        // 1. 헤더 렌더링
        let headerHtml = '<tr>';
        headerHtml += '<th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">번호</th>';
        headerHtml += '<th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">지구</th>';
        headerHtml += '<th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">본당 및 기관명</th>';
        if (showExt) headerHtml += '<th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">내선</th>';
        if (showTel) headerHtml += '<th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">국번</th>';
        headerHtml += '<th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">성명</th>';
        if (showBapt) headerHtml += '<th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">세례명</th>';
        if (showWorkType) headerHtml += '<th style="border: 1px solid #ddd; padding: 8px;background:#579;color:white;">고용형태</th>';
        headerHtml += '</tr>';
        tableHead.innerHTML = headerHtml;

        // 2. 데이터 재구성 (성지 분리)
        const regular = rawData.filter(r => !(r.ORG_NM || '').endsWith('성지'));
        const holySites = rawData.filter(r => (r.ORG_NM || '').endsWith('성지'));
        holySites.forEach(r => r.UPPR_ORG_NM = '성지');

        const data = [...regular, ...holySites];
        let html = '';
        let parishGlobalCounter = 0;
        let districtStartIdx = 0;

        while (districtStartIdx < data.length) {
            const districtName = data[districtStartIdx].UPPR_ORG_NM || '미지정';
            let districtEndIdx = districtStartIdx;
            while (districtEndIdx < data.length && (data[districtEndIdx].UPPR_ORG_NM || '미지정') === districtName) {
                districtEndIdx++;
            }

            const districtRowCount = districtEndIdx - districtStartIdx;
            const districtPersonnelCount = districtRowCount;
            let parishStartIdx = districtStartIdx;
            let isFirstRowOfDistrict = true;

            while (parishStartIdx < districtEndIdx) {
                const parishName = data[parishStartIdx].ORG_NM;
                let parishEndIdx = parishStartIdx;
                while (parishEndIdx < districtEndIdx && data[parishEndIdx].ORG_NM === parishName) {
                    parishEndIdx++;
                }

                const parishRowCount = parishEndIdx - parishStartIdx;
                let isFirstRowOfParish = true;
                parishGlobalCounter++;

                let posCounter = {};
                let parishRows = data.slice(parishStartIdx, parishEndIdx);

                for (let k = 0; k < parishRows.length; k++) {
                    const row = parishRows[k];
                    const isEvenParish = parishGlobalCounter % 2 === 0;
                    const rowClass = isEvenParish ? 'parish-even' : '';

                    html += `<tr class="${rowClass}" style="border: 1px solid #ddd;">`;

                    // 번호
                    if (isFirstRowOfParish) {
                        html += `<td rowspan="${parishRowCount}" class="txtCenter" style="border: 1px solid #ddd; vertical-align: middle;">${parishGlobalCounter}</td>`;
                    }

                    // 지구
                    if (isFirstRowOfDistrict) {
                        html += `<td rowspan="${districtRowCount}" class="txtCenter" style="border: 1px solid #ddd; vertical-align: middle; background-color: #fff;">${districtName}<br>(${districtPersonnelCount}명)</td>`;
                        isFirstRowOfDistrict = false;
                    }

                    // 본당명
                    if (isFirstRowOfParish) {
                        html += `<td rowspan="${parishRowCount}" class="txtCenter" style="border: 1px solid #ddd; vertical-align: middle;">${parishName}</td>`;

                        let extension = '';
                        if (row.ORG_IN_TEL) {
                            const match = row.ORG_IN_TEL.match(/\d{4}/);
                            if (match) extension = match[0];
                        }
                        const outTel = row.ORG_OUT_TEL || '';

                        if (showExt) {
                            html += `<td rowspan="${parishRowCount}" class="txtCenter" style="border: 1px solid #ddd; vertical-align: middle;">${extension}</td>`;
                        }
                        if (showTel) {
                            html += `<td rowspan="${parishRowCount}" class="txtCenter" style="border: 1px solid #ddd; vertical-align: middle;">${outTel}</td>`;
                        }
                        isFirstRowOfParish = false;
                    }

                    // 성명
                    let displayPos = row.POSITION;
                    if (useShortenPos) {
                        if (displayPos === '사무장') displayPos = '장';
                        else if (displayPos === '사무원') displayPos = '원';
                        else if (displayPos === '관리장' || displayPos === '관리원') displayPos = '관';
                        else if (displayPos === '가사사용인') displayPos = '가';

                        if (!posCounter[displayPos]) posCounter[displayPos] = 0;
                        posCounter[displayPos]++;

                        let totalSamePosInParish = parishRows.filter(r => {
                            let rPos = r.POSITION;
                            if (rPos === '사무장') rPos = '장';
                            else if (rPos === '사무원') rPos = '원';
                            else if (rPos === '관리장' || rPos === '관리원') rPos = '관';
                            else if (rPos === '가사사용인') rPos = '가';
                            return rPos === displayPos;
                        }).length;

                        if (totalSamePosInParish > 1) displayPos += posCounter[displayPos];
                    }
                    html += `<td class="txtCenter" style="border: 1px solid #ddd; padding: 4px;">(${displayPos})${row.PSNL_NM}</td>`;

                    // 세례명
                    if (showBapt) {
                        html += `<td class="txtCenter" style="border: 1px solid #ddd; padding: 4px;">${row.BAPT_NM}</td>`;
                    }
                    // 고용형태
                    if (showWorkType) {
                        html += `<td class="txtCenter" style="border: 1px solid #ddd; padding: 4px;">${row.WORK_TYPE}</td>`;
                    }

                    html += `</tr>`;
                }
                parishStartIdx = parishEndIdx;
            }
            districtStartIdx = districtEndIdx;
        }
        tableBody.innerHTML = html;
    }

    function printTable(rawData) {
        const useShortenPos = shortenPosCheck.checked;
        const baseDate = statBaseDateInput.value;

        // 1. 데이터 재구성 (성지 분리) - renderTable과 동일한 순서 유지
        const regular = rawData.filter(r => !(r.ORG_NM || '').endsWith('성지'));
        const holySites = rawData.filter(r => (r.ORG_NM || '').endsWith('성지'));
        holySites.forEach(r => r.UPPR_ORG_NM = '성지');
        const sourceData = [...regular, ...holySites];

        // 2. 출력용 플랫 데이터 생성 (rowspan 없이 단순화)
        let printRows = [];
        let parishCounter = 0;
        let lastParishName = '';

        // 동일 본당 내 직책 번호 매김을 위한 로직
        let parishGroups = [];
        let currentGroup = [];
        sourceData.forEach((row, idx) => {
            if (idx > 0 && row.ORG_NM !== sourceData[idx - 1].ORG_NM) {
                parishGroups.push(currentGroup);
                currentGroup = [];
            }
            currentGroup.push(row);
            if (idx === sourceData.length - 1) parishGroups.push(currentGroup);
        });

        parishGroups.forEach(group => {
            parishCounter++;
            let posCounter = {};

            group.forEach(row => {
                // 내선 추출
                let extension = '';
                if (row.ORG_IN_TEL) {
                    const match = row.ORG_IN_TEL.match(/\d{4}/);
                    if (match) extension = match[0];
                }
                
                // 내선이 없는 경우 국번(OUT_TEL)에서 앞자리 제거 후 표시
                if (!extension && row.ORG_OUT_TEL) {
                    extension = row.ORG_OUT_TEL.replace(/^031-/, '');
                }

                // 직책 단축 및 번호
                let displayPos = row.POSITION;
                if (useShortenPos) {
                    if (displayPos === '사무장') displayPos = '장';
                    else if (displayPos === '사무원') displayPos = '원';
                    else if (displayPos === '관리장' || displayPos === '관리원') displayPos = '관';
                    else if (displayPos === '가사사용인') displayPos = '가';

                    if (!posCounter[displayPos]) posCounter[displayPos] = 0;
                    posCounter[displayPos]++;

                    // 해당 본당 내에서 이 직책이 여러 명인지 확인
                    let totalSamePosInParish = group.filter(r => {
                        let rPos = r.POSITION;
                        if (rPos === '사무장') rPos = '장';
                        else if (rPos === '사무원') rPos = '원';
                        else if (rPos === '관리장' || rPos === '관리원') rPos = '관';
                        else if (rPos === '가사사용인') rPos = '가';
                        return rPos === displayPos;
                    }).length;

                    if (totalSamePosInParish > 1) displayPos += posCounter[displayPos];
                }

                printRows.push({
                    district: row.UPPR_ORG_NM || '미지정',
                    no: parishCounter,
                    parish: row.ORG_NM,
                    ext: extension,
                    name: `(${displayPos})${row.PSNL_NM}`
                });
            });
        });

        // 3. 3단 분할
        const totalRows = printRows.length;
        const rowsPerBank = Math.ceil(totalRows / 3);
        const banks = [
            printRows.slice(0, rowsPerBank),
            printRows.slice(rowsPerBank, rowsPerBank * 2),
            printRows.slice(rowsPerBank * 2)
        ];

        // 4. HTML 생성
        let printHtml = `
            <div style="position: relative; margin-bottom: 20px; font-family: sans-serif; display: flex; justify-content: center; align-items: flex-end;">
                <h2 style="margin: 0; font-size: 24px; text-align: center; width: 100%;">제1대리구 본당 내선 번호</h2>
                <div style="position: absolute; right: 0; bottom: 0; font-size: 11px; color: #333;">(기준일: ${baseDate})</div>
            </div>
            <div>`;

        banks.forEach((bankData, i) => {
            printHtml += `<table class="print-bank">`;
            printHtml += `<thead><tr>
                <th>지구</th>
                <th>번호</th>
                <th style="width: 55px;">본당명</th>
                <th style="width: 60px; white-space: nowrap;">내선</th>
                <th>성명</th>
            </tr></thead>`;
            printHtml += `<tbody>`;

            let dIdx = 0;
            while (dIdx < bankData.length) {
                let currentDistrict = bankData[dIdx].district;
                let dCount = 1;
                while (dIdx + dCount < bankData.length && bankData[dIdx + dCount].district === currentDistrict) {
                    dCount++;
                }

                let endDIdx = dIdx + dCount;
                let pIdx = dIdx;
                let isFirstD = true;

                while (pIdx < endDIdx) {
                    let currentNo = bankData[pIdx].no;
                    let pCount = 1;
                    while (pIdx + pCount < endDIdx && bankData[pIdx + pCount].no === currentNo) {
                        pCount++;
                    }

                    let endPIdx = pIdx + pCount;
                    let isFirstP = true;

                    for (let r = pIdx; r < endPIdx; r++) {
                        let row = bankData[r];
                        let trClass = (row.no % 2 === 0) ? "print-parish-even" : "";
                        printHtml += `<tr class="${trClass}">`;

                        if (isFirstD) {
                            let verticalDist = currentDistrict.split('').join('<br>');
                            // 괄호(︵, ︶)만 회전된 유니코드를 사용하고 숫자는 수직으로 나열
                            let verticalNum = String(dCount).split('').join('<br>');
                            let countStr = `<div style="margin-top: 5px; line-height: 1;">︵<br>${verticalNum}<br>︶</div>`;
                            printHtml += `<td rowspan="${dCount}" style="background-color: #fff !important; width: 1.5em; vertical-align: middle; line-height: 1.2;">
                                ${verticalDist}<br>${countStr}
                            </td>`;
                            isFirstD = false;
                        }

                        if (isFirstP) {
                            printHtml += `<td rowspan="${pCount}" style="vertical-align: middle;">${row.no}</td>`;
                            printHtml += `<td rowspan="${pCount}" style="vertical-align: middle;">${row.parish}</td>`;
                            printHtml += `<td rowspan="${pCount}" style="vertical-align: middle;">${row.ext}</td>`;
                            isFirstP = false;
                        }

                        printHtml += `<td>${row.name}</td>`;
                        printHtml += `</tr>`;
                    }
                    pIdx = endPIdx;
                }
                dIdx = endDIdx;
            }

            printHtml += `</tbody></table>`;
        });

        printHtml += '</div>';

        const printArea = document.getElementById('printArea');
        printArea.innerHTML = printHtml;

        window.print();
    }
})();
