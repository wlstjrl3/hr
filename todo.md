# statWorkTypeGraph2 개발 계획 (Gemini 3 Flash용)

이 문서는 `statWorkTypeGraph2` 페이지 개발을 위한 구체적인 작업 지시서입니다. 액트 단계(Gemini 3 Flash)에서 아래의 항목들을 순차적으로 구현하고 완료할 때마다 체크표시([x])를 해주세요.

## 1. 프론트엔드 (UI) 구현: `public_html/statWorkTypeGraph2.php`
- [x] `statWorkTypeGraph.php`를 복사하여 `statWorkTypeGraph2.php`를 생성합니다.
- [x] 상단 타이틀을 "직군별 상세 통계 그래프" 등으로 변경합니다.
- [x] 검색/필터 영역 수정:
  - 시작일(`STT_DATE`)과 종료일(`END_DATE`) 입력 폼은 유지합니다.
  - 다음 4가지 유형을 선택할 수 있는 콤보박스(Select Box, ID: `GRAPH_TYPE` 등)를 추가합니다.
    1. 연령 (Age)
    2. 근속연수 (Years of Service)
    3. 정규직/계약직 비율 (Reg/Cont Ratio)
    4. 정규직 급수별 비율 (Reg Grade Ratio)
- [x] 그래프 영역 구성:
  - 한 유형당 2개의 막대그래프를 보여주어야 하므로, 사무직용과 관리직용 Canvas 엘리먼트를 각각 나란히 혹은 상하로 배치합니다. (예: `chartOffice`, `chartManagement`)
- [x] Javascript 파일 연결: `<script src=".../assets/js/statWorkTypeGraph2.js"></script>` 로 변경합니다.

## 2. 프론트엔드 (로직) 구현: `public_html/assets/js/statWorkTypeGraph2.js`
- [x] `assets/js/statWorkTypeGraph.js`를 참고하여 `statWorkTypeGraph2.js`를 생성합니다.
- [x] Chart.js를 이용해 2개의 막대그래프(Bar Chart)를 초기화하는 함수를 만듭니다.
  - X축: 날짜 (조회된 기간의 월별 또는 년별)
  - Y축: 인원수
- [x] 데이터 로딩(fetch) 로직 작성:
  - 사용자가 설정한 기간(`STT_DATE`, `END_DATE`) 및 유형(`GRAPH_TYPE`) 값을 읽어와 `sys/statWorkTypeGraph2.php`로 API 요청을 보냅니다.
- [x] API 응답 처리:
  - 응답받은 데이터를 기반으로 사무직(Office) 차트와 관리직(Management) 차트에 각각 데이터를 분배하여 업데이트합니다.

## 3. 백엔드 (API) 구현: `sys/statWorkTypeGraph2.php`
- [x] `sys/statWorkTypeGraph.php`를 기반으로 `sys/statWorkTypeGraph2.php`를 생성합니다.
- [x] 요청 파라미터로 기간 및 `GRAPH_TYPE`을 받습니다.
- [x] 데이터 집계 로직:
  - 직원들의 발령 정보(`PSNL_TRANSFER`) 및 인적 정보(`PSNL_INFO`)를 조회하여, 해당 날짜(X축) 기준의 재직 상태를 확인합니다.
  - 직원을 **사무직**과 **관리직**으로 분류하는 기준(예: 직종, 직책 등 프로젝트 내 기준)을 적용하여 두 그룹으로 분리합니다.
  - 선택된 `GRAPH_TYPE`에 따라 다음 데이터를 집계합니다.
    - **유형 1 (연령)**: 20대, 30대, 40대, 50대 이상 등으로 인원수 집계
    - **유형 2 (근속연수)**: 근속연수 구간별(예: 1~3년, 3~5년, 5~10년 등) 인원수 집계 (입사일 기준)
    - **유형 3 (정규/계약 비율)**: `WORK_TYPE` 등을 참조하여 정규직 vs 계약직 인원수 집계
    - **유형 4 (정규직 급수별)**: 정규직 직원들만 필터링한 후, 급수(Grade)별 인원수 집계
- [x] 최종 집계 결과를 프론트엔드에서 2개의 차트(사무직, 관리직)를 그리기 편한 형태의 JSON으로 반환합니다.
