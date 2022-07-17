email templete
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:v="urn:schemas-microsoft-com:vml" lang="en">
   <head>
      <link rel="stylesheet" type="text/css" hs-webfonts="true" href="https://fonts.googleapis.com/css?family=Lato|Lato:i,b,bi">
      <title>Email Appointment</title>
      <meta property="og:title" content="Email template">
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <style type="text/css">
         a{ 
            text-decoration: underline;
            color: inherit;
            font-weight: bold;
            color: #253342;
         }
         h1 {
            font-size: 56px;
         }
         h2{
            font-size: 28px;
            font-weight: 900; 
         }
         p {
            font-weight: 100;
         }
         td {
            vertical-align: top;
         }
         #email {
            margin: auto;
            width: 600px;
            background-color: white;
         }
         .subtle-link {
            font-size: 9px; 
            text-transform:uppercase; 
            letter-spacing: 1px;
            color: #CBD6E2;
         }
      </style>
   </head>
   <body bgcolor="#F5F8FA" style="width: 100%; margin: auto 0; padding:0; font-family:Lato, sans-serif; font-size:18px; color:#33475B; word-break:break-word">
      <div id="email">
         <!-- Banner --> 
         <table role="presentation" width="100%">
            <tr>
               <td bgcolor="#1e83ec" align="center" style="color: white;">
                  <h1><?php echo lang('common_welcome'); ?>!</h1>
               </td>
         </table>
         <!-- First Row --> 
         <table role="presentation" border="0" cellpadding="0" cellspacing="10px" style="padding: 30px;">
            <tr>
               <td>
                  <h2><?php echo lang('appointments_one_or_multiple'); ?>, <?php echo $company_name; ?></h2>
                  <?php 
                  if($appointment_data['location_id']){
                     $location_info = $this->Location->get_info($appointment_data['location_id']);
                  }
                  ?>
                  <?php if($this->config->item('additional_appointment_note')){ ?>
                     <p><?php echo nl2br(make_marked_string_bold_italic_underline($this->Appconfig->replace_keywords_with_actual_word($this->config->item('additional_appointment_note'), $appointment_data['location_id'] , $appointment_data['person_id'], $appointment_data['employee_id'] )));?></p>
                  <?php } ?>
                  
                  <p><strong><?php echo lang('appointments_start_date'); ?>:</strong> <?php echo date(get_date_format().' '.get_time_format(),strtotime($appointment_data["start_time"])); ?></p>
                  <p><strong><?php echo lang('appointments_end_date'); ?>:</strong> <?php echo date(get_date_format().' '.get_time_format(),strtotime($appointment_data["end_time"])); ?></p>
                  <p><strong><?php echo lang('common_category'); ?>:</strong> <?php echo $this->Appointment->get_info_category($appointment_data["appointments_type_id"])->name;?></p>
                  <?php if($appointment_data['employee_id']){ ?>
                     <p><strong><?php echo lang('common_employee'); ?>:</strong> <?php echo ($employee = $this->Person->get_info($appointment_data['employee_id'])) ? $employee->full_name : "";?></p>
                  <?php } ?>
                  <?php if($appointment_data['location_id']){ ?>
                     <p><strong><?php echo lang('common_location'); ?>:</strong> <?php echo $location_info->name;?></p>
                     <p><strong><?php echo lang('common_phone_number'); ?>:</strong> <?php echo $location_info->phone;?></p>
                     <p><strong><?php echo lang('common_email'); ?>:</strong> <?php echo $location_info->email;?></p>
                     <p><strong><?php echo lang('common_address'); ?>:</strong> <?php echo $location_info->address;?></p>
                  <?php } ?>

                  <?php if($appointment_data['notes']){ ?>
                     <p style="text-align: justify;"><strong><?php echo lang('common_notes'); ?>:</strong> <?php echo $appointment_data["notes"];?></p>
                  <?php } ?>
               </td>
            </tr>
         </table>

         <!-- Banner Row --> 
         <table role="presentation" bgcolor="#EAF0F6" width="100%" style="margin-top: 10px;" >
            <tr>
               <td align="center" style="padding: 30px 30px;">
                  <h2><?php echo $company_name; ?></h2>
               </td>
            </tr>
         </table>
      </div>
   </body>
</html>
