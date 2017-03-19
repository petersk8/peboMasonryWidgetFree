<?php
/*
Plugin Name: PEBO Free Masonry with Hover effects
Plugin URI:  http://blog.pebo.pro/shop/wordpress/pebo-wordpress-masonry-pro/
Description: Boostraped posts Masonery block with hover effects
Version:     0.1
Author:      Pedro E. Borrego R.
Author URI:  http://www.pebo.pro/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

class PeboMasonryWidgetFree_Widget extends WP_Widget {

	/**
	 * Sets up the widgets name etc
	 */
	public function __construct() {
		$widget_ops = array( 
			'classname' => 'pebo_hover_masonry_widget_free',
			'description' => 'Awesome masonry with hover effects',
		);
		parent::__construct( 'pebo_hover_masonry_widget_free', 'PEBO Free Masonry', $widget_ops );
	}

	/**
	 * Outputs the content of the widget
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		
		//Enque the style sheets.
		wp_enqueue_style('pebo-hover-freestyles', plugin_dir_url(__FILE__) . 'css/freeset.css');
		wp_enqueue_style('pebo-bootstrap-grid', plugin_dir_url(__FILE__) . 'css/bootstrap_grid_system.min.css');
		
		$maxPostNumber = (isset($instance['maxPostNumber'])) ? $instance['maxPostNumber'] : -1;		
		$rand_posts = get_posts( array('numberposts' => $maxPostNumber,	) );
		$effectsArray = [ "jazz", "ming", "lexi", "duke" ];
		$randomEffects = ($instance["randomize"]=='on') ? true : false;
		$totalcontent = "";		
		$selectedEffect =  (isset($instance['Effect'])) ? $instance['Effect'] :  "jazz";

		/*This is the template for each block of the masonery*/
		$MasonryBlock = '			
				<div class="grid" >
					<figure class="effect-PEBO_SELECTED_EFFECT">
						<img src="PEBO_PATH_TO_IMAGE" alt="img12" style=" height:130%; width: 130%;"/>
						<figcaption>
							<div>
								<h2 style="font-size:15px;">PEBO_TITTLE_1 <span>PEBO_TITTLE_2</span></h2>
								<p style="font-size:15px;">PEBO_POST_RESUME</p>
							</div>
							<a href="PEBO_POST_LINK">View more</a>
						</figcaption>			
					</figure>
				</div>';

		/*Filling the columns from the array*/
		$NumberOfColumns = isset($instance["Columns"]) ? $instance["Columns"] : '2';
		$colClass= ColumnsToBSCol($NumberOfColumns);		
		/*set the column header for each one*/
		for ($i = 0; $i < $NumberOfColumns ; $i++) {
			$columArray[$i] = '<div class="'. $colClass . '" style="padding:0;">';
		}
		/*Compile the columns body*/
		$colIndex=0;
		foreach($rand_posts as $post){
			if($randomEffects){
				$selectedEffect = $effectsArray[rand(0,sizeof($effectsArray)-1)];
			}
			$imgURL = wp_get_attachment_url( get_post_thumbnail_id($post->ID), 'thumbnail' ); 
			if(empty($imgURL)) $imgURL = plugin_dir_url(__FILE__) . "/img/default.jpg";
			$withthelink = str_replace("PEBO_PATH_TO_IMAGE", $imgURL ,$MasonryBlock);
			$titleArray = explode(" ",  $post->post_title, 2);
			$withthetitle1 = str_replace("PEBO_TITTLE_1" ,$titleArray[0], $withthelink );
			$withthetitle2 = str_replace("PEBO_TITTLE_2" ,$titleArray[1], $withthetitle1 );
			$thecontent =  get_the_category($post->ID)[0]->name; 
			$withthecontent = str_replace("PEBO_POST_RESUME", $thecontent, $withthetitle2);
			$theLink = get_post_permalink($post->ID);
			$withthelink = str_replace("PEBO_POST_LINK" , $theLink, $withthecontent);
			$totalcontent = str_replace("PEBO_SELECTED_EFFECT", $selectedEffect, $withthelink);
			$columArray[$colIndex] = $columArray[$colIndex] . $totalcontent;
			$totalcontent = "";
			if($colIndex < $NumberOfColumns-1){
				$colIndex ++;
			}else{
				$colIndex = 0;
			}
		}
		
		/*ad each column content and close the div at the end*/
		$masonry = "";
		for ($i = 0; $i < $NumberOfColumns ; $i++) {
			$masonry = $masonry . $columArray[$i] . '</div>';
		}	

		//Surround the colums with a propper container	
		$Masonrycontainer = '
		<div class="container-fluid">
  			<div class="row">'.
    			$masonry
  			.'</div>
		</div>';

		/*The final output*/		
		echo($Masonrycontainer);		
	}

	/**
	 * Outputs the options form on admin
	 *
	 * @param array $instance The widget options
	 */
	public function form( $instance ) {
		/*array of effect names*/
		$effectsArray = ["jazz", "ming", "lexi", "duke" ];

		$selectedColNum = $instance['Columns'];
		/*Check last selection for the number of colums*/
		$is2 = ($selectedColNum == 2 ? "selected" : "");
		$is3 = ($selectedColNum == 3 ? "selected" : "");
		$is4 = ($selectedColNum == 4 ? "selected" : "");
		$is5 = ($selectedColNum == 5 ? "selected" : "");

		// outputs the options form on admin
		$instance = wp_parse_args( (array) $instance, array( 'Columns' => '' ) ); 
		$title = '<h1>Post Masonry Configuration</h1><br>';		
		$columsOption = 'Number of columns: 
		<select id="'. $this->get_field_id("Columns") . '" 
			name= "' . $this->get_field_name("Columns") . '">
  			<option value="2" ' . $is2 . '>2</option>
  			<option value="3" ' . $is3 . '>3</option>
  			<option value="4" ' . $is4 . '>4</option>
  			<option value="5" ' . $is5 . '>5</option>
		</select><br>';
		$effectOption = 'Select a hover Effect: 
		<select id="'. $this->get_field_id("Effect") . '" 
			name= "' . $this->get_field_name("Effect") . '">';
  		foreach($effectsArray as $option){
			  $selectedOption = ($instance["Effect"] == $option) ? " selected" : " ";
			  $effectOption = $effectOption . '<option value="' . $option . '"'.
			   $selectedOption . '>' . $option . '</option>';
		  }
		$effectOption = $effectOption .'</select><br>';
		$checked = ($instance["randomize"] == 'on') ? 'checked' : ''; 
		$randomizeCheck = '
		<input id="'. $this->get_field_id("randomize") . '" 
			name= "' . $this->get_field_name("randomize") . '"type="checkbox" ' . $checked . '> randomize effects<br>
		';
		$maxPostNumber = '<br>Number of post to show : <input id="'. $this->get_field_id("maxPostNumber") . '" 
			name= "' . $this->get_field_name("maxPostNumber") .
			 ' type="text" size = "1" value="'.$instance['maxPostNumber'].'"><br>';
		$collabLink = '<br>Like this widget? 
		<a href="http://blog.pebo.pro/shop/wordpress/pebo-wordpress-masonry-pro/"
		target= "blank">
		donate and get the PRO VERSION </a><br>';	
	
			
		echo (
			$title 
			. $columsOption 
			. $effectOption 
			. $randomizeCheck 
			. $maxPostNumber
			. $collabLink
		);
	}

	/**
	 * Processing widget options on save
	 *
	 * @param array $new_instance The new options
	 * @param array $old_instance The previous options
	 */
	public function update( $new_instance, $old_instance ) {
		// processes widget options to be saved
		$instance = $old_instance;
    	$instance['Columns'] = $new_instance['Columns'];
		$instance['Effect'] = $new_instance['Effect'];
		$instance['randomize'] = $new_instance['randomize'];
		$instance['maxPostNumber'] = $new_instance['maxPostNumber'];
    	return $instance;
	}
}


//Register the widget
add_action( 'widgets_init', function(){
	register_widget( 'PeboMasonryWidgetFree_Widget' );
});

/**
*Transforms the number of columns selecetd to a proper boostrap class.
*/
function ColumnsToBSColPeboFree($columns){
	$valueOf = [
		"2" => "col-md-6",
		"3" => "col-md-4",
		"4" => "col-md-3",
		"5" => "col-md-15",
		"6" => "col-md-2",
	];
	return $valueOf[$columns];	
}

//Function made for debuging, remove for production
function DEBUG_free( $data ) {
    if ( is_array( $data ) )
        $output = "<script>console.log( 'Debug Objects: " . implode( ',', $data) . "' );</script>";
    else
        $output = "<script>console.log( 'Debug Objects: " . $data . "' );</script>";
    echo $output;
}


