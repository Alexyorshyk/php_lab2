<?php
// text.php: запис тексту у файл log.txt і показ його вмісту

$logFile = __DIR__ . DIRECTORY_SEPARATOR . 'log.txt';
$message = '';
$messageClass = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $text = trim($_POST['text'] ?? '');

    if ($text === '') {
        $message = 'Введіть текст перед відправленням форми.';
        $messageClass = 'error';
    } else {
        $record = '[' . date('Y-m-d H:i:s') . '] ' . $text . PHP_EOL;
        $result = file_put_contents($logFile, $record, FILE_APPEND | LOCK_EX);

        if ($result === false) {
            $message = 'Не вдалося записати дані у log.txt. Перевірте права на запис.';
            $messageClass = 'error';
        } else {
            $message = 'Текст успішно записано у log.txt.';
            $messageClass = 'success';
        }
    }
}

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    if ($content === false) {
        $content = 'Не вдалося прочитати файл log.txt.';
    } elseif ($content === '') {
        $content = 'Журнал поки що порожній.';
    }
} else {
    $content = 'Файл log.txt ще не створено.';
}
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Текстовий журнал</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            padding: 24px;
            font-family: Arial, sans-serif;
            color: #172033;
            background: #f1f5f9;
        }
        .card {
            width: min(760px, 100%);
            margin: 30px auto;
            padding: 28px;
            border: 1px solid #dbe3ee;
            border-radius: 16px;
            background: #ffffff;
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.08);
        }
        h1 { margin-top: 0; }
        .message {
            padding: 12px 14px;
            border-radius: 9px;
            font-weight: 700;
        }
        .success { color: #166534; background: #dcfce7; }
        .error { color: #991b1b; background: #fee2e2; }
        pre {
            min-height: 160px;
            padding: 16px;
            overflow-wrap: anywhere;
            white-space: pre-wrap;
            border: 1px solid #dbe3ee;
            border-radius: 10px;
            background: #f8fafc;
            font-family: Consolas, monospace;
            line-height: 1.5;
        }
        a {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 15px;
            border-radius: 8px;
            color: #ffffff;
            background: #2563eb;
            font-weight: 700;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <main class="card">
        <h1>Вміст файла log.txt</h1>

        <?php if ($message !== ''): ?>
            <p class="message <?= htmlspecialchars($messageClass, ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
            </p>
        <?php endif; ?>

        <pre><?= htmlspecialchars($content, ENT_QUOTES, 'UTF-8') ?></pre>
        <a href="index.html">Повернутися на головну</a>
    </main>
</body>
</html>
