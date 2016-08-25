<?php

namespace Drupal\phantomjs_capture\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class PhantomJSCaptureTestForm
 *
 * Provide a form to test the output of PhantomJS Capture.
 *
 * @package Drupal\phantomjs_capture\Form
 */
class PhantomJSCaptureTestForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'phantomjs_capture_test';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => t('URL'),
      '#description' => t('Absolute URL to the page that you want to capture (it must to be a complete URL with http://).'),
      '#default_value' => 'http://www.google.com',
    );

    $form['format'] = array(
      '#type' => 'select',
      '#title' => 'File format',
      '#options' => array(
        '.png' => 'png',
        '.jpg' => 'jpg',
        '.pdf' => 'pdf',
      ),
    );

    $form['result'] = array(
      '#prefix' => '<div id="capture-result">',
      '#suffix' => '</div>',
      '#markup' => '',
    );

    $form['submit'] = array(
      '#type' => 'button',
      '#value' => t('Capture'),
      '#ajax' => array(
        'callback' => array($this, 'capture'),
        'wrapper' => 'capture-result',
        'method' => 'replace',
        'effect' => 'fade',
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // empty
  }

  public function capture(array &$form, FormStateInterface $form_state) {
    $config = $this->config('phantomjs_capture.settings');
    $values = $form_state->getValues();

    // Build urls and destination.
    $url = $values['url'];
    $file = 'capture_test' . $values['format'];
    $destination = \Drupal::config('system.file')->get('default_scheme') . '://' . $config->get('destination') . '/test/' . REQUEST_TIME;
    $file_url = file_create_url($destination . '/' . $file);

    if (!file_prepare_directory($destination, FILE_CREATE_DIRECTORY)) {
      $form_state->setError($form['destination'], t('The path was not writeable or could not be created.'));
    }

    if (phantomjs_capture_screen($url, $destination, $file) && file_exists($file_url)) {
      $output = $this->t('The address entered could not be retrieved, or phantomjs could not perform the action requested.');
    } else {
      $output = $this->t('The file has been generated! You can view it <a href=":url">here</a>', array(':url' => $file_url));
    }

    return array(
      'phantomjs_capture_test' => array(
        'result' => array(
          '#prefix' => '<div id="capture-result">',
          '#suffix' => '</div>',
          '#markup' => '<p>' . $output . '</p>',
        ),
      ),
    );
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    // empty
  }
}