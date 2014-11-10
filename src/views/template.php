<?php  

$maxlength = Comments::config('maxlength');
$base_url = Comments::config('base_url');

//Include asssets
if ( Comments::config('bootstrap') ) {
	echo HTML::style('public/packages/idfluid/comments/css/bootstrap.css');
}
	echo HTML::style('public/packages/idfluid/comments/css/comments.css');

if ( Comments::config('jquery') ) {
	echo HTML::script('public/packages/idfluid/comments/js/jquery-1.9.1.min.js');
}
	echo HTML::script('public/packages/idfluid/comments/js/jquery-ui-1.9.2.custom.min.js');

if ( Comments::config('bootstrap') ) {
	echo HTML::script('public/packages/idfluid/comments/js/bootstrap.js');
}
?>

<script>
	var commentsConfig = {
		max_depth  : <?php echo Comments::config('max_depth'); ?>,
		per_page   : <?php echo Comments::config('comments_per_page'); ?>,
		page       : '<?php echo Comments::config('comment_page') ? Comments::config('comment_page') : Comments::currentURL(); ?>',
		page_title : '<?php echo Comments::config('page_title'); ?>',
		lang       : <?php echo json_encode( Comments::config('js_lang') ); ?>,
		ajaxurl    : '<?php echo Comments::config('ajaxurl'); ?>'
	};
</script>
<?php
echo HTML::script('public/packages/idfluid/comments/js/mustache.min.js');
echo HTML::script('public/packages/idfluid/comments/js/jquery-linkify.min.js');
echo HTML::script('public/packages/idfluid/comments/js/jquery.elastic.source.js');
echo HTML::script('public/packages/idfluid/comments/js/comments.js');
echo HTML::script('public/packages/idfluid/comments/js/jquery.validate.js');
?>

<!-- Comments Template -->
<script id="commentsTemplate" type="text/template">
	<li id="comment-{{id}}" class="item {{hidden}}">
		{{#author_url}}
			<a href="{{author_url}}" class="avatar pull-left" target="_blank"><img src="{{avatar}}" height="50" width="50"></a>
		{{/author_url}}
		{{^author_url}}
			<span class="avatar pull-left"><img src="{{avatar}}" height="50" width="50"></span>
		{{/author_url}}
		<div class="right">
			<div class="bubble"></div>
			<div class="box highlight">
				<div class="header">
					{{#author_url}}
						<a href="{{author_url}}" target="_blank" class="author">{{author}}</a>
					{{/author_url}}
					{{^author_url}}
						<span class="author">{{author}}</span>
					{{/author_url}}
					<a href="#page/__paged__/comment/{{id}}" class="date" title="{{date}}">
						{{short_date}}
					</a>
					{{#status}}
						<span class="pending label label-warning">{{_status}}</span>
					{{/status}}
					{{#admin}}
						<a href="<?php echo $base_url; ?>admin/?page=comments#edit-{{id}}" class="actions">Edit</a>
					{{/admin}}
				</div>
				<div class="content">{{{comment}}}</div>
				{{#reply}}
					{{#depth}}{{/depth}}
					{{^depth}}
						<a href="#" class="reply" data-id="{{id}}">Reply</a>
					{{/depth}}
				{{/reply}}
			</div>
		</div>
		<div class="reply-box"></div>
		<ul class="list replies">
			{{{replies}}}
		</ul>
	</li>
</script>

<!-- Pagination Template -->
<script id="paginationTemplate" type="text/template">
	<div class="pagination pagination-small pagination-centered">
	  <ul class="pagination">
	    <li><a href="#page/{{prev}}" data-paged="{{prev}}" title="Previous">&laquo;</a></li>
	    {{#pages}}
	    	<li><a href="#page/{{.}}" data-paged="{{.}}">{{.}}</a></li>
	    {{/pages}}
	    <li><a href="#page/{{next}}" data-paged="{{next}}" title="Next">&raquo;</a></li>
	  </ul>
	</div>
</script>

<div class="comments">
	<nav class="navbar navbar-default">
		<ul>
			<li class="left">
				<span class="comment-count" id="comment-count">1 Comment</span>
			</li>
			<li class="right">
				<?php $user = Sentry::getUser(); ?>
				<?php if (Sentry::check()): ?>
					<div class="dropdown">
			          <a href="#" class="dropdown-toggle user" data-toggle="dropdown"><?php echo $user->first_name .' '. $user->last_name; ?><span class="caret"></span></a>
			          <ul class="dropdown-menu" role="menu">
			            	<li><a href="<?php echo URL::to('logout'); ?>">Logout</a></li>
			          </ul>
			        </div>
			    <?php endif; ?>
			</li>
		</ul>
	</nav>		
	<div id="comments-wrap">
		<div class="add-comment">
			<form action="" method="post" class="form" id="commentform">
				<div class="commentbox">
					<div class="avatar">
						<img src="<?php echo URL::to('public/packages/idfluid/comments/images/noavatar.png'); ?>" alt="Avatar">
					</div>
					<div class="textarea-wrapper">
						<textarea name="comment" id="commentContent" <?php echo ($maxlength)?' maxlength="'.$maxlength.'"':''; ?> placeholder="Leave a message..." class="form-control"></textarea>
						<div class="post-actions">
							<div class="remaining pull-left"><span><?php echo $maxlength; ?></span> remaining</div>
							<div class="pull-right">
								<a href="#" class="cancel">Cancel</a>
								<?php if (Sentry::check()): ?>
									<input type="submit" class="btn btn-primary pull-right btn-submit" value="Submit" name="post">
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
				<input type="hidden" id="commentReply" name="reply" value="0">
				<?php if (Sentry::check()): ?>
					<input type="hidden" id="commentUserId" name="user_id" value="<?php echo $user->id; ?>">
					<input type="hidden" id="commentAuthor" name="author" value="<?php echo $user->first_name .' '. $user->last_name; ?>">
				<?php endif; ?>
			</form>
			<?php if (! Sentry::check()): ?>
				
				<div class="auth">
					<div class="row">
						<div class="col-sm-6">
							<ul class="login">
								<li><a href="#" class="toggle local" data-target=".login-form">Login</a></li>
								<li><a href="<?php echo URL::to('facebookLogin?url-back='.Request::url()) ?>" class="facebook">Facebook</a></li>
							</ul>
							<div class="login-form _hide">
								<?php echo Form::open(array('url'=>'login', 'id'=>'loginForm')); ?> 
									<input type="hidden" name="url-back" value="<?php echo Request::url(); ?>">
								  	<div class="form-group">
								    	<input type="email" name="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
								  	</div>
								  	<div class="form-group">
								    	<input type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
								  	</div>
								  	<button type="submit" class="btn btn-primary">Login</button>
								<?php echo Form::close(); ?>
							</div>
						</div>
						<div class="col-sm-6">
							<a href="#" class="toggle" data-target=".register-form">Register</a>
							<div class="register-form _hide">
								<?php echo Form::open(array('url'=>'register','id'=>'signupForm')); ?> 
									<input type="hidden" name="url-back" value="<?php echo Request::url(); ?>">
								  	<div class="form-group">
								  		<div class="row">
								  			<div class="col-sm-6">
									  			<input type="text" name="first_name" class="form-control" id="first_name" placeholder="First Name">
									  		</div>
									  		<div class="col-sm-6">
									  			<input type="text" name="last_name" class="form-control" id="last_name" placeholder="Last Name">
									  		</div>
								  		</div>
								  	</div>
								  	<div class="form-group">
								    	<input type="email" name="email" class="form-control" id="exampleInputEmail1" placeholder="Enter email">
								  	</div>
								  	<div class="form-group">
								    	<input type="password" name="password" class="form-control" id="exampleInputPassword1" placeholder="Password">
								  	</div>
								  	<button type="submit" class="btn btn-primary">Register</button>
								<?php echo Form::close(); ?>
							</div>
						</div>
					</div>
				</div>

			<?php endif ?>
		</div>
		
		<div id="comments" class="_hide"></div>
	</div>
</div>

<script type="text/javascript">
	jQuery(document).ready(function(){			
		jQuery('#commentContent').elastic();
	});	
	// ]]>
</script>