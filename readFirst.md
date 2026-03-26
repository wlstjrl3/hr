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
**제증명서 발급 페이지** 구축 (재직/경력/퇴직 증명서 통합 관리)
1. **발급 대장 기록:** 증명서 구분(CERT_TYPE), 발급번호, 본적, 주소, **소속기관 주소**를 DB에 저장. (성명, 생년월일, 입/퇴사일, 조직명 등은 기존 직원 테이블 조회 활용)
2. **발급번호 자동 채번 로직:** `[제 YYYY-NNN 호]` (예: 특정 연도의 첫 발급은 `[제 2026-001 호]`, 이어서 `002`, `003`...)
3. **증명서 폼 (UI/인쇄 통합):** 세 가지 증명서가 출력 양식 디자인(레이아웃)만 다를 뿐 요구 데이터가 동일하므로, 요청된 증명서 종류(`CERT_TYPE`)에 맞게 배경 및 직인 이미지를 스위칭하여 브라우저 인쇄(`window.print()`)를 지원.

---

## 🚀 진행 현황 (Progress Report)

### ✅ 완료된 작업 (Done)

1.  **Step 1: 데이터베이스 설계 및 생성**
    *   `TB_CERT_PRINT` 테이블 생성 완료 (연도별 자동 채번 `ISSUE_NO` 포함).
2.  **Step 2: 백엔드 API 구현**
    *   `sys/certList.php`: 필터링 기반 목록 조회.
    *   `sys/certConfig.php`: C/R/D 및 자동 채번 로직 구현.
3.  **Step 3: 프론트엔드 UI 구축 및 인쇄 엔진 구현**
    *   `certPrint.php`: 발급 대장 화면 및 사원 검색 팝업 연동.
    *   `certPrint.js`: 모달 제어 및 인쇄 데이터 동적 매핑.
4.  **Step 4: 인쇄 레이아웃 정밀 조정 (Fine-tuning)**
    *   사용자 제공 디자인(십자가 배경 및 텍스트 중심 스타일)에 맞춰 HTML/CSS 구조 전면 개편.
    *   `CERT_TYPE`별 동적 문구 생성 로직 적용 (재직/경력/퇴직 분기).
    *   날짜 및 발급번호 표시 형식을 디자인 시안에 맞춰 최적화.
    *   절대 좌표 기반의 데이터 배치 구조 마련 (이미지 업로드 시 즉시 대응 가능).

### ⏳ 향후 작업 (Next Steps)

*   **Step 5: 메뉴 연동 및 최종 검토**
    *   `header.php` 메뉴 목록에 '제증명 발급 대장' 링크 추가.
    *   실제 배경 이미지(`cert_bg_cross.png`, `official_seal.png`) 업로드 후 위치 미세 조정.

---

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

### TB_CERT_PRINT (제증명서 발급 기록)
| 컬럼명 | 데이터 타입 | 설명 | 조건/키 |
|---|---|---|---|
| ISSUE_NO | VARCHAR(20) | 발급번호 (예: `제 2026-001 호`) | PK |
| EMP_NO | VARCHAR(20) | 사번 (대상자) | FK (TB_USER.EMP_NO) |
| CERT_TYPE | VARCHAR(20) | 증명서 종류 (재직,경력,퇴직 구별 코드) | |
| ORIGIN_ADDR | VARCHAR(255) | 본적 | |
| CURR_ADDR | VARCHAR(255) | 현재 주소 (개인 주소) | |
| ORG_ADDR | VARCHAR(255) | 추가 입력 - 소속기관 주소 | |
| ISSUE_DT | DATE | 발급일자 | Default: SYSDATE |
| REG_EMP_NO | VARCHAR(20) | 발급담당자 (요청자 등) | |

* **데이터 연동 포인트 (실제 DB 스키마 조인 매핑 정보)**
  - 성명: `PSNL_INFO.PSNL_NM`
  - 생년월일: `PSNL_INFO.PSNL_NUM` 앞 6자리(주민번호 앞자리 활용) 또는 파생 조회 결과
  - 입사일: 해당 사원의 `PSNL_TRANSFER` 내역 중 최초 `TRS_DT` (또는 `APP_DT`)
  - 퇴사일: 해당 사원의 `PSNL_TRANSFER.TRS_TYPE = 2`(퇴사) 레코드의 `TRS_DT`
  - 소속 조직명: `ORG_INFO.ORG_NM` (가장 최근 `PSNL_TRANSFER.ORG_CD`와 조인)
  - 직책/직위: 가장 최근 `PSNL_TRANSFER.POSITION`

---

## 페이지별 세부 구현 방향

**1. 제증명서 통합 발급 (`certPrint.php` 상호작용 설계)**

> **UI 구조 및 레이아웃 참조:** `fmlList` 페이지와 동일한 상단 필터/하단 데이터 그리드 구조를 베이스로 반영.

* **동작 상세 흐름 (Workflow):**
  1. **조회 및 필터링 (`certList.php`):**
     - 상단 필터 영역(`searchArea`)에 사원 검색, **증명서 종류(전체/재직/경력/퇴직) 셀렉트 박스**, **발급일자 구간(Datepicker)**, **발급번호 검색 필드**를 배치한다.
     - 검색 시 하단 데이터 테이블(`hrTbl`)에 복합 조건이 반영된 기존 발급 내역 목록이 표시된다.
  2. **신규 발급 tiến행 (`certConfig.php` 호출):**
     - 우측 상단 또는 테이블 헤더 근처의 `[신규]` 버튼을 클릭한다.
     - 신규 발급용 정보 입력 모달창이 나타난다.
     - 모달창 내에서 **발급 대상자(직원) 검색**, **증명서 종류 지정**, **본적**, **주소**, 그리고 **소속기관 주소**를 기입한 뒤 `[저장]` 버튼을 누른다.
  3. **목록 갱신 및 인쇄 영역 표시:**
     - 저장이 정상적으로 완료되면 채번결과와 함께 DB 인서트가 완료되며, 모달창이 닫히고 하단 테이블(`hrTbl`) 데이터가 자동으로 새로고침된다.
     - 갱신된 내역 목록 우측 끝자리 데이터 열(Grid Column)마다 `[인쇄]` 버튼이 노출된다.
  4. **증명서 인쇄 (출력용 분기 모달):**
     - 특정 내역의 `[인쇄]` 버튼을 클릭하면, 해당 행 데이터의 `CERT_TYPE`(종류 코드)을 판단하여 **동일한 데이터(본적, 기관주소 등)를 얹을 출력용 모달창(레이아웃 템플릿만 다름)**을 띄운다.
     - 템플릿(재직/경력/퇴직 증명서 배경 폼 및 직인 조합) 위에 DB에서 가져온 사원 정보와 발급 번호를 텍스트 매핑한다.
     - 관리자는 뷰어 모달 하단 `[출력/인쇄하기]` 버튼을 통해 브라우저 인쇄 모듈(`window.print()`)을 가동하여 증명서를 출력한다.
