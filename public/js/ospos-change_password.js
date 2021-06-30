//validation and submit handling
$(document).ready(function()
{
  $.validator.setDefaults({ ignore: [] });

  $.validator.addMethod("notEqualTo", function(value, element, param) {
    return this.optional(element) || value != $(param).val();
  }, '<?php echo $this->lang->line('employees_password_not_must_match'); ?>');

  $('#employee_form').validate($.extend({
    submitHandler: function(form) {
      $(form).ajaxSubmit({
        success: function(response)
        {
          dialog_support.hide();
          $.notify(response.message, {type: response.success ? 'success' : 'danger'});
        },
        dataType: 'json'
      });
    },

    rules:
    {
      current_password:
      {
        required:true,
        minlength: 8
      },
      password:
      {
        required:true,
        minlength: 8,
        notEqualTo: "#current_password"
      },
      repeat_password:
      {
        equalTo: "#password"
      }
      },

    messages:
    {
      password:
      {
        required:"<?php echo $this->lang->line('employees_password_required'); ?>",
        minlength: "<?php echo $this->lang->line('employees_password_minlength'); ?>"
      },
      repeat_password:
      {
        equalTo: "<?php echo $this->lang->line('employees_password_must_match'); ?>"
        }
    }
  }, form_support.error));
});
