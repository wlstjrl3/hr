document.addEventListener("DOMContentLoaded", () => {
    loadSmtpConfig();

    document.getElementById("saveBtn").addEventListener("click", () => {
        saveSmtpConfig();
    });
});

function loadSmtpConfig() {
    fetch(DIR_ROOT + "/sys/smtpConfig.php?key=" + API_TOKEN)
        .then(res => res.json())
        .then(json => {
            if (json.result === "success" && json.data) {
                const d = json.data;
                document.getElementById("SMTP_HOST").value = d.SMTP_HOST || "";
                document.getElementById("SMTP_PORT").value = d.SMTP_PORT || "";
                document.getElementById("SMTP_USER").value = d.SMTP_USER || "";
                document.getElementById("SMTP_PASS").value = d.SMTP_PASS || "";
                document.getElementById("SMTP_SECURE").value = d.SMTP_SECURE || "";
            }
        })
        .catch(err => {
            console.error("Failed to load SMTP config:", err);
        });
}

function saveSmtpConfig() {
    const formData = new FormData(document.getElementById("smtpForm"));
    formData.append("key", API_TOKEN);

    if (!confirm("SMTP 설정을 변경하시겠습니까? (잘못된 설정 시 메일 발송이 중단됩니다.)")) return;

    fetch(DIR_ROOT + "/sys/smtpConfig.php", {
        method: "POST",
        body: formData
    })
    .then(res => res.json())
    .then(json => {
        if (json.result === "success") {
            alert("SMTP 설정이 저장되었습니다.");
            loadSmtpConfig();
        } else {
            alert("저장 실패: " + json.message);
        }
    })
    .catch(err => {
        alert("통신 오류가 발생했습니다.");
        console.error(err);
    });
}
