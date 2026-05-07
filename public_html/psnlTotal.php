<?php include('./components/header.php'); ?>
<div class="modalForm">
    <div class="modalBg"></div>
    <div class="modalWindow">
        <div class="modalHeader" style="background:#f8fafc; border-bottom:1px solid #e2e8f0; font-weight:700; font-size:15px; display:flex; justify-content:space-between; align-items:center; padding:14px 20px;">
            <span style="color:#333;">직원 종합정보 조회</span>
            <button onclick="modalClose()" style="background:transparent;border:none;color:#94a3b8;font-size:18px;cursor:pointer;padding:0;"></button>
        </div>
        <div class="modalBody" style="background:#f1f5f9; padding:20px;">
            <div style="margin-bottom:10px; font-size:14px; color:#1e40af; font-weight:bold;" id="mdBdOrgInTel"></div>
            
            <div class="info-card" id="cardBasic">
                <div class="card-hd card-hd--basic">
                    <span>① 직원 요약 프로필</span>
                </div>
                <div class="card-body" id="psnlSummaryBody">
                    <!-- JS will populate -->
                </div>
            </div>

            <div class="info-card" id="cardFml" style="display:none;">
                <div class="card-hd card-hd--fml"><span>② 가족정보</span></div>
                <div class="card-body" style="padding:0;">
                    <table class="inline-tbl">
                        <thead><tr><th>가족성명</th><th>관계</th><th>생년월일</th><th>상세정보</th></tr></thead>
                        <tbody id="fmlTblBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="info-card" id="cardAdj" style="display:none;">
                <div class="card-hd card-hd--adj"><span>③ 제수당</span></div>
                <div class="card-body" style="padding:0;">
                    <table class="inline-tbl">
                        <thead><tr><th>수당타입</th><th>명칭</th><th>등급</th><th>수당금액</th></tr></thead>
                        <tbody id="adjTblBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="info-card" id="cardOpi" style="display:none;">
                <div class="card-hd card-hd--opi"><span>④ 상벌/평가</span></div>
                <div class="card-body" style="padding:0;">
                    <table class="inline-tbl">
                        <thead><tr><th>타입</th><th>날짜</th><th>평가자</th><th>내용</th></tr></thead>
                        <tbody id="opiTblBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="info-card" id="cardTui" style="display:none;">
                <div class="card-hd card-hd--tui"><span>⑤ 자녀학비보조</span></div>
                <div class="card-body" style="padding:0;">
                    <table class="inline-tbl">
                        <thead><tr><th>자녀명</th><th>생일</th><th>지급시작</th><th>지급회수</th><th>잔여회수</th><th>누계금액</th></tr></thead>
                        <tbody id="tuitionTblBody"></tbody>
                    </table>
                </div>
            </div>

            <div class="info-card" id="cardPtt" style="display:none;">
                <div class="card-hd" style="background:linear-gradient(135deg,#7c3aed,#c4b5fd);"><span>① 최저임금 근무조건 이력</span></div>
                <div class="card-body" id="pttCardsBody" style="padding:12px;">
                    <!-- JS will populate -->
                </div>
            </div>

        </div>
        <div class="modalFooter" style="background:#fff; border-top:1px solid #e5e7eb; padding:15px 20px; text-align:right;">
            <button class="btn btn-primary btn-block" id="goEditBtn">직원 통합정보 수정</button>
        </div>
    </div>
</div>

<!-- 모달: 사진 등록 -->
<div class="custom-modal-wrap" id="photoModal" style="z-index:99999; display:none;">
    <div class="custom-modal-bg" onclick="document.getElementById('photoModal').style.display='none'"></div>
    <div class="custom-modal-box" style="max-width:400px;">
        <div class="custom-modal-hd">
            <span>개인 사진 등록</span>
            <a onclick="document.getElementById('photoModal').style.display='none'" style="cursor:pointer;color:#94a3b8;font-size:18px;">✖</a>
        </div>
        <div class="custom-modal-bd" style="text-align:center;">
            <p style="font-size:13px; color:#64748b; margin-bottom:15px;">이미지 파일을 선택하거나, 복사한 이미지를 <b>Ctrl+V</b>로 붙여넣으세요.</p>
            <div id="photoPreviewBox" style="width:150px; height:180px; margin:0 auto 15px auto; background:#f1f5f9; border:2px dashed #cbd5e1; border-radius:8px; display:flex; align-items:center; justify-content:center; overflow:hidden; position:relative;">
                <span id="photoPreviewPlaceholder" style="color:#94a3b8; font-size:40px;">👤</span>
                <img id="photoPreviewImg" src="" style="width:100%; height:100%; object-fit:cover; display:none;">
            </div>
            <input type="file" id="photoFileInput" accept="image/*" style="display:none;">
            <button class="btn btn-ghost btn-sm" onclick="document.getElementById('photoFileInput').click()">파일 찾기</button>
        </div>
        <div class="custom-modal-ft">
            <button class="btn btn-primary" id="savePhotoBtn">사진 업로드</button>
        </div>
    </div>
</div>
<br><!--이 위로는 모달 팝업영역, 아래로는 페이지 코드-->
<div class="container">

    <h4 class="cl3 pddS">
        직원 종합정보 조회
    </h4>

    <div class="searchArea">
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>기준일</b></span></div>
            <div class="colBd"><input id="STAT_BASE_DATE" class="dateBox filter" value="<?php echo @$_REQUEST['STAT_BASE_DATE']; ?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>소속</b></span></div>
            <div class="colBd"><input id="ORG_NM" class="filter" value="<?php echo @$_REQUEST['ORG_NM']; ?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>성명</b></span></div>
            <div class="colBd"><input id="PSNL_NM" class="filter" value="<?php echo @$_REQUEST['PSNL_NM']; ?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>세례명</b></span></div>
            <div class="colBd"><input id="BAPT_NM" class="filter" value="<?php echo @$_REQUEST['BAPT_NM']; ?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>직책</b></span></div>
            <div class="colBd"><input id="POSITION" class="filter" value="<?php echo @$_REQUEST['POSITION']; ?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>고용형태</b></span></div>
            <div class="colBd"><select id="WORK_TYPE" class="filter">
                <option value="">전체</option><option>정규</option><option>계약</option><option>기능</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>급(Lv)</b></span></div>
            <div class="colBd">
                <input id="GRD_GRADE_From" class="dualDateBox filter" type="number" value="<?php echo @$_REQUEST['GRD_GRADE_From'] ?: @$_REQUEST['GRD_GRADE']; ?>"><span>~</span><input id="GRD_GRADE_To" class="dualDateBox filter" type="number" value="<?php echo @$_REQUEST['GRD_GRADE_To'] ?: @$_REQUEST['GRD_GRADE']; ?>">
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>호</b></span></div>
            <div class="colBd">
                <input id="GRD_PAY_From" class="dualDateBox filter" type="number" value="<?php echo @$_REQUEST['GRD_PAY_From'] ?: @$_REQUEST['GRD_PAY']; ?>"><span>~</span><input id="GRD_PAY_To" class="dualDateBox filter" type="number" value="<?php echo @$_REQUEST['GRD_PAY_To'] ?: @$_REQUEST['GRD_PAY']; ?>">
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>재직구분</b></span></div>
            <div class="colBd"><select id="TRS_TYPE" class="filter">
                <option value="1">재직</option><option value="2">퇴사</option><option value="">전체</option>
            </select></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>연락처</b></span></div>
            <div class="colBd"><input id="PHONE_NUM" class="phoneNumBox filter"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>나이 유형</b></span></div>
            <div class="colBd">
                <select id="USE_KOREAN_AGE" class="filter">
                    <option value="N">만나이(기본)</option>
                    <option value="Y">한국나이</option>
                </select>
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>나이 범위</b></span></div>
            <div class="colBd">
                <input type="number" id="AGE_MIN" class="dualDateBox" style="width:40px;"><span>~</span><input type="number" id="AGE_MAX" class="dualDateBox" style="width:40px;">
            </div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>생년월일</b></span></div>
            <div class="colBd"><input class="dateBox dualDateBox filter" id="PSNL_BIRTH_From" value="<?php echo @$_REQUEST['PSNL_BIRTH_From']; ?>"><span>~</span><input class="dateBox dualDateBox filter" id="PSNL_BIRTH_To" value="<?php echo @$_REQUEST['PSNL_BIRTH_To']; ?>"></div>
        </div>
        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>주민번호</b></span></div>
            <div class="colBd"><input id="PSNL_NUM" class="juminNumBox filter"></div>
        </div>

        <div class="colGrp">
            <div class="colHd clBg5 cl2"><span><b>입/퇴사일</b></span></div>
            <div class="colBd"><input class="dateBox dualDateBox filter" id="TRS_DT_From" value="<?php echo @$_REQUEST['TRS_DT_From']; ?>"><span>~</span><input class="dateBox dualDateBox filter" id="TRS_DT_To" value="<?php echo @$_REQUEST['TRS_DT_To']; ?>"></div>
        </div>        
        <div class="colGrp clBg4">
            <div class="txtCenter" style="padding:5px 0 6px 0;">
                <a class="pddSS clBg2 clW rndCorner pointer quikSetBtn" id="setRetire">정년 대상</a>
                <a class="pddSS clBg2 clW rndCorner pointer quikSetBtn" id="set10Yr">근속10년</a>
                <a class="pddSS clBg2 clW rndCorner pointer quikSetBtn" id="set20Yr">근속20년</a>
                <a class="pddSS clBg2 clW rndCorner pointer quikSetBtn" id="set30Yr">근속30년</a>
            </div>
        </div>
    </div>
    <div class="clearB"></div>

    <br>
    <div class="tableOutFrm">
        <div class="pddS floatL">
            <div class="showColBg"></div>
            <a id="showCol" class="pddS clBg3 clW rndCorner pointer">표시 항목 변경</a>
        </div><div id="showColList"></div>
        <div class="pddS floatL">
            <a href="/psnlList" class="pddS clBg3 clW rndCorner pointer">신규입력 페이지이동</a>
        </div>
        <div class="pddS floatL">
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
        <div class="xScroll">        
            <table id="myTbl" class="width1850"></table>
        </div>
        <div id="tblPagination"></div>
    </div>
    <br>
</div>

<link href="<?php echo DIR_ROOT; ?>/assets/css/hr_tbl.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/modal.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/showColList.css?ver=1775259319" rel="stylesheet" />
<link href="<?php echo DIR_ROOT; ?>/assets/css/psnlTotal.css?ver=1775259319.1" rel="stylesheet" />
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/hr_tbl.js?ver=1775259319'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/modal.js?ver=1775259319'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/library/xlsx.mini.min.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/psnlTotal.js?ver=1'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/dateForm.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/phoneForm.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/juminForm.js'></script>
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/dateFormat.js'></script>

<?php include('components/footer.php'); ?>

<script>

</script>
