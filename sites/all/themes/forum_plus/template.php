<?php
include_once(drupal_get_path('theme', 'forum_plus') . '/common.inc');

function forum_plus_theme() {
  $items = array();
  $items['render_panel'] = array(
    "variables" => array(
      'page' => array(),
      'panels_list' => array(),
      'panel_regions_width' => array(),
    ),
    'preprocess functions' => array(
      'forum_plus_preprocess_render_panel'
    ),
    'template' => 'panel',
    'path' => drupal_get_path('theme', 'forum_plus') . '/tpl',
  );
  return $items;
}

function forum_plus_process_html(&$vars) {
  $current_skin = theme_get_setting('skin');
  if (isset($_COOKIE['weebpal_skin'])) {
    $current_skin = $_COOKIE['weebpal_skin'];
  }
  if (!empty($current_skin) && $current_skin != 'default') {
    $vars['classes'] .= " skin-$current_skin";
  }

  $current_layout = theme_get_setting('layout');
  if (isset($_COOKIE['weebpal_layout'])) {
    $current_layout = $_COOKIE['weebpal_layout'];
  }

  $current_background = theme_get_setting('background');
  if (isset($_COOKIE['weebpal_background'])) {
    $current_background = $_COOKIE['weebpal_background'];
  }
  if (!empty($current_background) && $current_layout == 'layout-boxed') {
    $vars['classes'] .= ' ' . $current_background;
  }

  if (user_access('access contextual links')) {
    $vars['classes'] .= ' show-contextual-links';
  }
}

function forum_plus_preprocess_html(&$vars) {
  $node_id = drupal_lookup_path('source','404-page');
  if(!empty($node_id)) {
    $parts = explode("/", $node_id);
    $n_id = false;
    if(count($parts) > 1) {
      $n_id = $parts[1];
    }
    if(in_array("html__node__$n_id", $vars['theme_hook_suggestions'])) {
      $vars['theme_hook_suggestions'][] = 'html__404';
    }
  }
  if (count($vars['theme_hook_suggestions']) == 1) {
    if (isset($vars['page']['content']['system_main']['main']['#markup']) &&
        trim($vars['page']['content']['system_main']['main']['#markup']) == t('The requested page "@path" could not be found.', array('@path' => request_uri()))) {
      $vars['theme_hook_suggestions'][] = 'html__404';
    }
  }
}

function forum_plus_preprocess_page(&$vars) {
  global $theme_key;
  $vars['page_css'] = '';

  $vars['regions_width'] = forum_plus_regions_width($vars['page']);
  $panel_regions = forum_plus_panel_regions();
  if (count($panel_regions)) {
    foreach ($panel_regions as $panel_name => $panels_list) {
      $panel_markup = theme("render_panel", array(
        'page' => $vars['page'],
        'panels_list' => $panels_list,
        'regions_width' => $vars['regions_width'],
      ));
      $panel_markup = trim($panel_markup);
      $vars['page'][$panel_name] = empty($panel_markup) ? FALSE : array('content' => array('#markup' => $panel_markup));
    }
  }

  if (isset($vars['node']) && $vars['node']->type != 'page' && !in_array('page__node__delete', $vars['theme_hook_suggestions'])) {
    $result = db_select('node_type', NULL, array('fetch' => PDO::FETCH_ASSOC))
    ->fields('node_type', array('name'))
    ->condition('type', $vars['node']->type)
    ->execute()->fetchField();
    $vars['title'] = $result;
  }

  $current_skin = theme_get_setting('skin');
  if (isset($_COOKIE['weebpal_skin'])) {
    $current_skin = $_COOKIE['weebpal_skin'];
  }

  $layout_width = (theme_get_setting('layout_width') == '')
                  ? theme_get_setting('layout_width_default')
                  : theme_get_setting('layout_width');
  $vars['page']['show_skins_menu'] = $show_skins_menu = theme_get_setting('show_skins_menu');


    $current_layout = theme_get_setting('layout');
    if (isset($_COOKIE['weebpal_layout'])) {
      $current_layout = $_COOKIE['weebpal_layout'];
    }

    if ($current_layout == 'layout-boxed') {
      $vars['page_css'] = 'style="max-width:' . $layout_width . 'px;margin: 0 auto;" class="boxed"';
    }
    $data = array(
      'layout_width' => $layout_width,
      'current_layout' => $current_layout
    );

  if($show_skins_menu) {   
    $skins_menu = theme_render_template(drupal_get_path('theme', 'forum_plus') . '/tpl/skins-menu.tpl.php', $data);
    $vars['page']['show_skins_menu'] = $skins_menu;
  }

  $vars['page']['weebpal_skin_classes'] = !empty($current_skin) ? ($current_skin . "-skin") : "";
  if (!empty($current_skin) && $current_skin != 'default' && theme_get_setting("default_logo") && theme_get_setting("toggle_logo")) {
    $vars['logo'] = file_create_url(drupal_get_path('theme', $theme_key)) . "/css/colors/" . $current_skin . "/images/logo.png";
  }

  ////////

  $skin = theme_get_setting('skin');
  if (isset($_COOKIE['weebpal_skin'])) {
    $skin = $_COOKIE['weebpal_skin'] == 'default' ? '' : $_COOKIE['weebpal_skin'];
  }
  if (!empty($skin) && file_exists(drupal_get_path('theme', $theme_key) . "/css/colors/" . $skin . "/style.css")) {
    $css = drupal_add_css(drupal_get_path('theme', $theme_key) . "/css/colors/" . $skin . "/style.css", array(
      'group' => CSS_THEME,
    ));
  }
}

function forum_plus_preprocess_render_panel(&$variables) {
  $page = $variables['page'];
  $panels_list = $variables['panels_list'];
  $regions_width = $variables['regions_width'];
  $variables = array();
  $variables['page'] = array();
  $variables['panel_width'] = $regions_width;
  $variables['panel_classes'] = array();
  $variables['panels_list'] = $panels_list;
  $is_empty = TRUE;
  $panel_keys = array_keys($panels_list);

  foreach ($panels_list as $panel) {
    $variables['page'][$panel] = $page[$panel];
    $panel_width = $regions_width[$panel];
    if (render($page[$panel])) {
      $is_empty = FALSE;
    }
    $classes = array("panel-column");
    //$classes[] = "col-lg-$panel_width";
    //$classes[] = "col-md-$panel_width";
	$classes[] = "col-sm-$panel_width";
    //$classes[] = "col-sm-12";
    //$classes[] = "col-xs-12";
    $classes[] = str_replace("_", "-", $panel);
    $variables['panel_classes'][$panel] = implode(" ", $classes);
  }
  $variables['empty_panel'] = $is_empty;
}

function forum_plus_css_alter(&$css) {
}

function forum_plus_preprocess_maintenance_page(&$vars) {
}

function forum_plus_preprocess_views_view_fields(&$vars) {
  $view = $vars['view'];
  foreach ($vars['fields'] as $id => $field) {
    if(isset($field->handler->field_info) && $field->handler->field_info['type'] === 'image') {
      $prefix = $field->wrapper_prefix;
      if(strpos($prefix, "views-field ") !== false) {
        $parts = explode("views-field ", $prefix);
        $type = str_replace("_", "-", $field->handler->field_info['type']);
        $prefix = implode("views-field views-field-type-" . $type . " ", $parts);
      }
      $vars['fields'][$id]->wrapper_prefix = $prefix;
    }
  }
}

function forum_plus_node_view_alter(&$build) {
  if ($build['#view_mode'] =='teaser' && $build['#bundle'] == 'product') {
    unset($build['links']['comment']);
  }
  if (isset($build['links']['comment']['#links']['comment-new-comments'])) {
    unset($build['links']['comment']['#links']['comment-new-comments']);
  }
    
  //var_dump($build['links']['comment']);
}

function _get_predefined_param($param, $pre_array = array(), $suf_array = array()) {
  global $theme_key;
  $theme_data = list_themes();
  $result = isset($theme_data[$theme_key]->info[$param]) ? $theme_data[$theme_key]->info[$param] : array();
  return $pre_array + $result + $suf_array;
}

function forum_plus_preprocess_node(&$vars) {
  $skins = _get_predefined_param('skins', array('default' => t("Default skin")));
  $alias = '';
  if (arg(0) == 'node') {
    $alias = drupal_get_path_alias('node/' . arg(1));
  }
  foreach ($skins as $key => $val) {

    if (strpos($alias, 'skins/' . $key) !== FALSE && (!isset($_COOKIE['weebpal_skin']) || $_COOKIE['weebpal_skin'] != $key)) {
      setcookie('weebpal_skin', $key, time() + 100000, '/');
      //header('Location: ' . $vars['node_url']);
      drupal_goto("");
    }
  }
  $node = $vars['node'];
  if (variable_get('node_submitted_' . $node->type, TRUE)) {
    $vars['submitted'] = t(
      '<span class="author">By !username</span> <span class="created">!datetime</span>',
      array('!username' => $vars['name'], '!datetime' => date('F d, Y', $vars['created']))
    );
  }


if ($vars['type'] == 'blog' || $vars['type'] == 'article' ) {
    $vars['title_link'] = FALSE;
    if (in_array('node-teaser', $vars['classes_array']) || in_array('node-preview', $vars['classes_array'])) {
      $vars['title_link'] = TRUE;
    }

    $vars['forum_plus_media_field'] = false;
    foreach($vars['content'] as $key => $field) {
      if (isset($field['#field_type']) && isset($field['#weight'])) {
        if ($field['#field_type'] == 'image' || $field['#field_type'] == 'video_embed_field' || $field['#field_type'] == 'youtube') {
          $vars['forum_plus_media_field'] = drupal_render($field);
          $vars['classes_array'][] = 'forum_plus-media-first';
          unset($vars['content'][$key]);
          break;
        }
      }
    }
  }
  if ($vars['type'] == 'video' ) {
    $vars['title_link'] = FALSE;
    if (in_array('node-teaser', $vars['classes_array']) || in_array('node-preview', $vars['classes_array'])) {
      $vars['title_link'] = TRUE;
    }

    $vars['forum_plus_media_field'] = false;
    foreach($vars['content'] as $key => $field) {
      if (isset($field['#field_type']) && isset($field['#weight'])) {
        if ($field['#field_type'] == 'video_embed_field' || $field['#field_type'] == 'youtube') {
          $vars['forum_plus_media_field'] = drupal_render($field);
          $vars['classes_array'][] = 'forum_plus-media-first';
          unset($vars['content'][$key]);
          break;
        }
      }
    }
  }


}

function forum_plus_preprocess_comment(&$vars) {
  $comment = $vars['comment'];
  $vars['submitted'] = t(
    '<span class="author">Submitted by !username</span><span class="created">!datetime</span>',
    array('!username' => $comment->name, '!datetime' => date('D M d, Y h:ma', $comment->created))
  );
}

function forum_plus_date_nav_title($params) {
  $granularity = $params['granularity'];
  $view = $params['view'];
  $date_info = $view->date_info;
  $link = !empty($params['link']) ? $params['link'] : FALSE;
  $format = !empty($params['format']) ? $params['format'] : NULL;
  switch ($granularity) {
    case 'year':
      $title = $date_info->year;
      $date_arg = $date_info->year;
      break;
    case 'month':
      $format = !empty($format) ? $format : (empty($date_info->mini) ? 'F Y' : 'F Y');
      $title = date_format_date($date_info->min_date, 'custom', $format);
      $date_arg = $date_info->year .'-'. date_pad($date_info->month);
      break;
    case 'day':
      $format = !empty($format) ? $format : (empty($date_info->mini) ? 'l, F j Y' : 'l, F j');
      $title = date_format_date($date_info->min_date, 'custom', $format);
      $date_arg = $date_info->year .'-'. date_pad($date_info->month) .'-'. date_pad($date_info->day);
      break;
    case 'week':
      $format = !empty($format) ? $format : (empty($date_info->mini) ? 'F j Y' : 'F j');
      $title = t('Week of @date', array('@date' => date_format_date($date_info->min_date, 'custom', $format)));
      $date_arg = $date_info->year .'-W'. date_pad($date_info->week);
      break;
  }
  if (!empty($date_info->mini) || $link) {
    // Month navigation titles are used as links in the mini view.
    $attributes = array('title' => t('View full page month'));
    $url = date_pager_url($view, $granularity, $date_arg, TRUE);
    return l($title, $url, array('attributes' => $attributes));
  }
  else {
    return $title;
  }
}