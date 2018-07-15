<?php
/**
 * @file
 * Theme setting callbacks for the nucleus theme.
 */
include_once(drupal_get_path('theme', 'forum_plus') . '/common.inc');

function forum_plus_reset_settings() {
  global $theme_key;
  variable_del('theme_' . $theme_key . '_settings');
  variable_del('theme_settings');
  $cache = &drupal_static('theme_get_setting', array());
  $cache[$theme_key] = NULL;
}

function forum_plus_form_system_theme_settings_alter(&$form, $form_state) {
  if (theme_get_setting('forum_plus_use_default_settings')) {
    forum_plus_reset_settings();
  }
  $form['#attached']['js'][] = array(
    'data' => drupal_get_path('theme', 'forum_plus') . '/js/weebpal.js',
    'type' => 'file',
  );
  $form['forum_plus']['forum_plus_version'] = array(
    '#type' => 'hidden',
    '#default' => '1.0',
  );
  forum_plus_settings_layout_tab($form);
  forum_plus_feedback_form($form);
  $form['#submit'][] = 'forum_plus_form_system_theme_settings_submit';
}

function forum_plus_settings_layout_tab(&$form) {
  global $theme_key;
  $skins = forum_plus_get_predefined_param('skins', array('' => t("Default skin")));
  $backgrounds = forum_plus_get_predefined_param('backgrounds', array('bg-default' => t("Default")));
  $layout = forum_plus_get_predefined_param('layout', array('layout-default' => t("Default Layout")));

  $form['forum_plus']['settings'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed' => FALSE,
    '#title' => t('Settings'),
    '#weight' => 0,
  );

  if (count($skins) > 1) {
    $form['forum_plus']['settings']['configs'] = array(
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
      '#title' => t('Configs'),
      '#weight' => 0,
    );
    $form['forum_plus']['settings']['configs']['skin'] = array(
      '#type' => 'select',
      '#title' => t('Skin'),
      '#default_value' => theme_get_setting('skin'),
      '#options' => $skins,
    );
  }

  $form['forum_plus']['settings']['configs']['background'] = array(
    '#type' => 'select',
    '#title' => t('Background'),
    '#default_value' => theme_get_setting('background'),
    '#options' => $backgrounds,
    '#weight' => 1,
  );

  $form['forum_plus']['settings']['configs']['layout'] = array(
    '#type' => 'select',
    '#title' => t('Layout'),
    '#default_value' => theme_get_setting('layout'),
    '#options' => $layout,
    '#weight' => -2,
  );
  $default_layout_width = (theme_get_setting('layout_width') == '') ? '1400' : theme_get_setting('layout_width');
  $form['forum_plus']['settings']['configs']['layout_width'] = array(
    '#type' => 'textfield',
    '#title' => t('Layout Width(px)'),
    '#default_value' => $default_layout_width,
    '#size' => 15,
    '#require' => TRUE,
    '#weight' => -1,
    '#states' => array(
      'visible' => array(
        'select[name="layout"]' => array(
          'value' => 'layout-boxed',
        ),
      ),
    ),
  );

  $form['theme_settings']['toggle_logo']['#default_value'] = theme_get_setting('toggle_logo');
  $form['theme_settings']['toggle_name']['#default_value'] = theme_get_setting('toggle_name');
  $form['theme_settings']['toggle_slogan']['#default_value'] = theme_get_setting('toggle_slogan');
  $form['theme_settings']['toggle_node_user_picture']['#default_value'] = theme_get_setting('toggle_node_user_picture');
  $form['theme_settings']['toggle_comment_user_picture']['#default_value'] = theme_get_setting('toggle_comment_user_picture');
  $form['theme_settings']['toggle_comment_user_verification']['#default_value'] = theme_get_setting('toggle_comment_user_verification');
  $form['theme_settings']['toggle_favicon']['#default_value'] = theme_get_setting('toggle_favicon');
  $form['theme_settings']['toggle_secondary_menu']['#default_value'] = theme_get_setting('toggle_secondary_menu');
  $form['theme_settings']['show_skins_menu'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show Skins Menu'),
    '#default_value' => theme_get_setting('show_skins_menu'),
  );
  $form['theme_settings']['loading_page'] = array(
    '#type' => 'checkbox',
    '#title' => t('Use loading'),
    '#default_value' => theme_get_setting('loading_page'),
  );

  $form['logo']['default_logo']['#default_value'] = theme_get_setting('default_logo');
  $form['logo']['settings']['logo_path']['#default_value'] = theme_get_setting('logo_path');
  $form['favicon']['default_favicon']['#default_value'] = theme_get_setting('default_favicon');
  $form['favicon']['settings']['favicon_path']['#default_value'] = theme_get_setting('favicon_path');
  $form['theme_settings']['#collapsible'] = TRUE;
  $form['theme_settings']['#collapsed'] = FALSE;
  $form['logo']['#collapsible'] = TRUE;
  $form['logo']['#collapsed'] = FALSE;
  $form['favicon']['#collapsible'] = TRUE;
  $form['favicon']['#collapsed'] = FALSE;
  $form['forum_plus']['settings']['theme_settings'] = $form['theme_settings'];
  $form['forum_plus']['settings']['logo'] = $form['logo'];
  $form['forum_plus']['settings']['favicon'] = $form['favicon'];

  unset($form['theme_settings']);
  unset($form['logo']);
  unset($form['favicon']);

  $form['forum_plus']['forum_plus_use_default_settings'] = array(
    '#type' => 'hidden',
    '#default_value' => 0,
  );
  $form['actions']['forum_plus_use_default_settings_wrapper'] = array(
    '#markup' => '<input type="submit" value="' . t('Reset theme settings') . '" class="form-submit form-reset" onclick="return Drupal.Light.onClickResetDefaultSettings();" style="float: right;">',
  );
}

function forum_plus_feedback_form(&$form) {
  $form['forum_plus']['about_forum_plus'] = array(
    '#type' => 'fieldset',
    '#title' => t('Feedback Form'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#weight' => 40,
  );

  $form['forum_plus']['about_forum_plus']['about_forum_plus_wrapper'] = array(
    '#type' => 'container',
    '#attributes' => array('class' => array('about-forum_plus-wrapper')),
  );

  $form['forum_plus']['about_forum_plus']['about_forum_plus_wrapper']['about_forum_plus_content'] = array(
    '#markup' => '<iframe width="100%" height="650" scrolling="no" class="nucleus_frame" frameborder="0" src="http://www.weebpal.com/static/feedback/"></iframe>',
  );
}

function forum_plus_form_system_theme_settings_submit($form, &$form_state) {
  if(isset($form_state['input']['skin']) && $form_state['input']['skin'] != $form_state['complete form']['forum_plus']['settings']['configs']['skin']['#default_value']) {
    setcookie('weebpal_skin', $form_state['input']['skin'], time() + 100000, base_path());
  }
}
