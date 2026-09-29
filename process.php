<?php
// process.php: обробка завантаження зображень

const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2 МБ

$allowedExtensions = ['png', 'jpg', 'jpeg'];
$allowedMimeTypes = ['image/png', 'image/jpeg'];
$uploadDirectory = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';

function showResult(string $title, string $message, bool $success = false, string $downloadUrl = ''): void
{
    $safeTitle = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
    $safeMessage = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');
    $statusClass = $success ? 'success' : 'error';

    echo '<!DOCTYPE html>';
    echo '<html lang="uk">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . $safeTitle . '</title>';
    echo '<style>
        body{margin:0;min-height:100vh;display:grid;place-items:center;padding:20px;box-sizing:border-box;font-family:Arial,sans-serif;background:#f1f5f9;color:#172033}
        .card{width:min(560px,100%);padding:28px;box-sizing:border-box;border:1px solid #dbe3ee;border-radius:18px;background:#fff;box-shadow:0 14px 40px rgba(15,23,42,.1)}
        h1{margin-top:0}.success{color:#15803d}.error{color:#b91c1c}.details{padding:14px;border-radius:10px;background:#f8fafc;line-height:1.6}
        a{display:inline-block;margin:12px 10px 0 0;padding:10px 16px;border-radius:9px;color:#fff;background:#2563eb;text-decoration:none;font-weight:700}
        a.secondary{color:#1d4ed8;background:#eff6ff}
    </style>';
    echo '</head><body><main class="card">';
    echo '<h1 class="' . $statusClass . '">' . $safeTitle . '</h1>';
    echo '<div class="details">' . nl2br($safeMessage) . '</div>';

    if ($success && $downloadUrl !== '') {
        $safeUrl = htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8');
        echo '<a href="' . $safeUrl . '" download>Завантажити файл</a>';
    }

    echo '<a class="secondary" href="index.html">Повернутися на головну</a>';
    echo '<a class="secondary" href="list.php">Переглянути файли</a>';
    echo '</main></body></html>';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    showResult('Помилка', 'Сторінка приймає лише POST-запити.');
}

if (!isset($_FILES['uploaded_file'])) {
    showResult('Помилка', 'У запиті відсутній файл із полем uploaded_file.');
}

$file = $_FILES['uploaded_file'];

$uploadErrors = [
    UPLOAD_ERR_INI_SIZE => 'Розмір файла перевищує обмеження upload_max_filesize у PHP.',
    UPLOAD_ERR_FORM_SIZE => 'Розмір файла перевищує обмеження HTML-форми.',
    UPLOAD_ERR_PARTIAL => 'Файл завантажено лише частково.',
    UPLOAD_ERR_NO_FILE => 'Файл не було вибрано.',
    UPLOAD_ERR_NO_TMP_DIR => 'На сервері відсутня тимчасова директорія.',
    UPLOAD_ERR_CANT_WRITE => 'Сервер не зміг записати файл на диск.',
    UPLOAD_ERR_EXTENSION => 'Завантаження зупинено розширенням PHP.'
];

if ($file['error'] !== UPLOAD_ERR_OK) {
    $message = $uploadErrors[$file['error']] ?? 'Невідома помилка завантаження.';
    showResult('Помилка завантаження', $message);
}

if (!is_uploaded_file($file['tmp_name'])) {
    showResult('Помилка перевірки', 'Файл не був завантажений через HTTP POST.');
}

if ($file['size'] <= 0) {
    showResult('Помилка', 'Завантажений файл порожній.');
}

if ($file['size'] > MAX_FILE_SIZE) {
    showResult('Завеликий файл', 'Максимально допустимий розмір становить 2 МБ.');
}

$originalName = basename($file['name']);
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions, true)) {
    showResult('Недопустиме розширення', 'Дозволені лише файли PNG, JPG та JPEG.');
}

if (!class_exists('finfo')) {
    showResult('Помилка сервера', 'У PHP не ввімкнено розширення fileinfo.');
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($file['tmp_name']);

if (!in_array($mimeType, $allowedMimeTypes, true)) {
    showResult('Недопустимий тип файла', 'Фактичний MIME-тип файла: ' . (string)$mimeType . '. Дозволені лише PNG та JPEG.');
}

// Додатково перевіряємо, чи файл справді є зображенням.
if (@getimagesize($file['tmp_name']) === false) {
    showResult('Пошкоджене зображення', 'Файл неможливо розпізнати як коректне зображення.');
}

if (!is_dir($uploadDirectory)) {
    if (!mkdir($uploadDirectory, 0755, true)) {
        showResult('Помилка сервера', 'Не вдалося створити директорію uploads.');
    }
}

if (!is_writable($uploadDirectory)) {
    showResult('Помилка доступу', 'Директорія uploads недоступна для запису.');
}

$baseName = pathinfo($originalName, PATHINFO_FILENAME);
$baseName = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $baseName);
$baseName = trim((string)$baseName, '_-');

if ($baseName === '') {
    $baseName = 'image';
}

// Розширення визначається за MIME-типом, а не лише за введеною назвою.
$safeExtension = $mimeType === 'image/png' ? 'png' : 'jpg';
$storedName = $baseName . '.' . $safeExtension;
$counter = 1;

while (file_exists($uploadDirectory . DIRECTORY_SEPARATOR . $storedName)) {
    $storedName = $baseName . '_' . date('Ymd_His') . '_' . $counter . '.' . $safeExtension;
    $counter++;
}

$destination = $uploadDirectory . DIRECTORY_SEPARATOR . $storedName;

if (!move_uploaded_file($file['tmp_name'], $destination)) {
    showResult('Помилка збереження', 'Не вдалося перемістити файл у директорію uploads.');
}

$sizeKilobytes = number_format(filesize($destination) / 1024, 2, '.', '');
$message = "Файл успішно завантажено.\n"
    . "Початкове ім’я: {$originalName}\n"
    . "Збережене ім’я: {$storedName}\n"
    . "MIME-тип: {$mimeType}\n"
    . "Розмір: {$sizeKilobytes} КБ";

$downloadUrl = 'uploads/' . rawurlencode($storedName);
showResult('Завантаження успішне', $message, true, $downloadUrl);
