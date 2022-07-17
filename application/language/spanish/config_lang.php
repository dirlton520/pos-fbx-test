<?php
$lang['config_info']='Información de la configuración de la tienda';

$lang['config_address']='Dirección de la compañía';
$lang['config_phone']='Teléfono de la compañía';
$lang['config_website']='Sitio web';
$lang['config_fax']='Fax';
$lang['config_default_tax_rate']='% de la tasa de impuestos predeterminado';


$lang['config_company_required']='Es necesario ingresar el nombre de la compañía';

$lang['config_phone_required']='Es necesario ingresar el teléfono de la compañía';
$lang['config_default_tax_rate_required']='Es necesario ingresar el porcentaje de la tasa de impuestos';
$lang['config_default_tax_rate_number']='La tasa de impuestos predeterminada debe contener un valor numérico';
$lang['config_company_website_url']='El sitio web que ingresó no mantiene un formato estándar (http://...)';

$lang['config_saved_unsuccessfully']='Error al guardar la configuración. No se permite generar cambios en el modo de demostración o los impuestos no fueron guardados de manera exitosa ';
$lang['config_return_policy_required']='Es necesario ingresar la política de devoluciones';
$lang['config_print_after_sale']='¿Desea imprimir el recibo después de una venta?';


$lang['config_currency_symbol'] = 'Símbolo de moneda';
$lang['config_backup_database'] = 'Copia de seguridad de base de datos';
$lang['config_restore_database'] = 'Restaurar base de datos';

$lang['config_number_of_items_per_page'] = 'Número de artículos por página';
$lang['config_date_format'] = 'Formato de fecha';
$lang['config_time_format'] = 'Formato de hora';
$lang['config_company_logo'] = 'Logotipo de la compañía';
$lang['config_delete_logo'] = 'Eliminar logotipo';

$lang['config_database_optimize_successfully'] = 'La base de datos ha sido optimizada de manera exitosa';
$lang['config_payment_types'] = 'Método de pago';
$lang['select_sql_file'] = 'Seleccione archivo SQL';
$lang['restore_heading'] = 'Esto le permitirá restaurar la base de datos';

$lang['type_file'] = 'Seleccione el archivo SQL desde su dispositivo';

$lang['restore'] = 'Restaurar';

$lang['required_sql_file'] = 'No se ha seleccionado el archivo SQL';

$lang['restore_db_success'] = 'La base de datos se ha restaurado de manera exitosa';

$lang['db_first_alert'] = '¿Está seguro que desea restaurar la base de datos?';
$lang['db_second_alert'] = 'Los datos existentes se eliminarán. ¿Desea continuar de cualquier manera?';
$lang['password_error'] = 'La contraseña no es válida';
$lang['password_required'] = 'Es necesario ingresar la constraseña';
$lang['restore_database_title'] = 'Restaurar base de datos';
$lang['config_use_scale_barcode'] = 'Usar códigos de barra a escala';

$lang['config_environment'] = 'Entorno';


$lang['config_sandbox'] = 'Área de pruebas';
$lang['config_production'] = 'Producción';
$lang['disable_confirmation_sale']='¿Desea desactivar la confirmación de venta completada?';



$lang['config_default_payment_type'] = 'Método de pago estándar';
$lang['config_speed_up_note'] = 'Solo se recomienda si usted tiene más de 10.000 artículos o clientes';
$lang['config_hide_signature'] = '¿Ocultar firma?';
$lang['config_automatically_email_receipt']='Envío automático al correo electrónico del cliente';
$lang['config_barcode_price_include_tax']='Incluyen el impuesto sobre los códigos de barras';
$lang['config_round_cash_on_sales'] = 'Redondear a la cifra más cercana en .05 (Sólo para Canadá)';

$lang['config_prefix'] = 'Prefijo del Id. de Venta';
$lang['config_sale_prefix_required'] = 'Es necesario introducir un prefijo Id. de Venta';
$lang['config_customers_store_accounts'] = 'Permitir cuentas de crédito dentro de la tienda';
$lang['config_change_sale_date_when_suspending'] = 'Cambiar la fecha de venta cuando se suspende una venta';
$lang['config_change_sale_date_when_completing_suspended_sale'] = 'Cambiar la fecha de venta al completar una venta suspendida';
$lang['config_price_tiers'] = 'Niveles de precios';
$lang['config_add_tier'] = 'Agregar nivel';
$lang['config_show_receipt_after_suspending_sale'] = 'Mostrar recibo después de la suspensión de una venta';
$lang['config_backup_overview'] = 'Información general de la copia de seguridad';
$lang['config_backup_overview_desc'] = 'Realizar copias de seguridad de sus datos es muy importante, pero puede ser un problema con una gran cantidad de datos. Si usted tiene una gran variedad de imágenes, artículos y ventas, esto podría aumentar significativamente el tamaño de su base de datos.';
$lang['config_backup_options'] = 'Contamos con varias opciones para crear copias de seguridad para que así usted pueda decidir entre las mejores opciones';
$lang['config_backup_simple_option'] = 'Al hacer clic en &quot;Copia de seguridad de la base de datos&quot;. Este intentará descargar toda su base de datos a un archivo. Si se muestra una pantalla en blanco o no puede descargar el archivo, pruebe con una de las diferentes opciones.';
$lang['config_backup_phpmyadmin_1'] = 'PhpMyAdmin es una herramienta popular para la gestión de bases de datos. Si está utilizando la versión de descarga con el instalador, se puede acceder dirigiéndose hacia';
$lang['config_backup_phpmyadmin_2'] = 'Su nombre de usuario es root y la contraseña es la que se utilizó durante la instalación inicial de PHP POS. Una vez que se ha conectado, seleccione la base de datos desde el panel de la izquierda. A continuación, seleccione la exportación y luego envíe el formulario.';
$lang['config_backup_control_panel'] = 'Si ha instalado en su propio servidor que cuenta con un panel de control como Cpanel, busque el módulo de copias de seguridad que a menudo le permitirá descargar copias de seguridad de su base de datos.';
$lang['config_backup_mysqldump'] = 'Si usted tiene acceso al Shell y a Mysqldump en su servidor, usted puede tratar de ejecutarlo haciendo clic en el enlace de abajo. De lo contrario, tendrá que intentar con otras opciones disponibles.';
$lang['config_mysqldump_failed'] = 'El proceso de respaldo de Mysqldump falló. Esto podría deberse a una restricción en el servidor o en el comando que podría no estar disponible. Por favor intente otro método de copia de seguridad';



$lang['config_looking_for_location_settings'] = '¿Está buscando otras opciones de configuración? Diríjase a';
$lang['config_module'] = 'Módulo';
$lang['config_automatically_calculate_average_cost_price_from_receivings'] = 'Calcular el costo promedio de la compra';
$lang['config_averaging_method'] = 'Método de promedio';
$lang['config_historical_average'] = 'Promedio histórico';
$lang['config_moving_average'] = 'Promedio móvil';

$lang['config_hide_dashboard_statistics'] = 'Ocultar el panel de estadísticas';
$lang['config_hide_store_account_payments_in_reports'] = 'Ocultar las cuentas por pagar en los reportes de la tienda';
$lang['config_id_to_show_on_sale_interface'] = 'Columna para mostrar en la interfaz de ventas';
$lang['config_auto_focus_on_item_after_sale_and_receiving'] = 'Posicionar el cursor en el campo del artículo en la interfaz de ventas y entradas';
$lang['config_automatically_show_comments_on_receipt'] = 'Mostrar automáticamente los comentarios en el recibo';
$lang['config_hide_customer_recent_sales'] = 'Ocultar ventas recientes para el cliente';
$lang['config_spreadsheet_format'] = 'Formato de la hoja de cálculo';
$lang['config_csv'] = 'CSV';
$lang['config_xlsx'] = 'XLSX';
$lang['config_disable_giftcard_detection'] = 'Desactivar la detección de tarjetas de regalo';
$lang['config_disable_subtraction_of_giftcard_amount_from_sales'] = 'Deshabilitar la substracción de dinero de una tarjeta de regalo mientras se realiza una venta';
$lang['config_always_show_item_grid'] = 'Mostrar siempre la cuadrícula de artículos';
$lang['config_legacy_detailed_report_export'] = 'Exportación de un reporte detallado de activos a un archivo de Excel';
$lang['config_print_after_receiving'] = 'Imprimir recibo después de que se ha generado una entrada';
$lang['config_company_info'] = 'Información de la compañía';


$lang['config_suspended_sales_layaways_info'] = 'Ventas suspendidas / Sistema de apartado';
$lang['config_application_settings_info'] = 'Configuración de la aplicación';
$lang['config_hide_barcode_on_sales_and_recv_receipt'] = 'Ocultar el código de barras dentro de un recibo';
$lang['config_round_tier_prices_to_2_decimals'] = 'Redondear nivel de precios a dos decimales';
$lang['config_group_all_taxes_on_receipt'] = 'Agrupar todos los impuestos en el recibo';
$lang['config_receipt_text_size'] = 'Tamaño del texto en el recibo';
$lang['config_small'] = 'Pequeño';
$lang['config_medium'] = 'Mediano';
$lang['config_large'] = 'Grande';
$lang['config_extra_large'] = 'Extra grande';
$lang['config_select_sales_person_during_sale'] = 'Seleccionar al vendedor mientras se realiza la venta';
$lang['config_default_sales_person'] = 'Vendedor predeterminado';
$lang['config_require_customer_for_sale'] = 'Requerir ingresar a un cliente para proceder con la venta';

$lang['config_hide_store_account_payments_from_report_totals'] = 'Ocultar pagos a la cuenta de la tienda desde el Reporte de totales';
$lang['config_disable_sale_notifications'] = 'Desactivar notificaciones dentro de una venta';
$lang['config_id_to_show_on_barcode'] = 'Id. para mostrar en el código de barras';
$lang['config_currency_denoms'] = 'Denominaciones de moneda';
$lang['config_currency_value'] = 'Valor de la moneda';
$lang['config_add_currency_denom'] = 'Añadir denominación de moneda';
$lang['config_enable_timeclock'] = 'Activar reloj de asistencia';
$lang['config_change_sale_date_for_new_sale'] = 'Cambiar la fecha de venta para una venta nueva';
$lang['config_dont_average_use_current_recv_price'] = 'Sin promedio, utilice el precio actual recibido';
$lang['config_number_of_recent_sales'] = 'Número de ventas recientes para mostrar por cliente';
$lang['config_hide_suspended_recv_in_reports'] = 'Ocultar entradas suspendidas en los informes';
$lang['config_calculate_profit_for_giftcard_when'] = 'Calcular la ganancia de la tarjeta de regalo cuando';
$lang['config_selling_giftcard'] = 'Vendiendo tarjeta de regalo';
$lang['config_redeeming_giftcard'] = 'Redimiendo tarjeta de regalo';
$lang['config_remove_customer_contact_info_from_receipt'] = 'Eliminar información de contacto del cliente del recibo';
$lang['config_speed_up_search_queries'] = '¿Agilizar las consultas de búsqueda?';




$lang['config_redirect_to_sale_or_recv_screen_after_printing_receipt'] = 'Redirigir a la pantalla de ventas o entradas después de imprimir el recibo';
$lang['config_enable_sounds'] = 'Activar sonidos para mensajes de estado';
$lang['config_charge_tax_on_recv'] = 'Cargar impuesto en entradas';
$lang['config_report_sort_order'] = 'Orden en los informes';
$lang['config_asc'] = 'Primero lo antiguo';
$lang['config_desc'] = 'Primero lo nuevo';
$lang['config_do_not_group_same_items'] = 'No agrupar los artículos que son iguales';
$lang['config_show_item_id_on_receipt'] = 'Mostrar Id. del artículo en el recibo';
$lang['config_show_language_switcher'] = 'Mostrar la selección de lenguajes';
$lang['config_do_not_allow_out_of_stock_items_to_be_sold'] = 'No permitir la venta de productos sin existencias.';
$lang['config_number_of_items_in_grid'] = 'Número de artículos por página en la cuadrícula';
$lang['config_edit_item_price_if_zero_after_adding'] = 'Editar el precio del artículo si es igual a 0 después de añadir a la venta';
$lang['config_override_receipt_title'] = 'Anular el título del recibo';
$lang['config_automatically_print_duplicate_receipt_for_cc_transactions'] = 'Imprima automáticamente un recibo duplicado para las transacciones con tarjeta de crédito';






$lang['config_default_type_for_grid'] = 'Tipo predeterminado para la cuadrícula';
$lang['config_billing_is_managed_through_paypal'] = 'La facturación se gestiona a través de <a target="_blank" href="http://paypal.com">Paypal</a>. Usted puede cancelar su suscripción haciendo clic <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=BNTRX72M8UZ2E">aquí</a>. <a href="http://phppointofsale.com/update_billing.php" target="_blank">Puede actualizar la facturación aquí</a>';
$lang['config_cannot_change_language'] = 'El idioma no se puede guardar en el nivel de aplicación. Sin embargo, el empleado de administración predeterminado puede cambiar el idioma utilizando el selector en el encabezado del programa';
$lang['disable_quick_complete_sale'] = 'Desactivar la función de venta rápida';
$lang['config_fast_user_switching'] = 'Activar cambio rápido de usuario (la contraseña no es obligatoria)';
$lang['config_require_employee_login_before_each_sale'] = 'Exigir a los empleados iniciar sesión antes de cada venta';
$lang['config_keep_same_location_after_switching_employee'] = 'Conserve la misma localización después de cambiar de empleado';
$lang['config_number_of_decimals'] = 'Número de decimales';
$lang['config_let_system_decide'] = 'Dejar que el sistema tome la mejor decisión (recomendado)';
$lang['config_thousands_separator'] = 'Separador de miles';
$lang['config_enhanced_search_method'] = 'Método de búsqueda mejorado';
$lang['config_hide_store_account_balance_on_receipt'] = 'Ocultar el saldo de la cuenta de la tienda en el recibo';
$lang['config_decimal_point'] = 'Punto decimal';
$lang['config_hide_out_of_stock_grid'] = 'Ocultar cuadrícula con artículos fuera de inventario.';
$lang['config_highlight_low_inventory_items_in_items_module'] = 'Resaltar elementos con un bajo inventario en el módulo de artículos';
$lang['config_sort'] = 'Tipo';
$lang['config_enable_customer_loyalty_system'] = 'Activar el sistema de lealtad del cliente';
$lang['config_spend_to_point_ratio'] = 'Señalar la proporción de la cantidad de gasto';
$lang['config_point_value'] = 'Valor del punto';
$lang['config_hide_points_on_receipt'] = 'Ocultar puntos en el recibo';
$lang['config_show_clock_on_header'] = 'Mostrar el reloj en el encabezado';
$lang['config_show_clock_on_header_help_text'] = 'Esto solo se visualizará en pantallas amplias';
$lang['config_loyalty_explained_spend_amount'] = 'Ingrese la cantidad de gasto';
$lang['config_loyalty_explained_points_to_earn'] = 'Introduzca los puntos obtenidos';
$lang['config_simple'] = 'Sencillo';
$lang['config_advanced'] = 'Avanzado';
$lang['config_loyalty_option'] = 'Opción del programa de lealtad';
$lang['config_number_of_sales_for_discount'] = 'Número de ventas para alcanzar un descuento';
$lang['config_discount_percent_earned'] = 'Porciento de descuento alcanzado por las ventas generadas';
$lang['hide_sales_to_discount_on_receipt'] = 'Ocultar ventas necesarias en el recibo para obtener un descuento';
$lang['config_hide_price_on_barcodes'] = 'Ocultar precio en los códigos de barras';
$lang['config_always_use_average_cost_method'] = 'Usar siempre el método de costo promedio para la venta de un artículo. (NO marque a menos que sepa lo que significa)';

$lang['config_test_mode_help'] = 'En el modo de prueba las ventas NO se guardan';
$lang['config_require_customer_for_suspended_sale'] = 'Requerir al cliente para una venta suspendida';
$lang['config_default_new_items_to_service'] = 'Hacer los nuevos artículos predeterminados como artículos de servicio';






$lang['config_prompt_for_ccv_swipe'] = 'Preguntar por el CCV al pasar la tarjeta de crédito';
$lang['config_disable_store_account_when_over_credit_limit'] = 'Desactivar la cuenta de la tienda cuando se supere el límite de crédito';
$lang['config_mailing_labels_type'] = 'Formato de las etiquetas de correo';
$lang['config_phppos_session_expiration'] = 'Expiración de la sesión';
$lang['config_hours'] = 'Horas';
$lang['config_never'] = 'Nunca';
$lang['config_on_browser_close'] = 'Cerrar navegador';
$lang['config_do_not_allow_below_cost'] = 'NO permitir que los artículos se vendan por debajo de su costo';
$lang['config_store_account_statement_message'] = 'Mensaje del estado de la cuenta de la tienda';
$lang['config_enable_markup_calculator'] = 'Activar la calculadora de margen';
$lang['config_disable_quick_edit'] = 'Desactivar la edición rápida en las páginas de administración';
$lang['config_show_orig_price_if_marked_down_on_receipt'] = 'Mostrar el precio original en el recibo si tiene descuento';
$lang['config_cancel_account'] = 'Cancelar cuenta';
$lang['config_update_billing'] = 'Puede actualizar y cancelar sus datos de facturación haciendo clic en los botones de abajo:';
$lang['config_include_child_categories_when_searching_or_reporting'] = 'Incluir categorías secundarias en la búsqueda o presentación de informes';
$lang['config_reset_location_when_switching_employee'] = 'Restablecer localización cuando se cambia empleado';
$lang['config_enable_quick_edit'] = 'Habilitar edición rápida en las páginas de administración';
$lang['config_confirm_error_messages_modal'] = 'Confirmar mensajes de error utilizando cuadros de diálogo modales';
$lang['config_remove_commission_from_profit_in_reports'] = 'Retire la comisión de la ganancia en los informes';
$lang['config_remove_points_from_profit'] = 'Eliminar los puntos de redención de beneficios';
$lang['config_capture_sig_for_all_payments'] = 'Captura de firmas para todas las ventas';
$lang['config_suppliers_store_accounts'] = 'Cuentas de tienda de proveedores';
$lang['config_currency_symbol_location'] = 'Localización del símbolo monetario';
$lang['config_before_number'] = 'antes de Número';
$lang['config_after_number'] = 'después de Número';
$lang['config_hide_desc_on_receipt'] = 'Ocultar Descripción en Recibo';
$lang['config_default_percent_off'] = 'Porciento de descuento predeterminado';
$lang['config_default_cost_plus_percent'] = 'Costo mas porciento predeterminado';
$lang['config_default_tier_percent_type_for_excel_import'] = 'Nivel de porciento predeterminado para la importación de Excel';
$lang['config_override_tier_name'] = 'Nivel anular Nombre de Recibo';
$lang['config_loyalty_points_without_tax'] = 'Puntos de fidelidad ganados sin incluir el impuesto';
$lang['config_lock_prices_suspended_sales'] = 'Bloquear precios al reactivar venta suspendida, incluso si pertenecen a un nivel';
$lang['config_remove_customer_name_from_receipt'] = 'Retire Nombre del cliente del recibo';
$lang['config_scale_1'] = 'UPC-12 de 4 dígitos de precios';
$lang['config_scale_2'] = 'UPC-12 5 dígitos Precio';
$lang['config_scale_3'] = 'EAN-13 5 dígitos de precios';
$lang['config_scale_4'] = 'EAN-13 6 dígitos de precios';
$lang['config_scale_format'] = 'Formato Escala de código de barras';
;
$lang['config_enable_scale'] = 'Habilitar Escala';
$lang['config_scale_divide_by'] = 'Escala Divide Precio Por';
$lang['config_logout_on_clock_out'] = 'Cerrar la sesión automáticamente al fichar a cabo';
$lang['config_user_configured_layaway_name'] = 'Nombre anular Pago a plazos';
$lang['config_use_tax_value_at_all_locations'] = 'Utilice los valores de impuesto en todos los lugares';
$lang['config_enable_ebt_payments'] = 'Permitir los pagos EBT';
$lang['config_item_id_auto_increment'] = 'Valor inicial incremento automático ID de artículo';
$lang['config_change_auto_increment_item_id_unsuccessful'] = 'Se ha producido un error al cambiar AUTO_INCREMENT para item_id';
$lang['config_item_kit_id_auto_increment'] = 'Valor inicial incremento automático ID Kit de artículos';
$lang['config_sale_id_auto_increment'] = 'Valor inicial incremento automático ID venta';
$lang['config_receiving_id_auto_increment'] = 'Valor inicial incremento automático ID recepción';
$lang['config_change_auto_increment_item_kit_id'] = 'Se ha producido un error al cambiar AUTO_INCREMENT para Item_kit_id';
$lang['config_change_auto_increment_sale_id'] = 'Se ha producido un error al cambiar AUTO_INCREMENT para sale_id';
$lang['config_change_auto_increment_receiving_id'] = 'Se ha producido un error al cambiar AUTO_INCREMENT para receiving_id';
$lang['config_auto_increment_note'] = 'Solo se pueden aumentar los valores de incremento automático. Su actualización no afectará a los ID de artículos, kits de artículos, ventas o recepciones que ya existen.';
$lang['config_woo_api_key'] = 'Clave de API WooCommerce';
$lang['config_email_settings_info'] = 'Ajustes del correo electrónico';
$lang['config_last_sync_date'] = 'Fecha última sincronización';
$lang['config_sync'] = 'Sincronizar';
$lang['config_online_price_tier'] = 'Nivel de precio en línea';
$lang['config_smtp_crypto'] = 'El cifrado SMTP';
$lang['config_email_protocol'] = 'Protocolo del envío de correo';
$lang['config_smtp_host'] = 'Dirección del servidor SMTP';
$lang['config_smtp_user'] = 'Dirección de correo electrónico';
$lang['config_smtp_pass'] = 'Contraseña de Email';
$lang['config_smtp_port'] = 'Puerto SMTP';
$lang['config_email_charset'] = 'Conjunto de caracteres';
$lang['config_email_newline'] = 'caracter de nueva línea';
$lang['config_email_crlf'] = 'CRLF';
$lang['config_smtp_timeout'] = 'Tiempo de espera SMTP';
$lang['config_send_test_email'] = 'Enviar correo electrónico de prueba';
$lang['config_please_enter_email_to_send_test_to'] = 'Por favor, introduzca la dirección de correo electrónico para enviar correo electrónico de prueba para';
$lang['config_email_succesfully_sent'] = 'El correo electrónico ha sido enviado con éxito';
$lang['config_taxes_info'] = 'Impuestos';
$lang['config_currency_info'] = 'Moneda';

$lang['config_receipt_info'] = 'Recibo';

$lang['config_barcodes_info'] = 'Los códigos de barras';
$lang['config_customer_loyalty_info'] = 'La lealtad del cliente';
$lang['config_price_tiers_info'] = 'Niveles de precios';
$lang['config_auto_increment_ids_info'] = 'números de ID';
$lang['config_items_info'] = 'Artículos';
$lang['config_employee_info'] = 'Empleados';
$lang['config_store_accounts_info'] = 'Cuentas de las tiendas';
$lang['config_sales_info'] = 'Ventas';
$lang['config_payment_types_info'] = 'Formas de pago';
$lang['config_profit_info'] = 'Cálculo de ganancia';
$lang['reports_view_dashboard_stats'] = 'Ver del panel de estadísticas';
$lang['config_keyword_email'] = 'Ajustes del correo electrónico';
$lang['config_keyword_company'] = 'empresa';
$lang['config_keyword_taxes'] = 'impuestos';
$lang['config_keyword_currency'] = 'moneda';
$lang['config_keyword_payment'] = 'pago';
$lang['config_keyword_sales'] = 'ventas';
$lang['config_keyword_suspended_layaways'] = 'layaways suspendidos';
$lang['config_keyword_receipt'] = 'recibo';
$lang['config_keyword_profit'] = 'lucro';
$lang['config_keyword_barcodes'] = 'códigos de barras';
$lang['config_keyword_customer_loyalty'] = 'la lealtad del cliente';
$lang['config_keyword_price_tiers'] = 'niveles de precios';
$lang['config_keyword_auto_increment'] = 'a partir de bases de datos de incremento automático números de identificación';
$lang['config_keyword_items'] = 'artículos';
$lang['config_keyword_employees'] = 'empleados';
$lang['config_keyword_store_accounts'] = 'cuentas de tiendas';
$lang['config_keyword_application_settings'] = 'Configuraciones de la aplicación';
$lang['config_keyword_ecommerce'] = 'plataforma de comercio electrónico';
$lang['config_keyword_woocommerce'] = 'WooCommerce configuración de comercio electrónico';
$lang['config_billing_info'] = 'Datos de facturación';
$lang['config_keyword_billing'] = 'cancelar la actualización de facturación';
$lang['config_woo_version'] = 'Versión WooCommerce';

$lang['sync_phppos_item_changes'] = 'cambios elemento de sincronización';
$lang['config_sync_phppos_item_changes'] = 'cambios elemento de sincronización';
$lang['config_import_ecommerce_items_into_phppos'] = 'Importar elementos en phppos';
$lang['config_sync_inventory_changes'] = 'cambios en el inventario de sincronización';
$lang['config_export_phppos_tags_to_ecommerce'] = 'etiquetas de exportación a comercio electrónico';
$lang['config_export_phppos_categories_to_ecommerce'] = 'categorías de exportación a comercio electrónico';
$lang['config_export_phppos_items_to_ecommerce'] = 'artículos de exportación a comercio electrónico';
$lang['config_ecommerce_cron_sync_operations'] = 'Las operaciones de sincronización de comercio electrónico';
$lang['config_ecommerce_progress'] = 'El progreso de sincronización';
$lang['config_woocommerce_settings_info'] = 'Ajustes WooCommerce';
$lang['config_store_location'] = 'Localización de la tienda';
$lang['config_woo_api_secret'] = 'WooCommerce API Secreto';
$lang['config_woo_api_url'] = 'WooCommerce API Url';
$lang['config_ecommerce_settings_info'] = 'Plataforma de comercio electrónico';
$lang['config_ecommerce_platform'] = 'Seleccionar plataforma';
$lang['config_magento_settings_info'] = 'Ajustes de Magento';
$lang['confirmation_woocommerce_cron_cancel'] = '¿Está seguro de que desea cancelar la sincronización?';
$lang['config_force_https'] = 'Requerir HTTPS para el programa de';

$lang['config_keyword_price_rules'] = 'Reglas de precios';
$lang['config_disable_price_rules_dialog'] = 'Desactivar diálogo Reglas Precio';
$lang['config_price_rules_info'] = 'Reglas de precios';

$lang['config_prompt_to_use_points'] = 'Solicitud de uso puntos cuando esté disponible';



$lang['config_always_print_duplicate_receipt_all'] = 'Siempre imprima un recibo duplicado para todas las transacciones';


$lang['config_orders_and_deliveries_info'] = 'Pedidos y entregas';
$lang['config_delivery_methods'] = 'Métodos de entrega';
$lang['config_shipping_providers'] = 'Proveedores de envío';
$lang['config_expand'] = 'Expandir';
$lang['config_add_delivery_rate'] = 'Añadir tasa de entrega';
$lang['config_add_shipping_provider'] = 'Agregar proveedor de envío';
$lang['config_delivery_rates'] = 'Tarifas de Entrega';
$lang['config_delivery_fee'] = 'Gastos de envío';
$lang['config_keyword_orders_deliveries'] = 'Ordena las entregas';
$lang['config_delivery_fee_tax'] = 'Impuesto a la Entrega';
$lang['config_add_rate'] = 'Añadir tarifa';
$lang['config_delivery_time'] = 'Tiempo de entrega en días';
$lang['config_delivery_rate'] = 'Cargo de entrega';
$lang['config_rate_name'] = 'Nombre de la tarifa';
$lang['config_rate_fee'] = 'Tarifa de tarifa';
$lang['config_rate_tax'] = 'Tasa de Impuestos';
$lang['config_tax_classes'] = 'Grupos de impuestos';
$lang['config_add_tax_class'] = 'Agregar grupo de impuestos';

$lang['config_wide_printer_receipt_format'] = 'Formato de recibo de impresora';

$lang['config_default_cost_plus_fixed_amount'] = 'Costo predeterminado más cantidad fija';
$lang['config_default_tier_fixed_type_for_excel_import'] = 'Nivel predeterminado de monto fijo para importación de Excel';
$lang['config_default_reorder_level_when_creating_items'] = 'Nivel de reorden predeterminado al crear artículos';
$lang['config_remove_customer_company_from_receipt'] = 'Eliminar el nombre de la empresa del cliente del recibo';

$lang['config_import_ecommerce_categories_into_phppos'] = 'Importar categorías en phppos';
$lang['config_import_ecommerce_tags_into_phppos'] = 'Importa etiquetas en phppos';

$lang['config_shipping_zones'] = 'Zonas de envío';
$lang['config_add_shipping_zone'] = 'Añadir zona de envío';
$lang['config_no_results'] = 'No hay resultados';
$lang['config_zip_search_term'] = 'Escriba un código postal';
$lang['config_searching'] = 'Buscando...';
$lang['config_tax_class'] = 'Grupo de impuestos';
$lang['config_zone'] = 'Zona';

$lang['config_zip_codes'] = 'Códigos ZIP';
$lang['config_add_zip_code'] = 'Añadir código postal';
$lang['config_ecom_sync_logs'] = 'Registros de sincronización de comercio electrónico';
$lang['config_currency_code'] = 'Código de moneda';

$lang['config_add_currency_exchange_rate'] = 'Añadir Tipo de cambio de moneda';
$lang['config_currency_exchange_rates'] = 'Los tipos de cambio';
$lang['config_exchange_rate'] = 'Tipo de cambio';
$lang['config_item_lookup_order'] = 'Orden de búsqueda de artículos';
$lang['config_item_id'] = 'Identificación del artículo';
$lang['config_reset_ecommerce'] = 'Restablecer comercio electrónico';
$lang['config_confirm_reset_ecom'] = '¿Está seguro de que desea restablecer el comercio electrónico? Esto solo restablecerá el punto de venta de php para que los elementos ya no estén vinculados';
$lang['config_reset_ecom_successfully'] = 'Ha restablecido el comercio electrónico correctamente';
$lang['config_number_of_decimals_for_quantity_on_receipt'] = 'Número de decimales para la cantidad en el recibo';
$lang['config_enable_wic'] = 'Habilitar WIC';
$lang['config_store_opening_time'] = 'Hora de apertura de la tienda';
$lang['config_store_closing_time'] = 'Hora de cierre de la tienda';
$lang['config_limit_manual_price_adj'] = 'Limitar ajustes manuales de precios y descuentos';
$lang['config_always_minimize_menu'] = 'Minimizar siempre el menú de la barra lateral izquierda';

$lang['config_emailed_receipt_subject'] = 'Asunto del recibo por correo electrónico';

$lang['config_do_not_tax_service_items_for_deliveries'] = 'NO cobrar impuestos por las entregas';


$lang['config_do_not_show_closing'] = 'No mostrar el monto de cierre esperado al cerrar la caja registradora';

$lang['config_paypal_me'] = 'Nombre de usuario de PayPal.me';


$lang['config_show_barcode_company_name'] = 'Mostrar el nombre de la empresa en el código de barras';
$lang['config_import_ecommerce_attributes_into_phppos'] = 'Importar atributos en phppos';
$lang['config_export_phppos_attributes_to_ecommerce'] = 'Exportar atributos al comercio electrónico';

$lang['config_sku_sync_field'] = 'Campo SKU para sincronizar con';



$lang['config_overwrite_existing_items_on_excel_import'] = 'Sobreescribir los artículos existentes en la importación de Excel';

$lang['config_do_not_force_http'] = 'No fuerce HTTP cuando sea necesario para el procesamiento de tarjeta de crédito EMV';
$lang['config_add_suspended_sale_type'] = 'Agregar tipo de venta suspendida';
$lang['config_additional_suspend_types'] = 'Tipos de venta suspendida adicionales';
$lang['config_remove_employee_from_receipt'] = 'Eliminar el nombre del empleado del recibo';
$lang['config_import_ecommerce_orders_into_phppos'] = 'Importar pedidos en phppos';
$lang['import_ecommerce_orders_into_phppos'] = 'Importar pedidos a php pos';
$lang['config_hide_name_on_barcodes'] = 'Ocultar nombre en códigos de barras';


$lang['config_api_settings_info'] = 'Configuración de API';
$lang['config_keyword_api'] = 'API';
$lang['config_api_keys'] = 'Claves de API';
$lang['config_api_key_ending_in'] = 'Finalización de la clave API en';
$lang['config_permissions'] = 'Permisos';
$lang['config_last_access'] = 'Ultimo acceso';
$lang['config_add_key'] = 'Agregar clave de API';
$lang['config_api_key'] = 'Clave API';
$lang['config_read'] = 'Leer';
$lang['config_read_write'] = 'Leer escribir';
$lang['config_submit_api_key'] = '¿Seguro que quieres agregar esta clave? Asegúrese de haber copiado la clave de ubicación segura ya que no se volverá a mostrar.';
$lang['config_write'] = 'Escribir';
$lang['config_api_key_confirm_delete'] = '¿Seguro que quieres eliminar esta clave api?';
$lang['config_key_copied_to_clipboard'] = 'Clave copiada al portapapeles';

$lang['config_new_items_are_ecommerce_by_default'] = 'Los nuevos artículos son comercio electrónico predeterminado';


$lang['config_new_items_are_ecommerce_by_default'] = 'Los nuevos artículos son comercio electrónico predeterminado';

$lang['config_hide_description_on_sales_and_recv'] = 'Ocultar descripción en interfaces de ventas y recepciones';





$lang['config_hide_item_descriptions_in_reports'] = 'ocultar la descripción del artículo en los informes';





$lang['config_do_not_allow_item_with_variations_to_be_sold_without_selecting_variation'] = 'NO permita que los artículos de variación se vendan sin seleccionar la variación';



$lang['config_verify_age_for_products'] = 'Verificar la edad de los productos';
$lang['config_default_age_to_verify'] = 'Edad predeterminada para verificar';




$lang['config_remind_customer_facing_display'] = 'Recordar al empleado que abra la pantalla orientada al cliente';

$lang['config_import_tax_classes_into_phppos'] = 'Importación de clases de impuestos en phppos';
$lang['config_export_tax_classes_into_phppos'] = 'Exportar clases de impuestos al comercio electrónico';
$lang['config_import_shipping_classes_into_phppos'] = 'Importar clases de envío a phppos';
$lang['config_disable_confirm_recv'] = 'Inhabilitar confirmación para recibir por completo';
$lang['config_minimum_points_to_redeem'] = 'Número mínimo de puntos para canjear';
$lang['config_default_days_to_expire_when_creating_items'] = 'Días predeterminados para caducar al crear elementos';


$lang['config_quickbooks_settings'] = 'Configuración de Quickbooks';
$lang['config_qb_sync_operations'] = 'Operaciones de sincronización de Quickbooks';
$lang['config_import_quickbooks_items_into_phppos'] = 'Importar elementos en phppos';
$lang['config_export_phppos_items_to_quickbooks'] = 'Exportar elementos a libros rápidos';
$lang['config_import_customers_into_phppos'] = 'Importar clientes en phppos';
$lang['config_import_suppliers_into_phppos'] = 'Importar proveedores a phppos';
$lang['config_import_employees_into_phppos'] = 'Importar empleados a phppos';
$lang['config_export_employees_to_quickbooks'] = 'Exportar empleados a libros rápidos';
$lang['config_export_sales_to_quickbooks'] = 'Exportación de ventas a QuickBooks';
$lang['config_export_receivings_to_quickbooks'] = 'Exportar recepciones a quickbooks';
$lang['config_export_customers_to_quickbooks'] = 'Exportar clientes a quickbooks';
$lang['config_export_suppliers_to_quickbooks'] = 'Exportar proveedores a quickbooks';
$lang['config_connect_to_qb_online'] = 'Conéctese a libros rápidos en línea';
$lang['config_refresh_tokens'] = 'Refrescar fichas';
$lang['config_reconnect_quickbooks'] = 'Vuelva a conectar a libros rápidos en línea';
$lang['config_reset_quickbooks'] = 'Restablecer Quickbooks';
$lang['config_qb_sync_logs'] = 'Registros de sincronización de Quickbooks';
$lang['config_quickbooks_progress'] = 'Progreso de sincronización de Quickbooks';
$lang['config_last_qb_sync_date'] = 'Última fecha de sincronización';
$lang['config_confirmation_qb_cron_cancel'] = '¿Seguro que quieres cancelar la sincronización de libros rápidos?';
$lang['config_confirmation_qb_cron'] = '¿Seguro que quieres sincronizar QuickBooks?';
$lang['config_confirm_reset_qb'] = '¿Seguro que quieres restablecer los libros rápidos? Esto lo desvinculará de los libros rápidos.';
$lang['$platform=$this->Appconfig->get("ecommerce_platform");'] = 'if ($ plataforma == "woocommerce")';
$lang['config_reset_qb_successfully'] = 'Ha restablecido Quickbooks con éxito';
$lang['config_export_phppos_categories_to_quickbooks'] = 'Exportar categorías de phppos a quickbooks';
$lang['config_create_payment_methods'] = 'Crear métodos de pago en QB';


$lang['config_allow_scan_of_customer_into_item_field'] = 'Permitir el escaneo del cliente en el campo del artículo';
$lang['config_cash_alert_high'] = 'Alerta cuando el efectivo está arriba';
$lang['config_cash_alert_low'] = 'Alerta cuando el efectivo está debajo';


$lang['config_sync_inventory_changes_qb'] = 'Sincronizar cambios de inventario';

$lang['config_sort_receipt_column'] = 'Ordenar columna de recibo';





$lang['config_show_tax_per_item_on_receipt'] = 'Mostrar impuesto por artículo en recibo';





$lang['config_enable_timeclock_pto'] = 'Habilitar tiempo libre pagado por reloj de tiempo';


$lang['config_enable_timeclock_pto'] = 'Habilitar tiempo libre pagado por reloj de tiempo';

$lang['config_show_item_id_on_recv_receipt'] = 'Mostrar identificación del artículo al recibir';





$lang['config_import_all_past_orders_for_woo_commerce'] = 'Importar TODAS las órdenes pasadas para WooCommerce';




$lang['config_enable_margin_calculator'] = 'Habilitar la Calculadora de Margen';










$lang['config_hide_barcode_on_barcode_labels'] = 'Ocultar código de barras en las etiquetas';



$lang['config_do_not_delete_saved_card_after_failure'] = 'NO borre la tarjeta guardada después de la falla';





$lang['config_capture_internal_notes_during_sale'] = 'Captura de notas internas durante la venta';





$lang['config_hide_prices_on_fill_sheet'] = 'Ocultar precios en la hoja de cumplimiento';



$lang['$platform=$this->Appconfig->get("ecommerce_platform");'] = 'if ($ plataforma == "woocommerce")';
$lang['config_default_revenue_account_for_item'] = 'Cuenta de ingresos por defecto para artículos';
$lang['config_default_asset_account_for_item'] = 'Cuenta de activos predeterminada para artículos';
$lang['config_default_expense_account_for_item'] = 'Cuenta de gastos por defecto para artículos';
$lang['config_export_expenses_to_quickbooks'] = 'Gastos de exportación a quickbooks.';
$lang['config_chart_of_accounts'] = 'Quickbooks Plan de cuentas';
$lang['config_keyword_chart_of_account'] = 'Quickbooks Plan de cuentas';
$lang['config_default_refund_cash_account_name'] = 'Cuenta de reembolso en efectivo';
$lang['config_default_refund_credit_account_name'] = 'Cuenta de crédito de reembolso';
$lang['config_default_refund_debit_card_account_name'] = 'Tarjeta de débito de reembolso';
$lang['config_default_refund_credit_card_account_name'] = 'Cuenta de tarjeta de crédito de reembolso';
$lang['config_default_refund_check_account_name'] = 'Cuenta de cheques de reembolso';
$lang['config_default_refund_deposit_account_name'] = 'Cuenta de depósito de reembolso';
$lang['config_default_expense_account_name'] = 'Cuenta de gastos';
$lang['config_default_expense_bank_credit_account_name'] = 'Cuenta de gastos / banco';
$lang['config_default_commission_credit_account_name'] = 'Cuenta de crédito de la Comisión';
$lang['config_default_commission_debit_account_name'] = 'Cuenta de débito de la comisión';
$lang['config_default_house_account_name'] = 'Nombre de cuenta de la tienda';
$lang['config_default_discount_item_name'] = 'Artículo con descuento';
$lang['config_default_house_item_name'] = 'Nombre del artículo de la casa';
$lang['config_default_store_account_item_name'] = 'Artículo de cuenta de tienda';
$lang['config_default_house_account_category_name'] = 'Categoría de cuenta de la casa';
$lang['config_default_customer_id'] = 'Nombre de cliente predeterminado';
$lang['config_revenue_id'] = 'Error al guardar la configuración. Falta la cuenta de ingresos predeterminada para artículos.';
$lang['config_asset_id'] = 'Error al guardar la configuración. Falta la cuenta de activos predeterminada para artículos';
$lang['config_export_confirm_box_text'] = '¿Quieres exportar artículos a quickbooks?';
$lang['config_discount_accounting_id'] = 'Falta la identificación de la cuenta del artículo de descuento en venta';
$lang['config_sync_for_discount_accounting_id'] = 'Por favor, sincronice los artículos antes de crear facturas con descuento';


$lang['config_hide_desc_emailed_receipts'] = 'Ocultar descripción en recibos por correo electrónico';


$lang['config_default_tax'] = 'Impuesto por Defecto';
$lang['config_default_store_account_tax'] = 'Impuesto de cuenta de tienda predeterminado';
$lang['config_check_tax_name'] = 'El nombre del impuesto proporcionado no es correcto. Por favor, compruebe ID de venta:';
$lang['config_qb_start_sync_date'] = 'Iniciar la fecha de sincronización';
$lang['config_default_tax_id'] = 'Impuesto por Defecto';
$lang['config_markup_markdown'] = 'Markup / Markdown';
$lang['config_show_total_discount_on_receipt'] = 'Mostrar descuento total en el recibo';
$lang['config_enable_pdf_receipts'] = 'Habilitar recibos en PDF';
$lang['config_default_credit_limit'] = 'Límite de crédito predeterminado';

$lang['config_hide_expire_date_on_barcodes'] = 'Ocultar fecha de caducidad en códigos de barras';

$lang['config_auto_capture_signature'] = 'Firma de captura automática';


$lang['config_pdf_receipt_message'] = 'PDF recibo mensaje en el cuerpo del correo';

$lang['config_hide_merchant_id_from_receipt'] = 'Ocultar ID de comerciante desde el recibo';


$lang['config_hide_all_prices_on_recv'] = 'Ocultar TODOS los precios en la recepción';
$lang['config_do_not_delete_serial_number_when_selling'] = 'NO borre el número de serie al vender';
$lang['config_webhooks'] = 'Ganchos de red';
$lang['config_new_customer_web_hook'] = 'Nuevo URL del Web Hook del cliente';
$lang['config_new_sale_web_hook'] = 'Nueva URL de gancho web de venta';
$lang['config_new_receiving_web_hook'] = 'Nuevo gancho web receptor';

$lang['config_strict_age_format_check'] = 'Verificación de edad estricta fecha de verificación de formato';

$lang['config_flat_discounts_discount_tax'] = 'Descuento plano también descuentos impuestos';
$lang['config_show_item_kit_items_on_receipt'] = 'Mostrar artículos del artículo en el recibo';
$lang['config_amount_of_cash_to_be_left_in_drawer_at_closing'] = 'Cantidad de efectivo que se dejará en el cajón al cierre';
$lang['config_hide_tier_on_receipt'] = 'Ocultar nivel en el recibo';
$lang['config_second_language'] = 'Segundo Idioma en Recibos';
$lang['config_disable_gift_cards_sold_from_loyalty'] = 'Deshabilitar tarjetas de regalo vendidas de ganar lealtad';
$lang['config_track_shipping_cost_for_receivings'] = 'Rastrear el costo de envío para recibos';
$lang['config_enable_points_for_giftcard_payments'] = 'Habilitar puntos para pagos con tarjeta de regalo.';




$lang['config_enable_tips'] = 'Habilitar consejos';

$lang['config_support_regex'] = 'Soporta expresiones regulares. Ejemplo: 144. * coincide con cualquier cosa que comience con 144';

$lang['config_not_all_processors_support_tips'] = 'No todos los procesadores admiten el procesamiento integrado de puntas';
$lang['config_require_supplier_recv'] = 'Requerir Proveedor para Recibir';
$lang['config_default_payment_type_recv'] = 'Tipo de pago predeterminado para recibos';
$lang['config_taxjar_api_key'] = 'Clave API de TaxJar (solo en EE. UU.)';

$lang['config_quick_variation_grid'] = 'Habilitar selección rápida para variantes en la cuadrícula de elementos';


$lang['config_quick_variation_grid'] = 'Selección rápida para variaciones';


$lang['config_quick_variation_grid'] = 'Habilitar selección rápida en la cuadrícula de elementos para variaciones';



$lang['config_show_full_category_path'] = 'Mostrar ruta de categoría completa al buscar';


$lang['config_do_not_upload_images_to_ecommerce'] = 'NO cargue imágenes a E-Commerce';

$lang['config_woo_enable_html_desc'] = 'Habilitar HTML para descripciones';

$lang['config_use_rtl_barcode_library'] = 'Use la biblioteca de códigos de barras RTL';
$lang['config_default_new_customer_to_current_location'] = 'Nuevo cliente predeterminado a la ubicación actual';
$lang['config_week_start_day'] = 'Día de inicio de semana';
$lang['config_scan_and_set_sales'] = 'Elija la cantidad después de agregar un artículo en ventas';
$lang['config_scan_and_set_recv'] = 'Elija la cantidad después de agregar un artículo en los recibos';
$lang['config_edit_sale_web_hook'] = 'Editar URL de enlace web de venta';
$lang['config_edit_recv_web_hook'] = 'Editar URL de enlace web de recepción';
$lang['config_hide_expire_dashboard'] = 'Ocultar elementos que caducan en el tablero';
$lang['config_hide_images_in_grid'] = 'Ocultar imágenes en la cuadrícula';
$lang['config_taxes_summary_on_receipt'] = 'Mostrar resumen imponible y no imponible en el recibo';
$lang['config_collapse_sales_ui_by_default'] = 'Contraer interfaz de ventas por defecto';
$lang['config_collapse_recv_ui_by_default'] = 'Contraer la interfaz de recepción de forma predeterminada';
$lang['config_enable_customer_quick_add'] = 'Habilitar la adición rápida de clientes';
$lang['config_uppercase_receipts'] = 'Texto de recibo en mayúscula';

$lang['config_edit_customer_web_hook'] = 'Editar URL de enlace web del cliente';
$lang['config_show_selling_price_on_recv'] = 'Mostrar precio de venta al recibir el recibo';

$lang['config_hide_email_on_receipts'] = 'Ocultar correo electrónico en el recibo';



$lang['config_hide_available_giftcards'] = 'Ocultar tarjetas de regalo disponibles en el registro de ventas';


$lang['config_enable_supplier_quick_add'] = 'Habilitar la adición rápida de proveedores';
$lang['config_sync_inventory_from_location'] = 'Sincronizar inventario desde ubicación';
$lang['config_taxes_summary_details_on_receipt'] = 'Mostrar detalles de impuestos en el recibo';
$lang['config_disable_recv_number_on_barcode'] = 'Desactivar número de recepción en código de barras';
$lang['config_tax_jar_location'] = 'Use la API de ubicación de TaxJar para extraer impuestos';
$lang['config_disable_loyalty_by_default'] = 'Deshabilitar lealtad por defecto';

$lang['config_ecommerce_only_sync_completed_orders'] = 'Solo sincronizar pedidos de comercio electrónico completados';

$lang['config_damaged_reasons'] = 'Razones Dañadas';

$lang['config_display_item_name_first_for_variation_name'] = 'Mostrar el nombre del elemento primero para variaciones en los códigos de barras';


$lang['config_do_not_allow_sales_with_zero_value'] = 'NO permita ventas con valor cero';

$lang['config_dont_recalculate_cost_price_when_unsuspending_estimates'] = 'No vuelva a calcular el precio de costo cuando las estimaciones no se suspendan';


$lang['config_show_signature_on_receiving_receipt'] = 'Mostrar firma al recibir el recibo';

$lang['config_do_not_treat_service_items_as_virtual'] = 'NO trate los artículos de servicio como productos virtuales en woo commerce';

$lang['config_hide_latest_updates_in_header'] = 'Ocultar las últimas actualizaciones en el encabezado';
$lang['config_prompt_amount_for_cash_sale'] = 'Importe pronto para la venta en efectivo';
$lang['config_do_not_allow_items_to_go_out_of_stock_when_transfering'] = 'No permita que los artículos se agoten al transferir';
$lang['config_show_tags_on_fulfillment_sheet'] = 'Mostrar etiquetas de artículo en la hoja de cumplimiento';
$lang['config_automatically_sms_receipt'] = 'Recepción automática de SMS';
$lang['config_items_per_search_suggestions'] = 'Número de elementos para sugerencias de búsqueda';

$lang['config_shopify_settings_info'] = 'Configuración de Shopify';
$lang['config_shopify_shop'] = 'URL de la tienda Shopify';
$lang['config_connect_to_shopify'] = 'Conectarse a Shopify';
$lang['config_connect_to_shopify_reconnect'] = 'Vuelve a conectarte a Shopify';
$lang['config_connected_to_shopify'] = 'Estás conectado a Shopify';
$lang['config_disconnect_to_shopify'] = 'Desconectarse de Shopify';

$lang['config_offline_mode'] = 'Habilitar el modo sin conexión';
$lang['config_reset_offline_data'] = 'Restablecer datos sin conexión';



$lang['config_remove_quantity_suspending'] = 'Eliminar cantidad al suspender';
$lang['config_auto_sync_offline_sales'] = 'Sincronización automática de ventas sin conexión cuando vuelva a estar en línea';

$lang['config_shopify_billing_terms'] = 'Activar la facturación: prueba de 14 días y luego {SHOPIFY_PRICE} por mes';
$lang['config_shopfiy_billing_failed'] = 'Error de facturación de Shopify';
$lang['config_cancel_shopify'] = 'Cancelar facturación de Shopify';
$lang['config_confirm_cancel_shopify'] = '¿Estás seguro de que deseas cancelar Shopify?';
$lang['config_step_1'] = 'Paso 1';
$lang['config_step_2'] = 'Paso 2';
$lang['config_step_3'] = 'Paso 3';
$lang['config_step_4'] = 'Etapa 4';
$lang['config_install_shopify_app'] = 'Instalar la aplicación Shopify';
$lang['config_connect_billing'] = 'Conectar facturación';
$lang['config_choose_sync_options'] = 'Elija Opciones de sincronización';
$lang['config_ecommerce_sync_running'] = 'La sincronización de comercio electrónico ahora se está ejecutando en segundo plano. Puede comprobar el estado en Store Config.';
$lang['config_show_total_on_fulfillment'] = 'Mostrar total en la hoja de cumplimiento';
$lang['config_connect_shopify_in_app_store'] = 'No estás conectado a Shopify. Puedes conectarte a Shopify en App Store';
$lang['config_override_signature_text'] = 'Anular el texto de la firma';

$lang['config_delivery_color_based_on'] = 'Color de entrega basado en';
$lang['config_delivery_color_based_on_status'] = 'Estado';
$lang['config_delivery_color_based_on_category'] = 'Categoría';


$lang['config_update_cost_price_on_transfer'] = 'Actualizar precio de costo en transferencia';



$lang['config_tip_preset_zero'] = 'Cantidad preestablecida de propina del 0%';



$lang['config_layaway_statement_message'] = 'Mensaje de declaración de layaway';


$lang['config_show_person_id_on_receipt'] = 'Mostrar identificación de persona al recibirlo';




$lang['config_import_ecommerce_orders_suspended'] = 'Importación de pedidos de comercio electrónico suspendidos';

$lang['config_show_images_on_receipt'] = 'Mostrar imágenes en el recibo';

$lang['config_disabled_fixed_discounts'] = 'Deshabilitar descuentos fijos completos en la interfaz de ventas';



$lang['config_always_put_last_added_item_on_top_of_cart'] = 'Siempre coloque el último artículo agregado en la parte superior del carrito';



$lang['config_show_giftcards_even_if_0_balance'] = 'Mostrar tarjetas de regalo incluso si el saldo es cero';

$lang['config_scale_5'] = 'Peso Código de barras integrado';

$lang['config_disable_modules'] = 'Deshabilitar módulos';

$lang['config_hide_description_on_suspended_sales'] = 'Ocultar descripción de artículo en ventas suspendidas';
$lang['config_override_symbol_non_taxable'] = 'Símbolo de anulación para no imponible';



$lang['config_hide_categories_sales_grid'] = 'Ocultar categorías en la cuadrícula de ventas';
$lang['config_hide_tags_sales_grid'] = 'Ocultar etiquetas en la cuadrícula de ventas';
$lang['config_hide_favorites_sales_grid'] = 'Ocultar favoritos en la cuadrícula de ventas';
$lang['config_hide_categories_receivings_grid'] = 'Ocultar categorías en la cuadrícula de recibos';
$lang['config_hide_tags_receivings_grid'] = 'Ocultar etiquetas en la cuadrícula de recibos';
$lang['config_hide_suppliers_receivings_grid'] = 'Ocultar proveedores en la cuadrícula de recibos';
$lang['config_hide_favorites_receivings_grid'] = 'Ocultar favoritos en la cuadrícula de recibos';
$lang['config_hide_suppliers_sales_grid'] = 'Ocultar proveedores en la cuadrícula de ventas';

$lang['config_offline_mode_sync_period'] = 'Tiempo de ciclo de sincronización del modo fuera de línea (hora)';



$lang['config_receipt_download_filename_prefix'] = 'Prefijo de nombre de archivo de descarga de recibo';
$lang['config_remove_employee_lastname_from_receipt'] = 'Eliminar el apellido del empleado del recibo';
$lang['config_override_symbol_taxes_summary_on_receipt'] = 'SÍMBOLO DE ANULACIÓN PARA MOSTRAR RESUMEN IMPONIBLE Y NO IMPONIBLE EN EL RECIBO';

$lang['config_show_images_on_receipt_width_percent'] = 'Ancho máximo de las imágenes del artículo en el recibo (porcentaje)';

$lang['config_link_to_receipt'] = 'Enlace al recibo';
$lang['config_sale_summary_info'] = 'Información de resumen de venta';
$lang['config_qr_code_format'] = 'Formato de código QR';
$lang['config_override_symbol_taxable_summary'] = 'Símbolo de anulación para resumen imponible al recibo';
$lang['config_override_symbol_non_taxable_summary'] = 'Símbolo de anulación para resumen no imponible al recibo';
$lang['config_allow_drag_drop_recv'] = 'Permitir reordenar en la pantalla de recepción';
$lang['config_allow_drag_drop_sale'] = 'Permitir reordenar en la pantalla de ofertas';
$lang['config_disable_signature_capture_on_terminal_for_phppos_credit_card_processing'] = 'Deshabilitar la captura de firmas para el procesamiento de tarjetas de crédito PHP POS';


$lang['config_capture_internal_notes_during_receiving'] = 'Capturar notas internas durante la recepción';


$lang['config_default_employee_for_deliveries'] = 'Empleado predeterminado para entregas';

$lang['config_disable_verification_for_qr_codes'] = 'Deshabilite la verificación de códigos QR (no requiere un número de teléfono o correo electrónico)';

$lang['config_disable_variation_popup_in_receivings'] = 'Deshabilitar la ventana emergente de variación en la recepción';

$lang['config_saudi_arabia_digital_receipt'] = 'Recibo digital de Arabia Saudita';

$lang['config_scale_6'] = 'Peso Código de barras integrado EAN 13';

$lang['config_hide_location_name_on_receipt'] = 'Ocultar el nombre de la ubicación en el recibo';
$lang['config_disable_discount_by_percentage'] = 'Deshabilitar descuentos completos por porcentaje en la interfaz de ventas';

$lang['Delivery ID'] = 'ID de entrega';
$lang['config_scale_7'] = 'Peso Códigos de barras integrados EAN 13 4 dígitos de precio';


$lang['config_use_main_image_as_default_image_in_e_commerce'] = 'Utilice la imagen principal como imagen predeterminada en el comercio electrónico';

$lang['config_disable_discounts_percentage_per_line_item'] = 'Deshabilitar porcentaje de descuentos por artículo de línea';
$lang['config_send_sms_via_whatsapp'] = 'Enviar SMS a través de Whatsapp';
$lang['config_additional_appointment_note'] = 'Nota de cita adicional';
$lang['config_keywords_help_text'] = 'Aquí, puede utilizar algunas palabras clave.';

$lang['config_number_of_decimals_displayed_on_sales_interface'] = 'Número de decimales que se muestran en la interfaz de ventas';

$lang['shopify_private_key'] = 'Clave privada de Shopify';
$lang['shopify_public_key'] = 'Clave pública de Shopify';
$lang['config_reconnect_to_shopify'] = 'Volver a conectarse a Shopify';

$lang['config_customer_allow_partial_match'] = 'Permitir coincidencia parcial para la búsqueda de clientes';
?>