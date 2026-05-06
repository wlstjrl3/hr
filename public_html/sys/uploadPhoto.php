<?php
include "sql_safe_helper.php";
// verifyApiKey($conn, @$_POST['key']); // If needed, can enable it, but we might rely on session or other token.
// Actually, newEmpReg uses API_TOKEN.
verifyApiKey($conn, @$_POST['key']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse($conn, ["success" => false, "message" => "Invalid request method"]);
    exit;
}

$psnlCd = @$_POST['PSNL_CD'];
if (!$psnlCd) {
    jsonResponse($conn, ["success" => false, "message" => "No PSNL_CD provided"]);
    exit;
}

// Check if file is uploaded via normal file input
if (isset($_FILES['photoFile']) && $_FILES['photoFile']['error'] === UPLOAD_ERR_OK) {
    $tmpName = $_FILES['photoFile']['tmp_name'];
    $fileType = mime_content_type($tmpName);
    
    if (strpos($fileType, 'image/') !== 0) {
        jsonResponse($conn, ["success" => false, "message" => "Uploaded file is not an image"]);
        exit;
    }

    $uploadDir = '../assets/photos/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    $destPath = $uploadDir . $psnlCd . '.jpg';
    
    // We can use GD to convert any image to JPG and resize if necessary, but for simplicity we just move it.
    // However, it's safer to read and save as jpeg to ensure format.
    $image = false;
    switch ($fileType) {
        case 'image/jpeg': $image = imagecreatefromjpeg($tmpName); break;
        case 'image/png': $image = imagecreatefrompng($tmpName); break;
        case 'image/gif': $image = imagecreatefromgif($tmpName); break;
        case 'image/webp': $image = imagecreatefromwebp($tmpName); break;
    }
    
    if ($image !== false) {
        imagejpeg($image, $destPath, 90);
        imagedestroy($image);
        jsonResponse($conn, ["success" => true, "message" => "Photo uploaded successfully"]);
    } else {
        if (move_uploaded_file($tmpName, $destPath)) {
            jsonResponse($conn, ["success" => true, "message" => "Photo moved successfully"]);
        } else {
            jsonResponse($conn, ["success" => false, "message" => "Failed to save photo"]);
        }
    }
} 
// Check if base64 encoded image is sent
else if (isset($_POST['photoBase64'])) {
    $base64String = $_POST['photoBase64'];
    
    if (preg_match('/^data:image\/(\w+);base64,/', $base64String, $type)) {
        $data = substr($base64String, strpos($base64String, ',') + 1);
        $type = strtolower($type[1]); // jpg, png, gif

        if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png', 'webp' ])) {
            jsonResponse($conn, ["success" => false, "message" => "Invalid image type"]);
            exit;
        }
        $data = base64_decode($data);
        if ($data === false) {
            jsonResponse($conn, ["success" => false, "message" => "Base64 decode failed"]);
            exit;
        }

        $uploadDir = '../assets/photos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $destPath = $uploadDir . $psnlCd . '.jpg';
        
        $im = imagecreatefromstring($data);
        if ($im !== false) {
            imagejpeg($im, $destPath, 90);
            imagedestroy($im);
            jsonResponse($conn, ["success" => true, "message" => "Base64 photo uploaded successfully"]);
        } else {
            // fallback
            file_put_contents($destPath, $data);
            jsonResponse($conn, ["success" => true, "message" => "Base64 photo saved"]);
        }
    } else {
        jsonResponse($conn, ["success" => false, "message" => "Invalid base64 string"]);
    }
} else {
    jsonResponse($conn, ["success" => false, "message" => "No valid photo data received"]);
}
