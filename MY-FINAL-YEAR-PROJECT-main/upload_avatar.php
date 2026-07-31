<?php
// upload_avatar.php
require_once __DIR__ . '/config/Database.php';

function handleProfilePicUpload(PDO $db, int $userId): ?string {
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $fileTmp  = $_FILES['profile_pic']['tmp_name'];
        $fileName = $_FILES['profile_pic']['name'];
        $fileExt  = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($fileExt, $allowed, true)) {
            $uploadDir = __DIR__ . '/uploads/profiles/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $newFileName = 'Avatar_User_' . $userId . '_' . time() . '.' . $fileExt;
            $destPath    = $uploadDir . $newFileName;

            if (move_uploaded_file($fileTmp, $destPath)) {
                $stmt = $db->prepare("UPDATE users SET profile_pic = :pic WHERE user_id = :uid");
                $stmt->execute([':pic' => $newFileName, ':uid' => $userId]);

                $_SESSION['profile_pic'] = $newFileName;
                return $newFileName;
            }
        }
    }
    return null;
}