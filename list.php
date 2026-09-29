<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Лабораторна робота №2</title>
  <style>
    body {font-family: Arial, sans-serif; max-width: 760px; margin: 40px auto; padding: 0 16px; background:#f5f7fb; color:#172033;}
    section {background:#fff; padding:24px; margin-bottom:20px; border-radius:12px; box-shadow:0 4px 18px #0001;}
    h1,h2 {color:#174ea6;} label {display:block; margin:12px 0 6px;} input,textarea,button {font:inherit;}
    textarea {width:100%; min-height:120px; box-sizing:border-box;} button {margin-top:12px; padding:10px 16px; border:0; border-radius:8px; background:#174ea6; color:white; cursor:pointer;}
    a {color:#174ea6;}
  </style>
</head>
<body>
  <h1>Робота з файлами у PHP</h1>
  <section>
    <h2>Завантаження зображення</h2>
    <form action="process.php" method="post" enctype="multipart/form-data">
      <label for="uploaded_file">Оберіть PNG, JPG або JPEG (до 2 МБ):</label>
      <input type="file" id="uploaded_file" name="uploaded_file" accept=".png,.jpg,.jpeg,image/png,image/jpeg" required>
      <button type="submit">Завантажити</button>
    </form>
  </section>
  <section>
    <h2>Запис тексту у log.txt</h2>
    <form action="text.php" method="post">
      <label for="text">Текст:</label>
      <textarea id="text" name="text" required></textarea>
      <button type="submit">Записати та показати журнал</button>
    </form>
  </section>
  <p><a href="list.php">Переглянути список завантажених файлів</a></p>
</body>
</html>
