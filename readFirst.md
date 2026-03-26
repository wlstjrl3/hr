# 대리구청 직원 관리 시스템 설계안 (Implementation Plan)

> **[AI Model Handover Context — 새 대화창/다른 모델 인수인계용]**
>
> **1. 디렉토리 구조**
> - 페이지 경로: `c:/projectCoding/hr/public_html/`
> - 프론트 참조: `user.php` (모달+테이블 구조), `assets/js/hr_tbl.js`
> - 백엔드 API: `sys/` 폴더 (query string CRUD 방식, RESTful X)
> - 참조 파일: `sys/userList.php`, `sys/userConfig.php`, `sys/psnlList.php`, `sys/psnlConfig.php`
>
> **2. 프론트엔드 UI 규칙**
> - `include('./components/header.php');` 시작 / `include('components/footer.php');` 종료
> - 컨테이너: `.container` / 검색: `.searchArea > .colGrp > .colHd + .colBd`
> - 테이블: `.tableOutFrm` + `<table id="myTbl">` (JS 바인딩)
> - 모달: `.modalForm > .modalBg + .modalWindow > .modalHeader + .modalBody + .modalFooter`
>
> **3. 백엔드 규칙 (PHP)**
> - `include "sql_safe_helper.php";` + `verifyApiKey($conn, $_REQUEST['key'] ?? '');`
> - 리스트: `executeQuery()`, `safeOrderBy()`, `safeLimit()`, `jsonResponse()`
> - CRUD: `$_REQUEST['CRUD']` (C/R/D) 분기 + `executeUpdate()` + 바인딩 문자열 필수
>
> **4. DB 규칙**
> - 스키마: `SW1D_HUB` / 테이블: `TB_` 접두사 대문자 / 컬럼: 대문자 축약어
> - PK 접미사: `_CD`, 이름: `_NM`, 날짜: `_DT`, 금액: `_AMT`

---

## 구현 목표


## 🚀 진행 현황 (Progress Report)

### ✅ 완료된 작업 (Done)
기존 프로젝트가 완료되고 자잘한 버그를 수정할 예정

### ⏳ 향후 작업 (Next Steps)
/hr/trsList 의 모달창 [직원정보:] 에 조직 직책 성명이 붙어있는데 초기 값으로 조직정보가 없기에 undefined가 나타나고 있음. 이런 경우 "조직정보 없음" 이라고 표시되도록 수정


## 🛠️ 기술적 참고 사항 (Dev Notes) & 🚨 인쇄 레이아웃 핵심 가이드

**[중요] 제증명서 등 A4 기반 웹 인쇄(`window.print()`) 구현 시 다음 원칙을 반드시 준수할 것! (플래시/클로드 등 타 AI 모델 공유용 컨텍스트)**

1. **절대 좌표(Absolute Positioning) 기반 설계 원칙**
    *   **문제점:** 브라우저 인쇄 시 `display: flex` 플로우나 상대적인 여백(`margin` 등) 누적은 페이지 컨텐츠의 전체 길이를 미세하게 연장시켜 '2페이지 넘김' 현상 및 '전체 배율 강제 축소' 오류를 유발함.
    *   **해결책:** `.cert-inner`를 절대 기준점(`position: absolute; top:0; left:0;`)으로 잡고, 내부의 모든 텍스트 블록(제목, 성명, 본문 등)을 `top: xx mm`, `left: yy mm` 형태의 절대 좌표로만 찍어서 렌더링해야 함. 절대 좌표를 쓰면 내용물이 서로를 밀어내지 않아 100% 1페이지 내에 고정됨.
2. **모달(Modal) 래퍼 간섭 완벽 제한 (Flattening 구조)**
    *   **문제점:** 모달창(`modalWindow`)의 화면 정중앙 배치 CSS (`position: fixed/absolute`, `transform: translate(-50%, -50%)`, `box-shadow`)가 인쇄 미리보기 화면에도 적용되면, A4 종이가 모니터 화면처럼 확대/크롭/오프셋(Shift) 되는 치명적인 렌더링 붕괴가 발생.
    *   **해결책:** `@media print` 블록 최상단에서 모달과 얽힌 모든 부모 래퍼 요소(`#certPrintModal`, `.modalWindow`, `.modalBody`, `.print-container` 등)를 `position: static !important`, `transform: none !important`, `margin: 0 !important`, `padding: 0 !important`로 선언하여 **평탄화(Flatten)** 해야 함. 오직 `.cert-paper` 요소만 좌표(0,0)에 올려 A4 사이즈로 고정.
3. **용지 규격 고정 및 마진(Margin) 제거**
    *   `@page { size: A4; margin: 0; }` 설정과 함께, 인쇄 용지로 쓰이는 컨테이너(`.cert-paper`) 높이를 `297mm`가 아닌 `296mm` 정도로 1mm 작게 잡아 브라우저 고유 여백 연산을 속여야 확실한 1페이지 핏(Fit)이 보장됨.
4. **글자 간격(Letter Spacing) 분산 기법**
    *   "재 직 증 명 서" 처럼 띄어쓰기가 필요한 곳에 스페이스바나 브라켓(`{ { } }`)을 하드코딩하지 말고, 텍스트는 "재직증명서" 원형 그대로 두되 CSS의 `letter-spacing: 25px; padding-left: 25px;`를 적용하여 수치적으로 벌려야 유지보수와 중앙 정렬이 정확히 이루어짐.

*   **발급번호 규칙**: 연도가 바뀌면 자동으로 `제 YYYY-001 호`부터 다시 시작하도록 구현됨.
*   **폰트 가이드**: 증명서 본문과 제목은 나눔명조(`Nanum Myeongjo`, 혹은 바탕체), 번호 및 일반 텍스트는 고딕/돋움 계열 혼용(시안 기준).

---

## 파일 목록

| 유형 | 경로 | 설명 |
|---|---|---|
| NEW | `sys/certList.php` | 제증명서 발급 내역 및 검색용 리스트 조회 API |
| NEW | `sys/certConfig.php` | 발급 정보 저장(CRUD) 및 번호 자동 채번 API |
| NEW | `public_html/certPrint.php` | 증명서 발급 대장 서치 및 UI 웹 페이지 |
| MODIFY | `public_html/components/header.php` | 헤더에 제증명서 발급(certPrint.php) 메뉴 링크 추가 |

---

## 데이터베이스 설계


## 페이지별 세부 구현 방향

**1. 제증명서 통합 발급 (`certPrint.php` 상호작용 설계)**

> **UI 구조 및 레이아웃 참조:** `fmlList` 페이지와 동일한 상단 필터/하단 데이터 그리드 구조를 베이스로 반영.

* **동작 상세 흐름 (Workflow):**
