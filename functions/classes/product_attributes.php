<?php
defined ('_DSITE') or die ('Access denied');
include_once($_SERVER['DOCUMENT_ROOT'].'/../functions/ccdb.php'); 
include_once($_SERVER['DOCUMENT_ROOT'].'/../functions/system.php'); 
$sql=new sql;
//define ('_DSITE',1);
class ProductAttributes
{
  const CHECKBOX = 'CB';
  const LISTBOX = 'LB';

  const TYPE_STRING = 'S';
  const TYPE_NUMBER = 'N';
  const TYPE_FILE = 'F';
  const TYPE_INTERVAL = 'I';
  const TYPE_LIST = 'L';
  const TYPE_HTML = 'H';

  public static function getMap()
  {
    return array(
      'PROPERTY_TYPES' => array(
        'types' => array(
          array(
            'name' => 'Строка',
            'value' => self::TYPE_STRING
          ),
          array(
            'name' => 'Число',
            'value' => self::TYPE_NUMBER
          ),
          array(
            'name' => 'Файл',
            'value' => self::TYPE_FILE
          ),
          array(
            'name' => 'Список',
            'value' => self::TYPE_LIST
          ),
          array(
            'name' => 'Интервал',
            'value' => self::TYPE_INTERVAL
          ),
          array(
            'name' => 'HTML',
            'value' => self::TYPE_HTML
          )
        ),
        'default_value' => self::TYPE_STRING,
      ),
			'WITH_DESCRIPTION' => array(
				'values' => array(true, false),
				'default_value' => false
			),
			'SEARCHABLE' => array(
				'values' => array(true, false),
				'default_value' => false
			),
			'FILTRABLE' => array(
				'values' => array(true, false),
				'default_value' => false
			),
			'IS_REQUIRED' => array(
				'values' => array(true, false),
				'default_value' => false
			)
    );
  }

  public static function getAttributesListById($id)
  {
    global $sql;
    
    $q = 'SELECT * FROM `formetoo_main`.`m_attributes_enum` WHERE `attribute_id`='.$id.';';

    $result = $sql->query($q);
    
    return $result;
  }
}
