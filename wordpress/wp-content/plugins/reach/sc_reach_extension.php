<?php
  /**
   * Plugin Name: Socialcast Like
   * Plugin URI: http://integrate.socialcast.com/wordpress/plugins/like
   * Description: Let your readers quickly share your content on Socialcast with a simple click by liking or recommending it.
   * Version: 1.0
   *
   * Author: Monica Wilkinson
   * Author URI: http://integrate.socialcast.com/author/ciberch/
   */

  /*
   * +--------------------------------------------------------------------------+
   * | Copyright (c) 2011 Socialcast, Inc.                                      |
   * +--------------------------------------------------------------------------+
   * | This program is free software; you can redistribute it and/or modify     |
   * | it under the terms of the GNU General Public License as published by     |
   * | the Free Software Foundation; either version 2 of the License, or        |
   * | (at your option) any later version.                                      |
   * |                                                                          |
   * | This program is distributed in the hope that it will be useful,          |
   * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
   * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
   * | GNU General Public License for more details.                             |
   * |                                                                          |
   * | You should have received a copy of the GNU General Public License        |
   * | along with this program; if not, write to the Free Software              |
   * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
   * +--------------------------------------------------------------------------+
   */

  /**
   * Returns major.minor WordPress version.
   */
function sc_reach_get_wp_version() {
  return (float)substr(get_bloginfo('version'),0,3);
}

function reach_init_method() {
  if (sc_reach_get_wp_version() >= 2.7) {
    if ( is_admin() ) {
      add_action( 'admin_init', 'sc_reach_register_like_settings' );
    }
  }
  add_filter('the_content', 'sc_reach_like_div');
  add_filter('admin_menu', 'sc_reach_like_admin_menu');
  add_option('sc_host', 'reach.socialcast.com');
  add_option('sc_reach_like_token', 'PASTE YOUR REACH TOKEN HERE');

  //  add_action('wp_head', 'sc_reach_header_meta');
  add_action('wp_footer', 'sc_reach_add_like');
}

function sc_reach_register_like_settings() {
  register_setting('sc_reach_like', 'sc_host');
  register_setting('sc_reach_like', 'sc_reach_like_token');
}

function sc_reach_like_div() {
  echo '<div id="like_container_id" style="width:300px;height:60px"></div>';
}

function sc_reach_like_admin_menu() {
  add_options_page('Like Plugin Options', 'Like', 8, __FILE__, 'sc_reach_like_options');
}

function sc_reach_like_options () {
?>
    <table>
    <tr>
    <td>

    <div class="wrap">
    <h2>Reach Like Button by <a href="http://www.socialcast.com" target="_blank">Socialcast</a></h2>

    <form method="post" action="options.php">
    <?php
    if (sc_reach_get_wp_version() < 2.7) {
    wp_nonce_field('update-options');
    } else {
    settings_fields('tt_like');
    }
    ?>

    <table class="form-table">
      <tr valign="top">
        <th scope="row">Reach Extension Token</th>
        <td><input type="text" name="sc_reach_like_token" value="<?php echo get_option('sc_reach_like_token'); ?>" /></td>
      </tr>
      <tr valign="top">
	<th scope="row">Socialcast Community</th>
        <td><input type="text" name="sc_host" value="<?php echo get_option('sc_host'); ?>" /></td>
      </tr>

    <?php if (tt_get_wp_version() < 2.7) : ?>
       <input type="hidden" name="action" value="update" />
       <input type="hidden" name="page_options" value="sc_reach_like_token, sc_host" />
    <?php endif; ?>
    <p class="submit">
    <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
    </p>
</form>
    </div>
</table>
    <?php
       }
																		     }

function sc_reach_add_like() {
  $socialcast_url = get_option('sc_host');
  $reach_token = get_option('sc_reach_like_token');

  ?>
  <script type="text/javascript">
  var _reach = _reach || [];
  _reach.push({
    container: 'like_container_id',
	domain: 'https://$socialcast_url/',
	token: '$reach_token'
	});
  (function(){
    var e=document.createElement('script'); 
    e.type='text/javascript'; 
    e.async = true;
    e.src= document.location.protocol + '//$socialcast_url/services/reach/extension.js';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(e, s);
  })();
</script>
<?php


}

reach_init_method();

?>