<!-- INCLUDE pa_header.tpl -->
<table width="100%" cellpadding="2" cellspacing="2">
  <tr>
	<td valign="bottom">
		<span class="nav"><a href="{U_INDEX}" class="nav">{L_INDEX}</a> -> <a href="{U_DOWNLOAD}" class="nav">{DOWNLOAD}</a> -> {L_STATISTICS}</span>
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
	<th colspan="2" class="thHead">{L_STATISTICS}</th>
  </tr>
  <tr> 
	<td colspan="2" class="cat" align="center"><span class="cattitle">{L_GENERAL_INFO}</span></td>
  </tr>  
  <tr>
	<td colspan="2" class="row1"><span class="genmed">{STATS_TEXT}</span></td>
  </tr>
  <tr> 
	<td class="cat" width="50%" align="center"><span class="cattitle">{L_DOWNLOADS_STATS}</span></td>
	<td class="cat" width="50%" align="center"><span class="cattitle">{L_RATING_STATS}</span></td>
  </tr>  
  <tr> 
	<td class="row2" colspan="2" align="center"><span class="genmed">{L_OS}</span></td>
  </tr>    
  <tr> 
	<td class="row1" align="center">
		  <table cellspacing="0" cellpadding="2" border="0">
			<!-- BEGIN downloads_os -->
			<tr> 
			  <td><img src="pafiledb/images/stats/{downloads_os.OS_IMG}" alt="" />&nbsp;<span class="gen">{downloads_os.OS_NAME}</span></td>
			  <td> 
				<table cellspacing="0" cellpadding="0" border="0">
				  <tr> 
					<td><img src="templates/fiappleblue/images/vote_lcap.gif" width="4" alt="" height="12" /></td>
					<td><img src="{downloads_os.OS_OPTION_IMG}" width="{downloads_os.OS_OPTION_IMG_WIDTH}" height="12" alt="{downloads_os.OS_OPTION_RESULT}" /></td>
					<td><img src="templates/fiappleblue/images/vote_rcap.gif" width="4" alt="" height="12" /></td>
				  </tr>
				</table>
			  </td>
			  <td align="center"><span class="gen">[ {downloads_os.OS_OPTION_RESULT} ]</span></td>
			</tr>
			<!-- END downloads_os -->
		  </table>	
	</td>
	<td class="row1" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<!-- BEGIN rating_os -->
			<tr> 
			  <td><img src="pafiledb/images/stats/{rating_os.OS_IMG}" alt="" />&nbsp;<span class="gen">{rating_os.OS_NAME}</span></td>
			  <td> 
				<table cellspacing="0" cellpadding="0" border="0">
				  <tr> 
					<td><img src="templates/fiappleblue/images/vote_lcap.gif" width="4" alt="" height="12" /></td>
					<td><img src="{rating_os.OS_OPTION_IMG}" width="{rating_os.OS_OPTION_IMG_WIDTH}" height="12" alt="{rating_os.OS_OPTION_RESULT}" /></td>
					<td><img src="templates/fiappleblue/images/vote_rcap.gif" width="4" alt="" height="12" /></td>
				  </tr>
				</table>
			  </td>
			  <td align="center"><span class="gen">[ {rating_os.OS_OPTION_RESULT} ]</span></td>
			</tr>
			<!-- END rating_os -->
		  </table>		
	</td>
  </tr>
  <tr> 
	<td class="row2" colspan="2" align="center"><span class="genmed">{L_BROWSERS}</span></td>
  </tr>

  <tr> 
	<td class="row1" align="center">
		  <table cellspacing="0" cellpadding="2" border="0">
			<!-- BEGIN downloads_b -->
			<tr> 
			  <td><img src="pafiledb/images/stats/{downloads_b.B_IMG}" alt="" />&nbsp;<span class="gen">{downloads_b.B_NAME}</span></td>
			  <td> 
				<table cellspacing="0" cellpadding="0" border="0">
				  <tr> 
					<td><img src="templates/fiappleblue/images/vote_lcap.gif" width="4" alt="" height="12" /></td>
					<td><img src="{downloads_b.B_OPTION_IMG}" width="{downloads_b.B_OPTION_IMG_WIDTH}" height="12" alt="{downloads_b.B_OPTION_RESULT}" /></td>
					<td><img src="templates/fiappleblue/images/vote_rcap.gif" width="4" alt="" height="12" /></td>
				  </tr>
				</table>
			  </td>
			  <td align="center"><span class="gen">[ {downloads_b.B_OPTION_RESULT} ]</span></td>
			</tr>
			<!-- END downloads_b -->
		  </table>	
	</td>
	<td class="row1" align="center">
		<table cellspacing="0" cellpadding="2" border="0">
			<!-- BEGIN rating_b -->
			<tr> 
			  <td><img src="pafiledb/images/stats/{rating_b.B_IMG}" alt="" />&nbsp;<span class="gen">{rating_b.B_NAME}</span></td>
			  <td> 
				<table cellspacing="0" cellpadding="0" border="0">
				  <tr> 
					<td><img src="templates/fiappleblue/images/vote_lcap.gif" width="4" alt="" height="12" /></td>
					<td><img src="{rating_b.B_OPTION_IMG}" width="{rating_b.B_OPTION_IMG_WIDTH}" height="12" alt="{rating_b.B_OPTION_RESULT}" /></td>
					<td><img src="templates/fiappleblue/images/vote_rcap.gif" width="4" alt="" height="12" /></td>
				  </tr>
				</table>
			  </td>
			  <td align="center"><span class="gen">[ {rating_b.B_OPTION_RESULT} ]</span></td>
			</tr>
			<!-- END rating_b -->
		  </table>		
	</td>
  </tr>  
    
  <tr> 
	<td colspan="2" class="cat" height="28">&nbsp;</td>
  </tr>
</table>
<table border="0" cellpadding="0" cellspacing="0" class="ttb">
	<tr>
		<td><img src="templates/fiappleblue/images/tb12_l.gif" width="10" height="12" alt="" /></td>
		<td class="tb12bkg"><img src="images/spacer.gif" width="200" height="12" alt="" /></td>
		<td><img src="templates/fiappleblue/images/tb12_r.gif" width="10" height="12" alt="" /></td>
	</tr>
</table>
<!-- INCLUDE pa_footer.tpl -->

