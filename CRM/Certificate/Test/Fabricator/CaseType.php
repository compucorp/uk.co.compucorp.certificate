<?php

/**
 * Fabricates case types.
 */
class CRM_Certificate_Test_Fabricator_CaseType {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);
    $result = civicrm_api3(
      'CaseType',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  public static function getDefaultParams() {
    $title = md5(mt_rand());
    $activityTypes = md5(mt_rand());
    $activity = md5(mt_rand());
    return [
      'title' => $title,
      'name' => $title,
      'is_active' => 1,
      'sequential'   => 1,
      'weight' => 100,
      'definition' => [
        'activityTypes' => [
          ['name' => $activityTypes],
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
