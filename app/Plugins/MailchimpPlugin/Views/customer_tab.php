<?php
/**
 * @var array $mailchimpData
 * @var array $mailchimpActivity
 */
?>


<div class="tab-pane" id="customer_mailchimp_info">
                <fieldset>
                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_status'), 'mailchimp_status', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_dropdown(
                                'mailchimp_status',
                                [
                                    'subscribed'   => 'subscribed',
                                    'unsubscribed' => 'unsubscribed',
                                    'cleaned'      => 'cleaned',
                                    'pending'      => 'pending'
                                ],
                                $mailchimpData['status'],
                                ['id' => 'mailchimp_status', 'class' => 'form-control input-sm']
                            ) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_vip'), 'mailchimp_vip', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-1">
                            <?= form_checkbox('mailchimp_vip', 1, $mailchimpData['vip'] == 1) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_member_rating'), 'mailchimp_member_rating', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'mailchimp_member_rating',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpData['member_rating'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_activity_total'), 'mailchimp_activity_total', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'mailchimp_activity_total',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['total'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_activity_lastopen'), 'mailchimp_activity_lastopen', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'mailchimp_activity_lastopen',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['lastopen'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_activity_open'), 'mailchimp_activity_open', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'mailchimp_activity_open',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['open'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_activity_click'), 'mailchimp_activity_click', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'mailchimp_activity_click',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['click'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_activity_unopen'), 'mailchimp_activity_unopen', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'mailchimp_activity_unopen',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['unopen'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.mailchimp_email_client'), 'mailchimp_email_client', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'mailchimp_email_client',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpData['email_client'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>
                </fieldset>
            </div>
