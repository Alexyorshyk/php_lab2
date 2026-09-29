<?php
const MAX_FILE_SIZE = 2 * 1024 * 1024;
$allowedExtensions = ['png', 'jpg', 'jpeg'];
$allowedMimeTypes = ['image/png', 'image/jpeg'];
$uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

function finishPage(string $message): void {
    echo '<!DOCTYPE html><html lang="uk"><head><meta charset="UTF-8"><title>Результат</title></head><body>';
    echo $message;
    echo '<p><a href="index.html">На головну</a> | <a href="list.php">Список файлів</a></p></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['uploaded_file'])) {
    finishPage('<h1>Помилка</h1><p>Файл не передано.</p>');
}
$file = $_FILES['uploaded_file'];
if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
    finishPage('<h1>Помилка</h1><p>Файл не був успішно завантажений через HTTP POST.</p>');
}
if ($file['size'] > MAX_FILE_SIZE) {
    finishPage('<h1>Помилка</h1><p>Розмір файлу перевищує 2 МБ.</p>');
}
$originalName = basename($file['name']);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions, true)) {
    finishPage('<h1>Помилка</h1><p>Дозволені лише файли PNG, JPG та JPEG.</p>');
}
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);
if (!in_array($mimeType, $allowedMimeTypes, true)) {
    finishPage('<h1>Помилка</h1><p>Фактичний тип файлу не є дозволеним зображенням.</p>');
}
if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true)) {
    finishPage('<h1>Помилка</h1><p>Не вдалося створити папку uploads.</p>');
}
$baseName = pathinfo($originalName, PATHINFO_FILENAME);
$baseName = preg_replace('/[^a-zA-Z0-9_-]/u', '_', $baseName);
if ($baseName === '') $baseName = 'image';
$newName = $baseName . '.' . $extension;
$counter = 1;
while (file_exists($uploadDir . DIRECTORY_SEPARATOR . $newName)) {
    $newName = $baseName . '_' . date('Ymd_His') . '_' . $counter . '.' . $extension;
    $counter++;
}
$destination = $uploadDir . DIRECTORY_SEPARATOR . $newName;
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    finishPage('<h1>Помилка</h1><p>Не вдалося зберегти файл.</p>');
}
$safeName = htmlspecialchars($newName, ENT_QUOTES, 'UTF-8');
$safeType = htmlspecialchars($mimeType, ENT_QUOTES, 'UTF-8');
$sizeKb = number_format(filesize($destination) / 1024, 2, '.', '');
finishPage("<h1>Файл успішно завантажено</h1><ul><li>Ім'я: {$safeName}</li><li>Тип: {$safeType}</li><li>Розмір: {$sizeKb} КБ</li></ul><p><a href=\"uploads/" . rawurlencode($newName) . "\" download>Завантажити файл</a></p>");
?>
