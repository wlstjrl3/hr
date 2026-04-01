<?php include('./components/header.php'); ?>
<!-- 1. 제증명 발급 입력/수정 모달 -->
<div class="modalForm" id="certInputModal">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader">
            <b>제증명 발급 정보 입력</b>
            <button onclick="modalClose()"></button>
        </div>
        <div class="modalBody">
            <div class="modalGrp" style="width:100% !important; margin-bottom:12px;">
                <div class="modalHd">사원 검색</div>
                <div class="modalBd">
                    <input id="md_PSNL_NM_SEARCH" style="width:calc(100% - 90px);" placeholder="성명 입력 후 엔터">
                    <button id="md_PsnlSearchBtn" class="pddSS clBg3 clW rndCorner pointer"
                        style="width:70px; margin-left:5px; height:32px; vertical-align:top;">검색</button>
                </div>
            </div>
            <!-- 개인코드 / 조직명 박스 추가 -->
            <div class="modalGrp" style="width:100% !important; margin-bottom:12px;">
                <div class="modalHd">개인코드 / 조직</div>
                <div class="modalBd">
                    <input class="clBg5" id="md_PSNL_CD" readonly style="width:calc(30% - 10px); background:#EEE;"
                        placeholder="코드">
                    <input class="clBg5" id="md_ORG_NM" readonly style="width:calc(70% - 14px); background:#EEE;"
                        placeholder="조직명">
                </div>
            </div>
            <div class="modalGrp" style="width:100% !important; margin-bottom:12px;">
                <div class="modalHd">직책 / 성명</div>
                <div class="modalBd">
                    <input class="clBg5" id="md_POSITION" readonly style="width:calc(30% - 10px); background:#EEE;"
                        placeholder="직책">
                    <input class="clBg5" id="md_PSNL_NM" readonly style="width:calc(70% - 14px); background:#EEE;"
                        placeholder="성명">
                </div>
            </div>

            <div class="modalGrp" style="width:50% !important; margin-bottom:12px;">
                <div class="modalHd">증명서 종류</div>
                <div class="modalBd">
                    <select id="md_CERT_TYPE" style="width:calc(100% - 14px);">
                        <option value="재직">재직증명서</option>
                        <option value="경력">경력증명서</option>
                        <option value="퇴직">퇴직증명서</option>
                    </select>
                </div>
            </div>
            <div class="modalGrp" style="width:50% !important; margin-bottom:12px;">
                <div class="modalHd">본적</div>
                <div class="modalBd"><input id="md_ORIGIN_ADDR" placeholder="본적 주소 입력" autocomplete='off'
                        style="width:calc(100% - 14px);"></div>
            </div>
            <div class="modalGrp" style="width:50% !important; margin-bottom:12px;">
                <div class="modalHd">주소</div>
                <div class="modalBd"><input id="md_CURR_ADDR" placeholder="현재 거주지 주소 입력" autocomplete='off'
                        style="width:calc(100% - 14px);"></div>
            </div>
            <div class="modalGrp" style="width:50% !important; margin-bottom:12px;">
                <div class="modalHd">소속기관 주소</div>
                <div class="modalBd"><input id="md_ORG_ADDR" placeholder="소속기관 주소 입력" autocomplete='off'
                        style="width:calc(100% - 14px);"></div>
            </div>

            <input type="hidden" id="md_EMP_NO">
            <input type="hidden" id="md_ISSUE_NO">
            <div style="clear:both;"></div>
        </div>
        <div class="modalFooter">
            <button id="modalSaveBtn" style="padding:5px 15px;" class="clBg2 clW rndCorner">저장</button>
            <button id="modalDelBtn" style="padding:5px 15px;" class="clBg3 clW rndCorner">삭제</button>
        </div>
    </div>
</div>

<!-- 2. 인쇄용 미리보기 모달 -->
<div class="modalForm" id="certPrintModal" style="z-index:100;">
    <div class="modalBg"></div>
    <div class="modalWindow" style="width:850px; max-width:90vw;">
        <div class="modalHeader no-print">
            <b id="printModalTitle">증명서 인쇄 미리보기</b>
            <button
                onclick="document.getElementById('certPrintModal').style.visibility='hidden'; document.getElementById('certPrintModal').style.opacity='0';"></button>
        </div>
        <div class="modalBody" style="padding:0; background:#f0f0f0; overflow-y:auto; max-height:80vh;">
            <div id="printArea" class="print-container">
                <!-- 인쇄 레이아웃 (종류에 따라 클래스 동적 부여 필요) -->
                <div id="certPaper" class="cert-paper">
                    <!-- 외곽선 (시안 기준) -->
                    <div class="cert-outer-border"></div>

                    <!-- 배경 십자가 로고 -->
                    <img id="certLogoCross" src="" class="cert-logo-cross">

                    <!-- [1. 경력증명서 전용 레이아웃 - 초기 숨김] -->
                    <div id="layout_career" class="cert-inner layout-career" style="display:none;">
                        <div class="p_ISSUE_NO_wrap" style="top:25mm; left:15mm;">
                            제 <span id="p_ISSUE_NO_C"></span> 호
                        </div>
                        <div class="p_TITLE_wrap_career"
                            style="position:absolute; top:45mm; left:0; width:100%; text-align:center;">
                            <span
                                style="font-family:'CasuwonBold', 'Malgun Gothic', '맑은 고딕', sans-serif; font-size:35pt; font-weight:900; letter-spacing:15px; padding-left:15px;">경
                                력 증 명 서</span>
                        </div>
                        <div style="position:absolute; top:85mm; left:15mm; right:15mm;">
                            <table class="career-table"
                                style="width:100%; border-collapse:collapse; border:2px solid #555; text-align:center; font-size:13pt; line-height:1.5;">
                                <tr>
                                    <td class="lblBg"
                                        style="width:15%; font-weight:bold; letter-spacing:15px; padding-left:15px; border:1px solid #777; height:12mm;">
                                        성 명</td>
                                    <td style="width:35%; border:1px solid #777; font-family:'NanumMyeongjo', serif; font-size:17pt;" id="p_PSNL_NM_C">
                                    </td>
                                    <td class="lblBg"
                                        style="width:20%; font-weight:bold; letter-spacing:5px; border:1px solid #777;">
                                        생 년 월 일</td>
                                    <td style="width:30%; border:1px solid #777; font-family:'NanumMyeongjo', serif; font-size:17pt;" id="p_BIRTH_DT_C"></td>
                                </tr>
                                <tr>
                                    <td class="lblBg"
                                        style="font-weight:bold; letter-spacing:15px; padding-left:15px; border:1px solid #777; height:18mm;">
                                        주 소</td>
                                    <td colspan="3" style="border:1px solid #777; text-align:left; padding-left:10mm;"
                                        id="p_ADDR_C"></td>
                                </tr>
                                <tr>
                                    <td class="lblBg"
                                        style="font-weight:bold; letter-spacing:15px; padding-left:15px; border:1px solid #777; height:18mm;">
                                        본 적</td>
                                    <td colspan="3" style="border:1px solid #777; text-align:left; padding-left:10mm;"
                                        id="p_ORIGIN_C"></td>
                                </tr>
                                <tr class="lblBg" style="height:10mm;">
                                    <td colspan="2"
                                        style="font-weight:bold; letter-spacing:20px; padding-left:20px; border:1px solid #777; border-top:2px solid #555;">
                                        기 　 간</td>
                                    <td colspan="2"
                                        style="font-weight:bold; letter-spacing:30px; padding-left:30px; border:1px solid #777; border-top:2px solid #555;">
                                        경 력 사 항</td>
                                </tr>
                                <tr class="lblBg" style="height:10mm; font-size:12pt; font-weight:bold;">
                                    <td style="border:1px solid #777; letter-spacing:15px; padding-left:15px;">부 터</td>
                                    <td style="border:1px solid #777; letter-spacing:15px; padding-left:15px;">까 지</td>
                                    <td style="border:1px solid #777; letter-spacing:15px; padding-left:15px;">부 서</td>
                                    <td style="border:1px solid #777; letter-spacing:5px;">직위 및 직급</td>
                                </tr>
                                <tbody id="p_CAREER_LIST">
                                    <!-- JS에서 동적 생성 -->
                                </tbody>
                            </table>
                        </div>
                        <div class="p_SECTION_FOOTER_C"
                            style="position:absolute; top:225mm; left:0; width:100%; text-align:center;">
                            <div style="font-size:16pt; margin-bottom:10mm; font-family:'Nanum Myeongjo', serif;">위 사실을
                                증명함</div>
                            <div class="p_ISSUE_DT_wrap" id="p_ISSUE_DT_C" style="font-size:16pt; margin-bottom:10mm;">
                            </div>
                            <div class="p_ISSUER_wrap" style="position:relative; display:inline-block;">
                                <span class="issuer-name"
                                    style="font-family:'CasuwonBold', 'Malgun Gothic','맑은 고딕',sans-serif; font-size:33pt; font-weight:900; letter-spacing:4px; padding-right:20px;">천주교
                                    수원교구 제1대리구장</span>
                                <img src="" class="official-seal seal_C">
                            </div>
                        </div>
                    </div>

                    <!-- [2. 재직/퇴직 공통 베이스 레이아웃] -->
                    <div id="layout_standard" class="cert-inner layout-standard">
                        <div class="p_ISSUE_NO_wrap">
                            제 <span id="p_ISSUE_NO"></span> 호
                        </div>
                        <div class="p_TITLE_wrap">
                            <span><span id="p_TITLE"></span>증명서</span>
                        </div>
                        <div class="p_SECTION_TOP">
                            <div class="cert-row">
                                <div class="lbl">본 적</div>
                                <div class="val" id="p_ORIGIN_ADDR"></div>
                            </div>
                            <div class="cert-row" style="margin-top:15px;">
                                <div class="lbl">주 소</div>
                                <div class="val">
                                    <div id="p_CURR_ADDR_1"></div>
                                    <div id="p_CURR_ADDR_2" style="margin-top:6px;"></div>
                                </div>
                            </div>
                        </div>
                        <div class="p_SECTION_MID">
                            <div class="cert-row-mid">
                                <div class="lbl">성 　 명</div>
                                <div class="val" id="p_PSNL_NM"></div>
                            </div>
                            <div class="cert-row-mid">
                                <div class="lbl">생년월일</div>
                                <div class="val" id="p_BIRTH_DT"></div>
                            </div>
                        </div>
                        <div class="p_SECTION_BODY" id="p_BODY_CONTENT"></div>
                        <div class="p_SECTION_FOOTER">
                            <div class="p_ISSUE_DT_wrap" id="p_ISSUE_DT"></div>
                            <div class="p_ISSUER_wrap">
                                <span class="issuer-name">천주교 수원교구 제1대리구장</span>
                                <img id="certSeal" src="" class="official-seal">
                            </div>
                        </div>
                        <div id="p_OUTSIDE_FOOTER" class="p_OUTSIDE_FOOTER" style="display:none;">
                            천주교수원교구 제1대리구
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modalFooter no-print">
            <button onclick="window.print()" style="padding:8px 20px;" class="clBg2 clW rndCorner">인쇄하기</button>
            <button
                onclick="document.getElementById('certPrintModal').style.visibility='hidden'; document.getElementById('certPrintModal').style.opacity='0';"
                style="padding:8px 20px;" class="clBg3 clW rndCorner">닫기</button>
        </div>
    </div>
</div>

<div class="container">
    <!-- 팝업에서 데이터를 받기 위한 숨겨진 필드 (필터 영역에서 제거된 필드들의 버퍼 역할) -->
    <input type="hidden" id="PSNL_CD">
    <input type="hidden" id="ORG_NM">
    <input type="hidden" id="POSITION">

    <h4 class="cl3 pddS">제증명서 발급 대장</h4>

    <!-- 상단 필터 영역 (fmlList 참조) -->
    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>성명</b></span></div>
            <div class="colBd">
                <input id="PSNL_NM" class="filter" style="width:100%;" placeholder="">
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>증명서종류</b></span></div>
            <div class="colBd">
                <select id="CERT_TYPE_SEARCH" class="filter">
                    <option value="">전체</option>
                    <option value="재직">재직증명서</option>
                    <option value="경력">경력증명서</option>
                    <option value="퇴직">퇴직증명서</option>
                </select>
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>발급번호</b></span></div>
            <div class="colBd"><input id="ISSUE_NO_SEARCH" class="filter" placeholder="2026-001"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>발급일자</b></span></div>
            <div class="colBd">
                <input class="dualDateBox dateBox filter" id="ISSUE_DT_STT" placeholder="시작일"><span>~</span><input
                    class="dualDateBox dateBox filter" id="ISSUE_DT_END" placeholder="종료일">
            </div>
        </div>
    </div>
    <div class="clearB"></div>

    <br>
    <div class="tableOutFrm">
        <div class="pddS floatL">
            <a id="newBtn" class="pddS clBg3 clW rndCorner pointer">신규발급</a>
            <a id="xport" class="pddS clBg3 clW rndCorner pointer">엑셀 다운로드</a>
        </div>
        <div class="pddS floatR">
            <span>페이지당</span>
            <select class="tblLimit fs7 pddSS rndCorner clBrC">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
            <span>개씩 보기</span>
        </div>
        <table id="myTbl"></table>
        <div id="tblPagination"></div>
    </div>
    <br>
</div>

<style>
    /* 폰트 설정 */
    @import url('https://fonts.googleapis.com/css2?family=Nanum+Myeongjo:wght@400;700;800&display=swap');

    @font-face {
        font-family: 'CasuwonBold';
        src: url('<?php echo DIR_ROOT; ?>/assets/font/casuwonBold.TTF') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    .print-container {
        width: 100%;
        display: flex;
        justify-content: center;
        padding: 20px 0;
        background: #555;
    }

    /* A4 크기 하드코딩 (1장 고정을 위해 296mm로 살짝 축소) */
    .cert-paper {
        width: 210mm;
        height: 296mm;
        background: white;
        position: relative;
        overflow: hidden;
        box-sizing: border-box;
        font-family: 'CasuwonBold', 'NanumMyeongjo', 'Batang', serif;
        color: #000;
        margin: 0 auto;
    }

    @font-face {
        font-family: 'NanumMyeongjo';
        src: url('<?php echo DIR_ROOT; ?>/assets/font/NanumMyeongjo.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
    }

    /* 외곽선 추가 */
    .cert-outer-border {
        position: absolute;
        top: 15mm;
        right: 15mm;
        bottom: 15mm;
        left: 15mm;
        border: 2px solid #333;
        pointer-events: none;
        z-index: 10;
    }

    /* 십자가 배경 이미지 */
    .cert-logo-cross {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 350px;
        opacity: 1;
        z-index: 1;
        pointer-events: none;
    }

    /* 내부 컨테이너 (절대 좌표 기준점) */
    .cert-inner {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 2;
    }

    /* =====================================
   절대 좌표(Absolute) 기반 컴포넌트 배치
   - 높이 누적으로 인한 페이지 넘김을 원천 차단
   ===================================== */

    /* 1. 발급번호 */
    .p_ISSUE_NO_wrap {
        position: absolute;
        top: 25mm;
        left: 25mm;
        font-size: 14pt;
    }

    /* 2. 제목 (재직증명서) */
    .p_TITLE_wrap {
        position: absolute;
        top: 60mm;
        left: 0;
        width: 100%;
        text-align: center;
    }

    .p_TITLE_wrap span {
        font-family: 'CasuwonBold', 'Nanum Myeongjo', serif;
        letter-spacing: 25px;
        padding-left: 25px;
        font-size: 35pt;
        font-weight: 800;
        display: inline-block;
    }

    /* 3. 본적/주소 */
    .p_SECTION_TOP {
        position: absolute;
        top: 100mm;
        left: 25mm;
        font-size: 16pt;
        line-height: 1.6;
    }

    .cert-row {
        margin-bottom: 5mm;
        clear: both;
        overflow: hidden;
    }

    .cert-row .lbl {
        float: left;
        width: 40mm;
        font-weight: bold;
        letter-spacing: 15px;
    }

    .cert-row .val {
        float: left;
        width: 130mm;
        font-family: 'NanumMyeongjo', serif;
    }

    .cert-row .val div {
        font-family: 'NanumMyeongjo', serif;
    }

    /* 4. 성명/생년월일 (로고 중앙부) */
    .p_SECTION_MID {
        position: absolute;
        top: 145mm;
        left: 270px;
        width: 100%;
        display: flex;
        flex-direction: column;
        font-size: 17pt;
    }

    .cert-row-mid {
        display: flex;
        width: 140mm;
        margin-bottom: 7mm;
    }

    .cert-row-mid .lbl {
        width: 50mm;
        text-align: center;
        font-weight: bold;
        letter-spacing: 10px;
    }

    .cert-row-mid .val {
        width: 90mm;
        text-align: left;
        font-family: 'NanumMyeongjo', serif;
        letter-spacing: 1px;
    }

    /* 5. 본문 문구 */
    .p_SECTION_BODY {
        position: absolute;
        top: 185mm;
        left: 0;
        width: 100%;
        box-sizing: border-box;
        text-align: center;
        font-size: 18pt;
        line-height: 2.2;
        padding: 0 15mm;
        word-break: keep-all;
        font-family: 'NanumMyeongjo', serif;
    }

    /* 6. 하단 발급일 및 발행처 */
    .p_SECTION_FOOTER {
        position: absolute;
        top: 235mm;
        left: 0;
        width: 100%;
        text-align: center;
    }

    .p_ISSUE_DT_wrap {
        font-size: 18pt;
        margin-bottom: 12mm;
        font-family: 'NanumMyeongjo', serif;
    }

    .p_ISSUER_wrap {
        position: relative;
        display: inline-block;
    }

    .issuer-name {
        font-family: 'CasuwonBold', 'Nanum Myeongjo', serif;
        font-size: 24pt;
        font-weight: 900;
        letter-spacing: 3px;
        padding-right: 20px;
    }

    .official-seal {
        position: absolute;
        width: 100px;
        height: 100px;
        right: -20px;
        top: -30px;
        z-index: -1;
    }

    /* 인쇄 환경 최적화 (절대적 평탄화) */
    @media print {

        /* 1. 여백 없는 A4 강제 */
        @page {
            size: A4;
            margin: 0;
        }

        /* 2. 화면 전용 요소 숨김 */
        body>*:not(#certPrintModal),
        .modalBg,
        .modalHeader,
        .modalFooter,
        .no-print {
            display: none !important;
        }

        /* 3. HTML/Body 초기화 */
        html,
        body {
            margin: 0 !important;
            padding: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            background: #fff !important;
            overflow: hidden !important;
        }

        /* 4. 모달 및 래퍼들의 시각적 효과/위치값 완벽 해제 (Flatten) */
        #certPrintModal,
        .modalWindow,
        .modalBody,
        .print-container {
            position: static !important;
            transform: none !important;
            width: auto !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            background: transparent !important;
            overflow: visible !important;
            max-width: none !important;
            max-height: none !important;
        }

        /* 5. 실제 종이 컨테이너를 브라우저 최상단에 고정 */
        .cert-paper {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            background: #fff !important;
        }

        /* 6. 투명도 및 배경 옵션 */
        .cert-logo-cross {
            opacity: 0.9 !important;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
</style>

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/modal.css" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css" rel="stylesheet" />

<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/modal.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/dateForm.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/certPrint.js'></script>

<?php include('components/footer.php'); ?>