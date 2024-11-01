<?php 
function wp_new_Customcomment($commentdata)
{
	$commentdata = apply_filters('preprocess_comment', $commentdata);
	$commentdata['comment_post_ID'] = (int) $commentdata['comment_post_ID'];
	
	if ( isset($commentdata['user_ID']) )
		$commentdata['user_id'] = $commentdata['user_ID'] = (int) $commentdata['user_ID'];
	elseif ( isset($commentdata['user_id']) )
		$commentdata['user_id'] = (int) $commentdata['user_id'];

	$commentdata['comment_parent'] = isset($commentdata['comment_parent']) ? absint($commentdata['comment_parent']) : 0;
	$parent_status = ( 0 < $commentdata['comment_parent'] ) ? wp_get_comment_status($commentdata['comment_parent']) : '';
	$commentdata['comment_parent'] = ( 'approved' == $parent_status || 'unapproved' == $parent_status ) ? $commentdata['comment_parent'] : 0;
	$commentdata['comment_author_IP'] = preg_replace( '/[^0-9a-fA-F:., ]/', '',$_SERVER['REMOTE_ADDR'] );
	$commentdata['comment_agent']     = substr($_SERVER['HTTP_USER_AGENT'], 0, 254);
	$commentdata['comment_date']     = current_time('mysql');
	$commentdata['comment_date_gmt'] = current_time('mysql', 1);
	$commentdata['comment_artical_url'] = get_permalink( );
	$commentdata['comment_artical_name'] = get_the_title(); 
	$commentdata['comment_parent'] = $_REQUEST['comment_parent'];
	$commentdata = wp_filter_comment($commentdata);
	$commentdata['comment_approved'] = wp_allow_comment($commentdata);
	$comment_ID = wp_insert_Customcomment($commentdata);
	do_action('comment_post', $comment_ID, $commentdata['comment_approved']);
	
	if ( 'spam' !== $commentdata['comment_approved'] ) { // If it's spam save it silently for later crunching
		if ( '0' == $commentdata['comment_approved'] )
			wp_notify_moderator($comment_ID);

		$post = &get_post($commentdata['comment_post_ID']); // Don't notify if it's your own comment

		if ( get_option('comments_notify') && $commentdata['comment_approved'] && ( ! isset( $commentdata['user_id'] ) || $post->post_author != $commentdata['user_id'] ) )
			wp_notify_postauthor($comment_ID, isset( $commentdata['comment_type'] ) ? $commentdata['comment_type'] : '' );
	}

	return $comment_ID;
}

function wp_insert_Customcomment($commentdata)
{
	global $wpdb;
	extract(stripslashes_deep($commentdata), EXTR_SKIP);
	if ( ! isset($comment_author_IP) )
		$comment_author_IP = '';
	if ( ! isset($comment_date) )
		$comment_date = current_time('mysql');
	if ( ! isset($comment_date_gmt) )
		$comment_date_gmt = get_gmt_from_date($comment_date);
	if ( ! isset($comment_parent) )
		$comment_parent = 0;
	if ( ! isset($comment_approved) )
		$comment_approved = 1;
	if ( ! isset($comment_karma) )
		$comment_karma = 0;
	if ( ! isset($user_id) )
		$user_id = 0;
	if ( ! isset($comment_type) )
		$comment_type = '';

	$data = compact('comment_post_ID', 'comment_author', 'comment_author_email', 'comment_author_url', 'comment_author_IP', 'comment_date', 'comment_date_gmt', 'comment_content', 'comment_karma', 'comment_approved', 'comment_agent', 'comment_type', 'comment_parent', 'user_id','comment_artical_url','comment_artical_name');
	//$data = array("name" => "Hagrid", "age" => "36");                                                                    
	$data_string = json_encode($data);                                                                                   
	$ch = curl_init(SETCOMMENT_URL);                                                                      
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");                                                                     
	curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);                                                                  
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                                                                      
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/json',
		'Content-Length: ' . strlen($data_string))
	);                                                                                                                   

	$result = curl_exec($ch);
	//$wpdb->insert($wpdb->comments, $data);
	$id = (int) $wpdb->insert_id;
	if ( $comment_approved == 1 )
		wp_update_comment_count($comment_post_ID);
	$comment = get_comment($id);
	do_action('wp_insert_comment', $id, $comment);
}	

function CustomcommentForm()
{
}

function GerResponce($parentID,$decodeRepData1,$postID)
{
	global $post;
	for($j=0;$j<count($decodeRepData1);$j++)
	{
		if ($parentID ==$decodeRepData1[$j]->comment_parent)
		{
			?>
			<ol>
				<li>
					<article class="WCM_comment" id="WCM_comment-1">
						<footer class="WCM_comment-meta">
							<div class="WCM_comment-author vcard">
								<img width="68" height="68" class="WCM_avatar avatar-68 photo" src="http://0.gravatar.com/avatar/e18f1c583f8d3e6fa82a01ec3562f6ed?s=68&amp;d=http%3A%2F%2F0.gravatar.com%2Favatar%2Fad516503a11cd5ca435acc9bb6523536%3Fs%3D68&amp;r=G" alt="" />
								<span class="WCM_fn"><?php echo stripslashes($decodeRepData1[$j]->comment_author); ?></span>
							</div>
						</footer>
						<div class="WCM_comment-content"><?php echo stripslashes($decodeRepData1[$j]->comment_content); ?></div>
						<div id="commentReplay-<?php echo $decodeRepData1[$j]->comment_ID?>"></div>
						<div class="reply"><a class="comment-reply-link" onclick="addReplay('<?php echo $decodeRepData1[$j]->comment_ID?>','<?php echo $postID ;?>')">Reply</a></div>
					</article>
					<?php GerResponce($decodeRepData1[$j]->comment_ID,$decodeRepData1,$postID);  ?>
				</li>
			</ol>
		<?php
		}
	}
}

function GetComment($userName,$userArray)
{
	$countComment = 0;
	
	for($i=0;$i<count($userArray);$i++)
	{
		if($userArray[$i]->comment_author == $userName)
		{
			$countComment = $countComment + 1;
		}
	}
	return $countComment;
}

function GetMaxComment($userArray)
{
	$maxValue =array();
	$userName =array(); 

	for($i=0;$i<count($userArray);$i++)
	{
		$Comment = GetComment($userArray[$i]->comment_author,$userArray);
		array_push($maxValue,$Comment);
		array_push($userName,$userArray[$i]->comment_author);
	}
}

function commentUser()
{
	global $post;
	$link = get_permalink();
	$postID = $post->ID;
	$thred ="WCM_Commentlist";
	$title =get_the_title($post->ID);
	$shortName = "schmooztest";
	$json_url = GETCOMMENT_URL;
	$data = array("post_url"=>$link,"postID"=>$postID,"thred"=>$thred,"title"=>$title,"shortName"=>$shortName);
	$data_string = json_encode($data); 
	$ch = curl_init( $json_url );
	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => array('Content-type: application/json') ,
		CURLOPT_POSTFIELDS => $data_string
	);
	
	// Setting curl options
	curl_setopt_array( $ch, $options );
	// Getting results
	$result =  curl_exec($ch); // Getting jSON result string
	print_r($result);
	$decodeRepData = $result;
	
	GetMaxComment($decodeRepData);
	?>
	<div id="WCM_commentuser">
	<?php
	
	global $post;
	$Users = array();

	for($i=0;$i<count($decodeRepData);$i++)
	{
		if(in_array($decodeRepData[$i]->comment_author,$Users)){}else {array_push($Users,$decodeRepData[$i]->comment_author);}
	}

	$cnt =1;
	foreach($Users as $user)
	{
		$TotalComment = GetComment($user,$decodeRepData);
		if($cnt ==1 ){echo " <div class='WCM_inner'>";}
	?>
		<span class="WCM_username"><a title="Total Comment - <?php echo $TotalComment; ?>" onclick="CommentList('<?php echo $user ?>','<?php echo $post->ID ;?>')"><?php echo strtoupper($user) ; ?></a></span>
		<?php  if($cnt == 3 ){echo "</div>";$cnt = 0;}  $cnt ++;  }
	?>
	</div>
	</div>
	<?php
}