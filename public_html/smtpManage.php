<?php include('./components/header.php'); ?>

<div class="container">
    <h4 class="cl3 pddS">SMTP 서버 설정 관리</h4>
    
    <div class="tableOutFrm" style="max-width: 800px; margin: 20px 0;">
        <div class="searchArea" style="padding: 20px; border-radius: 8px;">
            <form id="smtpForm">
                <div class="colGrp" style="width: 100% !important; margin-bottom: 15px;">
                    <div class="colHd clBg5 cl2" style="width: 150px;"><span><b>SMTP 호스트</b></span></div>
                    <div class="colBd">
                        <input type="text" id="SMTP_HOST" name="SMTP_HOST" style="width: 100%;" placeholder="예: smtp.casuwon.or.kr">
                    </div>
                </div>
                
                <div class="colGrp" style="width: 100% !important; margin-bottom: 15px;">
                    <div class="colHd clBg5 cl2" style="width: 150px;"><span><b>SMTP 포트</b></span></div>
                    <div class="colBd">
                        <input type="number" id="SMTP_PORT" name="SMTP_PORT" style="width: 100px;" placeholder="예: 25, 587">
                        <span style="font-size: 11px; color: #666; margin-left: 10px;">(일반: 25, 보안: 587)</span>
                    </div>
                </div>

                <div class="colGrp" style="width: 100% !important; margin-bottom: 15px;">
                    <div class="colHd clBg5 cl2" style="width: 150px;"><span><b>사용자 계정</b></span></div>
                    <div class="colBd">
                        <input type="text" id="SMTP_USER" name="SMTP_USER" style="width: 100%;" placeholder="그룹웨어 이메일 주소">
                    </div>
                </div>

                <div class="colGrp" style="width: 100% !important; margin-bottom: 15px;">
                    <div class="colHd clBg5 cl2" style="width: 150px;"><span><b>비밀번호</b></span></div>
                    <div class="colBd">
                        <input type="password" id="SMTP_PASS" name="SMTP_PASS" style="width: 100%;" placeholder="그룹웨어 비밀번호">
                    </div>
                </div>

                <div class="colGrp" style="width: 100% !important; margin-bottom: 15px;">
                    <div class="colHd clBg5 cl2" style="width: 150px;"><span><b>보안 방식</b></span></div>
                    <div class="colBd">
                        <select id="SMTP_SECURE" name="SMTP_SECURE" style="width: 120px;">
                            <option value="">None</option>
                            <option value="tls">TLS (추천)</option>
                            <option value="ssl">SSL</option>
                        </select>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="button" id="saveBtn" class="pddS clBg3 clW rndCorner pointer" style="width: 150px; border: none; font-weight: bold;">설정 저장</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="<?php echo DIR_ROOT; ?>/assets/css/searchArea.css" rel="stylesheet" />
<script type='text/javascript' src='<?php echo DIR_ROOT; ?>/assets/js/smtpManage.js'></script>

<?php include('components/footer.php'); ?>
