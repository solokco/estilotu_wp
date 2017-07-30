<?php

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

	global $post;
		
?> 	

	<div class="woo-vou-popup-content woo-vou-import-content">
					
		<div class="woo-vou-header">
			<div class="woo-vou-header-title"><?php _e( 'Generate / Import Codes', 'woovoucher' ); ?></div>
			<div class="woo-vou-popup-close"><a href="javascript:void(0);" class="woo-vou-close-button"><img src="<?php echo WOO_VOU_URL .'includes/images/tb-close.png'; ?>" alt="<?php _e( 'Close','woovoucher' ); ?>"></a></div>
		</div>
			
		<div class="woo-vou-popup">

			<div class="woo-vou-file-errors"></div>
			<form method="POST" action="" enctype="multipart/form-data" id="woo_vou_import_csv">
				<table class="form-table woo-vou-import-table">
					<tbody>
						<tr>
							<td colspan="2"><strong><?php _e( 'General', 'woovoucher' ); ?><strong></td>
						</tr>
						<tr>
							<td scope="col" class="woo-vou-field-title"><?php _e( 'Delete Existing Code', 'woovoucher' ); ?></td>
							<td>
								<select name="woo_vou_delete_code" class="woo-vou-delete-code">
									<option value=""><?php _e( 'No', 'woovoucher' ); ?></option>
									<option value="y"><?php _e( 'Yes', 'woovoucher' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<strong><?php _e( 'Generate Options', 'woovoucher' ); ?><strong>
							</td>
						</tr>
					</tbody>
					<tbody id="woo-vou-code-generate-part">
						<tr>
							<td scope="col" class="woo-vou-field-title"><?php _e( 'Number of Voucher Codes', 'woovoucher' ); ?></td>
							<td>
								<input type="text" class="woo-vou-no-of-voucher" value="" />
							</td>
						</tr>
						<tr class="woo-vou-submisssion-tr">
							<td scope="col" class="woo-vou-field-title"><?php _e( 'Submission', 'woovoucher' ); ?></td>
							<td>
								<span class="woo-vou-prefix-span"><strong><?php _e( 'Prefix', 'woovoucher' ); ?></strong></span>
								<span class="woo-vou-seperator-span"><strong><?php _e( 'Seperator', 'woovoucher' ); ?></strong></span>
								<span class="woo-vou-pattern-span"><strong><?php _e( 'Pattern', 'woovoucher' ); ?></strong></span><br />
								<input type="text" class="woo-vou-code-prefix" value="" />
								<input type="text" class="woo-vou-code-seperator" value="" />
								<input type="text" class="woo-vou-code-pattern" value="" /><br />
								<span class="description">
									<strong><?php _e( 'L', 'woovoucher' ); ?></strong> - <?php _e( 'letter', 'woovoucher' ); ?>, <strong><?php _e( 'D', 'woovoucher' ); ?></strong> - <?php _e( 'digit', 'woovoucher' ); ?>
									<small><?php _e( 'e.g. CODE_LLDDD results in CODE_WT108 and code_llddd results in code_wt108', 'woovoucher' ); ?></small>
								</span>
							</td>
						</tr>
						<tr>
							<td scope="col"></td>
							<td>
								<input type="button" class="woo-vou-import-btn button-secondary" value="<?php _e( 'Generate Codes', 'woovoucher' ); ?>" />
								<img class="woo-vou-loader" src="<?php echo WOO_VOU_URL . 'includes/images/ajax-loader.gif'; ?>" alt="<?php _e('Loading...', 'woovoucher'); ?>" />
							</td>
						</tr>
					</tbody>
					<tbody>
						<tr>
							<td colspan="2">
								<strong><?php _e( 'Import Options', 'woovoucher' ); ?><strong>
							</td>
						</tr>
					</tbody>
					<tbody id="woo-vou-code-import-part">
						<tr>
							<td scope="col"><?php _e( 'CSV Separator', 'woovoucher' ); ?></td>
							<td>
								<input type="text" id="woo_vou_csv_sep" name="woo_vou_csv_sep" class="woo-vou-csv-sep"/>
							</td>
						</tr>
						<tr>
							<td scope="col"><?php _e( 'CSV Enclosure', 'woovoucher' ); ?></td>
							<td>
								<input type="text" id="woo_vou_csv_enc" name="woo_vou_csv_enc" class="woo-vou-csv-enc"/>
							</td>
						</tr>
						<tr>
							<td scope="col" class="woo-vou-field-title"><?php _e( 'Upload CSV File', 'woovoucher' ); ?></td>
							<td>
								<input type="file" id="woo_vou_csv_file" name="woo_vou_csv_file" class="woo-vou-csv-file"/>
							</td>
						</tr>
						<tr>
							<td scope="col"></td>
							<td>
								<input type="hidden" id="woo_vou_existing_code" name="woo_vou_existing_code" value="" />
								<input type="submit" name="woo_vou_import_csv" id="woo_vou_import_csv" value="<?php _e( 'Import Codes', 'woovoucher' ); ?>" class="button-secondary woo-vou-meta-vou-import-codes">
							</td>
						</tr>
							
					</tbody>
				</table>
			</form>
		</div><!--.woo-vou-popup-->
	</div><!--.woo-vou-popup-content-->
	
	<div class="woo-vou-popup-overlay woo-vou-import-overlay"></div>