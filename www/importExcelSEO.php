<?
define('_DSITE', 1);
require_once('../functions/main.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (is_uploaded_file($_FILES["filename"]["tmp_name"])) {
    move_uploaded_file($_FILES["filename"]["tmp_name"], "uploads/" . $_FILES["filename"]["name"]);
    $fileName = "uploads/" . $_FILES["filename"]["name"];
    uploadExcelSEOFile($fileName);
    header('Location: /');
  } else {
    echo ("Ошибка загрузки файла");
  }
} else { ?>

  <!doctype html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Импорт excel-документа</title>
  </head>

  <body>
    <h2>
      <p><b> Форма для загрузки excel-документа</b></p>
    </h2>
    <form action="" method="post" enctype="multipart/form-data">
      <input type="file" name="filename"><br>
      <input type="submit" value="Загрузить"><br>
    </form>
  </body>

  </html>
<? }
