<?php

/**
 * @file
 * Contains Drupal\system\Tests\Entity\EntityTypeConstraintValidatorTest.
 */

namespace Drupal\system\Tests\Entity;

use Drupal\system\Tests\Entity\EntityUnitTestBase;
use Drupal\system\Tests\TypedData;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Tests validation constraints for EntityTypeConstraintValidator.
 */
class EntityTypeConstraintValidatorTest extends EntityUnitTestBase {

  /**
   * The typed data manager to use.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedData;

  public static $modules = array('node', 'field', 'user');

  public static function getInfo() {
    return array(
      'name' => 'Entity type constraint',
      'description' => 'Tests validation constraints for EntityTypeConstraintValidator.',
      'group' => 'Validation',
    );
  }

  public function setUp() {
    parent::setUp();
    $this->typedData = $this->container->get('typed_data_manager');
  }

  /**
   * Tests the EntityTypeConstraintValidator.
   */
  public function testValidation() {
    // Create a typed data definition with an EntityType constraint.
    $entity_type = 'node';
    $definition = DataDefinition::create('entity_reference')
      ->setConstraints(array(
        'EntityType' => $entity_type,
      )
    );

    // Test the validation.
    $node = $this->container->get('entity.manager')->getStorageController('node')->create(array('type' => 'page'));
    $typed_data = $this->typedData->create($definition, $node);
    $violations = $typed_data->validate();
    $this->assertEqual($violations->count(), 0, 'Validation passed for correct value.');

    // Test the validation when an invalid value (in this case a user entity)
    // is passed.
    $account = $this->createUser();

    $typed_data = $this->typedData->create($definition, $account);
    $violations = $typed_data->validate();
    $this->assertEqual($violations->count(), 1, 'Validation failed for incorrect value.');

    // Make sure the information provided by a violation is correct.
    $violation = $violations[0];
    $this->assertEqual($violation->getMessage(), t('The entity must be of type %type.', array('%type' => $entity_type)), 'The message for invalid value is correct.');
    $this->assertEqual($violation->getRoot(), $typed_data, 'Violation root is correct.');
    $this->assertEqual($violation->getInvalidValue(), $account, 'The invalid value is set correctly in the violation.');
  }
}