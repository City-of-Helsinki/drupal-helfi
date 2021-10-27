<?php

namespace Drupal\custom_rest_menu_link\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form with examples on how to use batch api.
 */
class SyncAtomIdForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sync_atom_ids_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['schools_daycare'] = [
      '#type' => 'radios',
      '#title' => t('Select which menu to sync'),
      '#options' => [
        'schools' => t('Schools'),
        'daycare-centers' => t('Daycare Centers'),
      ],
      '#default_value' => 'schools',
    ];
    $form['sync_atom_id'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Sync all Atom IDs with parents'),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $batch = array(
      'title' => t('Verifying menu links...'),
      'operations' => [],
      'init_message'     => t('Commencing'),
      'progress_message' => t('Processed @current out of @total.'),
      'error_message'    => t('An error occurred during processing'),
      'finished' => 'sync_atomid_finished',
    );
    $tree = \Drupal::menuTree()->load($form_state->getValue('schools_daycare'), new \Drupal\Core\Menu\MenuTreeParameters());

    if (count($this->loadMenu($tree)) <= 1) {
      return ['#markup' => 'No links found!'];
    }

    foreach ($this->loadMenu($tree) as $menu_item) {
      $batch['operations'][] = ['sync_atom_ids_batch',[$menu_item]];
    }

    batch_set($batch);
  }

  /**
   * Load menu tree .
   */
  function loadMenu($tree) {
    $menu = [];
    foreach ($tree as $item) {
      if($item->link->isEnabled()) {
        if ($item->hasChildren) {
          $menu = $this->loadMenu($item->subtree);
        }
        if ($item->link->getUrlObject()->isRouted() == TRUE) {
          $menu[] = [
            'title' => $item->link->getTitle(),
            'pluginid' => $item->link->getPluginId(),
            'nodeid' => $item->link->getUrlObject()->getRouteParameters()['node'],
            'node_atom_id' => \Drupal::entityTypeManager()->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_atomid->value,
            'node_parent_atom_id' => \Drupal::entityTypeManager()->getStorage('node')->load($item->link->getUrlObject()->getRouteParameters()['node'])->field_parent_atomid->value,           ];
        }
      }
    }
    return $menu;
  }
}
