<?php

/*
 * @package WpOnLine.zip
 * @version 0.3
*/

/*
 Plugin Name: WpOnLine
 Plugin URI: http://www.roccosicilia.it/projects/wponline/
 Description: This plugin generate a box where you can visualize the number of users online. The widget have two option: counter of online users and IP list.
 Author: Rocco Sicilia
 Version: 0.3
 Author URI: http://www.roccosicilia.it/
 */

class WpOnLineWidget extends WP_Widget {
	
	function WpOnLineWidget() {
		
		parent::WP_Widget(false, $name = 'WpOnLine');
		
	}
	
	function widget($args, $instance) {
		
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$view = $instance['view'];
		
		echo $before_widget;
		
		if ( $title )
			echo $before_title . $title . $after_title;

			/* custom action begin */

			// logging IP and Expire
			$ip = $_SERVER['REMOTE_ADDR'];
			$dir = "./wp-content/plugins/wponline/DB/";
			$file = $ip.".txt";
			$time = time();
			$timeout = 60;
			$expire = $time + $timeout;
			$fp = fopen("./wp-content/plugins/wponline/DB/$ip.txt", 'w');
			fwrite($fp, "$expire");
			fclose($fp);
			
			// delete old sessions
			if ($dh = opendir($dir)) {
			
				while (($file = readdir($dh)) !== false) {
				
					if (($file != '.') && ($file != '..')) {
					
						$handle = fopen("./wp-content/plugins/wponline/DB/$file", "r");
						$contents = fread($handle, filesize("./wp-content/plugins/wponline/DB/$file"));
						fclose($handle);
						$expire = trim($contents);
						$now = time();
					
						if (($expire - $now) < 0) {
						
							unlink($dir.$file);
						
						}
		
					}
				
				}
			
			}
		
			closedir($dh);
			
			// output
			if ($view == 'counter') {
				
				if ($dh = opendir($dir)) {
		
					$online = 0;
		
					while (($file = readdir($dh)) !== false) {
		
						if (($file != '.') && ($file != '..')) {
		
							$online++;
		
						}
		
					}
		
					echo "$online user(s) online";
		
				}
		
				closedir($dh);
			
			}
			
			if ($view == 'ip-list') {
				
				if ($dh = opendir($dir)) {
			
					while (($file = readdir($dh)) !== false) {
			
						if (($file != '.') && ($file != '..')) {
			
							$handle = fopen("./wp-content/plugins/wponline/DB/$file", "r");
							$contents = fread($handle, filesize("./wp-content/plugins/wponline/DB/$file"));
							fclose($handle);
							$expire = trim($contents);
							$file = explode('.', $file);
							echo "$file[0].$file[1].$file[2].$file[3]<br />\n";
			
						}
			
					}
			
				}
			
				closedir($dh);
				
			}
				
			/* custom action end */
			
			echo $after_widget;
			
	}

	function update($new_instance, $old_instance) {
    	
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['view'] = $new_instance['view'];
		return $instance;
        
	}

	function form($instance) {
		
		$title = esc_attr($instance['title']);
		
		if ($instance['view'] == 'counter') {
			$selected1 = "selected=\"selected\"";
		}
		if ($instance['view'] == 'ip-list') {
			$selected2 = "selected=\"selected\"";
		}
		
		echo "<p>";
		echo "<label for=\"" . $this->get_field_id('title') . "\">" . _e('Title:') . "</label> ";
		echo "<input class=\"widefat\" id=\"" . $this->get_field_id('title') . "\" name=\"" . $this->get_field_name('title') . "\" type=\"text\" value=\"$title\" />";
		echo "<label for=\"" . $this->get_field_id('view') . "\">View</label> ";
		echo "<select id=\"" . $this->get_field_id( 'view' ) . "\" name=\"" . $this->get_field_name('view') . "\" class=\"widefat\" style=\"width:100%;\">";
			echo "<option value=\"counter\" $selected1 >couter</option>";
			echo "<option value=\"ip-list\" $selected2 >ip list</option>";
		echo "</select>";
		echo "</p>";

	}

}

add_action('widgets_init', create_function('', 'return register_widget("WpOnLineWidget");'));

?>