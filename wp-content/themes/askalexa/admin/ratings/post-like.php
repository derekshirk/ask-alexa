<?php 

function post_like() {

	// Check for nonce security
	$nonce = $_POST['nonce'];

	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
		die ( 'Busted!');
	 
	if(isset($_POST['post_like'])) {
		// Retrieve user IP address
		$ip = $_SERVER['REMOTE_ADDR'];
		$post_id = $_POST['post_id'];
		 
		// Get voters'IPs for the current post
		$meta_IP = get_post_meta($post_id, "voted_IP");
		$voted_IP = $meta_IP[0];

		if(!is_array($voted_IP))
		$voted_IP = array();
			 
		// Get votes count for the current post
		$meta_count = get_post_meta($post_id, "up_vote_count", true);

		// Use has already voted ?
		if(!hasAlreadyVoted($post_id)) {
			$voted_IP[$ip] = time();
			// Save IP and increase votes count
			update_post_meta($post_id, "voted_IP", $voted_IP);
			update_post_meta($post_id, "up_vote_count", ++$meta_count);
			// Display count (ie jQuery return value)
			echo $meta_count;
		}
		else
		echo "already";
	}
	exit;
}

function post_dislike() {

	// Check for nonce security
	$nonce = $_POST['nonce'];

	if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) )
		die ( 'Busted!');
	 
	if(isset($_POST['post_dislike'])) {
		// Retrieve user IP address
		$ip = $_SERVER['REMOTE_ADDR'];
		$post_id = $_POST['post_id'];
		 
		// Get voters'IPs for the current post
		$meta_IP = get_post_meta($post_id, "voted_IP");
		$voted_IP = $meta_IP[0];

		if(!is_array($voted_IP))
		$voted_IP = array();
			 
		// Get votes count for the current post
		$meta_count = get_post_meta($post_id, "down_vote_count", true);

		// Use has already voted ?
		if(!hasAlreadyVoted($post_id)) {
			$voted_IP[$ip] = time();
			// Save IP and increase votes count
			update_post_meta($post_id, "voted_IP", $voted_IP);
			update_post_meta($post_id, "down_vote_count", ++$meta_count);
			// Display count (ie jQuery return value)
			echo $meta_count;
		}
		else
		echo "already";
	}
	exit;
}

$timebeforerevote = 0; // = 2 hours
// $timebeforerevote = 120; // = 2 hours

function hasAlreadyVoted($post_id) {
    global $timebeforerevote;
 
    // Retrieve post votes IPs
    $meta_IP = get_post_meta($post_id, "voted_IP");
    $voted_IP = $meta_IP[0];
     
    if(!is_array($voted_IP))
        $voted_IP = array();
         
    // Retrieve current user IP
    $ip = $_SERVER['REMOTE_ADDR'];
     
    // If user has already voted
    if(in_array($ip, array_keys($voted_IP)))
    {
        $time = $voted_IP[$ip];
        $now = time();
         
        // Compare between current time and vote time
        if(round(($now - $time) / 60) > $timebeforerevote)
            return false;
             
        return true;
    }
     
    return false;
}

// function getPostLikeLink($post_id) {
//     $themename = "askalexa";
 
//     $vote_count = get_post_meta($post_id, "up_vote_count", true);
 
//     $output = '<p class="post-like">';
//     if(hasAlreadyVoted($post_id))
//         $output .= ' <span title="'.__('I like this article', $themename).'" class="like alreadyvoted"></span>';
//     else
//         $output .= '<a href="#" data-post_id="'.$post_id.'">
//                     <span  title="'.__('I like this article', $themename).'"class="qtip like"></span>
//                 </a>';
//     $output .= '<span class="count">'.$vote_count.'</span></p>';
     
//     return $output;
// }