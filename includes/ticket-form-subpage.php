<?php

add_action('admin_menu', function() {
    add_submenu_page(
        'mts-main-menu', // plugin main menu slug if you have one
        'Add Ticket Form Fields', // Page title
        'Ticket Form', // Menu title
        'manage_options', // Capability
        'ticket-fields', // Menu slug
        'mts_ticket_fields_page' // Callback function for the page content
    );});

function mts_ticket_fields_page() {
    ?>
    <div class="wrap">
        <h2>Ticket Custom Fields</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('mts_fields_group');
            do_settings_sections('ticket-fields');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function mts_register_form_settings() {
    register_setting('mts_fields_group', 'mts_ticket_fields');
    add_settings_section('section', 'Custom Fields', null, 'ticket-fields');
    add_settings_field('ticket_fields', 'Fields', 'mts_fields_callback', 'ticket-fields', 'section');
}
add_action('admin_init', 'mts_register_form_settings');

function mts_fields_callback() {
    $fields = get_option('mts_ticket_fields', []);
    if (!is_array($fields)) {
        $fields = []; // Ensure $fields is always an array.
    }
    echo '<pre>'; // This will help in making the output more readable
    var_dump($fields);
    echo '</pre>';
    echo '<div id="custom-fields-container">';
    foreach ($fields as $index => $field) {
        echo '<div class="custom-field">';
        echo '<input type="text" name="mts_ticket_fields[' . $index . '][label]" value="' . esc_attr($field['label']) . '" placeholder="Field Label"/>';
        echo '<select name="mts_ticket_fields[' . $index . '][type]">';
        echo '<option value="text" ' . selected($field['type'], 'text', false) . '>Text</option>';
        echo '<option value="textarea" ' . selected($field['type'], 'textarea', false) . '>Textarea</option>';
        echo '<option value="select" ' . selected($field['type'], 'select', false) . '>Select</option>';
        echo '</select>';
        echo '<input type="checkbox" name="mts_ticket_fields[' . $index . '][required]" ' . (isset($field['required']) && $field['required'] ? 'checked' : '') . '>Required';
        echo '<button type="button" onclick="removeField(this)">Remove</button>';
        echo '</div>';
    }
    echo '</div>';
    echo '<button type="button" onclick="addField()">Add Field</button>';
    ?>
    <script>
        function addField() {
            var container = document.getElementById('custom-fields-container');
            var index = container.getElementsByClassName('custom-field').length;

            var div = document.createElement('div');
            div.className = 'custom-field';

            var inputLabel = document.createElement('input');
            inputLabel.type = 'text';
            inputLabel.name = 'mts_ticket_fields[' + index + '][label]';
            inputLabel.placeholder = 'Field Label';

            var selectType = document.createElement('select');
            selectType.name = 'mts_ticket_fields[' + index + '][type]';
            var optionText = document.createElement('option');
            optionText.value = 'text';
            optionText.text = 'Text';
            selectType.appendChild(optionText);
            var optionTextarea = document.createElement('option');
            optionTextarea.value = 'textarea';
            optionTextarea.text = 'Textarea';
            selectType.appendChild(optionTextarea);
            var optionSelect = document.createElement('option');
            optionSelect.value = 'select';
            optionSelect.text = 'Select';
            selectType.appendChild(optionSelect);

            var inputRequired = document.createElement('input');
            inputRequired.type = 'checkbox';
            inputRequired.name = 'mts_ticket_fields[' + index + '][required]';

            var labelRequired = document.createElement('label');
            labelRequired.innerText = 'Required';
            labelRequired.htmlFor = inputRequired.id = 'required-' + index; // Assigning ID for better accessibility

            var buttonRemove = document.createElement('button');
            buttonRemove.type = 'button';
            buttonRemove.innerText = 'Remove';
            buttonRemove.onclick = function() { removeField(this); };

            div.appendChild(inputLabel);
            div.appendChild(selectType);
            div.appendChild(inputRequired);
            div.appendChild(labelRequired); // Append the label after the checkbox
            div.appendChild(buttonRemove);

            container.appendChild(div);
        }

        function removeField(button) {
            var field = button.parentNode;
            field.parentNode.removeChild(field);
        }
    </script>
    <?php
}
