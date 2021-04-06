<?php echo form_open('config/save_integrations/', array('id' => 'integrations_config_form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal')); ?>
<div id='config_wrapper'>
    <fieldset id='config_info'>
        <div id='required_fields_message'><?php echo $this->lang->line('common_fields_required_message'); ?></div>
        <ul id='integrations_error_message_box' class='error_message_box'></ul>


        <!-- Mailchimp Integration -->
        <div id='integrations_header'><?php echo $this->lang->line('config_mailchimp_configuration')?></div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_mailchimp_api_key'), 'mailchimp_api_key', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-cloud'></span></span>
					<?php echo form_input(array(
						'name'	=> 'mailchimp_api_key',
						'id'	=> 'mailchimp_api_key',
						'class'	=> 'form-control input-sm',
						'value'	=> $mailchimp['api_key']));
					?>
                </div>
            </div>
            <div class='col-xs-1'>
                <label class='control-label'>
                    <a href='http://eepurl.com/b9a05b' target='_blank'><span class='glyphicon glyphicon-info-sign' data-toggle='tooltip' data-placement='right' title='<?php echo $this->lang->line('config_mailchimp_tooltip'); ?>'></span></a>
                </label>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_mailchimp_lists'), 'mailchimp_list_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-user'></span></span>
					<?php echo form_dropdown(
						'mailchimp_list_id',
						$mailchimp['lists'],
						$mailchimp['list_id'],
						array('id' => 'mailchimp_list_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <!-- CLCdesq Integration -->
        <div id='integrations_header'><?php echo $this->lang->line('config_clcdesq_configuration')?></div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_enable'), 'clcdesq_enable', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-1'>
				<?php echo form_checkbox(array(
					'name'		=> 'clcdesq_enable',
					'value'		=> 'clcdesq_enable',
					'id'		=> 'clcdesq_enable',
					'checked'	=> $clcdesq['enable']));
				?>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_api_key'), 'clcdesq_api_key', array('class' => 'control-label col-xs-2 required')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-cloud'></span></span>
					<?php echo form_input(array(
						'name'	=> 'clcdesq_api_key',
						'id'	=> 'clcdesq_api_key',
						'class'	=> 'form-control input-sm required',
						'value'	=> $clcdesq['api_key'])); ?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_api_url'), 'clcdesq_api_url', array('class' => 'control-label col-xs-2 required')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-cloud'></span></span>
					<?php echo form_input(array(
						'name'	=> 'clcdesq_api_url',
						'id'	=> 'clcdesq_api_url',
						'class'	=> 'form-control input-sm required',
						'value'	=> $clcdesq['api_url'])); ?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_utilities'), 'config_clcdesq_items_upload', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-2'>
                <div id='items_upload' class='btn btn-default btn-sm'>
                    <span style='top:22%;'><?php echo $this->lang->line('config_clcdesq_items_upload'); ?></span>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'><?php echo form_label($this->lang->line('config_clcdesq_source'), 'clcdesq_clcdesqsource_id', array('class' => 'control-label col-xs-2','style' => 'text-decoration:underline;')); ?></div>
        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_aspectratio'), 'clcdesq_aspectratio_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-resize-full'></span></span>
					<?php echo form_dropdown(
						'clcdesq_aspectratio_id',
						$clcdesq['available_attributes'],
						$clcdesq['aspectratio_attribute'],
						array('id' => 'clcdesq_apectratio_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_audiencerating'), 'clcdesq_audiencerating_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-thumbs-up'></span></span>
					<?php echo form_dropdown(
						'clcdesq_audiencerating_id',
						$clcdesq['available_attributes'],
						$clcdesq['audiencerating_attribute'],
						array('id' => 'clcdesq_audiencerating_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_audioformat'), 'clcdesq_audioformat_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-volume-up'></span></span>
					<?php echo form_dropdown(
						'clcdesq_audioformat_id',
						$clcdesq['available_attributes'],
						$clcdesq['audioformat_attribute'],
						array('id' => 'clcdesq_audioformat_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_audiotracklisting'), 'clcdesq_audiotracklisting_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-list'></span></span>
					<?php echo form_dropdown(
						'clcdesq_audiotracklisting_id',
						$clcdesq['available_attributes'],
						$clcdesq['audiotracklisting_attribute'],
						array('id' => 'clcdesq_audiotracklisting_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_authorstext'), 'clcdesq_authorstext_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-user'></span></span>
					<?php echo form_dropdown(
						'clcdesq_authorstext_id',
						$clcdesq['available_attributes'],
						$clcdesq['authorstext_attribute'],
						array('id' => 'clcdesq_authorstext_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_binding'), 'clcdesq_binding_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-book'></span></span>
					<?php echo form_dropdown(
						'clcdesq_binding_id',
						$clcdesq['available_attributes'],
						$clcdesq['binding_attribute'],
						array('id' => 'clcdesq_binding_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_bookforeword'), 'clcdesq_bookforeword_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-menu-hamburger'></span></span>
					<?php echo form_dropdown(
						'clcdesq_bookforeword_id',
						$clcdesq['available_attributes'],
						$clcdesq['bookforeword_attribute'],
						array('id' => 'clcdesq_bookforeword_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_bookindex'), 'clcdesq_bookindex_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-list'></span></span>
					<?php echo form_dropdown(
						'clcdesq_bookindex_id',
						$clcdesq['available_attributes'],
						$clcdesq['bookindex_attribute'],
						array('id' => 'clcdesq_bookindex_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_booksamplechapter'), 'clcdesq_booksamplechapter_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-menu-hamburger'></span></span>
					<?php echo form_dropdown(
						'clcdesq_booksamplechapter_id',
						$clcdesq['available_attributes'],
						$clcdesq['booksamplechapter_attribute'],
						array('id' => 'clcdesq_booksamplechapter_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_category'), 'clcdesq_category_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-folder-open'></span></span>
					<?php echo form_dropdown(
						'clcdesq_category_id',
						$clcdesq['available_attributes'],
						$clcdesq['category_attribute'],
						array('id' => 'clcdesq_category_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_condition'), 'clcdesq_condition_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-folder-open'></span></span>
					<?php echo form_dropdown(
						'clcdesq_condition_id',
						$clcdesq['available_attributes'],
						$clcdesq['condition_attribute'],
						array('id' => 'clcdesq_condition_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_depth'), 'clcdesq_depth_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-indent-right'></span></span>
					<?php echo form_dropdown(
						'clcdesq_depth_id',
						$clcdesq['available_attributes'],
						$clcdesq['depth_attribute'],
						array('id' => 'clcdesq_depth_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_format'), 'clcdesq_format_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-book'></span></span>
					<?php echo form_dropdown(
						'clcdesq_format_id',
						$clcdesq['available_attributes'],
						$clcdesq['format_attribute'],
						array('id' => 'clcdesq_format_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_height'), 'clcdesq_height_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-indent-right'></span></span>
					<?php echo form_dropdown(
						'clcdesq_height_id',
						$clcdesq['available_attributes'],
						$clcdesq['height_attribute'],
						array('id' => 'clcdesq_height_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_location'), 'clcdesq_location_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-globe'></span></span>
					<?php echo form_dropdown(
						'clcdesq_location_id',
						$clcdesq['available_attributes'],
						$clcdesq['location_attribute'],
						array('id' => 'clcdesq_location_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_language'), 'clcdesq_language_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-globe'></span></span>
					<?php echo form_dropdown(
						'clcdesq_language_id',
						$clcdesq['available_attributes'],
						$clcdesq['language_attribute'],
						array('id' => 'clcdesq_language_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_numberofdiscs'), 'clcdesq_numberofdiscs_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-cd'></span></span>
					<?php echo form_dropdown(
						'clcdesq_numberofdiscs_id',
						$clcdesq['available_attributes'],
						$clcdesq['numberofdiscs_attribute'],
						array('id' => 'clcdesq_numberofdiscs_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_numberofpages'), 'clcdesq_numberofpages_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-duplicate'></span></span>
					<?php echo form_dropdown(
						'clcdesq_numberofpages_id',
						$clcdesq['available_attributes'],
						$clcdesq['numberofpages_attribute'],
						array('id' => 'clcdesq_numberofpages_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_originaltitle'), 'clcdesq_originaltitle_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-text-size'></span></span>
					<?php echo form_dropdown(
						'clcdesq_originaltitle_id',
						$clcdesq['available_attributes'],
						$clcdesq['originaltitle_attribute'],
						array('id' => 'clcdesq_originaltitle_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_pricenote'), 'clcdesq_pricenote_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-tags'></span></span>
					<?php echo form_dropdown(
						'clcdesq_pricenote_id',
						$clcdesq['available_attributes'],
						$clcdesq['pricenote_attribute'],
						array('id' => 'clcdesq_pricenote_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_producer'), 'clcdesq_producer_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-print'></span></span>
					<?php echo form_dropdown(
						'clcdesq_producer_id',
						$clcdesq['available_attributes'],
						$clcdesq['producer_attribute'],
						array('id' => 'clcdesq_producer_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_releasedate'), 'clcdesq_releasedate_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-calendar'></span></span>
					<?php echo form_dropdown(
						'clcdesq_releasedate_id',
						$clcdesq['available_attributes'],
						$clcdesq['releasedate_attribute'],
						array('id' => 'clcdesq_releasedate_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_runningtime'), 'clcdesq_runningtime_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-time'></span></span>
					<?php echo form_dropdown(
						'clcdesq_runningtime_id',
						$clcdesq['available_attributes'],
						$clcdesq['runningtime_attribute'],
						array('id' => 'clcdesq_runningtime_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_series'), 'clcdesq_series_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-option-horizontal'></span></span>
					<?php echo form_dropdown(
						'clcdesq_series_id',
						$clcdesq['available_attributes'],
						$clcdesq['series_attribute'],
						array('id' => 'clcdesq_series_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_showonwebsite'), 'clcdesq_showonwebsite_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-eye-open'></span></span>
					<?php echo form_dropdown(
						'clcdesq_showonwebsite_id',
						$clcdesq['available_attributes'],
						$clcdesq['showonwebsite_attribute'],
						array('id' => 'clcdesq_showonwebsite_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_stockonorder'), 'clcdesq_stockonorder_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-subscript'></span></span>
					<?php echo form_dropdown(
						'clcdesq_stockonorder_id',
						$clcdesq['available_attributes'],
						$clcdesq['stockonorder_attribute'],
						array('id' => 'clcdesq_stockonorder_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_subtitle'), 'clcdesq_subtitle_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-subscript'></span></span>
					<?php echo form_dropdown(
						'clcdesq_subtitle_id',
						$clcdesq['available_attributes'],
						$clcdesq['subtitle_attribute'],
						array('id' => 'clcdesq_subtitle_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_subtitles'), 'clcdesq_subtitles_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-globe'></span></span>
					<?php echo form_dropdown(
						'clcdesq_subtitles_id',
						$clcdesq['available_attributes'],
						$clcdesq['subtitles_attribute'],
						array('id' => 'clcdesq_subtitles_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_teaserdescription'), 'clcdesq_teaserdescription_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-menu-hamburger'></span></span>
					<?php echo form_dropdown(
						'clcdesq_teaserdescription_id',
						$clcdesq['available_attributes'],
						$clcdesq['teaserdescription_attribute'],
						array('id' => 'clcdesq_teaserdescription_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_upc'), 'clcdesq_upc_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-barcode'></span></span>
					<?php echo form_dropdown(
						'clcdesq_upc_id',
						$clcdesq['available_attributes'],
						$clcdesq['upc_attribute'],
						array('id' => 'clcdesq_upc_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_uniqueid'), 'clcdesq_uniqueid_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-barcode'></span></span>
					<?php echo form_dropdown(
						'clcdesq_uniqueid_id',
						$clcdesq['available_attributes'],
						$clcdesq['uniqueid_attribute'],
						array('id' => 'clcdesq_uniqueid_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_videotrailerembedcode'), 'clcdesq_videotrailerembedcode_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-film'></span></span>
					<?php echo form_dropdown(
						'clcdesq_videotrailerembedcode_id',
						$clcdesq['available_attributes'],
						$clcdesq['videotrailerembedcode_attribute'],
						array('id' => 'clcdesq_videotrailerembedcode_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_weight'), 'clcdesq_weight_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-scale'></span></span>
					<?php echo form_dropdown(
						'clcdesq_weight_id',
						$clcdesq['available_attributes'],
						$clcdesq['weight_attribute'],
						array('id' => 'clcdesq_weight_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_weightforshipping'), 'clcdesq_weightforshipping_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-envelope'></span></span>
					<?php echo form_dropdown(
						'clcdesq_weightforshipping_id',
						$clcdesq['available_attributes'],
						$clcdesq['weightforshipping_attribute'],
						array('id' => 'clcdesq_weightforshipping_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <div class='form-group form-group-sm'>
			<?php echo form_label($this->lang->line('config_clcdesq_width'), 'clcdesq_width_id', array('class' => 'control-label col-xs-2')); ?>
            <div class='col-xs-4'>
                <div class='input-group'>
                    <span class='input-group-addon input-sm'><span class='glyphicon glyphicon-indent-right'></span></span>
					<?php echo form_dropdown(
						'clcdesq_width_id',
						$clcdesq['available_attributes'],
						$clcdesq['width_attribute'],
						array('id' => 'clcdesq_width_id', 'class' => 'form-control input-sm'));
					?>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
		<?php
		echo form_submit(array(
			'name'	=> 'submit_integrations',
			'id'	=> 'submit_integrations',
			'value'	=> $this->lang->line('common_submit'),
			'class'	=> 'btn btn-primary btn-sm pull-right'));
		?>
    </fieldset>
</div>
<?php echo form_close(); ?>

<script type='text/javascript'>
    //validation and submit handling
    $(document).ready(function()
    {
        $('#mailchimp_api_key').change(function() {
            $.post("<?php echo site_url("$controller_name/ajax_check_mailchimp_api_key")?>", {
                    'mailchimp_api_key': $('#mailchimp_api_key').val()
                },
                function(response) {
                    $.notify(response.message, {type: response.success ? 'success' : 'danger'} );
                    $('#mailchimp_list_id').empty();
                    $.each(response.mailchimp_lists, function(val, text) {
                        $('#mailchimp_list_id').append(new Option(text, val));
                    });
                    $('#mailchimp_list_id').prop('selectedIndex', 0);
                },
                'json'
            );
        });

        var enable_disable_config_clcdesq_enable = (function() {
            var config_clcdesq_enable = $("#clcdesq_enable").is(":checked");
            $("input[name*='clcdesq_api_key']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("input[name*='clcdesq_api_url']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_aspectratio_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_audiencerating_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_audioformat_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_audiotracklisting_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_authorstext_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_binding']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_bookforeword']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_bookindex']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_booksamplechapter']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_category_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_condition_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_depth_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_format_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_height_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_language_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_location_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_numberofdiscs_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_numberofpages_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_originaltitle_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_pricenote_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_producer_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_releasedate_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_runningtime_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_series_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_showonwebsite_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_stockonorder_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_subtitle_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_subtitles_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_teaserdescription_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_uniqueid_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_upc_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_videotrailerembedcode_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_weight_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_weightforshipping_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);
            $("select[name*='clcdesq_width_id']:not(input[name=clcdesq_enable])").prop("disabled", !config_clcdesq_enable);

            return arguments.callee;
        })();

        $("#clcdesq_enable").change(enable_disable_config_clcdesq_enable);

        $("#items_upload").click(function() {
            window.location='<?php echo site_url('config/initial_items_upload') ?>';
        });

        $('#integrations_config_form').validate($.extend(form_support.handler, {
            submitHandler: function(form) {
                $(form).ajaxSubmit({
                    success: function(response) {
                        $.notify(response.message, { type: response.success ? 'success' : 'danger'} );
                    },
                    dataType: 'json'
                });
            },

            errorLabelContainer: '#integrations_error_message_box'
        }));
    });
</script>