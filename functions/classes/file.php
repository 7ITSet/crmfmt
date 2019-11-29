<?
defined('_DSITE') or die('Access denied');

class FileUpload
{
  static function loadFile(
    $file,
    $folder = '0000000000',
    $upload = false,
    $_main_dir = ''
  ) {
    global $main_dir;
    $uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/temp/uploads/';
    if ($upload && file_exists($file['tmp_name'])) {
      $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
      $name = $_FILES['file']['name'];
    } elseif (file_exists($file)) {
      $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
      $name = basename($file);
    } else return null;
    //создаем временную папку id товара
    $uploadDir .= $folder;
    
    if (!file_exists($uploadDir))
      mkdir($uploadDir, 0777);
    //уникальное имя для файла
    $un = get_id('', 0, '', true);
    //оригинальное фото
    $fileoriginal = $uploadDir . '/' . $un . '.' . $ext;

    if ($upload) {
      if (file_exists($file['tmp_name'])) {
        clearstatcache();
        move_uploaded_file($file['tmp_name'], $fileoriginal);
        $file = $fileoriginal;
      }
    }

    //обработка файла
    if (file_exists($file)) {
      $file_json['file']['name'] = $name;
      $file_json['file']['id'] = $un;
      $file_json['file']['ext'] = $ext;
      $file_json['file']['path'] = '/temp/uploads/' . $folder . '/' . $un . '.' . $ext;
      return json_encode($file_json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
  }
}
