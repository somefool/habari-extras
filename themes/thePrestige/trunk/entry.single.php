{hi:display:header}
		<div id="content">
			<div id="primaryContent">
				<div class="post {hi:post.statusname}">
					<div class="entry">
						<h2><a href="{hi:post.permalink}" rel="bookmark" title="{hi:post.title}">{hi:post.title_out}</a></h2>
						<div class="entryContent">
					{hi:post.content_out}
						</div>
					</div>
						<div class="entryMeta">
							<p>{hi:"Entry Details"}</p>
						
							<p class="timestamp">{hi:post.pubdate_out}</p>
					
							<p class="author">{hi:"By"} {hi:post.author.displayname}</p>
					
							{hi:?count(post.tags)}<p class="tags">{hi:"Tagged"}-{hi:post.tags_out}</p>{/hi:?}
						
						</div>
			</div>
			<div class="clear divider"></div>
			{hi:display:comments}
		</div>
		
		{hi:display:sidebar}
	</div>
	
{hi:display:footer}