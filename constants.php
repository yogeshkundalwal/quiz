<?php 

// print array and variables for debugging 
function debug($item = array(), $die = true, $display = true) {
	if( is_array($item) || is_object($item) ) {
		echo '<pre class="debug" style="'.($display?'':'display:none;').'">'; print_r($item); echo '</pre>';
	} else {
		echo '<div class="debug" style="'.($display?'':'display:none;').'">'.$item.'</div>';
	}
	
	if( $die ) die();
}

?>