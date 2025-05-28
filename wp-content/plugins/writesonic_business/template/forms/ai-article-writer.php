<form id="ai-article-writer" style="display: none;">
	<table class="form-table" role="presentation">
		<tbody>
			<tr>
				<th>Article Title <span class="require">*</span></th>
				<td><input type="text" id="article_title" name="article_title" class="google-ads field" placeholder="Article Title" required/></td>
			</tr>
			<tr>
				<th>Article Intro <span class="require">*</span></th>
				<td><textarea id="article_intro" name="article_intro" class="google-ads field" placeholder="Article Intro" required></textarea></td>
			</tr>
			<tr>
				<th></th>
				<td><input type="button" onClick="add_field(this);" value="Add section"/></td>
			</tr>
			<tr>
				<th>Article Section <span class="require">*</span></th>
				<td id="article_section"><input type="text" id="article_sections[]" name="article_sections[]"  class="google-ads field" required/></td>
			</tr>
			
		</tbody>
	</table>
</form>