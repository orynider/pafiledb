<!-- INCLUDE pa_header.tpl -->
<table width="100%" cellpadding="2" cellspacing="2">
  <tr>
	<td valign="bottom">
		<span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a> -> <a href="{U_DOWNLOAD_HOME}" class="nav">{DOWNLOAD}</a><!-- BEGIN navlinks --> -> <a href="{navlinks.U_VIEW_CAT}" class="nav">{navlinks.CAT_NAME}</a><!-- END navlinks --> -> {FILE_NAME}</span>
	</td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="ttb">
	<tr>
		<td><img src="templates/fiappleblue/images/tt12_l.gif" width="10" height="12" alt="" /></td>
		<td class="tt12bkg"><img src="images/spacer.gif" width="200" height="12" alt="" /></td>
		<td><img src="templates/fiappleblue/images/tt12_r.gif" width="10" height="12" alt="" /></td>
	</tr>
</table>
<table width="100%" cellpadding="3" cellspacing="1" class="forumline">
  <tr> 
	<th class="thHead" colspan="2">{L_FILE} - {FILE_NAME}</th>
  </tr>
<tr> 
	<td class="row2" valign="middle" width="20%"><span class="genmed">{L_DESC}:</span></td>
	<td class="row1" valign="middle" width="80%"><span class="genmed">{FILE_LONGDESC}</span></td>
</tr>
<tr>
	<td class="row2" valign="middle" width="20%"><span class="genmed">{L_SUBMITED_BY}:</span></td>
	<td class="row1" valign="middle" width="80%"><span class="name">{FILE_SUBMITED_BY}</span></td>
</tr>  
<!-- IF SHOW_AUTHOR -->
<tr>
	<td class="row2" valign="middle" width="20%"><span class="genmed">{L_AUTHOR}:</span></td>
	<td class="row1" valign="middle" width="80%"><span class="genmed">{FILE_AUTHOR}</span></td>
</tr>  
<!-- ENDIF -->
<!-- IF SHOW_VERSION -->
<tr> 
	<td class="row2" valign="middle" width="20%"><span class="genmed">{L_VERSION}:</span></td>
	<td class="row1" valign="middle" width="80%"><span class="genmed">{FILE_VERSION}</span></td>
  </tr>  
<!-- ENDIF -->  
<!-- IF SHOW_SCREENSHOT -->
<tr> 
	<td class="row2" valign="middle" width="20%"><span class="genmed">{L_SCREENSHOT}:</span></td>
	<!-- IF SS_AS_LINK -->
	<td class="row1" valign="middle" width="80%"><span class="genmed"><a href="{FILE_SCREENSHOT}" target="_blank">{L_CLICK_HERE}</a></span></td>
	<!-- ELSE -->
	<td class="row1" valign="middle" width="80%"><span class="genmed"><a href="javascript:mpFoto('{FILE_SCREENSHOT}')"><img src="{FILE_SCREENSHOT}" border="0" width="100" hight="100"></a></span></td>
	<!-- ENDIF -->
  </tr>  
<!-- ENDIF -->
<!-- IF SHOW_WEBSITE -->
  <tr> 
	<td class="row2" valign="middle" width="20%"><span class="genmed">{L_WEBSITE}:</span></td>
	<td class="row1" valign="middle" width="80%"><span class="genmed"><a href="{FILE_WEBSITE}" target="_blank">{L_CLICK_HERE}</a></span></td>
  </tr>
<!-- ENDIF --> 
<tr> 
	<td class="row2" valign="middle"><span class="genmed">{L_DATE}:</span></td>
	<td class="row1" valign="middle"><span class="genmed">{TIME}</span></td>
  </tr>
<tr> 
	<td class="row2" valign="middle"><span class="genmed">{L_UPDATE_TIME}:</span></td>
	<td class="row1" valign="middle"><span class="genmed">{UPDATE_TIME}</span></td>
  </tr>
  <tr> 
	<td class="row2" valign="middle"><span class="genmed">{L_LASTTDL}:</span></td>
	<td class="row1" valign="middle"><span class="genmed">{LAST}</span></td>
  </tr>
  <tr> 
	<td class="row2" valign="middle"><span class="genmed">{L_SIZE}:</span></td>
	<td class="row1" valign="middle"><span class="genmed">{FILE_SIZE}</span></td>
  </tr>  
  <tr> 
	<td class="row2" valign="middle"><span class="genmed">{L_RATING}:</span></td>
	<td class="row1" valign="middle"><span class="genmed">{RATING} ({FILE_VOTES} {L_VOTES})</span></td>
  </tr>
  <tr> 
	<td class="row2" valign="middle"><span class="genmed">{L_DLS}:</span></td>
	<td class="row1" valign="middle"><span class="genmed">{FILE_DLS}</span></td>
  </tr>
<!-- BEGIN custom_field -->
  <tr>
	<td class="row2" valign="middle"><span class="genmed">{custom_field.CUSTOM_NAME}:</span></td>
	<td class="row1" valign="middle"><span class="genmed">{custom_field.DATA}</span></td>
  </tr>
<!-- END custom_field -->
  <tr> 
	<td class="cat" align="center" colspan="2"></td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="ttb">
	<tr>
		<td><img src="templates/fiappleblue/images/tb12_l.gif" width="10" height="12" alt="" /></td>
		<td class="tb12bkg"><img src="images/spacer.gif" width="200" height="12" alt="" /></td>
		<td><img src="templates/fiappleblue/images/tb12_r.gif" width="10" height="12" alt="" /></td>
	</tr>
</table>
<table width="100%" cellpadding="0" cellspacing="0">
  <tr>
<!-- IF AUTH_DOWNLOAD -->  
	<td width="33%" align="center">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td><img src="templates/fiappleblue/images/lang_english/tabsp.gif" alt="" width="80" height="2" /></td>
		</tr>
		<tr>
	  		<td class="postbkg"><a href="{U_DOWNLOAD}"><img src="{DOWNLOAD_IMG}" border="0" alt="{L_DOWNLOAD}" /></a></td>
		</tr>
		<tr>
			<td><img src="templates/fiappleblue/images/lang_english/tabb.gif" alt="" width="80" height="6" /></td>
		</tr>
	</table>
	</td>
<!-- ENDIF -->
<!-- IF AUTH_RATE -->  
	<td width="34%" align="center">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td><img src="templates/fiappleblue/images/lang_english/tabsp.gif" alt="" width="80" height="2" /></td>
		</tr>
		<tr>
	  		<td class="postbkg"><a href="{U_RATE}"><img src="{RATE_IMG}" border="0" alt="{L_RATE}" /></a></td>
		</tr>
		<tr>
			<td><img src="templates/fiappleblue/images/lang_english/tabb.gif" alt="" width="80" height="6" /></td>
		</tr>
	</table>
	</td>
<!-- ENDIF -->
<!-- IF AUTH_EMAIL -->  
	<td width="33%" align="center">
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td><img src="templates/fiappleblue/images/lang_english/tabsp.gif" alt="" width="80" height="2" /></td>
		</tr>
		<tr>
	  		<td class="postbkg"><a href="{U_EMAIL}"><img src="{EMAIL_IMG}" border="0" alt="{L_EMAIL}" /></a></td>
		</tr>
		<tr>
			<td><img src="templates/fiappleblue/images/lang_english/tabb.gif" alt="" width="80" height="6" /></td>
		</tr>
	</table>
	</td>
<!-- ENDIF -->
  </tr>
</table>
<br />
<!-- IF INCLUDE_COMMENTS -->
	<!-- INCLUDE pa_comment_body.tpl -->
<!-- ENDIF -->
<!-- INCLUDE pa_footer.tpl -->
