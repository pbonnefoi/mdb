<?php

namespace Drupal\station;

use Drupal\views\EntityViewsData;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityType;

/**
 * Provides the views data for the node entity type.
 */
class StationEntityViewsData extends EntityViewsData {

  /**
   * The table mapping.
   *
   * @var \Drupal\Core\Entity\Sql\DefaultTableMapping
   */
  protected $tableMapping;

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $this->tableMapping = $this->storage->getTableMapping();

    $entity_type_id = $this->entityType->id();
    // Add missing reverse relationships. Workaround for core issue #2706431.
    $base_fields = $this->entityFieldManager->getBaseFieldDefinitions($entity_type_id);
    $entity_reference_fields = array_filter($base_fields, function (BaseFieldDefinition $field) {
      return $field->getType() === 'entity_reference' && !$field->isComputed();
    });
    $this->addReverseRelationships($data, $entity_reference_fields);

    return $data;
  }

  /**
   * Adds reverse relationships for the base entity reference fields.
   *
   * @param array $data
   *   The views data.
   * @param \Drupal\Core\Field\BaseFieldDefinition[] $fields
   *   The entity reference fields.
   */
  protected function addReverseRelationships(array &$data, array $fields) {
    $entity_type_id = $this->entityType->id();
    $base_table = $this->getViewsTableForEntityType($this->entityType);
    assert($this->entityType instanceof ContentEntityType);
    $revision_metadata_field_names = array_flip($this->entityType->getRevisionMetadataKeys());

    foreach ($fields as $field) {
      $target_entity_type_id = $field->getSettings()['target_type'];
      $target_entity_type = $this->entityTypeManager->getDefinition($target_entity_type_id);
      if (!($target_entity_type instanceof ContentEntityType)) {
        continue;
      }
      $target_table = $this->getViewsTableForEntityType($target_entity_type);
      $field_name = $field->getName();
      $field_storage = $field->getFieldStorageDefinition();

      $args = [
        '@label' => $target_entity_type->getSingularLabel(),
        '@entity' => $this->entityType->getLabel(),
        '@field_name' => $field_name,
      ];
      $pseudo_field_name = 'reverse__' . $entity_type_id . '__' . $field_name;
      $relationship_data = [
        'label' => $this->entityType->getLabel(),
        'group' => $target_entity_type->getLabel(),
        'entity_type' => $entity_type_id,
      ];
      if ($this->tableMapping->requiresDedicatedTableStorage($field_storage)) {
        $data[$target_table][$pseudo_field_name]['relationship'] = [
          'id' => 'entity_reverse',
          'title' => $this->t('@entity using @field_name', $args),
          'help' => $this->t('Relate each @entity with a @field_name field set to the @label.', $args),
          'base' => $base_table,
          'base field' => $this->entityType->getKey('id'),
          'field_name' => $field_name,
          'field table' => $this->tableMapping->getFieldTableName($field_name),
          'field field' => $this->tableMapping->getFieldColumnName($field_storage, 'target_id'),
        ] + $relationship_data;
      }
      elseif (isset($revision_metadata_field_names[$field_name])) {
        // Revision metadata fields exist only on the revision table, so the
        // relationship has to be to that rather than to the base table.
        $revision_table = $this->tableMapping->getRevisionTable();

        $data[$target_table][$pseudo_field_name]['relationship'] = [
          'id' => 'standard',
          'title' => $this->t('@entity revision using @field_name', $args),
          'help' => $this->t('Relate each @entity revision with a @field_name field set to the @label.', $args),
          'base' => $revision_table,
          'base field' => $this->tableMapping->getFieldColumnName($field_storage, 'target_id'),
          'relationship field' => $target_entity_type->getKey('id'),
        ] + $relationship_data;
      }
      else {
        // The data is on the base table.
        $data[$target_table][$pseudo_field_name]['relationship'] = [
          'id' => 'standard',
          'title' => $this->t('@entity using @field_name', $args),
          'help' => $this->t('Relate each @entity with a @field_name field set to the @label.', $args),
          'base' => $base_table,
          'base field' => $this->tableMapping->getFieldColumnName($field_storage, 'target_id'),
          'relationship field' => $target_entity_type->getKey('id'),
        ] + $relationship_data;
      }
    }
  }

}
