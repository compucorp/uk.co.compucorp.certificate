<?php

trait CRM_Certificate_Test_Helper_Certificate {

  public function provideCertificateDateData(): array {
    return [
      'start_date and end_date is null' => [
        'startDate' => NULL,
        'endDate' => NULL,
        'valid' => TRUE,
      ],
      'start_date is null and end_date was 3 days ago' => [
        'startDate' => NULL,
        'endDate' => $this->getDate("- 3 days"),
        'valid' => FALSE,
      ],
      'start_date is null and end_date is 3 days from now' => [
        'startDate' => NULL,
        'endDate' => $this->getDate("3 days"),
        'valid' => TRUE,
      ],
      'start_date is 3 days from now and end_date is null' => [
        'startDate' => $this->getDate("3 days"),
        'endDate' => NULL,
        'valid' => FALSE,
      ],
      'start_date was 3 days ago and end_date is null' => [
        'startDate' => $this->getDate("- 3 days"),
        'endDate' => NULL,
        'valid' => TRUE,
      ],
      'start_date was 3 days ago and end_date is 10 days from now' => [
        'startDate' => $this->getDate("- 3 days"),
        'endDate' => $this->getDate("10 days"),
        'valid' => TRUE,
      ],
      'start_date was 10 days ago and end_date is 3 days ago' => [
        'startDate' => $this->getDate("- 10 days"),
        'endDate' => $this->getDate("- 3 days"),
        'valid' => FALSE,
      ],
      'start_date is 10 days from now and end_date is 20 days from now' => [
        'startDate' => $this->getDate("10 days"),
        'endDate' => $this->getDate("20 days"),
        'valid' => FALSE,
      ],
    ];
  }

  public function getDate($from = "0 days") {
    return date('Y-m-d', strtotime(date('Y-m-d') . " $from"));
  }

}
