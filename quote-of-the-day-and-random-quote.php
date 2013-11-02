<?php
/*
Plugin Name: Quote of the Day and Random Quote
Plugin URI: http://welovequotes.net
Description: The Quote of the Day or a Random Quote on your website, from WeLoveQuotes.net
Version: 1.0
Author: WeLoveQuotes.net
Author URI: http://welovequotes.net
License: GPL2

  Copyright 2013  WeLoveQuotes.net  (email : mail@dailyverses.net)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function welovequotes_add_my_stylesheet() {
	wp_register_style( 'welovequotes-style', plugins_url('quote-of-the-day-and-random-quote.css', __FILE__) );
	wp_enqueue_style( 'welovequotes-style' );
}

add_action( 'wp_enqueue_scripts', 'welovequotes_add_my_stylesheet' );

function welovequotes_quote_of_the_day($showlink) {

	$quoteOfTheDay_date = get_option('quoteoftheday_Date');
	$quoteOfTheDay_quote = get_option('quoteoftheday_Quote');
	$quoteOfTheDay_lastAttempt = get_option('quoteoftheday_LastAttempt');
				
	$quoteOfTheDay_currentDate = date('Y-m-d');

	if($quoteOfTheDay_date != $quoteOfTheDay_currentDate && $quoteOfTheDay_lastAttempt < (date('U') - 3600))
	{
		$url = 'http://welovequotes.net/gettext.ashx?date=' . $quoteOfTheDay_currentDate . '&url=' . $_SERVER['HTTP_HOST'] . '&type=daily1_0';
		$result = wp_remote_get($url);

		update_option('quoteoftheday_LastAttempt', date('U'));
		
		if(!is_wp_error($result)) 
		{
			$quoteOfTheDay_quote = str_replace(',', '&#44;', $result['body']);

			update_option('quoteoftheday_Date', $quoteOfTheDay_currentDate);
			update_option('quoteoftheday_Quote', $quoteOfTheDay_quote);
		}
	}

	if($quoteOfTheDay_quote == "")
	{
		$quoteOfTheDay_quote = '<div class="weLoveQuotes quote">You can do anything, but not everything.</div><div class="weLoveQuotes author">- <a href="http://welovequotes.net/david-allen" target="_blank">David Allen</a></div>';
	}

    if($showlink == 'true' || $showlink == '1')
	{
		$html =  $quoteOfTheDay_quote . '<div class="weLoveQuotes linkToWebsite"><a href="http://welovequotes.net" target="_blank">WeLoveQuotes.net</a></div>';
	}
	else
	{
		$html = $quoteOfTheDay_quote;
	}
	
	return $html;
}

function welovequotes_random_quote($showlink) {
	$position = rand(0, 100);
	$randomquote = get_option('randomquote_' . $position);
	$randomquote_lastAttempt = get_option('randomquote_LastAttempt');
	
	if($randomquote == "" && $randomquote_lastAttempt < (date('U') - 3600))
	{
		$url = 'http://welovequotes.net/gettext.ashx?position=' . $position . '&url=' . $_SERVER['HTTP_HOST'] . '&type=random1_0';
		$result = wp_remote_get($url);

		if(!is_wp_error($result)) 
		{
			$randomquote = str_replace(',', '&#44;', $result['body']);

			update_option('randomquote_' . $position, $randomquote);
		}
		else
		{
			update_option('randomquote_LastAttempt', date('U'));
		}
	}

	if($randomquote == "")
	{
		$randomquote = '<div class="weLoveQuotes quote">You can do anything, but not everything.</div><div class="weLoveQuotes author">- <a href="http://welovequotes.net/david-allen" target="_blank">David Allen</a></div>';
	}
		
	if($showlink == 'true' || $showlink == '1')
	{
		$html = $randomquote . '<div class="weLoveQuotes linkToWebsite"><a href="http://welovequotes.net" target="_blank">WeLoveQuotes.net</a></div>';
	}
	else
	{
		$html = $randomquote;
	}
	
	return $html;
}

add_shortcode('quoteoftheday', 'welovequotes_quote_of_the_day'); 
add_shortcode('randomquote', 'welovequotes_random_quote'); 

class WeLoveQuotes_QuoteOfTheDayWidget extends WP_Widget
{
  function WeLoveQuotes_QuoteOfTheDayWidget()
  {
    $widget_ops = array('classname' => 'welovequotes_quoteoftheday', 'description' => 'Show the daily quote from WeLoveQuotes.net on your website!' );
    $this->WP_Widget('WeLoveQuotes_QuoteOfTheDayWidget', 'Quote of the Day', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => 'Quote of the day', 'showlink' => '1' ) );
    $title = $instance['title'];
	$showlink = $instance['showlink'];
	
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <br /><input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
  <p><input id="<?php echo $this->get_field_id('showlink'); ?>" name="<?php echo $this->get_field_name('showlink'); ?>" type="checkbox" value="1" <?php checked( '1', $showlink ); ?>/><label for="<?php echo $this->get_field_id('showlink'); ?>"><?php _e('&nbsp;Show link to WeLoveQuotes.net (thank you!)'); ?></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
	if($new_instance['showlink'] == '1')
	{
		$instance['showlink'] = '1';
	}
	else
	{
		$instance['showlink'] = '0';
	}	
	
    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;;
 
 	$showlink = $instance['showlink'];
	if($showlink == '')
	{
		$showlink = '1';
	}
	
    echo welovequotes_quote_of_the_day($showlink);
 
    echo $after_widget;
  } 
}

class WeLoveQuotes_RandomQuoteWidget extends WP_Widget
{
  function WeLoveQuotes_RandomQuoteWidget()
  {
    $widget_ops = array('classname' => 'welovequotes_randomquote', 'description' => 'Show a random quote from WeLoveQuotes.net on your website!' );
    $this->WP_Widget('WeLoveQuotes_RandomQuoteWidget', 'Random Quote', $widget_ops);
  }
 
  function form($instance)
  {
    $instance = wp_parse_args( (array) $instance, array( 'title' => 'Random Quote', 'showlink' => '1' ) );
    $title = $instance['title'];
	$showlink = $instance['showlink'];
	
?>
  <p><label for="<?php echo $this->get_field_id('title'); ?>">Title: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo attribute_escape($title); ?>" /></label></p>
  <p><input id="<?php echo $this->get_field_id('showlink'); ?>" name="<?php echo $this->get_field_name('showlink'); ?>" type="checkbox" value="1" <?php checked( '1', $showlink ); ?>/><label for="<?php echo $this->get_field_id('showlink'); ?>"><?php _e('&nbsp;Show link to WeLoveQuotes.net (thank you!)'); ?></label></p>
<?php
  }
 
  function update($new_instance, $old_instance)
  {
    $instance = $old_instance;
    $instance['title'] = $new_instance['title'];
	if($new_instance['showlink'] == '1')
	{
		$instance['showlink'] = '1';
	}
	else
	{
		$instance['showlink'] = '0';
	}

    return $instance;
  }
 
  function widget($args, $instance)
  {
    extract($args, EXTR_SKIP);
 
    echo $before_widget;
    $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
 
    if (!empty($title))
      echo $before_title . $title . $after_title;;
 
 	$showlink = $instance['showlink'];
	if($showlink == '')
	{
		$showlink = '1';
	}
	
    echo welovequotes_random_quote($showlink);
 
    echo $after_widget;
  }
}

add_action( 'widgets_init', create_function('', 'return register_widget("WeLoveQuotes_QuoteOfTheDayWidget");') );
add_action( 'widgets_init', create_function('', 'return register_widget("WeLoveQuotes_RandomQuoteWidget");') );
?>