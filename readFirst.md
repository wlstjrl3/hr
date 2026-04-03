# 대리구청 직원 관리 시스템 (HR) - ReadFirst

이 파일은 AI 에이전트가 가장 먼저 읽어야 할 메인 진입점(Entry Point)입니다.
현재 진행 중인 작업 현황을 확인하고, 작업 성격에 맞게 분류된 하위 가이드 문서를 선택하여 읽어주세요.


## 📂 기능별 세부 가이드 (필요한 문서만 읽으세요)

AI는 작업 주제 (readFirst/todo.md) 따라 필요한 내용만 참조하여 불필요한 토큰 사용과 혼동을 줄이세요.

*   **[작업 주제](readFirst/todo.md)**: 어떤식으로 무슨 파일에 작업을 할지 체크리스트를
*   **[프론트엔드 UI 규칙](readFirst/frontend.md)**: 화면 레이아웃, 테이블/모달 구조 등 프론트엔드 작업 시 참고
*   **[백엔드 API 규칙](readFirst/backend.md)**: PHP 기반 `sys/` 폴더 내 CRUD 작성 패턴 및 `sql_safe_helper.php` 가이드
*   **[데이터베이스 구조](readFirst/database.md)**: 스키마(`SW1D_HUB`), 테이블명 규칙 및 컬럼 네이밍 규약
*   **[인쇄(Print) 레이아웃 가이드](readFirst/print.md)**: `window.print()`를 사용한 증명서 등 인쇄 화면 구현 시 반드시 지켜야 할 절대 좌표 등의 규칙

---

## 파일 목록 정보 (참고)

| 유형 | 경로 | 설명 |
|---|---|---|
| NEW | `sys/tuitionList.php` | 학자금 발급 내역 및 검색용 리스트 조회 API |
| NEW | `sys/tuitionConfig.php` | 발급 정보 저장(CRUD) 및 번호 자동 채번 API |
| NEW | `public_html/tuition.php` | 자녀학비보조금 서치 및 UI 웹 페이지 |
| NEW | `public_html/assets/js/tuition.js` | 자녀학비보조금 전용 자바스크립트 로직 분리 파일 |
| MODIFY | `public_html/components/header.php` | 헤더에 자녀학비보조금(tuition.php) 메뉴 링크 추가 |

---

## 🛑 AI 에이전트 개발 시 주의사항 및 규칙 (필수 확인)

1. **사용 언어 및 라이브러리 제약 (No jQuery)**
   - 본 프로젝트의 프론트엔드는 제이쿼리(`$`, `$.ajax`)를 포함하고 있지 않습니다.
   - 모든 비동기 통신은 `fetch` API를 활용하며 DOM 제어는 순수 자바스크립트(`Vanilla JS`) 메서드(`document.getElementById()`, `querySelector()`)만 사용하세요.

2. **단일 HTML 내 `<script>` 태그 규약 (외부 JS 파일 분리)**
   - `.php` 페이지 하단에 인라인 `<script>` 형태로 긴 코드를 작성하지 마세요. 
   - 모든 자바스크립트 코드는 `public_html/assets/js/` 폴더 내에 해당하는 페이지 이름과 동일한 `.js` 파일(예: `tuition.js`)로 분리 생성한 후, PHP 파일에서 `<script src='...'></script>` 형태로 로드하세요.

3. **검색 필터 UI 통일 (엔터 키 검색)**
   - `searchArea` 내부의 검색 로직은 별도의 `<button>`을 눌러서 `submit` 하는 방식 대신 `input` 필드에 `class="filter"`를 부여하는 형식으로 일치시켜주세요.
   - `<input class="filter">`에 `keyup` / `change` 이벤트를 매핑하여 폼 제출 없이 실시간/엔터 검색이 수행되도록 구현합니다.
   - '신규', '엑셀 다운로드' 등 기능형 버튼들은 `tableOutFrm` 컨테이너 상단 좌우측(`floatL`, `floatR`)으로 따로 빼서 위치를 맞추세요.

4. **모달(Modal) 창 UI 디자인 및 스크립트 제어 규칙**
   - 모달 창 팝업은 스크립트에서 `.style.display = 'block'` 이 아니라, 프로젝트 룰(modal.css)에 맞춰 `.style.visibility = 'visible'` 및 `.style.opacity = '1'` 로 제어해야 애니메이션과 함께 정상 표출됩니다.
   - 모달 내부 폼은 임의의 `<table>` 클래스가 아닌 `modalGrpFrame`, `modalGrp`, `modalHd`, `modalBd` 래퍼 구조를 사용하여 폼 레이아웃을 구현해야 호환성이 유지됩니다.
   - 모달 내부에 데이터 그리드(표)를 표시할 때는 반드시 `.hr_tbl` 클래스를 적용해 통일된 규격을 유지하세요.
