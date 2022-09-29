<?php

/**
 * Fabricates case types.
 */
class CRM_Certificate_Test_Fabricator_CaseType {

  /**
   * Fabricates new CaseType entity.
   *
   * @param array $params
   *   Case parameters.
   *
   * @return array
   *   Values of newly created Case entity.
   */
  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);
    $result = civicrm_api3(
      'CaseType',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  /**
   * Returns default Parameters.
   *
   * @var array
   */
  public static function getDefaultParams() {
    $title = md5(mt_rand());
    $activity = md5(mt_rand());
    return [
      'title' => $title,
      'name' => $title,
      'is_active' => 1,
      'sequential'   => 1,
      'weight' => 100,
      'definition' => [
        'activityTypes' => [
          ['name' => 'Meeting'],
        ],
        'activitySets' => [
          [
            'name' => $activity,
            'label' => $activity,
            'timeline' => 1,
            'activityTypes' => [
              ['name' => 'Open Case', 'status' => 'Completed'],
            ],
          ],
        ],
      ],
    ];
  }

}
