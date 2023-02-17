<?php

namespace Drupal\station_schedule\Entity;

use Drupal\station\StationEntityViewsData;

/**
 * Provides the views data for the node entity type.
 */
class ScheduleItemViewsData extends StationEntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    return $data;
  }

}
