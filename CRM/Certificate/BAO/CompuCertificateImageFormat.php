<?php

class CRM_Certificate_BAO_CompuCertificateImageFormat extends CRM_Core_DAO_OptionValue {

  /**
   * Certificate Image Formats Option Group ID.
   * @var int
   */
  private static $_gid = NULL;

  const NAME = 'certificate_image_formats';

  /**
   * Certificate Image Format fields stored in the 'value' field of the Option Value table.
   * @var array
   */
  private static $optionValueFields = [
    'extension' => [
      'name' => 'extension',
      'type' => CRM_Utils_Type::T_STRING,
      'default' => 'JPG',
    ],
    'quality' => [
      'name' => 'quality',
      'type' => CRM_Utils_Type::T_INT,
      'default' => 7,
    ],
    'height' => [
      'name' => 'height',
      'type' => CRM_Utils_Type::T_FLOAT,
      'default' => 500,
    ],
    'width' => [
      'name' => 'width',
      'type' => CRM_Utils_Type::T_FLOAT,
      'default' => 500,
    ],
  ];

  /**
   * Get Option Group ID for Image Formats.
   *
   * @return int
   *   Group ID (null if Group ID doesn't exist)
   * @throws CRM_Core_Exception
   */
  private static function _getGid() {
    if (!self::$_gid) {
      self::$_gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', self::NAME, 'id', 'name');
      if (!self::$_gid) {
        throw new CRM_Core_Exception(ts('Certificate Image Format Option Group doesn\'t exist.'));
      }
    }
    return self::$_gid;
  }

  /**
   * Add ordering fields to certificate image formats.
   *
   * @param array $list
   *  Array of Certificate Image Format Options.
   * @param string $returnURL
   *   URL of page calling this function.
   */
  public static function addOrder(&$list, $returnURL) {
    $filter = "option_group_id = " . self::_getGid();
    CRM_Utils_Weight::addOrder($list, 'CRM_Core_DAO_OptionValue', 'id', $returnURL, $filter);
  }

  /**
   * Returns supported image extensions.
   *
   * @return array
   *   array of extensions
   */
  public static function getSupportedExtensions() {
    return [
      'jpg' => ts('JPG'),
      'png' => ts('PNG'),
    ];
  }

  /**
   * Get list of Image Formats.
   *
   * @param bool $namesOnly
   *   Return simple list of names.
   *
   * @return array
   *   (reference)   Image Format list
   */
  public static function &getList($namesOnly = FALSE) {
    static $list = [];
    if (self::_getGid()) {
      // get saved Image Formats from Option Value table
      $dao = new CRM_Core_DAO_OptionValue();
      $dao->option_group_id = self::_getGid();
      $dao->is_active = 1;
      $dao->orderBy('weight');
      $dao->find();
      while ($dao->fetch()) {
        if ($namesOnly) {
          $list[$dao->id] = $dao->name;
        }
        else {
          CRM_Core_DAO::storeValues($dao, $list[$dao->id]);
        }
      }
    }
    return $list;
  }

  /**
   * Save the Image Page Format in the DB.
   *
   * @param array $values associative array of name/value pairs
   * @param int $id
   *   Id of the database record (null = new record).
   * @throws CRM_Core_Exception
   */
  public function saveImageFormat(&$values, $id = NULL) {
    // get the Option Group ID for Image Formats (create one if it doesn't exist).
    $group_id = self::_getGid();

    // clear other default if this is the new default Image Format.
    if ($values['is_default']) {
      $query = "UPDATE civicrm_option_value SET is_default = 0 WHERE option_group_id = $group_id";
      CRM_Core_DAO::executeQuery($query);
    }
    if ($id) {
      // fetch existing record.
      $this->id = $id;
      if ($this->find()) {
        $this->fetch();
      }
    }
    // copy the supplied form values to the corresponding Option Value fields in the base class.
    foreach ($this->fields() as $name => $field) {
      $this->$name = trim(CRM_Utils_Array::value($name, $values, $this->$name));
      if (empty($this->$name)) {
        $this->$name = 'null';
      }
    }
    $this->id = $id;
    $this->option_group_id = $group_id;
    $this->label = $this->name;
    $this->is_active = 1;

    // serialize Image Format fields into a single string to store in the 'value' column of the Option Value table.
    $v = json_decode($this->value, TRUE);
    foreach (self::$optionValueFields as $name => $field) {
      $v[$name] = self::getValue($name, $values, CRM_Utils_Array::value($name, $v));
    }
    $this->value = json_encode($v);

    // make sure serialized array will fit in the 'value' column
    $attribute = CRM_Core_DAO::getAttribute('CRM_Certificate_BAO_CompuCertificateImageFormat', 'value');
    if (strlen($this->value) > $attribute['maxlength']) {
      throw new CRM_Core_Exception(ts('Image Format does not fit in database.'));
    }
    $this->save();
  }

  /**
   * Get Image Format field from associative array.
   *
   * @param string $field
   *   Name of a Image Format field.
   * @param array $values associative array of name/value pairs containing
   *                                           Image Format field selections
   *
   * @param null $default
   *
   * @return value
   */
  public static function getValue($field, &$values, $default = NULL) {
    if (array_key_exists($field, self::$optionValueFields)) {
      switch (self::$optionValueFields[$field]['type']) {
        case CRM_Utils_Type::T_INT:
          return (int) CRM_Utils_Array::value($field, $values, $default);

        case CRM_Utils_Type::T_FLOAT:
          // Round float values to three decimal places and trim trailing zeros.
          // Add a leading zero to values less than 1.
          $f = sprintf('%05.3f', $values[$field]);
          $f = rtrim($f, '0');
          $f = rtrim($f, '.');
          return (float) (empty($f) ? '0' : $f);
      }
      return CRM_Utils_Array::value($field, $values, $default);
    }
    return $default;
  }

  /**
   * Delete a Image Format.
   *
   * @param int $id
   *   ID of the Image Format to be deleted.
   * @throws CRM_Core_Exception
   */
  public static function del($id) {
    if ($id) {
      $dao = new CRM_Core_DAO_OptionValue();
      $dao->id = $id;
      if ($dao->find(TRUE)) {
        if ($dao->option_group_id == self::_getGid()) {
          $dao->delete();
          return;
        }
      }
    }
    throw new CRM_Core_Exception(ts('Invalid value passed to delete function.'));
  }


  /**
   * Retrieve DB object based on input parameters.
   *
   * It also stores all the retrieved values in the default array.
   *
   * @param array $params
   *   (reference ) an assoc array of name/value pairs.
   * @param array $values
   *   (reference ) an assoc array to hold the flattened values.
   *
   * @return CRM_Core_DAO_OptionValue
   */
  public static function retrieve(&$params, &$values) {
    $optionValue = new CRM_Core_DAO_OptionValue();
    $optionValue->copyValues($params);
    $optionValue->option_group_id = self::_getGid();
    if ($optionValue->find(TRUE)) {
      $values = json_decode($optionValue->value, TRUE);
      foreach (self::$optionValueFields as $name => $field) {
        if (!isset($values[$name])) {
          $values[$name] = $field['default'];
        }
      }

      CRM_Core_DAO::storeValues($optionValue, $values);
      return $optionValue;
    }
    return NULL;
  }
}
