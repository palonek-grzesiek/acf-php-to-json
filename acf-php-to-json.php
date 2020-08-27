<?php
/**
 * Plugin Name: Convert ACF PHP to JSON
 * Description: Convert Advanced Custom Fields Pro configuration from PHP to JSON.
 * Version: 1.0.0
 *
 * @source https://gist.github.com/ollietreend/df32c5cbe2914f6fc407332bf6cbfca5
 */

namespace ConvertAcfPhpToJson;

/**
 * Add submenu item under 'Custom Fields'
 */
function admin_menu() {
    add_submenu_page('edit.php?post_type=acf-field-group', 'Convert PHP fields to JSON', 'PHP to JSON', 'manage_options', 'acf-php-to-json', __NAMESPACE__ . '\\admin_page');
}
add_action('admin_menu', __NAMESPACE__ . '\\admin_menu', 20);

/**
 * Output the admin page
 */
function admin_page() {
    ?>
    <div class="wrap">
        <h1>Convert PHP fields to JSON</h1>
        <?php

        if (!isset($_GET['continue']) || $_GET['continue'] !== 'true') {
            admin_page_intro();
        }
        else {
            admin_page_convert();
        }
        ?>
    </div>
    <?php
}

/**
 * Output the introductory page
 */
function admin_page_intro() {
    $groups = get_groups_to_convert();

    if (empty($groups)) {
        echo '<p>No PHP field group configuration found. Nothing to convert.</p>';
        return;
    }
    else {
        echo sprintf('<p>%d field groups will be converted from PHP to JSON configuration.</p>', count($groups));
        echo '<a href="edit.php?post_type=acf-field-group&page=acf-php-to-json&continue=true" class="button button-primary">Convert Field Groups</a>';
    }
}

/**
 * Convert the field groups and output the conversion page
 */
function admin_page_convert() {
    $groups = get_groups_to_convert();

    echo sprintf('<p>Converting %d field groups from PHP to JSON configuration...</p>', count($groups));

    echo '<ol>';
    foreach ($groups as $group) {
        if (convert_group($group)) {
            echo sprintf('<li>Converted: <strong>%s</strong> (%s)</li>', $group['title'], $group['key']);
        }
        else {
            echo sprintf('<li><strong>Failed to convert: %s</strong> (%s)</li>', $group['title'], $group['key']);
        }
    }
    echo '</ol>';

    echo '<p>Done. Now remove the PHP field group configuration.</p>';
}

/**
 * Get the PHP field groups which will be converted.
 *
 * @return array
 */
function get_groups_to_convert() {
    $groups = acf_get_local_field_groups();
    if (!$groups) return [];
    return array_filter($groups, function($group) {
        return $group['local'] == 'php';
    });
}

/**
 * Convert a field group to JSON
 *
 * @param array $group
 * @return bool
 */
function convert_group($group) {
    $group['fields'] = acf_get_fields($group['key']);
    return acf_write_json_field_group($group);
}
