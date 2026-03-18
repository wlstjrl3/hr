(function () {
    const tableBody = document.getElementById('statPsnlTableBody');
    const psnlKey = document.getElementById('psnlKey').value;
    const includeDomesticCheck = document.getElementById('includeDomestic');
    const statBaseDateInput = document.getElementById('statBaseDate');
    const sortOrderSelect = document.getElementById('sortOrder');
    const shortenPosCheck = document.getElementById('shortenPos');

    async function loadData() {
        tableBody.innerHTML = '<tr><td colspan="6" class="txtCenter pddS">데이터를 불러오는 중입니다...</td></tr>';

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
                tableBody.innerHTML = '<tr><td colspan="6" class="txtCenter pddS">데이터가 없습니다.</td></tr>';
                return;
            }

            renderTable(data);

        } catch (error) {
            console.error('Data fetch error:', error);
            tableBody.innerHTML = '<tr><td colspan="6" class="txtCenter pddS">데이터를 불러오는 중 오류가 발생했습니다.</td></tr>';
        }
    }

    // 초기 로드
    loadData();

    // 필터 변경 시 새로고침
    includeDomesticCheck.addEventListener('change', loadData);
    statBaseDateInput.addEventListener('change', loadData);
    sortOrderSelect.addEventListener('change', loadData);
    shortenPosCheck.addEventListener('change', loadData);

    function renderTable(rawData) {
        let html = '';
        let parishGlobalCounter = 0; // 전역 본당 순번
        const useShortenPos = shortenPosCheck.checked;

        // 1. 성지 분리 및 데이터 재구성
        const regular = rawData.filter(r => !(r.ORG_NM || '').endsWith('성지'));
        const holySites = rawData.filter(r => (r.ORG_NM || '').endsWith('성지'));

        // 성지들의 지구명을 '성지'로 통일
        holySites.forEach(r => {
            r.UPPR_ORG_NM = '성지';
        });

        const data = [...regular, ...holySites];
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
                parishGlobalCounter++; // 본당이 바뀔 때마다 증가

                // 동일 본당 내 직책별 카운트 추적
                let posCounter = {};
                let parishRows = data.slice(parishStartIdx, parishEndIdx);

                for (let k = 0; k < parishRows.length; k++) {
                    const row = parishRows[k];
                    const isEvenParish = parishGlobalCounter % 2 === 0;
                    const rowClass = isEvenParish ? 'parish-even' : '';

                    html += `<tr class="${rowClass}" style="border: 1px solid #ddd;">`;

                    // 1. 번호 (본당별 병합) - 전역 순번 사용
                    if (isFirstRowOfParish) {
                        html += `<td rowspan="${parishRowCount}" class="txtCenter" style="border: 1px solid #ddd; vertical-align: middle;">${parishGlobalCounter}</td>`;
                    }

                    // 2. 지구 (지구별 병합) - 인원수 포함
                    if (isFirstRowOfDistrict) {
                        html += `<td rowspan="${districtRowCount}" class="txtCenter" style="border: 1px solid #ddd; vertical-align: middle; background-color: #fff;">${districtName}<br>(${districtPersonnelCount}명)</td>`;
                        isFirstRowOfDistrict = false;
                    }

                    // 3. 본당 및 기관명 (본당별 병합)
                    if (isFirstRowOfParish) {
                        html += `<td rowspan="${parishRowCount}" class="txtCenter" style="border: 1px solid #ddd; vertical-align: middle;">${parishName}</td>`;
                        isFirstRowOfParish = false;
                    }

                    // 4. 성명 (직책 포함 및 단축 로직)
                    let displayPos = row.POSITION;
                    if (useShortenPos) {
                        if (displayPos === '사무장') displayPos = '장';
                        else if (displayPos === '사무원') displayPos = '원';
                        else if (displayPos === '관리장' || displayPos === '관리원') displayPos = '관';
                        else if (displayPos === '가사사용인') displayPos = '가';

                        // 같은 직책 카운트 처리
                        if (!posCounter[displayPos]) posCounter[displayPos] = 0;
                        posCounter[displayPos]++;

                        // 해당 본당 내에서 이 직책이 여러 명인지 미리 확인
                        let totalSamePosInParish = parishRows.filter(r => {
                            let rPos = r.POSITION;
                            if (rPos === '사무장') rPos = '장';
                            else if (rPos === '사무원') rPos = '원';
                            else if (rPos === '관리장' || rPos === '관리원') rPos = '관';
                            else if (rPos === '가사사용인') rPos = '가';
                            return rPos === displayPos;
                        }).length;

                        if (totalSamePosInParish > 1) {
                            displayPos += posCounter[displayPos];
                        }
                    }

                    html += `<td class="txtCenter" style="border: 1px solid #ddd; padding: 4px;">(${displayPos})${row.PSNL_NM}</td>`;

                    // 5. 세례명
                    html += `<td class="txtCenter" style="border: 1px solid #ddd; padding: 4px;">${row.BAPT_NM}</td>`;
                    // 6. 고용형태
                    html += `<td class="txtCenter" style="border: 1px solid #ddd; padding: 4px;">${row.WORK_TYPE}</td>`;

                    html += `</tr>`;
                }
                parishStartIdx = parishEndIdx;
            }

            districtStartIdx = districtEndIdx;
        }

        tableBody.innerHTML = html;
    }
})();
