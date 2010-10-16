<?php
/*
 * Notice: 
 * 1. You have things below:
 *    $site_name, site_link,
 *    $post_title, $post_link,
 *    $comment_id, $comment_author, $comment_content,
 *    $reply_id, $reply_author, $reply_content,
 *    $unsubscribe_link
 * 2. Do not change the order of the sections
 */ ?>
====== Subject ======
<?php echo "[$site_name] You have a new reply on $post_title"; ?>
====== End ======

====== Text ======
<?php echo
"Hi $comment_author,

You have a new reply on $post_title from $reply_author.

Your comment:
  $comment_content

Reply
  $reply_content

See your reply from
  $post_link

================================================
This mail is sent by system automatically, do not reply please.  You can unsubscribe from
$unsubscribe_link
";
?>
====== End ======

====== HTML ======
<?php echo
"<?xml version=\"1.0\" ?>
<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"en\" lang=\"en\">
  <head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\"/>
    <title>[$site_name] You have a new reply on $post_title</title>
  </head>
  <body style=\"line-height: 150%; background-color: #eeeeee;\">
    <div id=\"wrapper\" style=\"padding: 20px; border: 1px solid #dddddd; background-color: #ffffff;\">
        Hi $comment_author, <br />
        <br />
        You have a new reply on <a href=\"$post_link\" title=\"$post_title\">$post_title</a> from $reply_author.
        <div style=\"padding: 10px; margin: 20px; border: 1px dashed #666666; background: #eeeeee;\">
          Your comment:
          <blockquote><p>$comment_content</p></blockquote>
        </div>
        <div style=\"padding: 10px; margin: 20px; border: 1px dashed #666666; background: #eeeeee;\">
          Reply:
          <blockquote><p>$reply_content</p></blockquote>
        </div>
        You can see your reply from <a href=\"$post_link#comment-$reply_id\" title=\"Reply\">$post_link#comment-$reply_id</a> <br/>
        <br />
        ==============================================<br />
        This mail is sent by system automatically, do not reply please. You can unsubscribe from <a href=\"$unsubscribe_link\" title=\"Unsubscribe\">here</a>
    </div>
  </body>
</html>";
?>
====== End ======
