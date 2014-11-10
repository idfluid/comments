<?php

return array(

//General settings
'base_url'          => '', // The base url of your website
'logged_only'       => FALSE,  // Only logged users can post comments
'comment_status'    => 1,      // Default comment status: 1 - approved, 0 - pending, 2 - spam
'maxlength'         => 500,    // Maximum characters allowed for comments
'comments_per_page' => 10,     // Comments per page
'comment_reply'     => TRUE,   // Enable threaded (nested) comments
'max_depth'         => 5,      // Levels deep
'comments_order'    => 'DESC', // Comments order

//Captcha settings
'comments_captcha'        => FALSE, // Enable captcha for not logged users
'comments_captcha_logged' => FALSE, // Enable captcha for logged users

//Comment moderation
'comments_limit'  => 3, // Maximum comments that can be posted per minute
'mark_comment_as' => 2,   // 0 - pending, 2 - spam , 3 - rejected
'moderation_keys' => '',
'blacklist_keys'  => '',

//Default avatar
'default_avatar' => 'monsterid', // mystery, gravatar_default, identicon, wavatar, monsterid, retro or image source

//E-mail notifications
'comment_notification' => '',    // Send emails here when comments are posted
'reply_notification'   => FALSE, // Send emails to comment author when someone replies

//Sender details
'from_email' => '',  // From email
'from_name'  => '',  // From name
//Use your Gmail account to send emails
'gmail_username' => '',
'gmail_password' => '',

//Admin login
'admin_user' => 'admin',
'admin_pass' => 'admin',

//Users table name and fields
'db_users' => array(
    'table'      => 'users',
    'id'         => 'id',
    'first_name' => 'first_name',
    'last_name'  => 'last_name',
    'email'      => 'email',
    //'url'        => 'url', //If false custom url functions will be used
    'avatar'     => false //If is false, gravatar or custom avatar will be used
),

//Options table name
'db_options_table' => 'options',
'db_prefix' => 'laravel_',

//Other settings that you probably never use..
//'jquery'            => false,           // Disable jQuery
//'bootstrap'         => false,           // Disable bootstrap assets
//'full_date_format'  => '',              // Date format for date() function
//'short_date_format' => '',              // Date format (short) for date() function
//'comments_template' => 'template2.php', // Comments template file
'ajaxurl'           => \URL::to('ajax'),  // Url to the ajax file

//Email templates
'email_templates' => array(
    'body' => 'Author: %1$s (IP: %2$s) <br>
               E-mail: <a href=>"mailto:%3$s">%3$s</a> <br>
               URL: <a href=>"%4$s">%4$s</a> <br>
               Comment: %5$s <br><br>',
    'new_comment' => array(
        'subject' => '[HazzardWeb New comment on "%1$s"',
        'header'  => 'New comment on "%1$s"<br><br>',
        'footer'  => 'You can see all comments on this page here: <br> <a href=>"%1$s">%1$s</a> <br>
                      Permalink: <a href=>"%2$s">%2$s</a> <br>',
    ),
    'moderate' => array(
        'subject' => '[HazzardWeb Please moderate: "%1$s"',
        'header'  => 'A new comment on "%1$s" is waiting for your approval<br><br>',
        'footer'  => 'Approve it <a href=>"%1$s">%1$s</a><br>
                     Spam it <a href=>"%2$s">%2$s</a><br>
                     Edit it <a href=>"%3$s">%3$s</a>',
    ),
    'reply' => array(
        'subject' => '[HazzardWeb New reply on your comment on "%1$s"',
        'body'    => '%1$s has replied to your comment: <br> %2$s <br><br> View comment <a href=>"%3$s">%3$s</a>'
    )
),

/*
//Javascript language vars
'js_lang' => array(
    'url'        => 'Enter a valid website',
    'author'     => 'Enter your name1',
    'email'      => 'Enter a valid email address',
    'captchar'   => 'Enter the verification code',
    'captcham'   => 'The verification code is invalid',
    'error'      => 'An unexpected error has occurred. Please try again.',
    'comment'    => '1 comment',
    'comments'   => ' comments',
    'loading'    => 'Loading comments...',
    'pending'    => 'Awaiting moderation',
    'spam'       => 'Marked as spam',
    'limit'      => 'You posted too many comments. Wait a minute.1',
    'empty_com'  => 'Enter a comment.',
    'nocomments' => 'no comments',
    'logged'     => 'You must be logged to leave a comment !',
),
*/

);
