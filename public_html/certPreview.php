<?php include('./components/header.php'); ?>

<?php
// 테스트용 데이터 (파라미터가 없을 경우 기본값)
$certType = $_GET['type'] ?? '재직';
$nm = $_GET['nm'] ?? '한지현';
$birth = $_GET['birth'] ?? '1970년 4월 12일';
$issueNo = $_GET['no'] ?? '2026-001';
$origin = $_GET['origin'] ?? '미 기 재';
$addr1 = $_GET['addr1'] ?? '현재주소 1행 예시';
$addr2 = $_GET['addr2'] ?? '현재주소 2행 예시 (상세주소)';
$orgNm = $_GET['org'] ?? '고등동 성당';
$pos = $_GET['pos'] ?? '사무원';
$joinDt = $_GET['join'] ?? '2026년 2월 1일';
$issueDt = date('Y. n. j.');
?>

<div class="container" style="background:#555; padding: 50px 0; display:flex; flex-direction:column; align-items:center;">
    
    <div style="background:#fff; padding:20px; margin-bottom:20px; border-radius:8px; width:800px; box-shadow:0 10px 30px rgba(0,0,0,0.3);">
        <h3 style="margin-top:0;">🎨 증명서 레이아웃 실시간 조정 (Preview Mode)</h3>
        <p style="color:#666; font-size:0.9rem;">
            <code>certPrint.php</code>의 스타일을 수정하신 뒤 이 페이지를 새로고침(F5) 하시면 즉시 반영됩니다.<br>
            인쇄 시뮬레이션을 위해 50% 축소 브라우저 뷰를 제공합니다.
        </p>
        <div style="display:flex; gap:10px;">
            <a href="?type=재직" class="pddS clBg3 clW rndCorner" style="text-decoration:none;">재직 예시</a>
            <a href="?type=경력" class="pddS clBg3 clW rndCorner" style="text-decoration:none;">경력 예시</a>
            <a href="?type=퇴직" class="pddS clBg3 clW rndCorner" style="text-decoration:none;">퇴직 예시</a>
            <button onclick="window.print()" class="pddS clBg2 clW rndCorner">테스트 인쇄</button>
        </div>
    </div>

<?php
// 종류별 클래스 매핑
$typeClass = 'type-emp';
if ($certType == '퇴직')
    $typeClass = 'type-retire';
elseif ($certType == '경력')
    $typeClass = 'type-career';
?>
    <!-- 실제 인쇄 영역 시뮬레이션 (Scale 적용) -->
    <div id="printArea" class="print-container">
        <div class="cert-paper <?php echo $typeClass; ?>">
            <div class="cert-outer-border"></div>
            <!-- 배경 로고 조작 (경력증명서에는 로고가 보이지 않거나 다를 수 있음. 시안엔 없으나 유지) -->
            <img src="<?php echo DIR_ROOT; ?>/assets/img/certs/cert_bg_cross.png" class="cert-logo-cross" style="opacity: <?php echo($certType == '퇴직') ? '0.9' : ($certType == '경력' ? '0.05' : '0.12'); ?>;" onerror="this.style.display='none'">
            
            <?php if ($certType == '경력'): ?>
            <!-- ==============================
                 [경력증명서 전용 레이아웃] 
                 ============================== -->
            <div class="cert-inner layout-career">
                <div class="p_ISSUE_NO_wrap" style="top:25mm; left:15mm;">
                    　제 <?php echo str_replace(['재직-', '경력-', '퇴직-'], '', $issueNo); ?> 호
                </div>
                
                <div class="p_TITLE_wrap_career" style="position:absolute; top:45mm; left:0; width:100%; text-align:center;">
                    <span style="font-family:'Malgun Gothic', '맑은 고딕', sans-serif; font-size:35pt; font-weight:900; letter-spacing:15px; padding-left:15px;">
                        경 력 증 명 서
                    </span>
                </div>

                <!-- 경력 테이블 영역 -->
                <div style="position:absolute; top:85mm; left:15mm; right:15mm; bottom:auto;">
                    <table class="career-table" style="width:100%; border-collapse:collapse; border:2px solid #555; text-align:center; font-size:13pt; line-height:1.5;">
                        <!-- 인적/주소 공통사항 -->
                        <tr>
                            <td class="lblBg" style="width:15%; font-weight:bold; letter-spacing:15px; padding-left:15px; border:1px solid #777; height:12mm;">성 명</td>
                            <td style="width:35%; border:1px solid #777; letter-spacing:10px;"><?php echo $nm; ?></td>
                            <td class="lblBg" style="width:20%; font-weight:bold; letter-spacing:5px; border:1px solid #777;">생 년 월 일</td>
                            <td style="width:30%; border:1px solid #777;"><?php echo $birth; ?></td>
                        </tr>
                        <tr>
                            <td class="lblBg" style="font-weight:bold; letter-spacing:15px; padding-left:15px; border:1px solid #777; height:18mm;">주 소</td>
                            <td colspan="3" style="border:1px solid #777; text-align:left; padding-left:10mm;"><?php echo $addr1 . " " . $addr2; ?></td>
                        </tr>
                        <tr>
                            <td class="lblBg" style="font-weight:bold; letter-spacing:15px; padding-left:15px; border:1px solid #777; height:18mm;">본 적</td>
                            <td colspan="3" style="border:1px solid #777; text-align:left; padding-left:10mm;"><?php echo $origin; ?></td>
                        </tr>
                        
                        <!-- 경력사항 헤더 -->
                        <tr class="lblBg" style="height:10mm;">
                            <td colspan="2" style="font-weight:bold; letter-spacing:20px; padding-left:20px; border:1px solid #777; border-top:2px solid #555;">기 　 간</td>
                            <td colspan="2" style="font-weight:bold; letter-spacing:30px; padding-left:30px; border:1px solid #777; border-top:2px solid #555;">경 력 사 항</td>
                        </tr>
                        <tr class="lblBg" style="height:10mm; font-size:12pt; font-weight:bold;">
                            <td style="border:1px solid #777; letter-spacing:15px; padding-left:15px;">부 터</td>
                            <td style="border:1px solid #777; letter-spacing:15px; padding-left:15px;">까 지</td>
                            <td style="border:1px solid #777; letter-spacing:15px; padding-left:15px;">부 서</td>
                            <td style="border:1px solid #777; letter-spacing:5px;">직위 및 직급</td>
                        </tr>
                        
                        <!-- 경력사항 데이터 내역 -->
                        <tr style="height:25mm;">
                            <td style="border:1px solid #777; font-family:sans-serif;"><?php echo str_replace(['년 ', '월 ', '일'], ['.', '.', '.'], $joinDt); ?></td>
                            <td style="border:1px solid #777; font-family:sans-serif;"><?php echo str_replace(['년 ', '월 ', '일'], ['.', '.', '.'], date("Y년 n월 j일", strtotime("-1 day"))); ?></td>
                            <td style="border:1px solid #777;">
                                천주교 수원교구<br><?php echo $orgNm; ?>
                            </td>
                            <td style="border:1px solid #777;"><?php echo $pos; ?></td>
                        </tr>
                        <tr style="height:20mm;">
                            <td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td>
                        </tr>
                        <tr style="height:20mm;">
                            <td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td><td style="border:1px solid #777;"></td>
                        </tr>
                    </table>
                </div>

                <div class="p_SECTION_FOOTER_C" style="position:absolute; top:225mm; left:0; width:100%; text-align:center;">
                    <div style="font-size:16pt; margin-bottom:10mm; font-family:'Nanum Myeongjo', serif;">
                        위 사실을 증명함
                    </div>
                    <div class="p_ISSUE_DT_wrap" style="font-size:16pt; margin-bottom:10mm;">
                        <?php echo $issueDt; ?>
                    </div>
                    
                    <div class="p_ISSUER_wrap" style="position:relative; display:inline-block;">
                        <span class="issuer-name" style="font-family:'Malgun Gothic','맑은 고딕',sans-serif; font-size:22pt; font-weight:900; letter-spacing:4px; padding-right:20px;">천주교 수원교구 제1대리구장</span>
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/certs/official_seal.png" class="official-seal" onerror="this.style.display='none'">
                    </div>
                </div>
            </div>
            
            <?php
else: ?>
            <!-- ==============================
                 [재직/퇴직 공통 베이스 레이아웃] 
                 ============================== -->
            <div class="cert-inner layout-standard">
                <div class="p_ISSUE_NO_wrap">
                    제 <?php echo str_replace(['재직-', '경력-', '퇴직-'], '', $issueNo); ?> 호
                </div>
                <div class="p_TITLE_wrap">
                    <span><?php echo $certType; ?>증명서</span>
                </div>

                <div class="p_SECTION_TOP">
                    <div class="cert-row">
                        <div class="lbl">본 적</div>
                        <div class="val"><?php echo $origin; ?></div>
                    </div>
                    <div class="cert-row" style="margin-top:15px;">
                        <div class="lbl">주 소</div>
                        <div class="val">
                            <div><?php echo $addr1; ?></div>
                            <div style="margin-top:6px;"><?php echo $addr2; ?></div>
                        </div>
                    </div>
                </div>

                <div class="p_SECTION_MID">
                    <div class="cert-row-mid">
                        <div class="lbl">성 　 명</div>
                        <div class="val"><?php echo $nm; ?></div>
                    </div>
                    <div class="cert-row-mid">
                        <div class="lbl">생년월일</div>
                        <div class="val"><?php echo $birth; ?></div>
                    </div>
                </div>

                <div class="p_SECTION_BODY">
                    <?php if ($certType == '재직'): ?>
                        이 사람은 <?php echo $joinDt; ?> 부터 현재 까지 본 천주<br>
                        교 수원교구 <?php echo $orgNm; ?> <?php echo $pos; ?>으로 재직 중임을 증명함.
                    <?php
    elseif ($certType == '퇴직'): ?>
                        위 사람은 <?php echo $joinDt; ?> 입사하여 <br>
                        2026년 3월 26일 퇴직하였음을 증명함.
                    <?php
    endif; ?>
                </div>

                <div class="p_SECTION_FOOTER">
                    <div class="p_ISSUE_DT_wrap"><?php echo $issueDt; ?></div>
                    <div class="p_ISSUER_wrap">
                        <span class="issuer-name">천주교 수원교구 제1대리구장</span>
                        <img src="<?php echo DIR_ROOT; ?>/assets/img/certs/official_seal.png" class="official-seal" onerror="this.style.display='none'">
                    </div>
                </div>
                
                <?php if ($certType == '퇴직'): ?>
                <div class="p_OUTSIDE_FOOTER">
                    천주교수원교구 제1대리구
                </div>
                <?php
    endif; ?>
            </div>
            <?php
endif; ?>
        </div>
    </div>
</div>

<style>
/* certPrint.php의 스타일과 완전히 동일하게 유지 */
@import url('https://fonts.googleapis.com/css2?family=Nanum+Myeongjo:wght@400;700;800&display=swap');

/* 공통 컨테이너 설정 */
.print-container { width: 100%; display: flex; justify-content: center; padding: 0; }

.cert-paper { 
    width: 210mm; height: 296mm; 
    background: white; position: relative; 
    overflow: hidden; box-sizing: border-box;
    font-family: 'Nanum Myeongjo', 'Batang', serif; color: #000;
    box-shadow: 0 0 40px rgba(0,0,0,0.5); 
}

/* 외곽선 */
.cert-outer-border {
    position: absolute; top: 15mm; right: 15mm; bottom: 15mm; left: 15mm;
    border: 2px solid #333; pointer-events: none; z-index: 10;
}

/* 십자가 로고 */
.cert-logo-cross {
    position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);
    width: 350px; opacity: 1; z-index: 1; pointer-events: none;
}

/* 테이블 공통 클래스 */
.career-table .lblBg { background: rgba(50, 50, 50, 0.05); }

/* 그 외 기존 CSS 유지 */
.cert-inner { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 2; }
.p_ISSUE_NO_wrap { position: absolute; top: 25mm; left: 25mm; font-size: 14pt; }
.p_TITLE_wrap { position: absolute; top: 60mm; left: 0; width: 100%; text-align: center; }
.p_TITLE_wrap span { letter-spacing: 25px; padding-left: 25px; font-size: 35pt; font-weight: 800; display: inline-block; }
.p_SECTION_TOP { position: absolute; top: 100mm; left: 25mm; font-size: 16pt; line-height: 1.6; }
.cert-row { margin-bottom: 5mm; clear: both; overflow: hidden; }
.cert-row .lbl { float: left; width: 40mm; font-weight: bold; letter-spacing: 15px; }
.cert-row .val { float: left; width: 130mm; }
.p_SECTION_MID { position: absolute; top: 145mm; left: 170px; width: 100%; display: flex; flex-direction: column; align-items: center; font-size: 17pt; }
.cert-row-mid { display: flex; width: 140mm; margin-bottom: 7mm; }
.cert-row-mid .lbl { width: 50mm; text-align: center; font-weight: bold; letter-spacing: 10px; }
.cert-row-mid .val { width: 90mm; text-align: left;}
.p_SECTION_BODY { position: absolute; top: 185mm; left: 0; width: 100%; box-sizing: border-box; text-align: center; font-size: 18pt; line-height: 2.2; padding: 0 15mm; word-break: keep-all; }
.p_SECTION_FOOTER { position: absolute; top: 235mm; left: 0; width: 100%; text-align: center; }
.p_ISSUE_DT_wrap { font-size: 18pt; margin-bottom: 17mm; }
.p_ISSUER_wrap { position: relative; display: inline-block; }
.issuer-name { font-size: 16pt; font-weight: 900; letter-spacing: 3px; padding-right: 20px; }
.official-seal { position: absolute; width: 100px; height: 100px; right: -20px; top: -30px; z-index: -1; }
.p_OUTSIDE_FOOTER { position: absolute; bottom: 8mm; right: 15mm; font-size: 10pt; font-family: 'Malgun Gothic', '맑은 고딕', sans-serif; color: #666; font-weight: bold; letter-spacing: 1px; }

/* [퇴직증명서 전용 스타일] */
.type-retire .p_TITLE_wrap span { font-family: 'Malgun Gothic', '맑은 고딕', sans-serif; font-weight: 900; font-size: 38pt; letter-spacing: 30px; padding-left: 30px; }
.type-retire .p_SECTION_TOP { top: 95mm; }
.type-retire .p_SECTION_MID { left: 0; align-items: center; top: 135mm; }
.type-retire .p_SECTION_MID .cert-row-mid { width: 100mm; margin-left: 50mm; }
.type-retire .p_SECTION_BODY { top: 175mm; font-size: 18pt; }
.type-retire .p_SECTION_FOOTER { top: 220mm; }
.type-retire .issuer-name { font-family: 'Malgun Gothic', '맑은 고딕', sans-serif; font-size: 20pt; letter-spacing: 4px; }

/* 인쇄 시 스타일 */
@media print {
    @page { size: A4; margin: 0; }
    body > *:not(.print-container), .no-print, h3, p, div:has(>a) { display: none !important; }
    html, body { margin: 0 !important; padding: 0 !important; width: 210mm !important; height: 297mm !important; overflow: hidden !important; }
    .container { padding: 0 !important; background: #fff !important; }
    .cert-paper { box-shadow: none !important; width: 210mm !important; height: 296mm !important; }
    .cert-logo-cross { opacity: 0.9 !important; }
    * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
}
</style>

<?php include('components/footer.php'); ?>
