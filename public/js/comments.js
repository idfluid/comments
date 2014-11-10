$(document).ready(function() {
	var config = commentsConfig || null,
		ajaxurl = config.ajaxurl || 'includes/ajax.php',
		clang = {
			author: 'Enter your name',
			email: 'Enter a valid email address',
			captchar: 'Enter the verification code',
			captcham: 'The verification code is invalid',
			url: 'Enter a valid website',
			error: 'An unexpected error has occurred. Please try again.',
			comment: '1 comment',
			comments: ' comments',
			nocomments: 'no comments',
			loading: 'Loading comments...',
			empty_com: 'Enter a comment.',
			pending: 'Awaiting moderation',
			spam: 'Marked as spam',
			limit: 'You posted too many comments. Wait a minute.',
			logged: 'You must be logged to leave a comment !',
		},
		max_depth = config.max_depth || 5,
		commentsTemplate = $('#commentsTemplate').html(),
		paginationTemplate = $('#paginationTemplate').html(),
		padding = $('.comments .add-comment img').length ? 0 : 60;

	// validate signup form on keyup and submit
	$("#loginForm").validate({
		rules: {
			email: {
				required: true,
				email: true
			},
			password: {
				required: true,
				minlength: 5
			}
		},
		messages: {
			email: {
				required: "Please enter your email",
				email: "Please enter a valid email address"
			},
			password: {
				required: "Please provide a password",
				minlength: "Your password must be at least 5 characters long"
			}
		}
	});

	// validate signup form on keyup and submit
	$("#signupForm").validate({
		rules: {
			first_name: "required",
			last_name: "required",
			email: {
				required: true,
				email: true
			},
			password: {
				required: true,
				minlength: 5
			}
		},
		messages: {
			firstname: "Please enter your firstname",
			lastname: "Please enter your lastname",
			email: {
				required: "Please enter your email",
				email: "Please enter a valid email address"
			},
			password: {
				required: "Please provide a password",
				minlength: "Your password must be at least 5 characters long"
			}
		}
	});

	$('.comments').on('keyup', 'textarea[maxlength]', function() {
		var val = $(this).val(),
			maxlength = $(this).attr('maxlength');
		
		if (maxlength) {
			if(val.length > maxlength){
				$(this).val(val.substr(0, maxlength));
				$(this).parent().find('.remaining span').text(0);
			}
			else $(this).parent().find('.remaining span').text(maxlength - val.length);
			/*if(val.length > 0)
				$(this).parent().find('.add').removeAttr('disabled');
			else $(this).parent().find('.add').attr('disabled', 'disabled');*/
		}
	});

	$('.comments').on('click', '.toggle', function(){
		var target = $(this).data('target');
		$(target).toggle();
	});
	//Show comment form for reply
	$('.comments').on('click', '.reply', function(){
		var el = $('#comment-' + $(this).attr('data-id') + ' .reply-box' ).first(),
			form = $('.comments .add-comment .form').clone();

		if (!form.length) 
			alert(clang.logged);

		if (el.html()!='')
			el.html('');
		else {
			el.html(form);
			el.find('textarea, input[type="text"]').val('');
			el.find('.help-inline').text('');
			el.find('.response').hide().text('');
			el.find('#commentReply').val( $(this).attr('data-id') );
			
			el.find('.form').show();
			//el.find('.form').slideDown(200);
			//refreshCaptcha();
		}
		return false;
	});

	//Hide comment form
	$('.comments').on('click', '.cancel', function(e) {
		e.preventDefault();
		var el = $(this).closest('.add-comment');
		if (el.length) {
			
		} else $(this).closest('.form').remove();
	});

	//Pagination
	$('.comments').on('click', '.pagination a', function() {
		if ( $(this).parent().hasClass('disabled') || $(this).parent().hasClass('active') )
			return false;
		getComments({ paged : $(this).attr('data-paged'), load: 1 });
		$('body').animate({
			scrollTop: $('#comments').offset().top - 20
		}, 800 );
	});

	//Add new comment
	$('.comments').on('submit', '.form', function() {

		var error = 0,
			form = $(this),
			reply = parseInt( form.find('#commentReply').val() ),
			userId = form.find('#commentUserId'),
			author = form.find('#commentAuthor'),
			comment = form.find('#commentContent');

		if ($.trim(comment.val()).length<1)
			comment.focus();
		else if (!error) {
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: {
					action:'add-comment',
					comment: comment.val(),
					reply: reply,
					user_id: userId.val(),
					author: author.val(),
					page: config.page
				},
				beforeSend: function() {
					form.find('.ajax-loader').removeClass('hidden');
					form.find('input,button,textarea').attr('disabled', 'disabled');
					form.find('.response').removeClass('alert-error').addClass('hidden').text('').hide();
				},
				complete: function(jqXHR) {
					form.find('.ajax-loader').addClass('hidden');
					form.find('input,button,textarea').removeAttr('disabled');
					
					if (jqXHR.responseText && $.parseJSON(jqXHR.responseText)) {
						if ($.parseJSON(jqXHR.responseText).success)
						$('.comments .cancel').trigger('click');
					}
				},
				success: function(data) {
					console.log(data);
					if (data.success) {
						data = data.data;
						data.hidden = '_hide';
						switch (data.status) {
							case '0' : data._status = clang.pending; break;
							case '2' : data._status = clang.spam; break;
						}
						var el = $('#comment-count'),
							result = Mustache.render(commentsTemplate, data),
							paged = getPaged() || 1;
						
						if (!reply && !isNaN(paged) && paged!=1) {
							window.location.hash = 'page/1';
							getComments({ paged : 1 });
						}

						result = result.replace('__paged__', paged);
						
						if (reply) {
							$('#comment-'+reply+' .list').first().prepend(result);
							$('.replies #comment-'+data.id).css({'margin-left' : '30px'}).fadeIn('slow');
						}
						else {
							$('#comments .list').first().prepend(result);
							$('#comment-'+data.id).fadeIn('slow');
						}

						if ( $('#comment-'+data.id).parents('.list').length > max_depth)
							$('#comment-'+data.id).find('.reply').remove();

						if (el.text().indexOf(' ')>-1 && !isNaN(parseInt(el.text().substr(0, el.text().indexOf(' ')))))
							el.text( parseInt(el.text().substr(0, el.text().indexOf(' ')))+1+clang.comments)
						else el.text( clang.comment );

						//Linkify
						if (paged == 1)
							$('#comment-'+data.id+' .content').linkify({target: '_blank'});

						$.post(ajaxurl, {
							action:'comment-notification',
							comment_id: data.id,
							reply: reply,
							page_url: window.location.href.replace(window.location.hash, '') + '#comments-list',
							page_title: config.page_title,
							comment_url: window.location.href.replace(window.location.hash, '') + '#page/'+paged+'/comment/'+data.id,
						});
					}
					else {
						data = data.data;
						var ul = $('<ul/>') ,i;

						for (i in data)
							ul.append( $('<li/>', {text:clang[i]})  );

						form.find('.response').addClass('alert-error').append(ul).slideDown();
					}
				},
				error: function() {
					form.find('.response').addClass('alert-error').text(clang.error).slideDown();
				}
			});
		}

		return false;
	});

	//Get comments
	function getComments(attrs) {
		var paged = getPaged(),
			comment,
			per_page = config.per_page || 10;

		if (paged && paged.indexOf('/comment/') > -1 )
			comment = paged.substr( paged.indexOf('mment/')+6 , paged.length);

		paged = parseInt((attrs && attrs.paged) ? attrs.paged : paged || 1);

		$.ajax({
			url: ajaxurl,
			type: 'GET',
			dataType: 'json',
			data: {
				action:'get-comments',
				page: config.page,
				paged: paged,
			},
			beforeSend: function() {
				$('.loading-comments').removeClass('hidden');
			},
			complete: function() {
				$('.loading-comments').addClass('hidden');
			},
			success: function(data) {
				console.log(data);
				if (data.success) {
					data = data.data;
					
					var result = '';
					
					result += processComments(data.comments || 0);
					
					$('#comment-count').text(( data.total ?  data.total==1 ? clang.comment : data.total+clang.comments  : clang.nocomments ));
					$('#comments').fadeOut('slow', function() {
						$(this).html(result).fadeIn('slow', function() {
							$('.comments .replies').animate({'margin-left' : '30px'});
							
							$('.comments a').each(function() {
								$(this).attr('href', $(this).attr('href').replace('__paged__', paged));
							});
							if (comment && $('#comment-'+comment).length)
								$('body').animate({
									scrollTop: $('#comment-'+comment).offset().top - 20
								}, 800 );
							if ($.effects)
								setTimeout( function(){$('#comment-'+comment+' .highlight').effect("highlight", {color:'#fcf8e3'}, 1000);}, 800 );
							
							//Linkify
							$('.comments li .content').linkify({target: '_blank'});
						});
					});

					var config = {},
						i = 1;

					if ( data.count && data.count > per_page ) {
						
						config.pg = Math.ceil(data.count / per_page);
						config.pages = [];
						
						for(;i<=config.pg;i++)
							config.pages.push(i);

						config.prev = paged - 1;
						config.next = paged + 1;

						if ( $('.comments .pagination').length )
							$('.comments .pagination').remove();
						
						$('#comments').after( Mustache.render(paginationTemplate, config) );
						
						if (paged<=1)
							$('.comments .pagination li').first().addClass('disabled');

						if (paged>=config.pg)
							$('.comments .pagination li').last().addClass('disabled');
		
						$('.comments .pagination [data-paged='+paged+']').parent().addClass('active');
					}
				}
				else console.log('Error: data.success=false');
			},
			error: function() {
				console.log('Error: ajax error');
			}
		});
	}
	//Do some processing to the comments
	function processComments(comments, replies, depth) {
		var result = '', i, depth = depth+1 || 0;
		if (comments) {
			for(i in comments) {
				if (comments[i].replies)
					comments[i].replies = processComments(comments[i].replies.comments, 1, depth);
				
				if (depth+1>max_depth)
					comments[i].depth = 1;
				
				result += Mustache.render(commentsTemplate, comments[i]);
			}
		}
		return replies ? result : '<ul class="list">' + result + '</ul>';
	}

	function getPaged() {
		var paged = window.location.hash;
		if (paged)
			paged = (paged.indexOf('page/') > -1) ? paged.substr(6, paged.length) : 0;
		return paged || 0;
	}

	getComments();
});