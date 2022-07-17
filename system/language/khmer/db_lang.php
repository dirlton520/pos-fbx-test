<?php
//Unable to determine the database settings based on the connection string you submitted.
$lang['db_invalid_connection_str'] = 'មិនអាចកំណត់ការកំណត់មូលដ្ឋានទិន្នន័យដែលមានមូលដ្ឋានលើខ្សែអក្សរការតភ្ជាប់អ្នកបានដាក់ស្នើ។';
//Unable to connect to your database server using the provided settings.
$lang['db_unable_to_connect'] = 'មិនអាចតភ្ជាប់ទៅម៉ាស៊ីនបម្រើមូលដ្ឋានទិន្នន័យរបស់អ្នកដោយប្រើការកំណត់ដែលបានផ្ដល់។';
//Unable to select the specified database: %s
$lang['db_unable_to_select'] = 'មិនអាចជ្រើសមូលដ្ឋានទិន្នន័យដែលបានបញ្ជាក់:% s';
//Unable to create the specified database: %s
$lang['db_unable_to_create'] = 'មិនអាចបង្កើតមូលដ្ឋានទិន្នន័យដែលបានបញ្ជាក់:% s';
//The query you submitted is not valid.
$lang['db_invalid_query'] = 'សំណួរអ្នកបានដាក់ស្នើមិនត្រឹមត្រូវ។';
//You must set the database table to be used with your query.
$lang['db_must_set_table'] = 'អ្នកត្រូវតែកំណត់តារាងមូលដ្ឋានទិន្នន័យត្រូវបានប្រើជាមួយនឹងសំណួររបស់អ្នក។';
//You must use the "set" method to update an entry.
$lang['db_must_use_set'] = 'អ្នកត្រូវតែប្រើ "កំណត់" វិធីសាស្រ្តក្នុងការធ្វើបច្ចុប្បន្នភាពធាតុ។';
//You must specify an index to match on for batch updates.
$lang['db_must_use_index'] = 'អ្នកត្រូវតែបញ្ជាក់សន្ទស្សន៍មួយដើម្បីផ្គូផ្គងនៅថ្ងៃសម្រាប់ការធ្វើបច្ចុប្បន្នភាពបាច់។';
//One or more rows submitted for batch updating is missing the specified index.
$lang['db_batch_missing_index'] = 'ជួរដេកមួយឬច្រើនដែលបានដាក់ជូនសម្រាប់ការធ្វើឱ្យទាន់សម័យបានបាត់សន្ទស្សន៍បាច់បានបញ្ជាក់។';
//Updates are not allowed unless they contain a "where" clause.
$lang['db_must_use_where'] = 'ការធ្វើឱ្យទាន់សម័យមិនត្រូវបានអនុញ្ញាតទេលុះត្រាតែពួកគេមានមួយ "ដែលជាកន្លែងដែល" ឃ្លា។';
//Deletes are not allowed unless they contain a "where" or "like" clause.
$lang['db_del_must_use_where'] = 'លុបមិនត្រូវបានអនុញ្ញាតទេលុះត្រាតែពួកគេមានមួយ "ដែលជាកន្លែងដែល" ឬ "ដូចជា" ឃ្លា។';
//To fetch fields requires the name of the table as a parameter.
$lang['db_field_param_missing'] = 'ដើម្បីទៅប្រមូលយកវាលតម្រូវឱ្យឈ្មោះនៃតារាងជាប៉ារ៉ាម៉ែត្រមួយ។';
//This feature is not available for the database you are using.
$lang['db_unsupported_function'] = 'លក្ខណៈពិសេសនេះមិនមានសម្រាប់មូលដ្ឋានទិន្នន័យដែលអ្នកកំពុងប្រើ។';
//Transaction failure: Rollback performed.
$lang['db_transaction_failure'] = 'ការបរាជ័យក្នុងការប្រតិបត្តិការ: Rollback បានអនុវត្ត។';
//Unable to drop the specified database.
$lang['db_unable_to_drop'] = 'មិនអាចទម្លាក់មូលដ្ឋានទិន្នន័យដែលបានបញ្ជាក់។';
//Unsupported feature of the database platform you are using.
$lang['db_unsupported_feature'] = 'លក្ខណៈពិសេសដែលមិនគាំទ្រនៃវេទិកាមូលដ្ឋានទិន្នន័យដែលអ្នកកំពុងប្រើ។';
//The file compression format you chose is not supported by your server.
$lang['db_unsupported_compression'] = 'នេះជាទ្រង់ទ្រាយការបង្ហាប់ឯកសារដែលអ្នកបានជ្រើសរើសមិនត្រូវបានគាំទ្រដោយម៉ាស៊ីនបម្រើរបស់អ្នក។';
//Unable to write data to the file path you have submitted.
$lang['db_filepath_error'] = 'មិនអាចសរសេរទិន្នន័យទៅក្នុងផ្លូវឯកសារដែលអ្នកបានដាក់ស្នើ។';
//The cache path you submitted is not valid or writable.
$lang['db_invalid_cache_path'] = 'ផ្លូវដែលអ្នកបានដាក់ស្នើឃ្លាំងសម្ងាត់គឺមិនត្រឹមត្រូវឬអាចសរសេរបាន។';
//A table name is required for that operation.
$lang['db_table_name_required'] = 'ឈ្មោះតារាងគឺត្រូវបានទាមទារសម្រាប់ការប្រតិបត្ដិការនោះ។';
//A column name is required for that operation.
$lang['db_column_name_required'] = 'ឈ្មោះជួរឈរត្រូវបានទាមទារសម្រាប់ការប្រតិបត្ដិការនោះ។';
//A column definition is required for that operation.
$lang['db_column_definition_required'] = 'អត្ថន័យជួរឈរមួយត្រូវបានទាមទារសម្រាប់ការប្រតិបត្ដិការនោះ។';
//Unable to set client connection character set: %s
$lang['db_unable_to_set_charset'] = 'មិនអាចកំណត់ម៉ាស៊ីនភ្ញៀវសំណុំតួអក្សរការតភ្ជាប់:% s';
//A Database Error Occurred
$lang['db_error_heading'] = 'កំហុសក្នុងមូលដ្ឋានទិន្នន័យមួយបានកើតឡើង';
?>