<?php

  if (!empty($_GET['user_id'])) {
    $user = new ent_user($_GET['user_id']);
  } else {
    $user = new ent_user();
  }

  if (empty($_POST)) {
    $_POST = $user->data;
  }

  document::$snippets['title'][] = !empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User');

  breadcrumbs::add(language::translate('title_users', 'Users'), document::href_link(WS_DIR_ADMIN, ['doc' => 'users'], ['app']));
  breadcrumbs::add(!empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'));

  if (isset($_POST['save'])) {

    try {

      if (empty($_POST['username'])) throw new Exception(language::translate('error_must_enter_username', 'You must enter a username'));
      if (empty($user->data['id']) && empty($_POST['password'])) throw new Exception(language::translate('error_must_enter_password', 'You must enter a password'));
      if (!empty($_POST['password']) && empty($_POST['confirmed_password'])) throw new Exception(language::translate('error_must_enter_confirmed_password', 'You must confirm the password'));
      if (!empty($_POST['password']) && $_POST['password'] != $_POST['confirmed_password']) throw new Exception(language::translate('error_passwords_missmatch', 'The passwords did not match'));
      
      $query = database::query(
        "SELECT COUNT(*) AS total
         FROM ". DB_TABLE_USERS ."
         WHERE username = '". database::input($_POST['username']) ."'
         ". (!empty($_POST['id']) ? "AND id != '". (int)$_POST['id'] ."'" : '') ."
         LIMIT 1;"
      );

      $result = database::fetch($query);

      if ($result['total'] > 0) {
        throw new Exception(language::translate('error_username_taken', 'The username is already taken'));
      }

      
      if (empty($_POST['apps'])) $_POST['apps'] = [];
      if (empty($_POST['widgets'])) $_POST['widgets'] = [];

      $fields = [
        'status',
        'username',
        'email',
        'password',
        'apps',
        'widgets',
        'date_valid_from',
        'date_valid_to',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $user->data[$field] = $_POST[$field];
      }

      if (!empty($_POST['password'])) $user->set_password($_POST['password']);

      $user->data['date_expire_sessions'] = date('Y-m-d H:i:s');

      if ($user->previous['username'] == user::$data['username']) {
        session::$data['user_security_timestamp'] = strtotime($user->data['date_expire_sessions']);
      }

      $user->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'users'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($user->data['id'])) throw new Exception(language::translate('error_must_provide_user', 'You must provide a user'));

      $user->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'users'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<style>
#app-permissions li,
#widget-permissions li {
  padding: .25em 0;
}
</style>

<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($user->data['username']) ? language::translate('title_edit_user', 'Edit User') : language::translate('title_create_new_user', 'Create New User'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('user_form', 'post', false, false, 'id="user_form_id" onsubmit="return validateForm()"  autocomplete="off" style="max-width: 960px;"'); ?>

      <div class="row">

        <div class="col-md-8">
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_status', 'Status'); ?></label>
              <?php echo functions::form_draw_toggle('status', (isset($_POST['status'])) ? $_POST['status'] : '1', 'e/d'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_username', 'Username'); ?></label>
              <?php echo functions::form_draw_text_field('username', true, 'id="username" autocomplete="off" required'); ?>
              <span id="error-message" style="color: red; display: none;">This field is required.</span>
            </div>

            <div class="form-group col-sm-6">
              <label><?php echo language::translate('title_email', 'Email'); ?></label>
              <?php echo functions::form_draw_email_field('email', true, 'id="email" autocomplete="off"'); ?>
              <span id="error-message1" style="color: red; display: none;">This field is required.</span>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_new_password', 'New Password'); ?></label>
              <?php echo functions::form_draw_password_field('password', '', 'id="password" autocomplete="new-password"'); ?>
              <span id="error-message2" style="color: red; display: none;">This field is required.</span>
              <span id="error-message7" style="color: red; display: none;">Password must be at least 6 characters long.</span>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_confirm_password', 'Confirm Password'); ?></label>
              <?php echo functions::form_draw_password_field('confirmed_password', '', 'id="cmpassword" autocomplete="new-password"'); ?>
              <span id="error-message3" style="color: red; display: none;">This field is required.</span>
              <span id="error-message8" style="color: red; display: none;">Confirm Password must be similar to the Password field</span>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_valid_from', 'Valid From'); ?></label>
              <?php echo functions::form_draw_datetime_field('date_valid_from', true, 'id="validfrom"'); ?>
              <span id="error-message4" style="color: red; display: none;">This field is required.</span>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_valid_to', 'Valid To'); ?></label>
              <?php echo functions::form_draw_datetime_field('date_valid_to', true, 'id="validto"'); ?>
              <span id="error-message5" style="color: red; display: none;">This field is required.</span>
              <span id="error-message6" style="color: red; display: none;">Valid from date must be less than or equal to Valid to date.</span>
            </div>
          </div>

          <?php if (!empty($user->data['id'])) { ?>
          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_ip', 'Last IP'); ?></label>
              <?php echo functions::form_draw_text_field('last_ip', true, 'readonly'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_host', 'Last Host'); ?></label>
              <?php echo functions::form_draw_text_field('last_host', true, 'readonly'); ?>
            </div>
          </div>

          <div class="row">
            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_login', 'Last Login'); ?></label>
              <?php echo functions::form_draw_text_field('date_login', true, 'readonly'); ?>
            </div>

            <div class="form-group col-md-6">
              <label><?php echo language::translate('title_last_active', 'Last Active'); ?></label>
              <?php echo functions::form_draw_text_field('date_active', true, 'readonly'); ?>
            </div>
          </div>
          <?php } ?>
        </div>

        <div class="col-md-4">
          <div id="app-permissions" class="form-group">
            <label><?php echo functions::form_draw_checkbox('apps_toggle', '1', !empty($_POST['apps']) ? '1' : '0'); ?> <?php echo language::translate('title_apps', 'Apps'); ?></label>
            <div class="form-control" style="height: 400px; overflow-y: scroll;">
              <ul class="list-unstyled">
<?php
  $apps = functions::admin_get_apps();
  foreach ($apps as $app) {
    echo '  <li data-app="'. functions::escape_html($app['code']) .'">' . PHP_EOL
       . '    <label>'. functions::form_draw_checkbox('apps['.$app['code'].'][status]', '1', true) .' '. $app['name'] .'</label>' . PHP_EOL;
    if (!empty($app['docs'])) {
      echo '    <ul class="list-unstyled">' . PHP_EOL;
      foreach ($app['docs'] as $doc => $file) {
        echo '      <li data-doc="'. functions::escape_html($doc) .'"><label>'. functions::form_draw_checkbox('apps['.$app['code'].'][docs][]', $doc, true) .' '. $doc .'</label>' . PHP_EOL;
      }
      echo '    </ul>' . PHP_EOL;
    }
    echo '  </li>' . PHP_EOL;
  }
?>
              </ul>
            </div>
          </div>

          <div id="widget-permissions" class="form-group">
            <label><?php echo functions::form_draw_checkbox('widgets_toggle', '1', !empty($_POST['widgets']) ? '1' : '0'); ?> <?php echo language::translate('title_widgets', 'Widgets'); ?></label>
            <div class="form-control" style="height: 150px; overflow-y: scroll;">
              <ul class="list-unstyled">
<?php
  $widgets = functions::admin_get_widgets();
  foreach ($widgets as $widget) {
    echo '  <li>' . PHP_EOL
       . '    <label>'. functions::form_draw_checkbox('widgets['.$widget['code'].']', '1', true) .' '. $widget['name'] .'</label>' . PHP_EOL
       . '  </li>' . PHP_EOL;
  }
?>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success" id="save"', 'save'); ?>
        <?php echo (!empty($user->data['id'])) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : false; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  function validateForm() {
    const fields = [
      { id: 'username', message: '#error-message' },
      { id: 'email', message: '#error-message1' },
      { id: 'password', message: '#error-message2' },
      { id: 'cmpassword', message: '#error-message3' },
      { id: 'validfrom', message: '#error-message4' },
      { id: 'validto', message: '#error-message5' },
    ];

    let isValid = true;

    fields.forEach(field => {
      const inputField = $(`#${field.id}`);
      const errorMessage = $(field.message);

      if (inputField.val().trim() === '') {
        errorMessage.css('display', 'inline');
        isValid = false;
      } else {
        errorMessage.css('display', 'none');
      }
    });

    const validfromField = $('#validfrom');
    const validtoField = $('#validto');
    const dateErrorMessage = $('#error-message6');

    if (validfromField.val() && validtoField.val()) {
      const validfromDate = new Date(validfromField.val());
      const validtoDate = new Date(validtoField.val());

      if (validfromDate > validtoDate) {
        dateErrorMessage.css('display', 'inline');
        isValid = false;
      } else {
        dateErrorMessage.css('display', 'none');
      }
    }

    const passwordField = $('#password');
    const passwordErrorMessage = $('#error-message7');
    if (passwordField.val().trim() !== '' && passwordField.val().trim().length < 6) {
      passwordErrorMessage.css('display', 'inline');
      isValid = false;
    } else {
      passwordErrorMessage.css('display', 'none');
    }

    const cmpasswordField = $('#cmpassword');
    const cmpasswordErrorMessage = $('#error-message8');

    if (passwordField.val() && cmpasswordField.val()) {
      if (passwordField.val() !== cmpasswordField.val()) {
        cmpasswordErrorMessage.css('display', 'inline');
        isValid = false;
      } else {
        cmpasswordErrorMessage.css('display', 'none');
      }
    }

    return isValid;
  }

  $('input[name="apps_toggle"]').change(function(){
    $('input[name^="apps"][name$="[status]"]').prop('disabled', !$(this).is(':checked'));
    $('input[name^="apps"][name$="[docs][]"]').prop('disabled', !$(this).is(':checked'));
  }).trigger('change');

  $('input[name^="apps"][name$="[status]"]').change(function() {
    if ($(this).prop('checked')) {
      if (!$(this).closest('[data-app]').find('ul :input:checked').length) {
        $(this).closest('[data-app]').find('ul :input').prop('checked', true);
      }
    } else {
      $(this).closest('[data-app]').find('ul :input').prop('checked', false);
    }
  });

  $('input[name^="apps"][name$="[docs][]"]').change(function() {
    if ($(this).is(':checked')) {
      $(this).closest('ul').closest('[data-app]').children().not('ul').find(':input').prop('checked', true);
    }
  });

  $('input[name="widgets_toggle"]').change(function(){
    $('input[name^="widgets["]').prop('disabled', !$(this).is(':checked'));
  }).trigger('change');
</script>