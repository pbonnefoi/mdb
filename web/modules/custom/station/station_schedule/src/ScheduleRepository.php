<?php

namespace Drupal\station_schedule;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\station_schedule\Entity\Schedule;

/**
 * @todo.
 */
class ScheduleRepository implements ScheduleRepositoryInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $scheduleItemStorage;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * ScheduleRepository constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory) {
    $this->scheduleItemStorage = $entity_type_manager->getStorage('station_schedule_item');
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentSchedule() {
    if ($current_schedule_id = $this->getCurrentScheduleId()) {
      return Schedule::load($current_schedule_id);
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentScheduleId() {
    return $this->configFactory->get('station_schedule.settings')->get('current_schedule');
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentScheduleItem() {
    $now = DatetimeHelper::deriveMinutesFromTime();
    $ids = $this->scheduleItemStorage->getQuery()
      ->condition('schedule', $this->getCurrentScheduleId())
      ->condition('start', $now, '<=')
      ->condition('finish', $now, '>')
      ->range(0, 1)
      ->sort('start')
      ->execute();
    if ($ids) {
      return $this->scheduleItemStorage->load(reset($ids));
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getNextScheduleItem() {
    $now = DatetimeHelper::deriveMinutesFromTime();
    $ids = $this->scheduleItemStorage->getQuery()
      ->condition('schedule', $this->getCurrentScheduleId())
      ->condition('start', $now, '>')
      ->range(0, 1)
      ->sort('start')
      ->execute();
    
    if (!$ids) {
      $ids = $this->scheduleItemStorage->getQuery()
        ->condition('schedule', $this->getCurrentScheduleId())
        ->condition('start', 0, '>=')
        ->range(0, 1)
        ->sort('start')
        ->execute();
    }

    if ($ids) {
      return $this->scheduleItemStorage->load(reset($ids));
    }
    return NULL;
  }

}
