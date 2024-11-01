<?php
/*
 * Plugin Name: Options for Block Themes
 * Version: 1.3.4
 * Plugin URI: https://webd.uk/support/
 * Description: Import / Export global styles, templates and template parts to Full Site Editing / Block Themes without a child theme!
 * Author: Webd Ltd
 * Author URI: https://webd.uk
 * Text Domain: template-editor
 */



if (!defined('ABSPATH')) {
    exit('This isn\'t the page you\'re looking for. Move along, move along.');
}



if (!class_exists('template_editor_class')) {

	class template_editor_class {

        public static $version = '1.3.4';
        public $is_block_theme = false;

		function __construct() {

        	register_activation_hook(__FILE__, array($this, 'activation_hook'));
            add_action('after_setup_theme', array($this, 'after_setup_theme'), 11);

        	$options = get_option('te_options');

            if (!(isset($options['hide_customize_link']) && $options['hide_customize_link'])) {

                add_action('customize_register', '__return_true');

            }

            if (is_admin()) {

				add_action('admin_init', array($this, 'admin_init'));
        	    add_action('admin_menu', 'template_editor_class::admin_menu');
                add_action('wp_ajax_te_save', 'template_editor_class::te_save');
                add_action('wp_ajax_te_delete', 'template_editor_class::te_delete');
                add_action('wp_ajax_te_download_wp_template', 'template_editor_class::te_download_wp_template');
                add_action('wp_ajax_te_upload_wp_template', 'template_editor_class::te_upload_wp_template');
                add_action('wp_ajax_te_upload_wp_template_part', 'template_editor_class::te_upload_wp_template_part');
                add_action('wp_ajax_te_download_wp_global_styles', 'template_editor_class::te_download_wp_global_styles');
                add_action('wp_ajax_te_global_styles_delete', 'template_editor_class::te_global_styles_delete');
                add_action('wp_ajax_te_upload_wp_global_styles', 'template_editor_class::te_upload_wp_global_styles');
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'template_editor_class::add_plugin_action_links');
                add_action('admin_notices', 'teCommon::admin_notices');
                add_action('wp_ajax_dismiss_te_notice_handler', 'teCommon::ajax_notice_handler');

            } else {

                add_action('wp_head' , array($this, 'wp_head'), 11);
                add_action('wp_footer', 'template_editor_class::wp_footer');

            }

            add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'), 11);
            add_filter('wp_theme_json_data_theme', array($this, 'wp_theme_json_data_theme'));
            add_filter('register_block_type_args', 'template_editor_class::register_block_type_args', 10, 2);
            add_action('enqueue_block_editor_assets', 'template_editor_class::enqueue_block_editor_assets');
            add_filter('render_block', 'template_editor_class::render_block', 10, 2);
            add_action('enqueue_block_assets', 'template_editor_class::enqueue_block_assets' );

		}

		public static function add_plugin_action_links($links) {

			$settings_links = teCommon::plugin_action_links(add_query_arg('page', 'template_editor', admin_url('themes.php')));

			return array_merge($settings_links, $links);

		}

        public static function enqueue_block_editor_assets() {

            wp_register_script(
                'template-editor',
                plugin_dir_url( __FILE__ ) . 'build/index.js',
                array('wp-blocks', 'wp-dom', 'wp-dom-ready', 'wp-edit-post'),
                filemtime(plugin_dir_path( __FILE__ ) . 'build/index.js')
            );
            wp_enqueue_script('template-editor');

        }

        public static function render_block($block_content, $block) {

        	if (
        	    $block_content &&
                isset($block['blockName']) &&
                'core/navigation' === $block['blockName'] &&
                isset($block['attrs']['hasExpandableModalSubmenus']) &&
                $block['attrs']['hasExpandableModalSubmenus']
            ) {

                $block_content = preg_replace(
                    '/' . preg_quote( 'class="', '/' ) . '/',
                    'class="' . esc_attr('has-expandable-modal-submenus') . ' ',
                    $block_content,
                    1
	            );

            }

            return $block_content;

        }

        public static function enqueue_block_assets() {

            wp_enqueue_style(
                'has-expandable-modal-submenus',
                plugin_dir_url( __FILE__ ) . 'css/has-expandable-modal-submenus.css',
                array(),
                self::$version
            );

        }

        public function after_setup_theme() {

            global $_wp_theme_features;

            if (!isset($_wp_theme_features['block-templates'])) {

            	$options = get_option('te_options');

                if (isset($options['enable_template_editor']) && $options['enable_template_editor']) {

                    add_theme_support('block-templates');

                }

            } else {

                $this->is_block_theme = true;

            }

        }

        public function wp_head() {

        	$options = get_option('te_options');

            if (isset($options['enable_sticky_header']) && $options['enable_sticky_header']) {

?>
<!--Template Editor CSS--> 
<style type="text/css">

.wp-site-blocks>header {
	position: fixed;
	z-index: 401;
	left: 0;
	right: 0;
	top: 0;
	padding-left: var(--wp--custom--spacing--outer);
	padding-right: var(--wp--custom--spacing--outer);
}

.wp-site-blocks>main,
.wp-site-blocks>.wp-block-group {
	margin-top: 0;
}

.admin-bar .wp-site-blocks>header {
	top: 32px;
}

.admin-bar .wp-site-blocks>main:not(.wp-block-query), .admin-bar .wp-site-blocks>div:first-child {
	margin-top: -32px;
}

@media screen and (max-width: 782px) {

	.admin-bar .wp-site-blocks>header {
		top: 46px;
    }

	.admin-bar .wp-site-blocks>main:not(.wp-block-query) {
		margin-top: -46px;
	}

}

</style> 
<!--/Template Editor CSS-->
<?php

            }

            if (isset($options['animate_header_logo']) && $options['animate_header_logo']) {

                $hide_logo = isset($options['animate_header_logo']) && 1 === absint($options['animate_header_logo_width']);

?>
<!--Template Editor CSS--> 
<style type="text/css">
header .wp-block-site-logo img,
header .wp-block-site-logo.is-default-size img {
	transition: width 0.3s ease-in-out, height 0.3s ease-in-out, margin 0.3s ease-in-out;<?php if ($hide_logo) { ?>
	vertical-align: top;<?php } ?>
}<?php if ($hide_logo) { ?>
header.shrink-logo .wp-block-site-logo img,
header.shrink-logo .wp-block-site-logo.is-default-size img {
		margin: 0;
}<?php } ?>
</style> 
<!--/Template Editor CSS-->
<?php

            }

        }

        public static function admin_menu() {

            global $submenu;

        	$options = get_option('te_options');

            if (version_compare(get_bloginfo('version'), '6.0', '<')) {

                if (
                    isset($submenu['themes.php'][6][2]) && 'site-editor.php' === $submenu['themes.php'][6][2] &&
                    isset($submenu['themes.php'][7][2]) && 'widgets.php' === $submenu['themes.php'][7][2]
                ) {

                    $submenu['themes.php'][8] = $submenu['themes.php'][7];

                    if (isset($options['hide_customize_link']) && $options['hide_customize_link']) {

                        unset($submenu['themes.php'][7]);

                    } else {

                        $customize_url = add_query_arg('return', urlencode(remove_query_arg(wp_removable_query_args(), wp_unslash( $_SERVER['REQUEST_URI']))), 'customize.php');
                        $submenu['themes.php'][7] = array(__('Customize','template-editor'), 'customize', esc_url($customize_url), '', 'hide-if-no-customize');

                    }

                }

            }

		    add_theme_page(__('Manage Templates', 'template-editor'), __('Manage Templates', 'template-editor'), 'manage_options', 'template_editor', 'template_editor_class::settings_page', 2);
            $submenu['themes.php'][] = array(__('Theme Options', 'template-editor'), 'customize', add_query_arg(array('page' => 'template_editor', 'tab' => 'theme_options'), admin_url('themes.php')), '', 'theme_options');

        }

        public function activation_hook() {

            if (!$this->is_block_theme) {

            	$options = get_option('te_options');

                if (!is_array($options)) {

                    $options = array();

                }

                $options['enable_template_editor'] = '1';
                $options['hide_customize_link'] = '1';
                update_option('te_options', $options);

            }

        }

        public static function settings_page() {

            if (isset($_GET['tab']) && in_array(sanitize_key($_GET['tab']), array('theme_options', 'manage_template_parts'))) {

                $current_tab = sanitize_key($_GET['tab']);

            } else {

                $current_tab = 'manage_templates';

            }

            $tabs = array(
                'manage_templates' => __('Manage Templates', 'template-editor'),
                'manage_template_parts' => __('Manage Template Parts', 'template-editor'),
                'manage_global_styles' => __('Manage Global Styles', 'template-editor'),
                'theme_options' => __('Theme Options', 'template-editor')
            );

?>
<h2 class="nav-tab-wrapper">
<?php

            foreach ($tabs as $tab => $title) {

                $class = ($current_tab === $tab) ? ' nav-tab-active' : '';

?>
<a id="<?php echo esc_attr($tab); ?>" class="nav-tab<?php echo $class; ?>" href="#" title="<?php echo esc_attr($title); ?>"><?php echo esc_html($title); ?></a>
<?php

            }

?>
</h2>
<script type="text/javascript">
(function($) {
    $('#adminmenu li.current').addClass('manage_templates');
    $('#adminmenu .current').removeClass('current');
    $('#adminmenu .<?php echo $current_tab; ?>').addClass('current');
    $('.nav-tab-wrapper .nav-tab').click(function() {
        $('.tab_content').hide();
        $('.tab_content.' + $(this).attr('id')).show();
        $('.nav-tab-active').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('#adminmenu .current').removeClass('current');
        $('#adminmenu .' + $(this).attr('id')).addClass('current');
    });
})(jQuery);
</script>
<?php

            $active_theme = get_stylesheet();

?>
<div class="wrap tab_content manage_templates"<?php echo ('manage_templates' === $current_tab ? '' : ' style="display: none;"'); ?>>
<h1><?php _e('Manage Templates', 'template-editor'); ?></h1>
<p><?php _e('Every theme default template you have edited is listed here ...', 'template-editor'); ?></p>
<p><?php _e('Here\'s how this works. When you use the Full Site Editor and save your changes to a template, the changes are saved to the database and will be listed here.', 'template-editor'); ?></p>
<p><?php _e('You can then rename the slug (and title / description) of the edited template to whatever you require and either save it or save it as a copy if you want to keep the original.', 'template-editor'); ?></p>
<p><?php _e('Using this plugin you can create new templates outside of those that are available by default <strong>without</strong> having to create a child them! :)', 'template-editor'); ?></p>
<h2><?php printf(__('Active Theme (%s) Templates', 'template-editor'), $active_theme); ?></h2>
<?php

            $templates = get_posts(array(
                'numberposts' => -1,
                'post_type' => 'wp_template',
                'tax_query' =>  array(
                    array(
                        'taxonomy' => 'wp_theme',
                        'field' => 'slug',
                        'terms' => array($active_theme),
                        'operator' => 'IN'
                    )
                 )
            ));

            if ($templates) {

?>
<table class="wp-list-table widefat striped">
<thead>
<tr>
<th class="manage-column column-name column-primary"><?php _e('Template', 'template-editor'); ?></th>
<th class="manage-column column-actions"><?php _e('Actions', 'template-editor'); ?></th>
</tr>
</thead>
<tbody>
<?php

                foreach ($templates as $template) {

                    $template_array = [
                        'ID' => $template->ID,
                        'post_title' => $template->post_title,
                        'post_name' => $template->post_name,
                        'post_excerpt' => $template->post_excerpt
                    ];

?>
<tr class="theme-mods-<?php echo esc_attr($template->ID); ?>">
<td class="plugin-title column-primary">
<strong><?php echo esc_html($template->post_title) . ' (' . esc_html($template->post_name) . '.html)'; ?></strong>
</td>
<td class="column-actions">
<span class="te-download button button-small" data-template="<?php echo esc_attr(json_encode($template_array)); ?>"><?php _e('Download', 'template-editor'); ?></span>
<span class="te-select button button-small" data-template="<?php echo esc_attr(json_encode($template_array)); ?>"><?php _e('Select', 'template-editor'); ?></span>
<span class="te-delete button button-small" data-template="<?php echo esc_attr($template->ID); ?>"><?php _e('Delete', 'template-editor'); ?></span>
</td>
</tr>
<?php

                }

?>
</tbody>
</table>
<?php

            } else {

?>
<p><?php _e('No customized templates can be found for the active theme.', 'template-editor'); ?></p>
<?php

            }

?>
<h2><?php printf(__('Other Theme Templates', 'template-editor'), $active_theme); ?></h2>
<?php

            $templates = get_posts(array(
                'numberposts' => -1,
                'post_type' => 'wp_template',
                'tax_query' =>  array(
                    array(
                        'taxonomy' => 'wp_theme',
                        'field' => 'slug',
                        'terms' => array($active_theme),
                        'operator' => 'NOT IN'
                    )
                 )
            ));

            if ($templates) {

?>
<table class="wp-list-table widefat striped">
<thead>
<tr>
<th class="manage-column column-name column-primary"><?php _e('Theme', 'template-editor'); ?></th>
<th class="manage-column column-name column-primary"><?php _e('Template', 'template-editor'); ?></th>
<th class="manage-column column-actions"><?php _e('Actions', 'template-editor'); ?></th>
</tr>
</thead>
<tbody>
<?php

                foreach ($templates as $template) {

                    $template_array = [
                        'ID' => $template->ID,
                        'post_title' => $template->post_title,
                        'post_name' => $template->post_name,
                        'post_excerpt' => $template->post_excerpt
                    ];

                    $template_theme = get_the_terms($template, 'wp_theme');

?>
<tr class="theme-mods-<?php echo esc_attr($template->ID); ?>">
<td class="plugin-title column-primary">
<strong><?php echo esc_html((is_array($template_theme) && $template_theme ? $template_theme[0]->slug : 'None')); ?></strong>
</td>
<td class="plugin-title column-primary">
<strong><?php echo esc_html($template->post_title) . ' (' . esc_html($template->post_name) . '.html)'; ?></strong>
</td>
<td class="column-actions">
<span class="te-download button button-small" data-template="<?php echo esc_attr(json_encode($template_array)); ?>"><?php _e('Download', 'template-editor'); ?></span>
<span class="te-select button button-small" data-template="<?php echo esc_attr(json_encode($template_array)); ?>"><?php _e('Select', 'template-editor'); ?></span>
<span class="te-delete button button-small" data-template="<?php echo esc_attr($template->ID); ?>"><?php _e('Delete', 'template-editor'); ?></span>
</td>
</tr>
<?php

                }

?>
</tbody>
</table>
<?php

            } else {

?>
<p><?php _e('No customized templates can be found for any other themes.', 'template-editor'); ?></p>
<?php

            }

?>
<input name="te_post_id" type="hidden" id="te_post_id" value="">
<table class="form-table" role="presentation">
<tbody>
<tr>
<th scope="row"><label for="te_post_title"><?php esc_html_e('Template Title', 'template-editor'); ?></label></th>
<td><input name="te_post_title" type="text" id="te_post_title" value="" class="regular-text"></td>
</tr>
<tr>
<th scope="row"><label for="te_post_name"><?php esc_html_e( 'Template Name', 'template-editor' ); ?></label></th>
<td><input name="te_post_name" type="text" id="te_post_name" value="" class="regular-text">.html<br />
<?php printf(__('<strong>This is the important bit!</strong> The template name <i>has</i> to match the slug that you would expect WordPress to look for in the <a href="%1$s" title="%2$s" target="_blank">Template Heirachy</a>','template-editor'),'https://developer.wordpress.org/themes/basics/template-hierarchy/', __('Template Heirachy','template-editor')); ?></td>
</tr>
<tr>
<th scope="row"><label for="te_post_excerpt"><?php esc_html_e('Template Description', 'template-editor'); ?></label></th>
<td><input name="te_post_excerpt" type="text" id="te_post_excerpt" value="" class="regular-text"></td>
</tr>
</tbody>
</table>
<p><span id="te_save" class="button button-small disabled"><?php _e('Save', 'template-editor'); ?></span> - <?php esc_html_e('Retains association with the origin theme.', 'template-editor'); ?></p>
<p><span id="te_save_as_copy" class="button button-small disabled"><?php _e('Save as copy', 'template-editor'); ?></span> - <?php esc_html_e('Saves a copy of the template to the active theme.', 'template-editor'); ?></p>
<h2><?php esc_html_e('Upload Template', 'template-editor'); ?></h2>
<p><?php esc_html_e('Upload a template .json file to the active theme.', 'template-editor'); ?></p>
<input type="file" id="te-json-file" accept=".json" style="display: none;" />
<p><span class="te-upload button button-small"><?php _e('Upload', 'template-editor'); ?></span></p>
<script type="text/javascript">
(function($) {
    $('.te-select').click(function() {
        var template = $(this).data('template');
        $('#te_post_id').val(template.ID);
        $('#te_post_title').val(template.post_title);
        $('#te_post_name').val(template.post_name);
        $('#te_post_excerpt').val(template.post_excerpt);
        if ($('#te_save').hasClass('disabled')) { $('#te_save').removeClass('disabled'); }
        if ($('#te_save_as_copy').hasClass('disabled')) { $('#te_save_as_copy').removeClass('disabled'); }
	});
    $('#te_save').click(function() { save_template(0); });
    $('#te_save_as_copy').click(function() { save_template(1); });
    function save_template(saveAsCopy) {
        if (confirm('<?php _e('Are you sure you want to save your changes to the template?', 'template-editor'); ?>')) {
            $('#te_save').unbind('click')
            var data = {
            	action: 'te_save',
            	_ajax_nonce: '<?php echo wp_create_nonce('template-editor-save'); ?>',
            	post_id: $('#te_post_id').val(),
            	post_title: $('#te_post_title').val(),
            	post_name: $('#te_post_name').val(),
            	post_excerpt: $('#te_post_excerpt').val(),
            	save_as_copy: saveAsCopy
            };
    	    $.ajax({
        	    url: ajaxurl,
        	    data: data,
                type: 'POST',
                success: function(response) {
                    if ('success' in response && response.success) {
                        window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                    } else {
                        alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                        window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                    }
                },
                error: function() {
                    alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                    window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                }
    	    });
        }
    }
    $('.te-download').click(function() {
        var data = {
        	action: 'te_download_wp_template',
        	_ajax_nonce: '<?php echo wp_create_nonce('download-wp-template'); ?>',
        	post_id: $(this).data('template').ID,
        	post_name: $(this).data('template').post_name
        };
	    $.ajax({
    	    url: ajaxurl,
    	    dataType: 'text',
    	    data: data,
            type: 'POST',
            success: function(wpTemplateJson) {
                var wpTemplateFile = new Blob(
                    [wpTemplateJson], {
                        type : "application/json;charset=utf-8"
                    }
                );
                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth() + 1;
                var yyyy = today.getFullYear();
                if (dd < 10) {
                    dd = '0' + dd;
                }
                if (mm < 10) {
                    mm = '0' + mm;
                }
                today = yyyy + '_' + mm + '_' + dd;
                var downloadLink = document.createElement('a');
                var downloadURL = window.URL.createObjectURL(wpTemplateFile);
                downloadLink.href = downloadURL;
                downloadLink.download = data.post_name + '_' + today + '.json';
                document.body.append(downloadLink);
                downloadLink.click();
                downloadLink.remove();
                window.URL.revokeObjectURL(downloadURL);
            },
            error: function() {
                alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
            }
	    });
    });
    $('.te-delete').click(function() {
        if (confirm('<?php _e('Are you sure you want to delete the template?', 'template-editor'); ?>')) {
            $('#te-delete').unbind('click')
            var data = {
            	action: 'te_delete',
            	_ajax_nonce: '<?php echo wp_create_nonce('template-editor-delete'); ?>',
            	post_id: $(this).data('template')
            };
    	    $.ajax({
        	    url: ajaxurl,
        	    data: data,
                type: 'POST',
                success: function(response) {
                    if ('success' in response && response.success) {
                        window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                    } else {
                        alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                        window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                    }
                },
                error: function() {
                    alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                    window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                }
    	    });
        }
    });
    $('.te-upload').click(function() {
        document.getElementById('te-json-file').click();
    });
    $('#te-json-file').change(function() {
        var confirmText = '<?php _e('Are you sure you want to upload %s as an active theme template?', 'template-editor'); ?>';
        if (confirm(confirmText.replace('%s', $('#te-json-file').prop('files')[0].name))) {
            var data = new FormData();
            data.append('action', 'te_upload_wp_template');
            data.append('_ajax_nonce', '<?php echo wp_create_nonce('upload-wp-template'); ?>');
            data.append('file', $('#te-json-file').prop('files')[0]);
    	    $.ajax({
        	    url: ajaxurl,
        	    data: data,
                type: 'POST',
                contentType: false,
                processData: false,
                success: function(response) {
                    if ('success' in response && response.success) {
                        window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                    } else {
                        alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                        window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                    }
                },
                error: function() {
                    alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                    window.location.href = '<?php echo add_query_arg('page', 'template_editor', admin_url('themes.php')); ?>';
                }
    	    });
        }
	});
})(jQuery);
</script>
</div>
<div class="wrap tab_content manage_template_parts"<?php echo ('manage_template_parts' === $current_tab ? '' : ' style="display: none;"'); ?>>
<h1><?php _e('Manage Template Parts', 'template-editor'); ?></h1>
<p><?php _e('Every theme default template part (header, footer, etc) you have edited is listed here ...', 'template-editor'); ?></p>
<h2><?php printf(__('Active Theme (%s) Template Parts', 'template-editor'), $active_theme); ?></h2>
<?php

            $template_parts = get_posts(array(
                'numberposts' => -1,
                'post_type' => 'wp_template_part',
                'tax_query' =>  array(
                    array(
                        'taxonomy' => 'wp_theme',
                        'field' => 'slug',
                        'terms' => array($active_theme),
                        'operator' => 'IN'
                    )
                 )
            ));

            if ($template_parts) {

?>
<table class="wp-list-table widefat striped">
<thead>
<tr>
<th class="manage-column column-name column-primary"><?php _e('Template Part', 'template-editor'); ?></th>
<th class="manage-column column-actions"><?php _e('Actions', 'template-editor'); ?></th>
</tr>
</thead>
<tbody>
<?php

                foreach ($template_parts as $template_part) {

                    $template_part_array = [
                        'ID' => $template_part->ID,
                        'post_title' => $template_part->post_title,
                        'post_name' => $template_part->post_name,
                        'post_excerpt' => $template_part->post_excerpt
                    ];

?>
<tr class="theme-mods-<?php echo esc_attr($template_part->ID); ?>">
<td class="plugin-title column-primary">
<strong><?php echo esc_html($template_part->post_title) . ' (' . esc_html($template_part->post_name) . '.html)'; ?></strong>
</td>
<td class="column-actions">
<span class="te-part-download button button-small" data-template-part="<?php echo esc_attr(json_encode($template_part_array)); ?>"><?php _e('Download', 'template-editor'); ?></span>
<span class="te-part-delete button button-small" data-template-part="<?php echo esc_attr($template_part->ID); ?>"><?php _e('Delete', 'template-editor'); ?></span>
</td>
</tr>
<?php

                }

?>
</tbody>
</table>
<?php

            } else {

?>
<p><?php _e('No customized template parts can be found for the active theme.', 'template-editor'); ?></p>
<?php

            }

?>
<h2><?php printf(__('Other Theme Template Parts', 'template-editor'), $active_theme); ?></h2>
<?php

            $template_parts = get_posts(array(
                'numberposts' => -1,
                'post_type' => 'wp_template_part',
                'tax_query' =>  array(
                    array(
                        'taxonomy' => 'wp_theme',
                        'field' => 'slug',
                        'terms' => array($active_theme),
                        'operator' => 'NOT IN'
                    )
                 )
            ));

            if ($template_parts) {

?>
<table class="wp-list-table widefat striped">
<thead>
<tr>
<th class="manage-column column-name column-primary"><?php _e('Theme', 'template-editor'); ?></th>
<th class="manage-column column-name column-primary"><?php _e('Template Part', 'template-editor'); ?></th>
<th class="manage-column column-actions"><?php _e('Actions', 'template-editor'); ?></th>
</tr>
</thead>
<tbody>
<?php

                foreach ($template_parts as $template_part) {

                    $template_part_array = [
                        'ID' => $template_part->ID,
                        'post_title' => $template_part->post_title,
                        'post_name' => $template_part->post_name,
                        'post_excerpt' => $template_part->post_excerpt
                    ];

?>
<tr class="theme-mods-<?php echo esc_attr($template_part->ID); ?>">
<td class="plugin-title column-primary">
<strong><?php echo esc_html(get_the_terms($template_part, 'wp_theme')[0]->slug); ?></strong>
</td>
<td class="plugin-title column-primary">
<strong><?php echo esc_html($template_part->post_title) . ' (' . esc_html($template_part->post_name) . '.html)'; ?></strong>
</td>
<td class="column-actions">
<span class="te-part-download button button-small" data-template-part="<?php echo esc_attr(json_encode($template_part_array)); ?>"><?php _e('Download', 'template-editor'); ?></span>
<span class="te-part-delete button button-small" data-template-part="<?php echo esc_attr($template_part->ID); ?>"><?php _e('Delete', 'template-editor'); ?></span>
</td>
</tr>
<?php

                }

?>
</tbody>
</table>
<?php

            } else {

?>
<p><?php _e('No customized template parts can be found for any other themes.', 'template-editor'); ?></p>
<?php

            }

?>
<h2><?php esc_html_e('Upload Template Part', 'template-editor'); ?></h2>
<p><?php esc_html_e('Upload a template part .json file to the active theme.', 'template-editor'); ?></p>
<input type="file" id="te-part-json-file" accept=".json" style="display: none;" />
<p><span class="te-part-upload button button-small"><?php _e('Upload', 'template-editor'); ?></span></p>
<script type="text/javascript">
(function($) {
    $('.te-part-download').click(function() {
        var data = {
        	action: 'te_download_wp_template',
        	_ajax_nonce: '<?php echo wp_create_nonce('download-wp-template'); ?>',
        	post_id: $(this).data('template-part').ID,
        	post_name: $(this).data('template-part').post_name
        };
	    $.ajax({
    	    url: ajaxurl,
    	    dataType: 'text',
    	    data: data,
            type: 'POST',
            success: function(wpTemplatePartJson) {
                var wpTemplatePartFile = new Blob(
                    [wpTemplatePartJson], {
                        type : "application/json;charset=utf-8"
                    }
                );
                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth() + 1;
                var yyyy = today.getFullYear();
                if (dd < 10) {
                    dd = '0' + dd;
                }
                if (mm < 10) {
                    mm = '0' + mm;
                }
                today = yyyy + '_' + mm + '_' + dd;
                var downloadLink = document.createElement('a');
                var downloadURL = window.URL.createObjectURL(wpTemplatePartFile);
                downloadLink.href = downloadURL;
                downloadLink.download = data.post_name + '_' + today + '.json';
                document.body.append(downloadLink);
                downloadLink.click();
                downloadLink.remove();
                window.URL.revokeObjectURL(downloadURL);
            },
            error: function() {
                alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
            }
	    });
    });
    $('.te-part-delete').click(function() {
        if (confirm('<?php _e('Are you sure you want to delete the template part?', 'template-editor'); ?>')) {
            $('#te-part-delete').unbind('click')
            var data = {
            	action: 'te_delete',
            	_ajax_nonce: '<?php echo wp_create_nonce('template-editor-delete'); ?>',
            	post_id: $(this).data('template-part')
            };
    	    $.ajax({
        	    url: ajaxurl,
        	    data: data,
                type: 'POST',
                success: function(response) {
                    if ('success' in response && response.success) {
                        window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_template_parts'
                        ), admin_url('themes.php')); ?>';
                    } else {
                        alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                        window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_template_parts'
                        ), admin_url('themes.php')); ?>';
                    }
                },
                error: function() {
                    alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                    window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_template_parts'
                        ), admin_url('themes.php')); ?>';
                }
    	    });
        }
    });
    $('.te-part-upload').click(function() {
        document.getElementById('te-part-json-file').click();
    });
    $('#te-part-json-file').change(function() {
        var confirmText = '<?php _e('Are you sure you want to upload %s as an active theme template part?', 'template-editor'); ?>';
        if (confirm(confirmText.replace('%s', $('#te-part-json-file').prop('files')[0].name))) {
            var data = new FormData();
            data.append('action', 'te_upload_wp_template_part');
            data.append('_ajax_nonce', '<?php echo wp_create_nonce('upload-wp-template-part'); ?>');
            data.append('file', $('#te-part-json-file').prop('files')[0]);
    	    $.ajax({
        	    url: ajaxurl,
        	    data: data,
                type: 'POST',
                contentType: false,
                processData: false,
                success: function(response) {
                    if ('success' in response && response.success) {
                        window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_template_parts'
                        ), admin_url('themes.php')); ?>';
                    } else {
                        alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                        window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_template_parts'
                        ), admin_url('themes.php')); ?>';
                    }
                },
                error: function() {
                    alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                    window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_template_parts'
                        ), admin_url('themes.php')); ?>';
                }
    	    });
        }
	});
})(jQuery);
</script>
</div>
<div class="wrap tab_content manage_global_styles"<?php echo ('manage_global_styles' === $current_tab ? '' : ' style="display: none;"'); ?>>
<h1><?php _e('Manage Global Styles', 'template-editor'); ?></h1>
<p><?php _e('Here is a list of Global Styles saved for each theme ...', 'template-editor'); ?></p>
<h2><?php _e('Global Styles', 'template-editor'); ?></h2>
<?php

            $global_styles = get_posts(array(
                'numberposts' => -1,
                'post_type' => 'wp_global_styles'
            ));

            if ($global_styles) {

?>
<table class="wp-list-table widefat striped">
<thead>
<tr>
<th class="manage-column column-name column-primary"><?php _e('Theme', 'template-editor'); ?></th>
<th class="manage-column column-actions"><?php _e('Actions', 'template-editor'); ?></th>
</tr>
</thead>
<tbody>
<?php

                foreach ($global_styles as $global_style) {

                    $global_style_array = [
                        'ID' => $global_style->ID,
                        'theme' => substr($global_style->post_name, strlen('wp-global-styles-')),
                        'post_content' => $global_style->post_content
                    ];

?>
<tr class="global-styles-<?php echo esc_attr($global_style->ID); ?>">
<td class="plugin-title column-primary">
<strong><?php echo esc_html($global_style_array['theme'] . (($global_style_array['theme'] === $active_theme) ? ' (active theme)' : '')); ?></strong>
</td>
<td class="column-actions">
<span class="te-styles-download button button-small" data-global-styles="<?php echo esc_attr(json_encode($global_style_array)); ?>"><?php _e('Download', 'template-editor'); ?></span>
<span class="te-styles-delete button button-small" data-global-styles="<?php echo esc_attr($global_style->ID); ?>"><?php _e('Delete', 'template-editor'); ?></span>
</td>
</tr>
<?php

                }

?>
</tbody>
</table>
<?php

            } else {

?>
<p><?php _e('No global styles can be found.', 'template-editor'); ?></p>
<?php

            }

?>
<h2><?php esc_html_e('Upload Global Styles', 'template-editor'); ?></h2>
<p><?php esc_html_e('Upload a global styles .json file to the active theme.', 'template-editor'); ?></p>
<input type="file" id="te-styles-json-file" accept=".json" style="display: none;" />
<p><span class="te-styles-upload button button-small"><?php _e('Upload', 'template-editor'); ?></span></p>
<script type="text/javascript">
(function($) {
    $('.te-styles-download').click(function() {
        var data = {
        	action: 'te_download_wp_global_styles',
        	_ajax_nonce: '<?php echo wp_create_nonce('download-wp-global-styles'); ?>',
        	post_id: $(this).data('global-styles').ID,
        	theme: $(this).data('global-styles').theme
        };
	    $.ajax({
    	    url: ajaxurl,
    	    dataType: 'text',
    	    data: data,
            type: 'POST',
            success: function(wpGlobalStylesJson) {
                var wpGlobalStylesFile = new Blob(
                    [wpGlobalStylesJson], {
                        type : "application/json;charset=utf-8"
                    }
                );
                var today = new Date();
                var dd = today.getDate();
                var mm = today.getMonth() + 1;
                var yyyy = today.getFullYear();
                if (dd < 10) {
                    dd = '0' + dd;
                }
                if (mm < 10) {
                    mm = '0' + mm;
                }
                today = yyyy + '_' + mm + '_' + dd;
                var downloadLink = document.createElement('a');
                var downloadURL = window.URL.createObjectURL(wpGlobalStylesFile);
                downloadLink.href = downloadURL;
                downloadLink.download = data.theme + '_' + today + '.json';
                document.body.append(downloadLink);
                downloadLink.click();
                downloadLink.remove();
                window.URL.revokeObjectURL(downloadURL);
            },
            error: function() {
                alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
            }
	    });
    });
    $('.te-styles-delete').click(function() {
        if (confirm('<?php _e('Are you sure you want to delete the selected global styles?', 'template-editor'); ?>')) {
            $('#te-styles-delete').unbind('click')
            var data = {
            	action: 'te_global_styles_delete',
            	_ajax_nonce: '<?php echo wp_create_nonce('template-editor-delete'); ?>',
            	post_id: $(this).data('global-styles')
            };
    	    $.ajax({
        	    url: ajaxurl,
        	    data: data,
                type: 'POST',
                success: function(response) {
                    if ('success' in response && response.success) {
                        window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_global_styles'
                        ), admin_url('themes.php')); ?>';
                    } else {
                        alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                        window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_global_styles'
                        ), admin_url('themes.php')); ?>';
                    }
                },
                error: function() {
                    alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                    window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_global_styles'
                        ), admin_url('themes.php')); ?>';
                }
    	    });
        }
    });
    $('.te-styles-upload').click(function() {
        document.getElementById('te-styles-json-file').click();
    });
    $('#te-styles-json-file').change(function() {
        var confirmText = '<?php _e('Are you sure you want to upload %s as the active theme\\\'s global styles?', 'template-editor'); ?>';
        if (confirm(confirmText.replace('%s', $('#te-styles-json-file').prop('files')[0].name))) {
            var data = new FormData();
            data.append('action', 'te_upload_wp_global_styles');
            data.append('_ajax_nonce', '<?php echo wp_create_nonce('upload-wp-global-styles'); ?>');
            data.append('file', $('#te-styles-json-file').prop('files')[0]);
    	    $.ajax({
        	    url: ajaxurl,
        	    data: data,
                type: 'POST',
                contentType: false,
                processData: false,
                success: function(response) {
                    if ('success' in response && response.success) {
                        window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_global_styles'
                        ), admin_url('themes.php')); ?>';
                    } else {
                        alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                        window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_global_styles'
                        ), admin_url('themes.php')); ?>';
                    }
                },
                error: function() {
                    alert('<?php _e('Something went wrong!', 'template-editor'); ?>');
                    window.location.href = '<?php echo add_query_arg(array(
                            'page' => 'template_editor',
                            'tab' => 'manage_global_styles'
                        ), admin_url('themes.php')); ?>';
                }
    	    });
        }
	});
})(jQuery);
</script>
</div>
<div class="wrap tab_content theme_options"<?php echo ('theme_options' === $current_tab ? '' : ' style="display: none;"'); ?>>
<h1><?php _e('Theme Options', 'template-editor'); ?></h1>
<form action="options.php" method="post">
<?php settings_fields('te_options'); ?>
<?php do_settings_sections('te_general_options'); ?>
<?php do_settings_sections('te_fonts'); ?>
<input name="submit" type="submit" value="<?php _e('Save Options', 'template-editor'); ?>" class="button" />
<input type="hidden" name="_wp_http_referer" value="<?php echo esc_url(add_query_arg(array(
    'page' => 'template_editor',
    'tab' => 'theme_options'
), admin_url('themes.php'))); ?>">
</form>
</div>
<?php

        }

        public static function te_save() {

            check_ajax_referer('template-editor-save');

            if (
                current_user_can('manage_options') &&
                isset($_POST['post_id']) && absint($_POST['post_id']) &&
                isset($_POST['post_title']) && sanitize_text_field($_POST['post_title']) &&
                isset($_POST['post_name']) && sanitize_title($_POST['post_name']) &&
                isset($_POST['post_excerpt']) && sanitize_text_field($_POST['post_excerpt']) &&
                isset($_POST['save_as_copy']) && in_array($_POST['save_as_copy'], ['0', '1'], true)
            ) {

                $post_id = absint($_POST['post_id']);
                $post_title = sanitize_text_field($_POST['post_title']);
                $post_name = sanitize_title($_POST['post_name']);
                $post_excerpt = sanitize_text_field($_POST['post_excerpt']);
                $save_as_copy = absint($_POST['save_as_copy']);

                if ($save_as_copy) {

                    $current_user = wp_get_current_user();
                    $post = get_post($post_id);

		            $new_post_id = wp_insert_post([
                        'comment_status' => 'closed',
                        'ping_status'    => 'closed',
                        'post_author'    => $current_user->ID,
                        'post_content'   => $post->post_content,
                        'post_excerpt'   => $post_excerpt,
                        'post_name'      => $post_name,
                        'post_status'    => 'publish',
                        'post_title'     => $post_title,
                        'post_type'      => 'wp_template'
                    ]);

                    if ($new_post_id && !is_wp_error($new_post_id)) {

        				wp_set_object_terms($new_post_id, get_stylesheet(), 'wp_theme', false);
                        update_post_meta($new_post_id, 'origin', 'theme');

                    } else {

                        wp_send_json_error();

                    }

                } else {

                    wp_update_post([
                        'ID' => $post_id,
                        'post_title' => $post_title,
                        'post_name' => $post_name,
                        'post_excerpt' => $post_excerpt
                    ]);

                }

                wp_send_json_success();

            } else {

                wp_send_json_error();

            }

        }

        public static function te_delete() {

            check_ajax_referer('template-editor-delete');

            if (
                current_user_can('manage_options') &&
                isset($_POST['post_id']) && absint($_POST['post_id'])
            ) {

                wp_delete_post(absint($_POST['post_id']), true);

                wp_send_json_success();

            } else {

                wp_send_json_error();

            }

        }

        public static function te_global_styles_delete() {

            check_ajax_referer('template-editor-delete');

            if (
                current_user_can('manage_options') &&
                isset($_POST['post_id']) && absint($_POST['post_id'])
            ) {

                wp_delete_post(absint($_POST['post_id']), true);

                wp_send_json_success();

            } else {

                wp_send_json_error();

            }

        }

        public static function te_download_wp_template() {

            check_ajax_referer('download-wp-template');

            if (
                current_user_can('manage_options') &&
                isset($_POST['post_id']) && absint($_POST['post_id'])
            ) {

                $template = get_post(absint($_POST['post_id']));

                if ($template) {

                    wp_send_json(array(
                        'post_content'   => $template->post_content,
                        'post_excerpt'   => $template->post_excerpt,
                        'post_name'      => $template->post_name,
                        'post_title'     => $template->post_title,
                    ));

                } else {

                    wp_send_json_error();

                }

            } else {

                wp_send_json_error();

            }

        }

        public static function te_download_wp_global_styles() {

            check_ajax_referer('download-wp-global-styles');

            if (
                current_user_can('manage_options') &&
                isset($_POST['post_id']) && absint($_POST['post_id'])
            ) {

                $global_styles = get_post(absint($_POST['post_id']));

                if ($global_styles) {

                    wp_send_json(array(
                        'post_content'   => json_decode($global_styles->post_content),
                        'post_name'      => $global_styles->post_name,
                        'post_title'     => $global_styles->post_title,
                    ));

                } else {

                    wp_send_json_error();

                }

            } else {

                wp_send_json_error();

            }

        }

        public static function te_upload_wp_template() {

            check_ajax_referer('upload-wp-template');

            if (
                current_user_can('manage_options') &&
                isset($_FILES['file']['tmp_name']) &&
                isset($_FILES['file']['type']) && $_FILES['file']['type'] == 'application/json'
            ) {

                $json_data = file_get_contents($_FILES['file']['tmp_name']);
                $template = false;

                if ($json_data) {

                    $template = json_decode($json_data, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {

                        $template = false;

                    }

                }

                if (!(
                    isset($template['post_content']) && $template['post_content'] &&
                    isset($template['post_name']) && sanitize_title($template['post_name']) &&
                    isset($template['post_title']) && sanitize_text_field($template['post_title'])
                )) {

                    $template = false;

                }

                if ($template) {

                    $current_user = wp_get_current_user();

		            $new_post_id = wp_insert_post([
                        'comment_status' => 'closed',
                        'ping_status'    => 'closed',
                        'post_author'    => $current_user->ID,
                        'post_content'   => $template['post_content'],
                        'post_excerpt'   => (isset($template['post_excerpt']) ? sanitize_text_field($template['post_excerpt']) : ''),
                        'post_name'      => sanitize_title($template['post_name']),
                        'post_status'    => 'publish',
                        'post_title'     => sanitize_text_field($template['post_title']),
                        'post_type'      => 'wp_template'
                    ]);

                    if ($new_post_id && !is_wp_error($new_post_id)) {

        				wp_set_object_terms($new_post_id, get_stylesheet(), 'wp_theme', false);
                        update_post_meta($new_post_id, 'origin', 'theme');

                    } else {

                        wp_send_json_error();

                    }

                    wp_send_json_success();

                } else {

                    wp_send_json_error();

                }

            } else {

                wp_send_json_error();

            }

        }

        public static function te_upload_wp_global_styles() {

            check_ajax_referer('upload-wp-global-styles');

            if (
                current_user_can('manage_options') &&
                isset($_FILES['file']['tmp_name']) &&
                isset($_FILES['file']['type']) && $_FILES['file']['type'] == 'application/json'
            ) {

                $json_data = file_get_contents($_FILES['file']['tmp_name']);
                $global_styles = false;

                if ($json_data) {

                    $global_styles = json_decode($json_data, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {

                        $global_styles = false;

                    }

                }

                if (!(
                    isset($global_styles['post_content']) && $global_styles['post_content']
                )) {

                    $global_styles = false;

                }

                if ($global_styles) {

                    $current_user = wp_get_current_user();
                    $global_styles_post_args = new stdClass();
                    $global_styles_post_args->comment_status = 'closed';
                    $global_styles_post_args->ping_status = 'closed';
                    $global_styles_post_args->post_author = $current_user->ID;
                    $global_styles_post_args->post_content = json_encode($global_styles['post_content']);
                    $global_styles_post_args->post_excerpt = '';
                    $global_styles_post_args->post_name = 'wp-global-styles-' . get_stylesheet();
                    $global_styles_post_args->post_status = 'publish';
                    $global_styles_post_args->post_title = 'Custom Styles';
                    $global_styles_post_args->post_type = 'wp_global_styles';

		            $new_post_id = wp_insert_post(wp_slash( (array) $global_styles_post_args), true, false);

                    if ($new_post_id && !is_wp_error($new_post_id)) {

        				wp_set_object_terms($new_post_id, get_stylesheet(), 'wp_theme', false);

                    } else {

                        wp_send_json_error();

                    }

                    if (wp_cache_supports('flush_group')) {

                        wp_cache_flush_group('theme_json');

                    } else {

                        wp_cache_flush();

                    }

                    wp_send_json_success();

                } else {

                    wp_send_json_error();

                }

            } else {

                wp_send_json_error();

            }

        }

        public static function te_upload_wp_template_part() {

            check_ajax_referer('upload-wp-template-part');

            if (
                current_user_can('manage_options') &&
                isset($_FILES['file']['tmp_name']) &&
                isset($_FILES['file']['type']) && $_FILES['file']['type'] == 'application/json'
            ) {

                $json_data = file_get_contents($_FILES['file']['tmp_name']);
                $template = false;

                if ($json_data) {

                    $template_part = json_decode($json_data, true);

                    if (json_last_error() !== JSON_ERROR_NONE) {

                        $template = false;

                    }

                }

                if (!(
                    isset($template_part['post_content']) && $template_part['post_content'] &&
                    isset($template_part['post_name']) && sanitize_title($template_part['post_name']) &&
                    isset($template_part['post_title']) && sanitize_text_field($template_part['post_title'])
                )) {

                    $template_part = false;

                }

                if ($template_part) {

                    $current_user = wp_get_current_user();

		            $new_post_id = wp_insert_post([
                        'comment_status' => 'closed',
                        'ping_status'    => 'closed',
                        'post_author'    => $current_user->ID,
                        'post_content'   => $template_part['post_content'],
                        'post_excerpt'   => (isset($template_part['post_excerpt']) ? sanitize_text_field($template_part['post_excerpt']) : ''),
                        'post_name'      => sanitize_title($template_part['post_name']),
                        'post_status'    => 'publish',
                        'post_title'     => sanitize_text_field($template_part['post_title']),
                        'post_type'      => 'wp_template_part'
                    ]);

                    if ($new_post_id && !is_wp_error($new_post_id)) {

        				wp_set_object_terms($new_post_id, get_stylesheet(), 'wp_theme', false);
                        update_post_meta($new_post_id, 'origin', 'theme');

                    } else {

                        wp_send_json_error();

                    }

                    wp_send_json_success();

                } else {

                    wp_send_json_error();

                }

            } else {

                wp_send_json_error();

            }

        }

        public function admin_init() {

	        register_setting('te_options', 'te_options', array($this, 'validate_options'));
	        add_settings_section('te_options', __('General Options', 'template-editor'), 'template_editor_class::general_options_text', 'te_general_options');

            if (!$this->is_block_theme) {

    	        add_settings_field('enable_template_editor', 'Template Editor', 'template_editor_class::enable_template_editor_string', 'te_general_options', 'te_options');

            }

	        add_settings_field('hide_customize_link', __('Hide Customize Link', 'template-editor'), 'template_editor_class::hide_customize_link_string', 'te_general_options', 'te_options');
	        add_settings_field('enable_sticky_header', __('Sticky Header', 'template-editor'), 'template_editor_class::enable_sticky_header_string', 'te_general_options', 'te_options');
	        add_settings_field('animate_header_logo', __('Animate Logo', 'template-editor'), 'template_editor_class::animate_header_logo_string', 'te_general_options', 'te_options');

            if (!$this->is_block_theme || version_compare(strtok(get_bloginfo('version'), '-'), '6.5', '<')) {

    	        add_settings_section('te_options', __('Fonts', 'template-editor'), 'template_editor_class::fonts_text', 'te_fonts');
    	        add_settings_field('inject_google_fonts', __('Inject Google Font', 'template-editor'), 'template_editor_class::inject_google_fonts_string', 'te_fonts', 'te_options');
    	        add_settings_field('google_font_display', __('Google Font Display', 'template-editor'), 'template_editor_class::google_font_display_string', 'te_fonts', 'te_options');

                if ($this->is_block_theme) {

    	            add_settings_field('remove_theme_fonts', __('Remove Theme Fonts', 'template-editor'), 'template_editor_class::remove_theme_fonts_string', 'te_fonts', 'te_options');

                }

        		wp_add_inline_style('wp-block-library', self::get_font_face_styles());

            } else {

    	        add_settings_section('te_options', __('Fonts', 'template-editor'), 'template_editor_class::fonts_text_new', 'te_fonts');

            }

        }

        public static function general_options_text() {

?>
<p><?php echo sprintf(wp_kses(__('Here are some options for Full Site Editor themes. If you\'d like to see more options here, <a href="%s">let us know in the support forum</a>.', 'template-editor'), array('a' => array('href' => array(), 'class' => array()))), esc_url('https://wordpress.org/support/plugin/template-editor/')); ?></p>
<?php

        }

        public static function enable_template_editor_string() {

        	$options = get_option('te_options');

?>
<p><input type="checkbox" id="enable_template_editor" name="te_options[enable_template_editor]" value="1"<?php checked('1', ((isset($options['enable_template_editor'])) ? $options['enable_template_editor'] : '')); ?> /> <?php _e('Enable the template editor for this theme.', 'template-editor'); ?></p>
<?php

        }

        public static function hide_customize_link_string() {

        	$options = get_option('te_options');

?>
<p><input type="checkbox" id="hide_customize_link" name="te_options[hide_customize_link]" value="1"<?php checked('1', ((isset($options['hide_customize_link'])) ? $options['hide_customize_link'] : '')); ?> /> <?php _e('Hide link to Customizer (this will be ineffective if your site\'s plugins or themes register widget areas or menu locations).', 'template-editor'); ?></p>
<?php

        }

        public static function enable_sticky_header_string() {

        	$options = get_option('te_options');

?>
<p><input type="checkbox" id="enable_sticky_header" name="te_options[enable_sticky_header]" value="1"<?php checked('1', ((isset($options['enable_sticky_header'])) ? $options['enable_sticky_header'] : '')); ?> /> <?php _e('Enable sticky header.', 'template-editor'); ?></p>
<?php

        }

        public static function animate_header_logo_string() {

        	$options = get_option('te_options');

?>
<p><input type="checkbox" id="animate_header_logo" name="te_options[animate_header_logo]" value="1"<?php checked('1', ((isset($options['animate_header_logo'])) ? $options['animate_header_logo'] : '')); ?> /> <?php _e('Shrink header logo on scroll. This option will also add a "shrink-logo" class to the header on scroll if you want to add your own CSS effects.', 'template-editor'); ?></p>
<p><input type="number" id="animate_header_logo_width" name="te_options[animate_header_logo_width]" value="<?php echo esc_attr((isset($options['animate_header_logo_width']) && absint($options['animate_header_logo_width'])) ? absint($options['animate_header_logo_width']) - 1 : '48'); ?>" /><?php _e('px width when shrunk', 'template-editor'); ?></p>
<?php

        }

        public static function fonts_text() {

?>
<p><?php echo __('We\'ve worked out how to inject Google Fonts into the Full Site Editor without modifying the theme or creating a child theme!', 'template-editor'); ?></p>
<?php

        }

        public static function fonts_text_new() {

        	$options = get_option('te_options');

?>
<p><?php echo __('With the launch of WordPress v6.5 came the new "Font Library". This awesome core update allows you to manage fonts from the Site Editor.', 'template-editor'); ?></p>
<?php

            if (isset($options['inject_google_fonts']) && is_array($options['inject_google_fonts']) && $options['inject_google_fonts']) {

?>
<p style="color: red;"><strong><?php echo __('We notice that you use this plugin to inject the following Google fonts. If you haven\'t already you should use the Site Editor to add these fonts again:', 'template-editor'); ?>
<?php

                $count_fonts = 0;

                foreach ($options['inject_google_fonts'] as $font => $styles) {

                    if (!is_array($styles)) { $styles = array($styles); }

                    foreach ($styles as $key => $style) {

?>
<?php if (0 !== $count_fonts) { echo ', '; } ?> <?php echo esc_html($font); ?> <?php echo esc_html(str_replace(array('i', '100', '200', '300', '400', '500', '600', '700', '800', '900'), array(' italic', 'Thin (100)', 'Extra Light (200)', 'Light (300)', 'Normal (400)', 'Medium (500)', 'Semi Bold (600)', 'Bold (700)', 'Extra Bold (800)', 'Ultra Bold (900)'), $style)); ?>
<?php

                        $count_fonts++;

                    }

                }

?>
</strong></p>
<p><?php echo __('Go to "Dashboard - Appearance - Editor - Styles - <edit pencil icon> - Typography". From there click on any font to enter the "Font Library". It is important that you first remove the above Google fonts then re-install them using the "Install Fonts" tab of the "Font Library".', 'template-editor'); ?></p>
<?php

            }

        }

        public static function inject_google_fonts_string() {

        	$options = get_option('te_options');

            $googlefonts = json_decode(file_get_contents(__DIR__ . '/json/google-fonts.json'), true);

            if ($googlefonts) {

                if (isset($options['abort_font_download']) && $options['abort_font_download']) {

?>
<p style="color: red;"><?php _e('Warning: Font download failed so fonts are not being hosted locally.', 'template-editor'); ?></p>
<?php

                }

?>
<p><select id="google-fonts">
<option value=""><?php _e('Choose a font family ...', 'template-editor'); ?></option>
<?php

                foreach ($googlefonts as $font) {

?>
<option value="<?php echo esc_attr($font['f']); ?>" data-slug="<?php echo esc_attr(sanitize_title($font['f'])); ?>" data-fonts="<?php echo esc_attr(json_encode($font['v'])); ?>"><?php echo esc_html($font['f']); ?></option>
<?php

                }

?>
</select></p>
<p><select id="google-font-style-selector"></select></p>
<div id="chosen-google-fonts"><?php

                if (isset($options['inject_google_fonts']) && is_array($options['inject_google_fonts']) && $options['inject_google_fonts']) {

                    foreach ($options['inject_google_fonts'] as $font => $styles) {

                        if (!is_array($styles)) { $styles = array($styles); }

                        foreach ($styles as $style) {

                            $value = (object)array(
                                'slug' => sanitize_title($font),
                                'title' => $font,
                                'style' => $style
                            );

?>
<label for="<?php echo esc_attr(sanitize_title($font)); ?>-<?php echo esc_attr(sanitize_title($style)); ?>"><input type="checkbox" id="<?php echo esc_attr(sanitize_title($font)); ?>-<?php echo esc_attr(sanitize_title($style)); ?>" name="te_options[inject_google_fonts][<?php echo esc_attr(sanitize_title($font)); ?>-<?php echo esc_attr(sanitize_title($style)); ?>]" class="chosen-google-font" value="<?php echo esc_attr(json_encode($value)); ?>" checked="checked"> <?php echo esc_html($font); ?> <?php echo esc_html(str_replace(array('i', '100', '200', '300', '400', '500', '600', '700', '800', '900'), array(' italic', 'Thin (100)', 'Extra Light (200)', 'Light (300)', 'Normal (400)', 'Medium (500)', 'Semi Bold (600)', 'Bold (700)', 'Extra Bold (800)', 'Ultra Bold (900)'), $style)); ?> </label>
<?php

                        }

                    }

                }

?></div>
<p><label for=""><input type="checkbox" id="local_google_fonts" name="te_options[local_google_fonts]" value="1" <?php checked('1', ((isset($options['google_font_css']) && $options['google_font_css']) ? '1' : '')); ?>/> <?php esc_html_e('Attempt to host fonts locally.', 'template-editor'); ?></label></p>
<script type="text/javascript">
    (function($) {
        $('#google-fonts').change(function() {
            var googleFont = $(this).find(':selected').val(),
                googleFontSlug = $(this).find(':selected').data('slug'),
                googleFontStyles = $(this).find(':selected').data('fonts'),
                chosenGoogleFont,
                chosenGoogleFontLabel;
            if (googleFontStyles.length > 1) {
                $('#google-font-style-selector').empty();
                $('<option />', { value: '' }).text(<?php echo json_encode(esc_html__('Choose a font style ...', 'template-editor')); ?>).appendTo($('#google-font-style-selector'));
                $.each(googleFontStyles, function(i, googleFontStyle) {
                    $('<option />', {
                        value: JSON.stringify({
                            'slug': googleFontSlug, 
                            'title': googleFont, 
                            'style': googleFontStyle
                        })
                    }).text(googleFont + ' ' + googleFontStyle.replace('i', ' italic').replace('100', 'Thin (100)').replace('200', 'Extra Light (200)').replace('300', 'Light (300)').replace('400', 'Normal (400)').replace('500', 'Medium (500)').replace('600', 'Semi Bold (600)').replace('700', 'Bold (700)').replace('800', 'Extra Bold (800)').replace('900', 'Ultra Bold (900)')).appendTo($('#google-font-style-selector'));
                });
            } else {
                $('#google-font-style-selector').empty();
                $('<option />', {
                    value: JSON.stringify({
                        'slug': googleFontSlug, 
                        'title': googleFont, 
                        'style': googleFontStyles[0]
                    }) 
                }).text(googleFont + ' ' + googleFontStyles[0].replace('i', ' italic').replace('100', 'Thin (100)').replace('200', 'Extra Light (200)').replace('300', 'Light (300)').replace('400', 'Normal (400)').replace('500', 'Medium (500)').replace('600', 'Semi Bold (600)').replace('700', 'Bold (700)').replace('800', 'Extra Bold (800)').replace('900', 'Ultra Bold (900)')).appendTo($('#google-font-style-selector'));
                if (!$('#chosen-google-fonts #google-font-' + googleFontSlug + '-' + googleFontStyles[0]).length) {
                    chosenGoogleFontLabel = $('<label />', { 'for': 'google-font-' + googleFontSlug + '-' + googleFontStyles[0], text: ' ' + googleFont + ' ' + googleFontStyles[0].replace('i', ' italic').replace('100', 'Thin (100)').replace('200', 'Extra Light (200)').replace('300', 'Light (300)').replace('400', 'Normal (400)').replace('500', 'Medium (500)').replace('600', 'Semi Bold (600)').replace('700', 'Bold (700)').replace('800', 'Extra Bold (800)').replace('900', 'Ultra Bold (900)') + ' ' });
                    chosenGoogleFont = $('<input />', {
                        type: 'checkbox',
                        id: 'google-font-' + googleFontSlug + '-' + googleFontStyles[0],
                        name: 'te_options[inject_google_fonts][' + googleFontSlug + '-' + googleFontStyles[0] + ']',
                        value: JSON.stringify({
                            'slug': googleFontSlug, 
                            'title': googleFont, 
                            'style': googleFontStyles[0]
                        }),
                        checked: 'checked'
                    });
                    chosenGoogleFont.change(function() { deselectChosenFont(this); });
                    chosenGoogleFont.prependTo(chosenGoogleFontLabel);
                    chosenGoogleFontLabel.appendTo($('#chosen-google-fonts'));
                }
            };
        });
        $('#google-font-style-selector').change(function() {
            if ($(this).find(':selected').val()) {
                var chosenGoogleFontObject = JSON.parse($(this).find(':selected').val()),
                    chosenGoogleFont,
                    chosenGoogleFontLabel;
                if (!$('#chosen-google-fonts #google-font-' + chosenGoogleFontObject.slug + '-' + chosenGoogleFontObject.style).length) {
                    chosenGoogleFontLabel = $('<label />', { 'for': 'google-font-' + chosenGoogleFontObject.slug + '-' + chosenGoogleFontObject.style, text: ' ' + chosenGoogleFontObject.title + ' ' + chosenGoogleFontObject.style.replace('i', ' italic').replace('100', 'Thin (100)').replace('200', 'Extra Light (200)').replace('300', 'Light (300)').replace('400', 'Normal (400)').replace('500', 'Medium (500)').replace('600', 'Semi Bold (600)').replace('700', 'Bold (700)').replace('800', 'Extra Bold (800)').replace('900', 'Ultra Bold (900)') + ' ' });
                    chosenGoogleFont = $('<input />', {
                        type: 'checkbox',
                        id: 'google-font-' + chosenGoogleFontObject.slug + '-' + chosenGoogleFontObject.style,
                        name: 'te_options[inject_google_fonts][' + chosenGoogleFontObject.slug + '-' + chosenGoogleFontObject.style + ']',
                        value: $(this).find(':selected').val(),
                        checked: 'checked'
                    });
                    chosenGoogleFont.change(function() { deselectChosenFont(this); });
                    chosenGoogleFont.prependTo(chosenGoogleFontLabel);
                    chosenGoogleFontLabel.appendTo($('#chosen-google-fonts'));
                }
            }
        });
        var deselectChosenFont = function(e) {
            $('label[for=' + $(e).attr('id') + ']').remove();
        }
        $('.chosen-google-font').change(function() { deselectChosenFont(this); });
    })(jQuery);
</script>
<?php

            }

        }

        public static function google_font_display_string() {

        	$options = get_option('te_options');
            $font_display_options = array(
                'auto' => '"auto" - Most browsers currently default to "block".', 
                'block' => '"block" - Wait until the font has loaded before drawing the text.', 
                'swap' => '"swap" - Draw the text straight away then re-draw the text when the font has loaded.',
                'fallback' => '"fallback" - Waits for a <strong>very</strong> short time before drawing the text anyway if the font hasn\'t yet loaded then re-draws the text if the font loads shortly after.',
                'optional' => '"optional" - Waits for a <strong>very</strong> short time before drawing the text anyway but <strong>doesn\'t</strong> re-draw when the font loads.'
            );

?><p><?php

            $i = 0;

            foreach ($font_display_options as $key => $value) {

                $i++;

?>
<label for="google-font-display-<?php echo $key; ?>">
<input type="radio" id="google-font-display-<?php echo $key; ?>" name="te_options[google_font_display]" value="<?php echo $key; ?>"<?php if (isset($options['google_font_display'])) { checked($options['google_font_display'], $key); } else { checked('auto', $key); } ?>>
<?php echo $value ?>
</label>
<?php

                if ($i !== count($font_display_options)) { echo '<br />'; }

            }

?></p>
<?php

        }

        public static function remove_theme_fonts_string() {

        	$options = get_option('te_options');

            if (method_exists('WP_Theme_JSON_Resolver', 'get_user_data_from_wp_global_styles')) {

    			$theme_json_file = is_readable(get_stylesheet_directory() . '/theme.json') ? get_stylesheet_directory() . '/theme.json' : '';
    			$wp_theme = wp_get_theme();

    			if ($theme_json_file) {

    				$theme_json_data = wp_json_file_decode($theme_json_file, array('associative' => true));

    			} else {

    				$theme_json_data = array();

    			}

                remove_filter('wp_theme_json_data_theme', 'template_editor_class::wp_theme_json_data_theme');
    			$theme_json = apply_filters('wp_theme_json_data_theme', new WP_Theme_JSON_Data($theme_json_data, 'theme'));
                add_filter('wp_theme_json_data_theme', 'template_editor_class::wp_theme_json_data_theme');
    			$theme_json_data = $theme_json->get_data();
    			$theme = new WP_Theme_JSON($theme_json_data);

    			if ($wp_theme->parent()) {

    				$parent_theme_json_file = is_readable(get_template_directory() . '/theme.json') ? get_template_directory() . '/theme.json' : '';

    				if ('' !== $parent_theme_json_file) {

    					$parent_theme_json_data = wp_json_file_decode($parent_theme_json_file, array('associative' => true));
    					$parent_theme = new WP_Theme_JSON($parent_theme_json_data);
    					$parent_theme->merge($theme);
    					$theme = $parent_theme;

    				}

    			}

                $theme_data = $theme->get_settings();

                if (isset($theme_data['typography']['fontFamilies']['theme']) && is_array($theme_data['typography']['fontFamilies']['theme'])) {

                    foreach ($theme_data['typography']['fontFamilies']['theme'] as $key => $font_family) {

?>
<p><label for="theme-font-<?php echo esc_attr($font_family['slug']); ?>">
<input type="checkbox" id="theme-font-<?php echo esc_attr($font_family['slug']); ?>" name="te_options[remove_theme_fonts][<?php echo esc_attr($font_family['slug']); ?>]" value="<?php echo esc_attr($font_family['name']); ?>"<?php checked(isset($options['remove_theme_fonts'][$font_family['slug']]), true); ?>>
<?php echo esc_html($font_family['name']); ?>
</label></p>
<?php

                    }

                }

            }

        }

        public function validate_options($input) {

            if (current_user_can('manage_options')) {

            	$options = get_option('te_options');

                if (isset($input['enable_template_editor']) && '1' === $input['enable_template_editor']) {

                    $options['enable_template_editor'] = '1';

                } else {

                    unset($options['enable_template_editor']);

                }

                if (isset($input['hide_customize_link']) && '1' === $input['hide_customize_link']) {

                    $options['hide_customize_link'] = '1';

                } else {

                    unset($options['hide_customize_link']);

                }

                if (isset($input['enable_sticky_header']) && '1' === $input['enable_sticky_header']) {

                    $options['enable_sticky_header'] = '1';

                } else {

                    unset($options['enable_sticky_header']);

                }

                if (isset($input['animate_header_logo']) && '1' === $input['animate_header_logo']) {

                    $options['animate_header_logo'] = '1';

                } else {

                    unset($options['animate_header_logo']);

                }

                if (isset($input['animate_header_logo_width'])) {

                    $options['animate_header_logo_width'] = absint($input['animate_header_logo_width']) + 1;

                } else {

                    unset($options['animate_header_logo_width']);

                }

                if (isset($input['inject_google_fonts']) && is_array($input['inject_google_fonts']) && $input['inject_google_fonts']) {

                    $options['inject_google_fonts'] = array();

                    foreach ($input['inject_google_fonts'] as $google_font) {

                        $google_font = json_decode($google_font);

                        if (isset($google_font->title) && isset($google_font->style)) {

                            if (isset($options['inject_google_fonts'][sanitize_text_field($google_font->title)])) {

                                $options['inject_google_fonts'][sanitize_text_field($google_font->title)][] = sanitize_key($google_font->style);

                            } else {

                                $options['inject_google_fonts'][sanitize_text_field($google_font->title)] = array(sanitize_key($google_font->style));

                            }

                        }

                    }

                    if (method_exists('WP_Theme_JSON_Resolver', 'get_user_data_from_wp_global_styles')) {

                        $user_cpt = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles(wp_get_theme());

		                if (array_key_exists('post_content', $user_cpt)) {

                			$decoded_data = json_decode($user_cpt['post_content'], true);

                            if (!isset($decoded_data['settings'])) {

                                $decoded_data['settings'] = array();

                            }

                            if (!isset($decoded_data['settings']['typography'])) {

                                $decoded_data['settings']['typography'] = array();

                            }

                            $decoded_data['settings']['typography']['fontFamilies'] = array();

                            foreach ($options['inject_google_fonts'] as $font => $styles) {

                                if (!is_array($styles)) { $styles = array($styles); }

                                foreach ($styles as $style) {

                                    $found = false;

                                    foreach ($decoded_data['settings']['typography']['fontFamilies'] as $value) {

                                        if (isset($value['slug']) && sanitize_title($font) === $value['slug']) {

                                            $found = true;
                                            break;
    
                                        }

                                    }

                                    if (!$found) {

                                        $decoded_data['settings']['typography']['fontFamilies'][] = array(
                                            'fontFamily' => '"' . $font . '"',
                                        	'name' => $font,
                                        	'slug' => sanitize_title($font)
                                        );

                                    }

                                }

                            }

                            if (isset($user_cpt['ID'])) {

                                wp_update_post(wp_slash(array(
                                    'ID' => $user_cpt['ID'],
                                    'post_content' => wp_json_encode($decoded_data)
                                )));

                            }

                        }

                    }

                } else {

                    unset($options['inject_google_fonts']);

                    if (!$this->is_block_theme || version_compare(strtok(get_bloginfo('version'), '-'), '6.5', '<')) {

                        if (method_exists('WP_Theme_JSON_Resolver', 'get_user_data_from_wp_global_styles')) {

                            $user_cpt = WP_Theme_JSON_Resolver::get_user_data_from_wp_global_styles(wp_get_theme());

	    	                if (array_key_exists('post_content', $user_cpt)) {

                    			$decoded_data = json_decode($user_cpt['post_content'], true);
                                unset($decoded_data['settings']['typography']['fontFamilies']);

                                if (isset($user_cpt['ID'])) {

                                    wp_update_post(wp_slash(array(
                                        'ID' => $user_cpt['ID'],
                                        'post_content' => wp_json_encode($decoded_data)
                                    )));

                                }

                            }

                        }

                    }

                }

                global $wp_filesystem;

                if (empty($wp_filesystem)) {

                    require_once (ABSPATH . '/wp-admin/includes/file.php');
                    WP_Filesystem();

                }

                unset($options['abort_font_download']);
                $upload_dir = wp_upload_dir();
                $filepath = $upload_dir['basedir'] . '/gstatic/';

                if (
                    isset($options['inject_google_fonts']) &&
                    is_array($options['inject_google_fonts']) &&
                    $options['inject_google_fonts'] &&
                    isset($input['local_google_fonts']) &&
                    '1' === $input['local_google_fonts']
                ) {

                    $google_font_families = array();

                    foreach ($options['inject_google_fonts'] as $font => $styles) {

                        if (!is_array($styles)) { $styles = array($styles); }

                        foreach ($styles as $style) {

                            if (isset($google_font_families[$font])) {

                                $google_font_families[$font]['styles'][] = $style;

                            } else {

                                $google_font_families[$font] = array('styles' => array($style));

                            }

                            if ('i' === substr($style, -1)) {

                                $google_font_families[$font]['italic'] = true;

                            }

                        }

                    }

                    $css_import_string = 'https://fonts.googleapis.com/css2';
                    $i = 1;

                    foreach ($google_font_families as $font => $styles) {

                        if (1 === $i) {

                            $css_import_string .= '?';

                        } else {

                            $css_import_string .= '&';

                        }

                        $css_import_string .= 'family=' . urlencode($font) . ':';

                        if (isset($styles['italic'])) {

                            $css_import_string .= 'ital,wght';

                        } else {

                            $css_import_string .= 'wght';

                        }

                        $j = 1;

                        sort($styles['styles']);

                        foreach ($styles['styles'] as $style) {

                            if (1 === $j) {

                                $css_import_string .= '@';

                            } else {

                                $css_import_string .= ';';

                            }

                            if (isset($styles['italic'])) {

                                $css_import_string .= ('i' === substr($style, -1) ? '1' : '0') . ',' . str_replace('i', '', $style);

                            } else {

                                $css_import_string .= $style;

                            }

                            $j++;

                        }

                        $i++;

                    }

                    $css_import_string .= '&display=' . (isset($options['google_font_display']) ? sanitize_key($options['google_font_display']) : 'auto');

                    $google_request = wp_safe_remote_get($css_import_string,  array(
                        'user-agent' => $_SERVER['HTTP_USER_AGENT']
                    ));

                    $abort_font_download = false;

                    if (
                        !is_wp_error($google_request) &&
                        isset($google_request['body']) &&
                        isset($google_request['response']['code']) &&
                        200 === $google_request['response']['code']
                    ) {

                        preg_match_all('/(https:\/\/fonts\.gstatic\.com\/s\/.*[\.woff|\.woff2|\.ttf|\.otf])\) /', $google_request['body'], $google_fonts);

                        if (isset($google_fonts[1]) && $google_fonts[1]) {

                            $google_fonts = array_unique($google_fonts[1]);

                            if (!is_dir($filepath)) {

                                mkdir($filepath);

                            } else {

                                $fileSystemDirect = new WP_Filesystem_Direct(false);
                                $fileSystemDirect->rmdir($filepath, true);
                                mkdir($filepath);

                            }

                            $google_font_css = $google_request['body'];

                            foreach ($google_fonts as $google_font_url) {

                                $google_font = wp_safe_remote_get($google_font_url,  array(
                                    'user-agent' => $_SERVER['HTTP_USER_AGENT']
                                ));

                                if (
                                    !is_wp_error($google_font) &&
                                    isset($google_font['body']) &&
                                    isset($google_font['response']['code']) &&
                                    200 === $google_font['response']['code']
                                ) {

                                    $google_font_filename = wp_basename($google_font_url);
                                    $wp_filesystem->put_contents($filepath . $google_font_filename, $google_font['body']);
                                    $parse_url = parse_url($upload_dir['baseurl']);

                                    $google_font_css = str_replace(
                                        $google_font_url,
                                        '\'[HTTP_HOST]' . $parse_url['path'] . '/gstatic/' . $google_font_filename . '\'',
                                        $google_font_css
                                    );

                                } else {

                                    $abort_font_download = true;

                                    break;

                                }

                            }

                            if (!$abort_font_download) {

                                $options['google_font_css'] = $google_font_css;

                            }

                        }

                    } else {

                        $abort_font_download = true;

                    }

                    if ($abort_font_download && is_dir($filepath)) {

                        $fileSystemDirect = new WP_Filesystem_Direct(false);
                        $fileSystemDirect->rmdir($filepath, true);

                    }

                    if ($abort_font_download) {

                        $options['abort_font_download'] = true;

                    }

                } else {

                    unset($options['google_font_css']);

                    if (is_dir($filepath)) {

                        $fileSystemDirect = new WP_Filesystem_Direct(false);
                        $fileSystemDirect->rmdir($filepath, true);

                    }

                }

                if (isset($input['google_font_display']) && sanitize_key($input['google_font_display'])) {

                    $options['google_font_display'] = sanitize_key($input['google_font_display']);

                } else {

                    $options['google_font_display'] = 'auto';

                }

                if (isset($input['remove_theme_fonts']) && is_array($input['remove_theme_fonts']) && $input['remove_theme_fonts']) {

                    $options['remove_theme_fonts'] = array();

                    foreach ($input['remove_theme_fonts'] as $key => $theme_font) {

                        $options['remove_theme_fonts'][$key] = sanitize_text_field($theme_font);

                    }

                } else {

                    unset($options['remove_theme_fonts']);

                }

            	return $options;

            }

        }

        public static function wp_footer() {

        	$options = get_option('te_options');

            if (
            	isset($options['enable_sticky_header']) && $options['enable_sticky_header'] &&
            	is_admin_bar_showing()
            ) {

?>
<script type="text/javascript">
    'scroll resize'.split(' ').forEach(function(e){
        window.addEventListener(e, function() {
            var headerWrapper = document.querySelectorAll('.admin-bar .wp-site-blocks>header');
            if (headerWrapper.length) {
                headerWrapper = headerWrapper[0];
                if (window.innerWidth < 601) {
                    if (document.documentElement.scrollTop > 46) {
                        headerWrapper.style.top = '0';
                        // mainWrapper.style.marginTop = parseInt(mainWrapper.getAttribute('data-margintop'), 10) + 'px';
                    } else {
                        headerWrapper.style.top = (46 - document.documentElement.scrollTop) + 'px';
                        // mainWrapper.style.marginTop = (parseInt(mainWrapper.getAttribute('data-margintop'), 10) - 46 + document.documentElement.scrollTop) + 'px';
                    }
                } else if (window.innerWidth < 783) {
                    headerWrapper.style.top = '46px';
                    // mainWrapper.style.marginTop = (parseInt(mainWrapper.getAttribute('data-margintop'), 10) - 46) + 'px';
                } else {
                    headerWrapper.style.top = '32px';
                    // mainWrapper.style.marginTop = (parseInt(mainWrapper.getAttribute('data-margintop'), 10) - 32) + 'px';
                }
            }
        });
    });
</script>
<?php

            }

            if (isset($options['animate_header_logo']) && $options['animate_header_logo']) {

                $new_logo_width = ((isset($options['animate_header_logo_width']) && absint($options['animate_header_logo_width'])) ? absint($options['animate_header_logo_width']) - 1 : 48);
                $new_logo_height = false;
            	$custom_logo_id = get_theme_mod( 'custom_logo' );

                if ($custom_logo_id) {

            		$image = wp_get_attachment_image_src($custom_logo_id, 'full');

                    if (
                        isset($image[1]) && absint($image[1]) &&
                        isset($image[2]) && absint($image[2])
                    ) {

                        $new_logo_height = absint($new_logo_width * absint($image[2]) / absint($image[1]));

                    }

                }

?>
<script type="text/javascript">
    (function() {
        Array.prototype.forEach.call(document.getElementsByClassName('custom-logo'), function(customLogo) {
            customLogo.setAttribute('originalwidth', ((customLogo.offsetWidth) ? customLogo.offsetWidth + 'px' : ((customLogo.style.pixelWidth) ? customLogo.style.pixelWidth + 'px' : '120px')));
            customLogo.setAttribute('originalheight', ((customLogo.offsetHeight) ? customLogo.offsetHeight + 'px' : ((customLogo.style.pixelHeight) ? customLogo.style.pixelHeight + 'px' : 'auto')));
            customLogo.style.height = customLogo.getAttribute('originalheight');
        });
        var headerWrapper = document.querySelectorAll('header');
        if (headerWrapper.length) {
            headerWrapper = headerWrapper[0];
            document.addEventListener('scroll', (e) => {
                var customLogo = document.getElementsByClassName('custom-logo');
                if (customLogo.length) {
                    customLogo = customLogo[0];
                }
				if (window.scrollY > 100) {
					if (!headerWrapper.classList.contains('shrink-logo')) {
						headerWrapper.classList.add('shrink-logo');
                        if ('undefined' !== customLogo.length) {
                            customLogo.style.width = '<?php echo $new_logo_width; ?>px';
                            customLogo.style.height = '<?php echo (false !== $new_logo_height ? $new_logo_height : 48); ?>px';
                        }
					}
				} else {
                    if (headerWrapper.classList.contains('shrink-logo')) {
                        headerWrapper.classList.remove('shrink-logo');
                        if ('undefined' !== customLogo.length) {
                            customLogo.style.width = customLogo.getAttribute('originalwidth');
                            customLogo.style.height = customLogo.getAttribute('originalheight');
                        }
					}
				}
			});
        }
    })();
</script>
<?php

            }

        }

        public function wp_theme_json_data_theme($theme_json_data_object) {

            if (!$this->is_block_theme || version_compare(strtok(get_bloginfo('version'), '-'), '6.5', '<')) {

            	$options = get_option('te_options');

                if (
                    isset($options['remove_theme_fonts']) &&
                    is_array($options['remove_theme_fonts']) &&
                    $options['remove_theme_fonts']
                ) {

                    $theme_json_data = $theme_json_data_object->get_data();

                    if (
                        isset($theme_json_data['settings']['typography']['fontFamilies']['theme']) &&
                        is_array($theme_json_data['settings']['typography']['fontFamilies']['theme']) &&
                        $theme_json_data['settings']['typography']['fontFamilies']['theme']
                    ) {

                        foreach ($options['remove_theme_fonts'] as $font_family_handle => $fontFamily) {

                            foreach ($theme_json_data['settings']['typography']['fontFamilies']['theme'] as $key => $value) {

                                if (isset($value['slug']) && $font_family_handle === $value['slug']) {

                                    unset($theme_json_data['settings']['typography']['fontFamilies']['theme'][$key]);
                                    break;

                                }

                            }

                        }

                        $theme_json_data['settings']['typography']['fontFamilies']['theme'] = array_values($theme_json_data['settings']['typography']['fontFamilies']['theme']);

                        $theme_json_data_object->update_with($theme_json_data);

                    }

                }

            }

            return $theme_json_data_object;

        }

        public function wp_enqueue_scripts() {

            if (!$this->is_block_theme || version_compare(strtok(get_bloginfo('version'), '-'), '6.5', '<')) {

                $options = get_option('te_options');

                if (isset($options['inject_google_fonts']) && is_array($options['inject_google_fonts']) && $options['inject_google_fonts']) {

            		wp_register_style('te-google-fonts', '');
            		wp_enqueue_style('te-google-fonts');
            		wp_add_inline_style('te-google-fonts', self::get_font_face_styles());

                }

            }

        }

        private static function get_font_face_styles() {

            $options = get_option('te_options');

            if (
                isset($options['inject_google_fonts']) &&
                is_array($options['inject_google_fonts']) &&
                $options['inject_google_fonts'] &&
                isset($options['google_font_css']) &&
                $options['google_font_css']
            ) {

                return str_replace(
                    '[HTTP_HOST]',
                    'http' . (is_ssl() ? 's' : '') . '://' . $_SERVER['HTTP_HOST'],
                    $options['google_font_css']
                );

            } elseif (
                isset($options['inject_google_fonts']) &&
                is_array($options['inject_google_fonts']) &&
                $options['inject_google_fonts']
            ) {

                $google_font_families = array();

                foreach ($options['inject_google_fonts'] as $font => $styles) {

                    if (!is_array($styles)) { $styles = array($styles); }

                    foreach ($styles as $style) {

                        if (isset($google_font_families[$font])) {

                            $google_font_families[$font]['styles'][] = $style;

                        } else {

                            $google_font_families[$font] = array('styles' => array($style));

                        }

                        if ('i' === substr($style, -1)) {

                            $google_font_families[$font]['italic'] = true;

                        }

                    }

                }

                $css_import_string = 'https://fonts.googleapis.com/css2';

                $i = 1;

                foreach ($google_font_families as $font => $styles) {

                    if (1 === $i) {

                        $css_import_string .= '?';

                    } else {

                        $css_import_string .= '&';

                    }

                    $css_import_string .= 'family=' . urlencode($font) . ':';

                    if (isset($styles['italic'])) {

                        $css_import_string .= 'ital,wght';

                    } else {

                        $css_import_string .= 'wght';

                    }

                    $j = 1;

                    foreach ($styles['styles'] as $style) {

                        if (1 === $j) {

                            $css_import_string .= '@';

                        } else {

                            $css_import_string .= ';';

                        }

                        if (isset($styles['italic'])) {

                            $css_import_string .= ('i' === substr($style, -1) ? '1' : '0') . ',' . str_replace('i', '', $style);

                        } else {

                            $css_import_string .= $style;

                        }

                        $j++;

                    }

                    $i++;

                }

                $css_import_string .= '&display=' . (isset($options['google_font_display']) ? sanitize_key($options['google_font_display']) : 'auto');

                $css_import_string = '
@import url(\'' . $css_import_string . '\');
';

    	        return $css_import_string;

            }

            return '';

        }

        public static function register_block_type_args($settings, $name) {

            if ($name === 'core/shortcode') {

                $settings['render_callback'] = function ($attributes, $content) {

                    return $content;

                };

            }

            return $settings;

        }

	}

    if (!class_exists('teCommon')) {

        require_once(dirname(__FILE__) . '/includes/class-te-common.php');

    }

    if (version_compare(get_bloginfo('version'), '5.8', '>=')) {

	    $template_editor_object = new template_editor_class();

    } else {

        global $pagenow;

        if ('update-core.php' !== $pagenow) {

            add_action('admin_notices', 'template_editor_upgrade_wordpress_notice');

        }

    }

    function template_editor_upgrade_wordpress_notice() {

?>

<div class="notice notice-error">

<p><strong><?php _e('Template Editor Plugin Error', 'template-editor'); ?></strong><br />
<?php

        printf(
            __('This plugin requires at least WordPress v5.8 to be installed in order to function. Your WordPress version "%s" is not compatible.', 'template-editor'),
            get_bloginfo('version')
        );

?></p>

<p><a class="button" href="<?php echo esc_url(admin_url('update-core.php')); ?>" title="<?php _e('WordPress Updates', 'template-editor'); ?>"><?php
        _e('WordPress Updates', 'template-editor');
?></a>.</p>

</div>

<?php

    }

}

?>
