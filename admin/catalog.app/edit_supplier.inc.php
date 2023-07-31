<?php

  if (!empty($_GET['supplier_id'])) {
    $supplier = new ent_supplier($_GET['supplier_id']);
  } else {
    $supplier = new ent_supplier();
  }

  if (empty($_POST)) {
    $_POST = $supplier->data;
  }

  document::$snippets['title'][] = !empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_create_new_supplier', 'Create New Supplier');

  breadcrumbs::add(language::translate('title_catalog', 'Catalog'));
  breadcrumbs::add(language::translate('title_suppliers', 'Suppliers'), document::link(WS_DIR_ADMIN, ['doc' => 'suppliers'], ['app']));
  breadcrumbs::add(!empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_create_new_supplier', 'Create New Supplier'));

  if (isset($_POST['save'])) {

    try {
      if (empty($_POST['name'])) throw new Exception(language::translate('error_name_missing', 'You must enter a name.'));

      if (!isset($_POST['status'])) $_POST['status'] = '0';

      $fields = [
        'code',
        'name',
        'description',
        'email',
        'phone',
        'link',
      ];

      foreach ($fields as $field) {
        if (isset($_POST[$field])) $supplier->data[$field] = $_POST[$field];
      }

      $supplier->save();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'suppliers'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }

  if (isset($_POST['delete'])) {

    try {
      if (empty($supplier->data['id'])) throw new Exception(language::translate('error_must_provide_supplier', 'You must provide a supplier'));

      $supplier->delete();

      notices::add('success', language::translate('success_changes_saved', 'Changes saved'));
      header('Location: '. document::link(WS_DIR_ADMIN, ['doc' => 'suppliers'], ['app']));
      exit;

    } catch (Exception $e) {
      notices::add('errors', $e->getMessage());
    }
  }
?>
<div class="card card-app">
  <div class="card-header">
    <div class="card-title">
      <?php echo $app_icon; ?> <?php echo !empty($supplier->data['id']) ? language::translate('title_edit_supplier', 'Edit Supplier') : language::translate('title_create_new_supplier', 'Create New Supplier'); ?>
    </div>
  </div>

  <div class="card-body">
    <?php echo functions::form_draw_form_begin('supplier_form', 'post', false, false, 'style="max-width: 640px;" id="supplier_form_id" onsubmit="return validateForm()"'); ?>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_code', 'Code'); ?></label>
          <?php echo functions::form_draw_text_field('code', true, 'id="code"'); ?>
          <span id="error-message" style="color: red; display: none;">This field is required.</span>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_name', 'Name'); ?></label>
          <?php echo functions::form_draw_text_field('name', true, 'id="name"'); ?>
          <span id="error-message1" style="color: red; display: none;">This field is required.</span>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_description', 'Description'); ?></label>
        <?php echo functions::form_draw_textarea('description', true, 'id="desc"'); ?>
        <span id="error-message2" style="color: red; display: none;">This field is required.</span>
      </div>

      <div class="row">
        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_email_address', 'Email Address'); ?></label>
          <?php echo functions::form_draw_email_field('email', true, 'id="email"'); ?>
          <span id="error-message3" style="color: red; display: none;">This field is required.</span>
        </div>

        <div class="form-group col-md-6">
          <label><?php echo language::translate('title_phone', 'Phone'); ?></label>
          <?php echo functions::form_draw_text_field('phone', true, 'id="phone"'); ?>
          <span id="error-message4" style="color: red; display: none;">This field is required.</span>
          <span id="error-message5" style="color: red; display: none;">Please enter a valid phone number.</span>
        </div>
      </div>

      <div class="form-group">
        <label><?php echo language::translate('title_link', 'Link'); ?></label>
        <?php echo functions::form_draw_text_field('link', true); ?>
      </div>

      <div class="card-action">
        <?php echo functions::form_draw_button('save', language::translate('title_save', 'Save'), 'submit', 'class="btn btn-success" id="save"', 'save'); ?>
        <?php echo !empty($supplier->data['id']) ? functions::form_draw_button('delete', language::translate('title_delete', 'Delete'), 'submit', 'formnovalidate class="btn btn-danger" onclick="if (!confirm(&quot;'. language::translate('text_are_you_sure', 'Are you sure?') .'&quot;)) return false;"', 'delete') : ''; ?>
        <?php echo functions::form_draw_button('cancel', language::translate('title_cancel', 'Cancel'), 'button', 'onclick="history.go(-1);"', 'cancel'); ?>
      </div>

    <?php echo functions::form_draw_form_end(); ?>
  </div>
</div>

<script>
  function validateForm() {
    const fields = [
      { id: 'code', message: '#error-message' },
      { id: 'name', message: '#error-message1' },
      { id: 'desc', message: '#error-message2' },
      { id: 'email', message: '#error-message3' },
      { id: 'phone', message: '#error-message4' },
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

    const phoneField = $('#phone');
    const phoneErrorMessage = $('#error-message5');
    const phoneRegex = /^(\+?\d+)?\d+$/;

    if (phoneField.val().trim() !== '' && (!phoneRegex.test(phoneField.val().trim())  || phoneField.val().trim().replace(/[^\d]/g, '').length < 10)) {
      phoneErrorMessage.css('display', 'inline');
      isValid = false;
    } else {
      phoneErrorMessage.css('display', 'none');
    }
    
    return isValid;
  }
</script>