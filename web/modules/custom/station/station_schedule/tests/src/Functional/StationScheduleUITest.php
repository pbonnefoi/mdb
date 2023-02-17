<?php

/**
 * @file
 * Contains \Drupal\station_schedule\Tests\StationScheduleUITest.
 */
namespace Drupal\Tests\station_schedule\Functional;

use Drupal\node\Entity\Node;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests the UI for station schedules
 *
 * @group station_schedule
 */
class StationScheduleUITest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['station_schedule'];

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $programNode;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalLogin($this->rootUser);
    $this->programNode = Node::create(['type' => 'station_program', 'title' => 'A Program']);
    $this->programNode->save();
  }

  /**
   * @todo.
   */
  public function testCreate() {
    $this->drupalGet('admin/station/schedule');
    $this->clickLink('Add schedule');
    $edit = [
      'title[0][value]' => 'A title',
      'unscheduled_message[0][value]' => "We're on autopilot",
    ];
    $this->submitForm($edit, 'Save');
  }

}
