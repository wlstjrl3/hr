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
            const data = result.data;

            if (!data || data.length === 0) {
                tableBody.innerHTML = `<tr><td colspan="${colspan}" class="txtCenter pddS">데이터가 없습니다.</td></tr>`;
                return;
            }

            renderTable(data);

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
})();
