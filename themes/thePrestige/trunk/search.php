{hi:display:header}
		<div id="content">
			<div id="primaryContent">
			<h2 class="pageTitle">{hi:"Search results for: "}{hi:escape:criteria}</h2>
				{hi:posts}
				<div class="post {hi:statusname}">
					
					<div class="entry">
						<h2><a href="{hi:permalink}" rel="bookmark" title="{hi:title}">{hi:title_out}</a></h2>
						<div class="entryContent">				
								{hi:content_excerpt}
						</div>
					</div>
				
					<div class="entryMeta">
						<p>Entry Details</p>
						
						<p class="timestamp">{hi:pubdate_out}</p>
					
						<p class="author">{hi:"By"} {hi:author.displayname}</p>
					
						<p class="tags">{hi:"Tagged"}—{hi:?count(tags)}{hi:tags_out}{/hi:?}</p>
						
						<p class="comments"><a href="{hi:permalink}#comments" title="{hi:"Comments on this post"}">{hi:"{hi:comments.approved.count} Comment" "{hi:comments.approved.count} Comments" comments.approved.count}</a></p>
					
					</div>
				</div>
				<div class="clear divider"></div>
				{/hi:posts}
			
			</div>
{hi:display:sidebar}			
			<div id="pagenav" class="clear">
				<p>{hi:@prev_page_link} {hi:@page_selector} {hi:@next_page_link}
			</div>

		</div>
{hi:display:footer}
			
		
		
					
				
