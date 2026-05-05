<?php
/**
 * @var array $mailchimpData
 * @var array $mailchimpActivity
 * @var array $subscriptionStatusOptions
 */

?>


<div class="tab-pane" id="activity_info">
                <fieldset>
                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.status'), 'status', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_dropdown(
                                'status',
                                $subscriptionStatusOptions,
                                $mailchimpData['status'],
                                ['id' => 'status', 'class' => 'form-control input-sm']
                            ) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.vip'), 'vip', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-1">
                            <?= form_checkbox('vip', 1, $mailchimpData['vip'] == 1) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.member_rating'), 'member_rating', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'member_rating',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpData['member_rating'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.activity_total'), 'activity_total', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'activity_total',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['total'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.activity_last_open'), 'activity_last_open', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'activity_last_open',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['last_open'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.activity_open'), 'activity_open', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'activity_open',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['open'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.activity_click'), 'activity_click', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'activity_click',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['click'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.activity_unopen'), 'activity_unopen', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'activity_unopen',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpActivity['unopen'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>

                    <div class="form-group form-group-sm">
                        <?= form_label(lang('MailchimpPlugin.email_client'), 'email_client', ['class' => 'control-label col-xs-3']) ?>
                        <div class="col-xs-4">
                            <?= form_input([
                                'name'     => 'email_client',
                                'class'    => 'form-control input-sm',
                                'value'    => $mailchimpData['email_client'],
                                'disabled' => ''
                            ]) ?>
                        </div>
                    </div>
                </fieldset>
            </div>
