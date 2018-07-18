<?php

namespace Drupal\agweb_layout\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\views\Views;
use Drupal\Core\Cache;
use Drupal\Core\Cache\CacheBackendInterface;


/**
 * Provides a custom configuration block.
 *
 * @Block(
 *   id = "agweb_layout_block",
 *   admin_label = @Translation("Agweb Layout Block"),
 *   category = @Translation("Agweb Layout Block"),
 * )
 */

class AgwebBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $tid1 = $config['select_brand'];
    $arg1 = implode('+', $tid1);
    $tid2 = $config['select_keyword'];
    $arg2 = implode('+', $tid2);
    $args = [$arg1,$arg2];
    $view = Views::getView('search_articles');
      
    if (is_object($view)) {
      $view->setArguments($args);
      $view->setDisplay('page_1');
      $view->preExecute();
      $view->execute();
      $content = $view->render();
      $output[] = $content;
    }

    return $output;
  }

  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $query1 = \Drupal::entityQuery('taxonomy_term');
    $query1->condition('vid', "brand");
    $tids1 = $query1->execute();
    $terms1 = Term::loadMultiple($tids1); 

    $term_list1 = [];

    foreach ($terms1 as $term) {
     	$term_list1[$term->get('tid')->getString()] = $term->get('name')->getString();
    }

    $query2 = \Drupal::entityQuery('taxonomy_term');
    $query2->condition('vid', "keyword");
    $tids2 = $query2->execute();
    $terms2 = Term::loadMultiple($tids2); 

    $term_list2 = [];

    foreach ($terms2 as $term) {
     	$term_list2[$term->get('tid')->getString()] = $term->get('name')->getString();
    }     

    $form['select_brand'] = [
      '#type' => 'checkboxes',
      '#title' => t('Select Brand'),
      '#options' => $term_list1,
      '#default_value' => isset($config['select_brand']) ? $config['select_brand'] : '',	
    ];

    $form['select_keyword'] = [
      '#type' => 'checkboxes',
      '#title' => t('Select Keyword'),
      '#options' => $term_list2,	
      '#default_value' => isset($config['select_keyword']) ? $config['select_keyword'] : '',
    ];    

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();

    $this->configuration['select_brand'] = $values['select_brand'];
    $this->configuration['select_keyword'] = $values['select_keyword'];
  }

}