<?php
//Store Configuration Information
$lang['config_info'] = '存储配置信息';
//Company Address
$lang['config_address'] = '公司地址';
//Company Phone
$lang['config_phone'] = '公司电话';
//Sale ID Prefix
$lang['config_prefix'] = '销售ID前缀';
//Fax
$lang['config_fax'] = '传真';
//Default Tax Rate %
$lang['config_default_tax_rate'] = '默认税率％';
//Company name is a required field
$lang['config_company_required'] = '公司名称是必填字段';
//Company phone is a required field
$lang['config_phone_required'] = '公司电话是必填字段';
//Sale ID prefix is a required field
$lang['config_sale_prefix_required'] = '销售ID前缀是必填字段';
//The default tax rate is a required field
$lang['config_default_tax_rate_required'] = '默认税率是必填字段';
//The default tax rate must be a number
$lang['config_default_tax_rate_number'] = '默认税率必须是一个数字';
//Company website is not a valid URL (http://...)
$lang['config_company_website_url'] = '公司网站不是有效的网址（http：// ...）';
//Failed to save configuration. Configuration changes are not allowed in demo mode or taxes weren't saved correctly
$lang['config_saved_unsuccessfully'] = '无法保存配置。在演示模式下不允许进行配置更改，或者不能正确保存税金';
//Return policy is a required field
$lang['config_return_policy_required'] = '退货政策是必填字段';
//Print receipt after sale
$lang['config_print_after_sale'] = '出售后打印收据';
//Automatically Email receipt
$lang['config_automatically_email_receipt'] = '自动电子邮件收据';
//Include tax on barcodes
$lang['config_barcode_price_include_tax'] = '在条形码上纳税';
//Disable confirmation for complete sale
$lang['disable_confirmation_sale'] = '禁止确认完成销售';
//Currency Symbol
$lang['config_currency_symbol'] = '货币符号';
//Backup Database
$lang['config_backup_database'] = '备份数据库';
//Restore Database
$lang['config_restore_database'] = '恢复数据库';
//Number Of Items Per Page
$lang['config_number_of_items_per_page'] = '每页数量';
//Date Format
$lang['config_date_format'] = '日期格式';
//Time Format
$lang['config_time_format'] = '时间格式';
//Optimize Database
//Optimized Database Successfully
$lang['config_database_optimize_successfully'] = '成功优化数据库';
//Payment Types
$lang['config_payment_types'] = '付款类型';
//select .sql file
$lang['select_sql_file'] = '选择.sql文件';
//This allows you to restore your database
$lang['restore_heading'] = '这样可以恢复数据库';
//select .sql file from your computer
$lang['type_file'] = '从您的计算机中选择.sql文件';
//restore
$lang['restore'] = '恢复';
//No sql file is selected
$lang['required_sql_file'] = '没有选择sql文件';
//DataBase is restored successfully
$lang['restore_db_success'] = 'DataBase已成功恢复';
//Are you sure of restoring the database?
$lang['db_first_alert'] = '确定还原数据库吗？';
//Present data will be lost , continue?
$lang['db_second_alert'] = '现在的数据会丢失，继续吗？';
//Password incorrect
$lang['password_error'] = '密码错误';
//Password field cannot be blank
$lang['password_required'] = '密码字段不能为空';
//Restore Database
$lang['restore_database_title'] = '恢复数据库';
//Environment
$lang['config_environment'] = '环境';
//Sandbox
$lang['config_sandbox'] = '砂箱';
//Production
$lang['config_production'] = '生产';
//Default Payment Type
$lang['config_default_payment_type'] = '默认付款类型';
//Only recommend if you have more than 10,000 items or customers
$lang['config_speed_up_note'] = '只有当您拥有超过10,000个物品或客户时才推荐';
//Hide Signature
$lang['config_hide_signature'] = '隐藏签名';
//Round to nearest .05 on receipt
$lang['config_round_cash_on_sales'] = '收到时最接近.05';
//Enable PDF receipts
$lang['config_enable_pdf_receipts'] = '启用PDF收据';
//Customers Store Accounts
$lang['config_customers_store_accounts'] = '客户商店帐户';
//Change sale date when suspending sale
$lang['config_change_sale_date_when_suspending'] = '更改销售日期，暂停销售';
//Change sale date when completing suspended sale
$lang['config_change_sale_date_when_completing_suspended_sale'] = '更改销售日期，完成暂停销售';
//Price tiers
$lang['config_price_tiers'] = '价格层';
//Add tier
$lang['config_add_tier'] = '添加层';
//Show receipt after suspending sale
$lang['config_show_receipt_after_suspending_sale'] = '暂停销售后显示收据';
//Backup Overview
$lang['config_backup_overview'] = '备份概述';
//Backing up your data is very important, but can be troublesome with large amount of data. If you have lots of images, items, and sales this can increase the size of your database.
$lang['config_backup_overview_desc'] = '备份数据非常重要，但是对于大量的数据可能会麻烦。如果您有大量图像，项目和销售量，则可以增加数据库的大小。';
//We offer many options for backup to help you decide how to proceed
$lang['config_backup_options'] = '我们提供许多备份选项，以帮助您决定如何继续';
//Clicking "Backup database". This will attempt to download your whole database to a file. If you get a blank screen or can't download the file, try one of the other options.
$lang['config_backup_simple_option'] = '点击“备份数据库”。这将尝试将整个数据库下载到一个文件。如果您获得空白屏幕或无法下载该文件，请尝试其他选项之一。';
//PHPMyAdmin is a popular tool for managing your databases. If you are using the download version with installer, it can be accessed by going to
$lang['config_backup_phpmyadmin_1'] = 'PHPMyAdmin是用于管理数据库的流行工具。如果您正在使用具有安装程序的下载版本，可以通过转到';
//Your username is root and password is what you used during initial installation of PHP POS. Once logged in select your database from the panel on the left. Then select export and then submit the form.
$lang['config_backup_phpmyadmin_2'] = '您的用户名是root，密码是您在初始安装PHP POS时使用的。登录后，从左侧的面板中选择您的数据库。然后选择导出，然后提交表单。';
//If you have installed on your own server that has a control panel such as cpanel, look for the backup module which will often let you download backups of your database.
$lang['config_backup_control_panel'] = '如果您已经安装在具有诸如cpanel这样的控制面板的自己的服务器上，请寻找备份模块，这将常常让您下载数据库的备份。';
//If you have access to the shell and mysqldump on your server, you can try to execute it by clicking the below link. Otherwise  you will need to try other options.
$lang['config_backup_mysqldump'] = '如果您可以访问服务器上的shell和mysqldump，可以单击下面的链接尝试执行它。否则，您将需要尝试其他选项。';
//mysqldump backup has failed. This could be due to a server restriction or the command might not be available. Please try another backup method
$lang['config_mysqldump_failed'] = 'mysqldump备份失败。这可能是由于服务器限制或命令可能不可用。请尝试另一种备份方法';
//Looking for other configuration options? Go to
$lang['config_looking_for_location_settings'] = '寻找其他配置选项？去';
//Module
$lang['config_module'] = '模';
//Calculate Average Cost Price from Receivings
$lang['config_automatically_calculate_average_cost_price_from_receivings'] = '计算收到的平均成本价格';
//Averaging Method
$lang['config_averaging_method'] = '平均方法';
//Historical Average
$lang['config_historical_average'] = '历史平均水平';
//Moving Average
$lang['config_moving_average'] = '移动平均线';
//Hide Dashboard Statistics
$lang['config_hide_dashboard_statistics'] = '隐藏仪表板统计信息';
//Hide Store Account Payments In Reports
$lang['config_hide_store_account_payments_in_reports'] = '在报告中隐藏商店帐户付款';
//Item ID to Show on Sales Interface
$lang['config_id_to_show_on_sale_interface'] = '要在销售界面显示的物品编号';
//Auto Focus On Item Field When using Sales/Receivings Interfaces
$lang['config_auto_focus_on_item_after_sale_and_receiving'] = '使用销售/接收接口时自动对焦项目字段';
//Automatically Show Comments on Receipt
$lang['config_automatically_show_comments_on_receipt'] = '自动显示收据的评论';
//Hide Recent Sales for Customer
$lang['config_hide_customer_recent_sales'] = '隐藏客户最近的销售';
//Spreadsheet Format
$lang['config_spreadsheet_format'] = '电子表格格式';
//CSV
$lang['config_csv'] = 'CSV';
//XLSX
$lang['config_xlsx'] = 'XLSX';
//Disable Giftcard Detection
$lang['config_disable_giftcard_detection'] = '禁用礼品卡检测';
//Disable giftcard subtraction when using giftcard during sale
$lang['config_disable_subtraction_of_giftcard_amount_from_sales'] = '销售时使用礼品卡时禁用礼品卡扣除';
//Always Show Item Grid
$lang['config_always_show_item_grid'] = '始终显示项目网格';
//Legacy Detailed Report Excel Export
$lang['config_legacy_detailed_report_export'] = '传统详细报告Excel导出';
//Print receipt after receiving
$lang['config_print_after_receiving'] = '收到后打印收据';
//Company Information
$lang['config_company_info'] = '公司信息';
//Suspended Sales/Layaways
$lang['config_suspended_sales_layaways_info'] = '暂停销售/折价';
//Application Settings
$lang['config_application_settings_info'] = '应用程序设置';
//Hide barcode on receipts
$lang['config_hide_barcode_on_sales_and_recv_receipt'] = '收据上隐藏条形码';
//Round tier Prices to 2 decimals
$lang['config_round_tier_prices_to_2_decimals'] = '圆形价格为2位小数';
//Group all taxes on receipt
$lang['config_group_all_taxes_on_receipt'] = '收取所有税款';
//Receipt text size
$lang['config_receipt_text_size'] = '收据文字大小';
//Small
$lang['config_small'] = '小';
//Medium
$lang['config_medium'] = '中';
//Large
$lang['config_large'] = '大';
//Extra large
$lang['config_extra_large'] = '超大';
//Select sales person during sale
$lang['config_select_sales_person_during_sale'] = '在销售期间选择销售人员';
//Default sales person
$lang['config_default_sales_person'] = '默认销售人员';
//Require customer for sale
$lang['config_require_customer_for_sale'] = '要求客户出售';
//Hide store account payments from report totals
$lang['config_hide_store_account_payments_from_report_totals'] = '从报告总额中隐藏商店帐户付款';
//Disable sale notifications
$lang['config_disable_sale_notifications'] = '禁用销售通知';
//ID to show on barcode
$lang['config_id_to_show_on_barcode'] = 'ID显示条形码';
//Currency Denominations
$lang['config_currency_denoms'] = '货币面值';
//Currency Value
$lang['config_currency_value'] = '货币价值';
//Add currency denomination
$lang['config_add_currency_denom'] = '添加货币面额';
//Enable Time Clock
$lang['config_enable_timeclock'] = '启用时钟';
//Change Sale Date For New Sale
$lang['config_change_sale_date_for_new_sale'] = '更改新销售的销售日期';
//Don't average, use current received price
$lang['config_dont_average_use_current_recv_price'] = '不平均，使用当前收到的价格';
//Number of recent sales by customer to show
$lang['config_number_of_recent_sales'] = '近期销售量由客户展示';
//Hide suspended Receivings in reports
$lang['config_hide_suspended_recv_in_reports'] = '隐藏报告中的暂停收款';
//Calculate Gift Card Profit When
$lang['config_calculate_profit_for_giftcard_when'] = '计算礼品卡利润时间';
//Selling Gift Card
$lang['config_selling_giftcard'] = '销售礼品卡';
//Redeeming Gift Card
$lang['config_redeeming_giftcard'] = '兑换礼品卡';
//Remove customer contact info from receipt
$lang['config_remove_customer_contact_info_from_receipt'] = '从收据中删除客户联系信息';
//Speed up search queries?
$lang['config_speed_up_search_queries'] = '加快搜索查询？';
//Redirect to sale or receiving screen after printing receipt
$lang['config_redirect_to_sale_or_recv_screen_after_printing_receipt'] = '打印收据后重定向到销售或接收屏幕';
//Enable sounds for status messages
$lang['config_enable_sounds'] = '启用状态消息的声音';
//Charge tax on receivings
$lang['config_charge_tax_on_recv'] = '收费征收税';
//Report Sort Order
$lang['config_report_sort_order'] = '报告排序顺序';
//Oldest first
$lang['config_asc'] = '最旧的';
//Newest first
$lang['config_desc'] = '最新的第一';
//Do NOT group items that are the same
$lang['config_do_not_group_same_items'] = '不要分组相同的项目';
//Show item id on receipt
$lang['config_show_item_id_on_receipt'] = '收到时显示项目ID';
//Show Language Switcher
$lang['config_show_language_switcher'] = '显示语言切换器';
//Do not allow out of stock items to be sold
$lang['config_do_not_allow_out_of_stock_items_to_be_sold'] = '不允许出售物品';
//Number of items per page in grid
$lang['config_number_of_items_in_grid'] = '网格中每页的数量';
//Edit item price if 0 after adding to sale
$lang['config_edit_item_price_if_zero_after_adding'] = '修改商品价格如果0加入销售后';
//Override receipt title
$lang['config_override_receipt_title'] = '覆盖收据标题';
//Automatically print duplicate receipt for credit card transactions
$lang['config_automatically_print_duplicate_receipt_for_cc_transactions'] = '自动打印信用卡交易的重复收据';
//Default type for Grid
$lang['config_default_type_for_grid'] = '网格的默认类型';
//Billing is managed through  <a target="_blank" href="http://paypal.com">Paypal</a>. You can cancel your subscription by clicking <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=BNTRX72M8UZ2E">here</a>. You can <a href="http://phppointofsale.com/update_billing.php" target="_blank">update billing here</a>.
$lang['config_billing_is_managed_through_paypal'] = '帐单通过<a target="_blank" href="http://paypal.com"> Paypal </a>进行管理。您可以点击<a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=BNTRX72M8UZ2E">此处</a>取消订阅。您可以<a href="http://phppointofsale.com/update_billing.php" target="_blank">在此更新结算</a>。';
//Language cannot be saved at application level. However the default admin employee can change the language using the selector in the header of the program
$lang['config_cannot_change_language'] = '语言无法在应用程序级保存。但是，默认管理员员可以使用程序标题中的选择器来更改语言';
//Disable sale quick complete
$lang['disable_quick_complete_sale'] = '禁止销售快速完成';
//Enable fast user switching (password not required)
$lang['config_fast_user_switching'] = '启用快速用户切换（密码不需要）';
//Require employee login before each sale
$lang['config_require_employee_login_before_each_sale'] = '在每次销售之前要求员工登录';
//Reset location when switching employee
$lang['config_reset_location_when_switching_employee'] = '切换员工时重置位置';
//Number of decimals
$lang['config_number_of_decimals'] = '小数位数';
//Let system decide (Recommended)
$lang['config_let_system_decide'] = '让系统决定（推荐）';
//Thousands Separator
$lang['config_thousands_separator'] = '数千分离器';
//Enhanced Search Method
$lang['config_enhanced_search_method'] = '增强搜索方法';
//Hide store account balance on receipt
$lang['config_hide_store_account_balance_on_receipt'] = '收据时隐藏商店帐户余额';
//Decimal Point
$lang['config_decimal_point'] = '小数点';
//Hide out of stock items in grid
$lang['config_hide_out_of_stock_grid'] = '隐藏网格中的库存物品';
//Highlight low inventory items in items module
$lang['config_highlight_low_inventory_items_in_items_module'] = '突出显示项目模块中的低库存项目';
//Sort
$lang['config_sort'] = '分类';
//Enable Customer Loyalty system
$lang['config_enable_customer_loyalty_system'] = '启用客户忠诚度系统';
//Spend amount to point ratio
$lang['config_spend_to_point_ratio'] = '花费点数比';
//Point Value
$lang['config_point_value'] = '点值';
//Hide Points On Receipt
$lang['config_hide_points_on_receipt'] = '隐藏接收点';
//Show Clock in Header
$lang['config_show_clock_on_header'] = '在标题中显示时钟';
//This is visible only on wide screens
$lang['config_show_clock_on_header_help_text'] = '这仅在宽屏幕上可见';
//Enter the amount to spend
$lang['config_loyalty_explained_spend_amount'] = '输入支出金额';
//Enter points to be earned
$lang['config_loyalty_explained_points_to_earn'] = '输入要获得的积分';
//Simple
$lang['config_simple'] = '简单';
//Advanced
$lang['config_advanced'] = '高级';
//Loyalty Program Option
$lang['config_loyalty_option'] = '忠诚计划选项';
//Number of sales for discount
$lang['config_number_of_sales_for_discount'] = '打折销售数量';
//Discount percent earned when reaching sales
$lang['config_discount_percent_earned'] = '达到销售时的折扣百分比';
//Hide sales to discount on receipt
$lang['hide_sales_to_discount_on_receipt'] = '隐藏销售收据打折';
//Hide price on barcodes
$lang['config_hide_price_on_barcodes'] = '隐藏条形码的价格';
//Always Use Global Average Cost Price For A Sale Item's Cost Price. (DO NOT check unless you know what it means)
$lang['config_always_use_average_cost_method'] = '始终使用全球平均成本价格出售物品的成本价格。 （除非你知道这是什么意思）';
//Sales NOT saved
$lang['config_test_mode_help'] = '销售未保存';
//Require customer for suspended sale
$lang['config_require_customer_for_suspended_sale'] = '要求客户暂停销售';
//Default New Items as service items
$lang['config_default_new_items_to_service'] = '默认新项目作为服务项目';
//Prompt for CCV when swiping credit card
$lang['config_prompt_for_ccv_swipe'] = '刷卡时提示CCV';
//Disable store account when over credit limit
$lang['config_disable_store_account_when_over_credit_limit'] = '超过信用限额时禁用商店帐户';
//Mailing Labels Format
$lang['config_mailing_labels_type'] = '邮寄标签格式';
//Session expiration
$lang['config_phppos_session_expiration'] = '会话过期';
//Hours
$lang['config_hours'] = '小时';
//Never
$lang['config_never'] = '决不';
//On Browser Close
$lang['config_on_browser_close'] = '浏览器关闭';
//Do NOT allow items to be sold below cost price
$lang['config_do_not_allow_below_cost'] = '不要让物品以低于成本价出售';
//Store Account Statement Message
$lang['config_store_account_statement_message'] = '存储帐户声明消息';
//Enable Mark Up Calculator
$lang['config_enable_markup_calculator'] = '启用标记计算器';
//Enable quick edit on manage pages
$lang['config_enable_quick_edit'] = '在管理页面上启用快速编辑';
//Show original price on receipt if marked down
$lang['config_show_orig_price_if_marked_down_on_receipt'] = '在收货时显示原价';
//Cancel Account
$lang['config_cancel_account'] = '取消帐户';
//You can update and cancel your billing information by clicking the buttons below:
$lang['config_update_billing'] = '您可以点击下面的按钮来更新和取消您的结算信息：';
//Include child categories when searching or reporting
$lang['config_include_child_categories_when_searching_or_reporting'] = '在搜索或报告时包括子类别';
//Confirm error messages using modal dialogs
$lang['config_confirm_error_messages_modal'] = '使用模态对话框确认错误消息';
//Remove commission from profit in reports
$lang['config_remove_commission_from_profit_in_reports'] = '从报告中的利润中删除佣金';
//Remove points redemption from profit
$lang['config_remove_points_from_profit'] = '清除积分赎回利润';
//Capture signature for all sales
$lang['config_capture_sig_for_all_payments'] = '捕获所有销售的签名';
//Suppliers Store Accounts
$lang['config_suppliers_store_accounts'] = '供应商商店帐户';
//Currency Symbol Location
$lang['config_currency_symbol_location'] = '货币符号位置';
//Before Number
$lang['config_before_number'] = '数字前';
//After Number
$lang['config_after_number'] = '数字后';
//Hide Description on Receipt
$lang['config_hide_desc_on_receipt'] = '隐藏收据说明';
//Default Percent Off
$lang['config_default_percent_off'] = '默认百分比关';
//Default Cost Plus Percent
$lang['config_default_cost_plus_percent'] = '默认成本加百分比';
//Default Tier Percent Type for excel import
$lang['config_default_tier_percent_type_for_excel_import'] = 'Excel导入的默认级别百分比类型';
//Override Tier Name on Receipt
$lang['config_override_tier_name'] = '覆盖收据上的层名称';
//Loyalty points earned not including tax
$lang['config_loyalty_points_without_tax'] = '积分不包括税金';
//Lock prices when unsuspending sale even if they belong to a tier
$lang['config_lock_prices_suspended_sales'] = '即使属于一个层次，也可以锁定销售时的价格';
//Remove Customer Name From Receipt
$lang['config_remove_customer_name_from_receipt'] = '从收货中删除客户名称';
//UPC-12 4 price digits
$lang['config_scale_1'] = 'UPC-12 4价格数字';
//UPC-12 5 Price Digits
$lang['config_scale_2'] = 'UPC-12 5价格数位';
//EAN-13 5 price digits
$lang['config_scale_3'] = 'EAN-13 5个价格数字';
//EAN-13 6 price digits
$lang['config_scale_4'] = 'EAN-13 6价格数字';
//Scale Barcode Format
$lang['config_scale_format'] = '缩放条形码格式';
//Enable Scale
$lang['config_enable_scale'] = '启用缩放';
//Woocommerce Settings
$lang['config_woocommerce_settings_info'] = '电子商务设置';
//Store Location
$lang['config_store_location'] = '商店位置';
//Woocommerce API Secret
$lang['config_woo_api_secret'] = 'Woocommerce API秘密';
//Woocommerce API Url
$lang['config_woo_api_url'] = 'Woocommerce API网址';
//Woocommerce API Key
$lang['config_woo_api_key'] = 'Woocommerce API密钥';
//Ecommerce Platform
$lang['config_ecommerce_settings_info'] = '电子商务平台';
//Select Platform
$lang['config_ecommerce_platform'] = '选择平台';
//Magento Settings
$lang['config_magento_settings_info'] = 'Magento设置';
//Scale Price Divide By
$lang['config_scale_divide_by'] = '规模价格分割';
//Do not force HTTP when needed for EMV Credit Card Processing
//Log out automatically when clocking out
$lang['config_logout_on_clock_out'] = '时钟输出时自动注销';
//Override Layaway Name
$lang['config_user_configured_layaway_name'] = '覆盖Layaway名称';
//Virtual Keyboard (On/Off)
//Use Tax Values at ALL locations
$lang['config_use_tax_value_at_all_locations'] = '在所有地点使用税值';
//Enable EBT payments
$lang['config_enable_ebt_payments'] = '启用EBT付款';
//Item ID Auto Increment Starting Value
$lang['config_item_id_auto_increment'] = '物品ID自动增量起始值';
//There was an error changing auto_increment for item_id
$lang['config_change_auto_increment_item_id_unsuccessful'] = '更改item_id的auto_increment时出错';
//Item Kit ID Auto Increment Starting Value
$lang['config_item_kit_id_auto_increment'] = '项目编号ID自动增量起始值';
//Sale ID Auto Increment Starting Value
$lang['config_sale_id_auto_increment'] = '销售ID自动增量起始值';
//Receiving ID Auto Increment Starting Value
$lang['config_receiving_id_auto_increment'] = '接收ID自动增量起始值';
//There was an error changing auto_increment for  Iitem_kit_id
$lang['config_change_auto_increment_item_kit_id'] = '更改Iitem_kit_id的auto_increment时发生错误';
//There was an error changing auto_increment for sale_id
$lang['config_change_auto_increment_sale_id'] = '更改sales_id的auto_increment时出错';
//There was an error changing auto_increment for receiving_id
$lang['config_change_auto_increment_receiving_id'] = '更改receive_id的auto_increment时发生错误';
//You can only increase Auto Increment values. Updating them will not affect IDs for items, item kits, sales or receivings that already exist.
$lang['config_auto_increment_note'] = '您只能增加自动增量值。更新它们不会影响已存在的项目，项目套件，销售或收货的ID。';
//Online Price Tier
$lang['config_online_price_tier'] = '在线价格层';
//Email Settings
$lang['config_email_settings_info'] = '电子邮件设置';
//Last Sync Date
$lang['config_last_sync_date'] = '最后同步日期';
//Sync
$lang['config_sync'] = '同步';
//SMTP Encryption
$lang['config_smtp_crypto'] = 'SMTP加密';
//Mail Sending Protocol
$lang['config_email_protocol'] = '邮件发送协议';
//SMTP Server Address
$lang['config_smtp_host'] = 'SMTP服务器地址';
//Email Address
$lang['config_smtp_user'] = '电子邮件地址';
//Email Password
$lang['config_smtp_pass'] = '电子邮件密码';
//SMTP Port
$lang['config_smtp_port'] = 'SMTP端口';
//Character set
$lang['config_email_charset'] = '字符集';
//Newline character
$lang['config_email_newline'] = '换行字符';
//CRLF
$lang['config_email_crlf'] = 'CRLF';
//SMTP Timeout
$lang['config_smtp_timeout'] = 'SMTP超时';
//Send Test Email
$lang['config_send_test_email'] = '发送测试电子邮件';
//Please enter email address to send test email to
$lang['config_please_enter_email_to_send_test_to'] = '请输入发送测试邮件的电子邮件地址';
//Email has been sent successfully
$lang['config_email_succesfully_sent'] = '电子邮件已经发送成功';
//Taxes
$lang['config_taxes_info'] = '税';
//Currency
$lang['config_currency_info'] = '货币';
//Receipt
$lang['config_receipt_info'] = '收据';
//Barcodes
$lang['config_barcodes_info'] = '条形码';
//Customer Loyalty
$lang['config_customer_loyalty_info'] = '客户忠诚度';
//Price Tiers
$lang['config_price_tiers_info'] = '价格层';
//ID Numbers
$lang['config_auto_increment_ids_info'] = '身份证号码';
//Items
$lang['config_items_info'] = '项目';
//Employee
$lang['config_employee_info'] = '雇员';
//Store Accounts
$lang['config_store_accounts_info'] = '商店帐号';
//Sales
$lang['config_sales_info'] = '销售';
//Payment Types
$lang['config_payment_types_info'] = '付款类型';
//Profit Calculation
$lang['config_profit_info'] = '利润计算';
//View Dashboard Statistics
$lang['reports_view_dashboard_stats'] = '查看仪表板统计信息';
//email settings
$lang['config_keyword_email'] = '电子邮件设置';
//company
$lang['config_keyword_company'] = '公司';
//taxes
$lang['config_keyword_taxes'] = '税';
//currency
$lang['config_keyword_currency'] = '货币';
//payment
$lang['config_keyword_payment'] = '付款';
//sales
$lang['config_keyword_sales'] = '销售';
//suspended layaways
$lang['config_keyword_suspended_layaways'] = '暂停摊铺';
//receipt
$lang['config_keyword_receipt'] = '收据';
//profit
$lang['config_keyword_profit'] = '利润';
//barcodes
$lang['config_keyword_barcodes'] = '条形码';
//customer loyalty
$lang['config_keyword_customer_loyalty'] = '客户忠诚度';
//price tiers
$lang['config_keyword_price_tiers'] = '价格层';
//starting auto increment id numbers database
$lang['config_keyword_auto_increment'] = '启动自动递增ID号数据库';
//items
$lang['config_keyword_items'] = '项目';
//employees
$lang['config_keyword_employees'] = '雇员';
//store accounts
$lang['config_keyword_store_accounts'] = '商店帐号';
//application settings
$lang['config_keyword_application_settings'] = '应用设置';
//ecommerce platform
$lang['config_keyword_ecommerce'] = '电子商务平台';
//woocommerce settings ecommerce
$lang['config_keyword_woocommerce'] = '电子商务设置';
//Billing Information
$lang['config_billing_info'] = '账单信息';
//billing cancel update
$lang['config_keyword_billing'] = '帐单取消更新';
//WooCommerce Version
$lang['config_woo_version'] = 'WooCommerce版本';
//Sync item changes
$lang['sync_phppos_item_changes'] = '同步项目更改';
//Sync item changes
$lang['config_sync_phppos_item_changes'] = '同步项目更改';
//Import items into phppos
$lang['config_import_ecommerce_items_into_phppos'] = '将项导入phppos';
//Sync inventory changes
$lang['config_sync_inventory_changes'] = '同步库存更改';
//Export tags to ecommerce
$lang['config_export_phppos_tags_to_ecommerce'] = '将标签导出到电子商务';
//Export categories to ecommerce
$lang['config_export_phppos_categories_to_ecommerce'] = '将类别导出为电子商务';
//Export items to ecommerce
$lang['config_export_phppos_items_to_ecommerce'] = '将项目导出到电子商务';
//Ecommerce Sync Operations
$lang['config_ecommerce_cron_sync_operations'] = '电子商务同步操作';
//Sync Progress
$lang['config_ecommerce_progress'] = '同步进度';
//Are you sure you want to cancel the sync?
$lang['confirmation_woocommerce_cron_cancel'] = '您确定要取消同步吗？';
//Require https for program
$lang['config_force_https'] = '需要https的程序';
//Price Rules
$lang['config_keyword_price_rules'] = '价格规则';
//Disable Price Rules dialog
$lang['config_disable_price_rules_dialog'] = '禁用价格规则对话框';
//Price Rules
$lang['config_price_rules_info'] = '价格规则';
//Prompt to use points when available
$lang['config_prompt_to_use_points'] = '提供时使用点';
//Always print duplicate receipt for all transactions
$lang['config_always_print_duplicate_receipt_all'] = '始终打印所有交易的重复收据';
//Orders And Deliveries
$lang['config_orders_and_deliveries_info'] = '订单和交货';
//Delivery Methods
$lang['config_delivery_methods'] = '交货方式';
//Shipping Providers
$lang['config_shipping_providers'] = '航运公司';
//Expand
$lang['config_expand'] = '扩大';
//Add Delivery Rate
$lang['config_add_delivery_rate'] = '添加交货率';
//Add Shipping Provider
$lang['config_add_shipping_provider'] = '添加货运提供商';
//Delivery Rates
$lang['config_delivery_rates'] = '交货率';
//Delivery Fee
$lang['config_delivery_fee'] = '快递费';
//orders delivery deliveries
$lang['config_keyword_orders_deliveries'] = '订单交货';
//Delivery Fee Tax
$lang['config_delivery_fee_tax'] = '运送费税';
//Add Rate
$lang['config_add_rate'] = '加价';
//Delivery Time In Days
$lang['config_delivery_time'] = '交货时间在天';
//Delivery Rate
$lang['config_delivery_rate'] = '交货率';
//Rate Name
$lang['config_rate_name'] = '价格名称';
//Rate Fee
$lang['config_rate_fee'] = '价格费';
//Rate Tax
$lang['config_rate_tax'] = '价格税';
//Tax Groups
$lang['config_tax_classes'] = '税组';
//Add Tax Group
$lang['config_add_tax_class'] = '添加税组';
//Wide Printer Receipt Format
$lang['config_wide_printer_receipt_format'] = '宽打印机收据格式';
//Default Cost Plus Fixed Amount
$lang['config_default_cost_plus_fixed_amount'] = '默认成本加固定金额';
//Default Tier Fixed Amount for Excel Import
$lang['config_default_tier_fixed_type_for_excel_import'] = 'Excel导入的默认层次固定金额';
//Default Reorder Level When Creating Items
$lang['config_default_reorder_level_when_creating_items'] = '创建项目时的默认重新排序级别';
//Remove customer company name from receipt
$lang['config_remove_customer_company_from_receipt'] = '从收据中删除客户公司名称';
//Import categories into phppos
$lang['config_import_ecommerce_categories_into_phppos'] = '将类别导入phppos';
//Imports tags into phppos
$lang['config_import_ecommerce_tags_into_phppos'] = '将标签导入phppos';
//Shipping Zones
$lang['config_shipping_zones'] = '运输区';
//Add Shipping Zone
$lang['config_add_shipping_zone'] = '添加运送区域';
//No Results
$lang['config_no_results'] = '没有结果';
//Type in a zipcode
$lang['config_zip_search_term'] = '输入邮政编码';
//Searching...
$lang['config_searching'] = '搜索...';
//Tax Group
$lang['config_tax_class'] = '税务组';
//Zone
$lang['config_zone'] = '区';
//Zip Codes
$lang['config_zip_codes'] = '邮政编码';
//Add Zip Code
$lang['config_add_zip_code'] = '添加邮政编码';
//E-Commerce Syncing Logs
$lang['config_ecom_sync_logs'] = '电子商务同步日志';
//Currency Code
$lang['config_currency_code'] = '货币代码';
//Add Currency Exchange Rate
$lang['config_add_currency_exchange_rate'] = '添加货币汇率';
//Exchange Rates
$lang['config_currency_exchange_rates'] = '汇率';
//Exchange Rate
$lang['config_exchange_rate'] = '汇率';
//Item Lookup Order
$lang['config_item_lookup_order'] = '物品查找单';
//Item Id
$lang['config_item_id'] = '物品编号';
//Reset E-Commerce
$lang['config_reset_ecommerce'] = '重置电子商务';
//Are you sure you want to reset e-commerce? This will only reset php point of sale so items are no longer linked
$lang['config_confirm_reset_ecom'] = '你确定要重置电子商务吗？这只会重置php销售点，所以项目不再链接';
//You have reset E-Commerce successfully
$lang['config_reset_ecom_successfully'] = '您已成功重置电子商务';
//Number of Decimals for Quantity On Receipt
$lang['config_number_of_decimals_for_quantity_on_receipt'] = '收货数量的小数位数';
//Enable WIC
$lang['config_enable_wic'] = '启用WIC';
//Store Opening Time
$lang['config_store_opening_time'] = '开店时间';
//Store Closing Time
$lang['config_store_closing_time'] = '商店关闭时间';
//Limit Manual Price Adjustments And Discounts
$lang['config_limit_manual_price_adj'] = '限制手动价格调整和折扣';
$lang['config_always_minimize_menu'] = '始终最小化左侧栏菜单';

$lang['config_emailed_receipt_subject'] = '电子邮件收据主题';



$lang['config_do_not_tax_service_items_for_deliveries'] = '不要为交货税服务项目';


$lang['config_do_not_show_closing'] = '关闭注册时不要显示预期关闭金额';

$lang['config_paypal_me'] = 'PayPal.me用户名';


$lang['config_show_barcode_company_name'] = '在条形码上显示公司名称';
$lang['config_import_ecommerce_attributes_into_phppos'] = '将属性导入phppos';
$lang['config_export_phppos_attributes_to_ecommerce'] = '将属性导出到电子商务';

$lang['config_sku_sync_field'] = 'SKU字段进行同步';



$lang['config_overwrite_existing_items_on_excel_import'] = '覆盖Excel导入时的现有项目';

$lang['config_do_not_force_http'] = 'EMV信用卡处理需要时不要强制HTTP';
$lang['config_add_suspended_sale_type'] = '添加暂停的销售类型';
$lang['config_additional_suspend_types'] = '其他暂停销售类型';
$lang['config_remove_employee_from_receipt'] = '从收据中删除员工姓名';
$lang['config_import_ecommerce_orders_into_phppos'] = '将订单导入到phppos';
$lang['import_ecommerce_orders_into_phppos'] = '导入订单到php pos';
$lang['config_hide_name_on_barcodes'] = '隐藏条形码上的名称';


$lang['config_api_settings_info'] = 'API设置';
$lang['config_keyword_api'] = 'API';
$lang['config_api_keys'] = 'API密钥';
$lang['config_api_key_ending_in'] = 'API键结束';
$lang['config_permissions'] = '权限';
$lang['config_last_access'] = '上次访问';
$lang['config_add_key'] = '添加API密钥';
$lang['config_api_key'] = 'API密钥';
$lang['config_read'] = '读';
$lang['config_read_write'] = '读/写';
$lang['config_submit_api_key'] = '你确定要添加这个键吗？请确保您已将密钥复制到安全的位置，因为它不会再显示。';
$lang['config_write'] = '写';
$lang['config_api_key_confirm_delete'] = '你确定要删除这个API密钥吗？';
$lang['config_key_copied_to_clipboard'] = '键复制到剪贴板';

$lang['config_new_items_are_ecommerce_by_default'] = '新项目默认为电子商务';


$lang['config_new_items_are_ecommerce_by_default'] = '新项目默认为电子商务';

$lang['config_hide_description_on_sales_and_recv'] = '隐藏销售和接收界面的描述';





$lang['config_hide_item_descriptions_in_reports'] = '在报告中隐藏项目描述';





$lang['config_do_not_allow_item_with_variations_to_be_sold_without_selecting_variation'] = '不要选择变化的情况下销售变体商品';



$lang['config_verify_age_for_products'] = '验证产品的年龄';
$lang['config_default_age_to_verify'] = '要验证的默认年龄';




$lang['config_remind_customer_facing_display'] = '提醒员工打开面向客户的展示';

$lang['config_import_tax_classes_into_phppos'] = '将税收类导入phppos';
$lang['config_export_tax_classes_into_phppos'] = '将税收类出口到电子商务';
$lang['config_import_shipping_classes_into_phppos'] = '将运输类导入到phppos';
$lang['config_disable_confirm_recv'] = '禁用确认完成接收';
$lang['config_minimum_points_to_redeem'] = '兑换的最低点数';
$lang['config_default_days_to_expire_when_creating_items'] = '创建项目时的默认日期将过期';


$lang['config_quickbooks_settings'] = 'Quickbooks设置';
$lang['config_qb_sync_operations'] = 'Quickbooks同步操作';
$lang['config_import_quickbooks_items_into_phppos'] = '将项目导入到phppos';
$lang['config_export_phppos_items_to_quickbooks'] = '将项目导出到quickbooks';
$lang['config_import_customers_into_phppos'] = '将客户导入到phppos';
$lang['config_import_suppliers_into_phppos'] = '将供应商导入phppos';
$lang['config_import_employees_into_phppos'] = '将员工导入phppos';
$lang['config_export_employees_to_quickbooks'] = '将员工导出到快速手册';
$lang['config_export_sales_to_quickbooks'] = '将销售导出至快速预览';
$lang['config_export_receivings_to_quickbooks'] = '将接收导出到快速手册';
$lang['config_export_customers_to_quickbooks'] = '将客户导出到快速书本';
$lang['config_export_suppliers_to_quickbooks'] = '将供应商出口到快速书籍';
$lang['config_connect_to_qb_online'] = '在线连接到快速书籍';
$lang['config_refresh_tokens'] = '刷新令牌';
$lang['config_reconnect_quickbooks'] = '在线重新连接到快速书籍';
$lang['config_reset_quickbooks'] = '重置Quickbooks';
$lang['config_qb_sync_logs'] = 'Quickbooks同步日志';
$lang['config_quickbooks_progress'] = 'Quickbooks同步进度';
$lang['config_last_qb_sync_date'] = '上次同步日期';
$lang['config_confirmation_qb_cron_cancel'] = '你确定要取消快速同步吗？';
$lang['config_confirmation_qb_cron'] = '你确定要同步快速书吗？';
$lang['config_confirm_reset_qb'] = '你确定要重置快捷书吗？这将取消您与快速书的链接。';
$lang['config_reset_qb_successfully'] = '您已成功重置快速手册';
$lang['config_export_phppos_categories_to_quickbooks'] = '将类别从phppos导出到quickbooks';
$lang['config_create_payment_methods'] = '在QB中创建付款方式';


$lang['config_allow_scan_of_customer_into_item_field'] = '允许将客户扫描到项目字段中';
$lang['config_cash_alert_high'] = '现金超过时发出警报';
$lang['config_cash_alert_low'] = '现金低于预算时发出警报';


$lang['config_sync_inventory_changes_qb'] = '同步库存变化';

$lang['config_sort_receipt_column'] = '对收据列进行排序';





$lang['config_show_tax_per_item_on_receipt'] = '在收货时显示每件商品的税';





$lang['config_enable_timeclock_pto'] = '启用时钟付费时间';


$lang['config_enable_timeclock_pto'] = '启用时钟付费时间';

$lang['config_show_item_id_on_recv_receipt'] = '在收到时显示项目ID';





$lang['config_import_all_past_orders_for_woo_commerce'] = '导入所有过去的WooCommerce订单';




$lang['config_enable_margin_calculator'] = '启用保证金计算器';










$lang['config_hide_barcode_on_barcode_labels'] = '隐藏标签上的条形码';



$lang['config_do_not_delete_saved_card_after_failure'] = '失败后请勿删除已保存的卡';





$lang['config_capture_internal_notes_during_sale'] = '在销售期间捕获内部票据';





$lang['config_hide_prices_on_fill_sheet'] = '在履行表上隐藏价格';



$lang['$platform=$this->Appconfig->get("ecommerce_platform");'] = '如果（$平台== “woocommerce”）';
$lang['config_default_revenue_account_for_item'] = '项目的默认收入帐户';
$lang['config_default_asset_account_for_item'] = '项目的默认资产帐户';
$lang['config_default_expense_account_for_item'] = '项目的默认费用帐户';
$lang['config_export_expenses_to_quickbooks'] = '出口费用到quickbooks';
$lang['config_chart_of_accounts'] = 'Quickbooks会计科目表';
$lang['config_keyword_chart_of_account'] = 'Quickbooks会计科目表';
$lang['config_default_refund_cash_account_name'] = '退款现金账户';
$lang['config_default_refund_credit_account_name'] = '退款信用账户';
$lang['config_default_refund_debit_card_account_name'] = '退款借记卡帐户';
$lang['config_default_refund_credit_card_account_name'] = '退款信用卡账户';
$lang['config_default_refund_check_account_name'] = '退款支票账户';
$lang['config_default_refund_deposit_account_name'] = '退款存款账户';
$lang['config_default_expense_account_name'] = '费用帐户';
$lang['config_default_expense_bank_credit_account_name'] = '费用银行/信用账户';
$lang['config_default_commission_credit_account_name'] = '佣金信用账户';
$lang['config_default_commission_debit_account_name'] = '佣金账户';
$lang['config_default_house_account_name'] = '存储帐户名称';
$lang['config_default_discount_item_name'] = '折扣商品';
$lang['config_default_house_item_name'] = '房屋物品名称';
$lang['config_default_store_account_item_name'] = '存储帐户项目';
$lang['config_default_house_account_category_name'] = '众议院账户类别';
$lang['config_default_customer_id'] = '默认客户名称';
$lang['config_revenue_id'] = '无法保存配置。缺少项目的默认收入帐户。';
$lang['config_asset_id'] = '无法保存配置。缺少项目的默认资产帐户';
$lang['config_export_confirm_box_text'] = '要将项目导出到quickbooks吗？';
$lang['config_discount_accounting_id'] = '出售时缺少折扣商品会计ID';
$lang['config_sync_for_discount_accounting_id'] = '请在创建折扣发票之前同步商品';


$lang['config_hide_desc_emailed_receipts'] = '隐藏电子邮件收据的说明';


$lang['config_default_tax'] = '默认税';
$lang['config_default_store_account_tax'] = '默认商店帐户税';
$lang['config_check_tax_name'] = '提供的税名不正确。请检查销售ID：';
$lang['config_qb_start_sync_date'] = '开始同步日期';
$lang['config_default_tax_id'] = '默认税';
$lang['config_markup_markdown'] = '标记/降价';
$lang['config_show_total_discount_on_receipt'] = '显示收据上的总折扣';
$lang['config_default_credit_limit'] = '默认信用额度';

$lang['config_hide_expire_date_on_barcodes'] = '隐藏条形码上的过期日期';

$lang['config_auto_capture_signature'] = '自动捕获签名';


$lang['config_pdf_receipt_message'] = '电子邮件正文中的PDF收据消息';

$lang['config_hide_merchant_id_from_receipt'] = '从收据中隐藏商家ID';


$lang['config_hide_all_prices_on_recv'] = '收到时隐藏所有价格';
$lang['config_do_not_delete_serial_number_when_selling'] = '销售时请勿删除序列号';
$lang['config_webhooks'] = '网络钩子';
$lang['config_new_customer_web_hook'] = '新客户Web挂钩URL';
$lang['config_new_sale_web_hook'] = '新销售Web Hook URL';
$lang['config_new_receiving_web_hook'] = '新接收Web挂钩';

$lang['config_strict_age_format_check'] = '年龄验证严格的日期格式检查';

$lang['config_flat_discounts_discount_tax'] = '平面折扣也折扣税';
$lang['config_show_item_kit_items_on_receipt'] = '在收据上显示项目工具包项目';
$lang['config_amount_of_cash_to_be_left_in_drawer_at_closing'] = '结算时留在抽屉的现金金额';
$lang['config_hide_tier_on_receipt'] = '隐藏收据上的层';
$lang['config_second_language'] = '收据上的第二语言';
$lang['config_disable_gift_cards_sold_from_loyalty'] = '禁用从赚取忠诚度中销售的礼品卡';
$lang['config_track_shipping_cost_for_receivings'] = '跟踪收货的运输成本';
$lang['config_enable_points_for_giftcard_payments'] = '启用礼品卡付款积分';




$lang['config_enable_tips'] = '启用提示';

$lang['config_support_regex'] = '支持正则表达式。示例：144。*匹配以144开头的任何内容';

$lang['config_not_all_processors_support_tips'] = '并非所有处理器都支持集成提示处理';
$lang['config_require_supplier_recv'] = '要求供应商接收';
$lang['config_default_payment_type_recv'] = '收款的默认付款方式';
$lang['config_taxjar_api_key'] = 'TaxJar API密钥（仅限美国）';

$lang['config_quick_variation_grid'] = '在项目网格上启用“快速选择变量”';


$lang['config_quick_variation_grid'] = '快速选择变体';


$lang['config_quick_variation_grid'] = '启用快速选择项目网格中的变体';



$lang['config_show_full_category_path'] = '搜索时显示完整类别路径';


$lang['config_do_not_upload_images_to_ecommerce'] = '不要将图像上传到电子商务';

$lang['config_woo_enable_html_desc'] = '为描述启用HTML';

$lang['config_use_rtl_barcode_library'] = '使用RTL条形码库';
$lang['config_default_new_customer_to_current_location'] = '默认新客户到当前位置';
$lang['config_week_start_day'] = '周开始日';
$lang['config_scan_and_set_sales'] = '在销售中添加项目后选择数量';
$lang['config_scan_and_set_recv'] = '选择在接收中添加项目后的数量';
$lang['config_edit_sale_web_hook'] = '编辑销售Web Hook URL';
$lang['config_edit_recv_web_hook'] = '编辑接收Web Hook URL';
$lang['config_hide_expire_dashboard'] = '隐藏仪表板上的过期项目';
$lang['config_hide_images_in_grid'] = '在网格中隐藏图像';
$lang['config_taxes_summary_on_receipt'] = '在收据上显示应纳税和不可纳税的摘要';
$lang['config_collapse_sales_ui_by_default'] = '默认情况下折叠销售界面';
$lang['config_collapse_recv_ui_by_default'] = '折叠收货界面默认情况下';
$lang['config_enable_customer_quick_add'] = '启用客户快速添加';
$lang['config_uppercase_receipts'] = '大写收据文本';

$lang['config_edit_customer_web_hook'] = '编辑客户Web挂钩URL';
$lang['config_show_selling_price_on_recv'] = '显示收货时的售价';

$lang['config_hide_email_on_receipts'] = '隐藏收据中的电子邮件';



$lang['config_hide_available_giftcards'] = '在销售记录中隐藏可用的礼品卡';


$lang['config_enable_supplier_quick_add'] = '启用供应商快速添加';
$lang['config_sync_inventory_from_location'] = '从位置同步库存';
$lang['config_taxes_summary_details_on_receipt'] = '在收据上显示税收明细';
$lang['config_disable_recv_number_on_barcode'] = '禁用条形码上的接收号码';
$lang['config_tax_jar_location'] = '使用TaxJar Location API提取税款';
$lang['config_disable_loyalty_by_default'] = '默认禁用忠诚度';

$lang['config_ecommerce_only_sync_completed_orders'] = '仅同步已完成的电子商务订单';

$lang['config_damaged_reasons'] = '损坏原因';

$lang['config_display_item_name_first_for_variation_name'] = '首先显示商品名称，以区分条形码';


$lang['config_do_not_allow_sales_with_zero_value'] = '不允许零值销售';

$lang['config_dont_recalculate_cost_price_when_unsuspending_estimates'] = '不暂停估算时，请勿重新计算成本价格';


$lang['config_show_signature_on_receiving_receipt'] = '在收据上显示签名';

$lang['config_do_not_treat_service_items_as_virtual'] = '在woo Commerce中不要将服务项视为虚拟产品';

$lang['config_hide_latest_updates_in_header'] = '在标题中隐藏最新更新';
$lang['config_prompt_amount_for_cash_sale'] = '提示现金销售金额';
$lang['config_do_not_allow_items_to_go_out_of_stock_when_transfering'] = '转移时不允许物品缺货';
$lang['config_show_tags_on_fulfillment_sheet'] = '在实现表上显示项目标签';
$lang['config_automatically_sms_receipt'] = '自动短信收据';
$lang['config_items_per_search_suggestions'] = '搜索建议项数';

$lang['config_shopify_settings_info'] = 'Shopify设置';
$lang['config_shopify_shop'] = 'Shopify商店网址';
$lang['config_connect_to_shopify'] = '连接到Shopify';
$lang['config_connect_to_shopify_reconnect'] = '重新连接到Shopify';
$lang['config_connected_to_shopify'] = '您已连接到Shopify';
$lang['config_disconnect_to_shopify'] = '与Shopify断开连接';

$lang['config_offline_mode'] = '启用离线模式';
$lang['config_reset_offline_data'] = '重置离线数据';



$lang['config_remove_quantity_suspending'] = '暂停时删除数量';
$lang['config_auto_sync_offline_sales'] = '重新联机后自动同步离线销售';

$lang['config_shopify_billing_terms'] = '激活结算-14天试用期，然后每月19美元';
$lang['config_shopfiy_billing_failed'] = 'Shopify开票失败';
$lang['config_cancel_shopify'] = '取消Shopify帐单';
$lang['config_confirm_cancel_shopify'] = '您确定要取消购物吗？';
$lang['config_step_1'] = '步骤1';
$lang['config_step_2'] = '第2步';
$lang['config_step_3'] = '第三步';
$lang['config_step_4'] = '第4步';
$lang['config_install_shopify_app'] = '安装Shopify应用';
$lang['config_connect_billing'] = '连接帐单';
$lang['config_choose_sync_options'] = '选择同步选项';
$lang['config_ecommerce_sync_running'] = '电子商务同步现在在后台运行。您可以在Store Config中检查状态。';
$lang['config_show_total_on_fulfillment'] = '在履行表上显示总计';
$lang['config_connect_shopify_in_app_store'] = '您尚未连接到Shopify。您可以在App Store中连接到Shopify';
$lang['config_override_signature_text'] = '覆盖签名文本';

$lang['config_delivery_color_based_on'] = '投放颜色基于';
$lang['config_delivery_color_based_on_status'] = '地位';
$lang['config_delivery_color_based_on_category'] = '类别';


$lang['config_update_cost_price_on_transfer'] = '转移时更新成本价';



$lang['config_tip_preset_zero'] = '小费预设量为0％';



$lang['config_layaway_statement_message'] = '散客声明消息';


$lang['config_show_person_id_on_receipt'] = '在收据上显示人员ID';




$lang['config_import_ecommerce_orders_suspended'] = '暂停导入电子商务订单';

$lang['config_show_images_on_receipt'] = '在收据上显示图像';

$lang['config_disabled_fixed_discounts'] = '在销售界面禁用整个固定折扣';



$lang['config_always_put_last_added_item_on_top_of_cart'] = '始终将最后添加的项目放在购物车顶部';



$lang['config_show_giftcards_even_if_0_balance'] = '即使余额为零也显示礼品卡';

$lang['config_scale_5'] = '重量嵌入条码';

$lang['config_disable_modules'] = '禁用模块';

$lang['config_hide_description_on_suspended_sales'] = '隐藏暂停销售的项目描述';
$lang['config_override_symbol_non_taxable'] = '覆盖非应税符号';



$lang['config_hide_categories_sales_grid'] = '在销售网格上隐藏类别';
$lang['config_hide_tags_sales_grid'] = '隐藏销售网格上的标签';
$lang['config_hide_favorites_sales_grid'] = '在销售网格上隐藏收藏夹';
$lang['config_hide_categories_receivings_grid'] = '在接收网格上隐藏类别';
$lang['config_hide_tags_receivings_grid'] = '隐藏接收网格上的标签';
$lang['config_hide_suppliers_receivings_grid'] = '在接收网格上隐藏供应商';
$lang['config_hide_favorites_receivings_grid'] = '在接收网格上隐藏收藏夹';
$lang['config_hide_suppliers_sales_grid'] = '在销售网格上隐藏供应商';

$lang['config_offline_mode_sync_period'] = '离线模式同步周期时间（小时）';



$lang['config_receipt_download_filename_prefix'] = '收据下载文件名前缀';
$lang['config_remove_employee_lastname_from_receipt'] = '从收据中删除员工姓氏';
$lang['config_override_symbol_taxes_summary_on_receipt'] = '在收据上显示应税和非应税摘要的覆盖符号';

$lang['config_show_images_on_receipt_width_percent'] = '收据上物品图像的最大宽度（百分比）';

$lang['config_link_to_receipt'] = '收据链接';
$lang['config_sale_summary_info'] = '销售摘要信息';
$lang['config_qr_code_format'] = '二维码格式';
$lang['config_override_symbol_taxable_summary'] = '收据上应税汇总的覆盖符号';
$lang['config_override_symbol_non_taxable_summary'] = '收据上的非应税汇总的覆盖符号';
$lang['config_allow_drag_drop_recv'] = '允许在接收屏幕上重新排序';
$lang['config_allow_drag_drop_sale'] = '允许在销售屏幕上重新订购';
$lang['config_disable_signature_capture_on_terminal_for_phppos_credit_card_processing'] = '禁用 PHP POS 信用卡处理的签名捕获';


$lang['config_capture_internal_notes_during_receiving'] = '在接收过程中捕捉内部笔记';


$lang['config_default_employee_for_deliveries'] = '交付的默认员工';

$lang['config_disable_verification_for_qr_codes'] = '禁用二维码验证（不需要电话号码或电子邮件）';


$lang['config_disable_variation_popup_in_receivings'] = '在接收中禁用变化弹出窗口';


$lang['config_saudi_arabia_digital_receipt'] = '沙特阿拉伯数字收据';

$lang['config_scale_6'] = '重量嵌入式条码 EAN 13';

$lang['config_hide_location_name_on_receipt'] = '在收据上隐藏位置名称';
$lang['config_disable_discount_by_percentage'] = '在销售界面上按百分比禁用整个折扣';

$lang['Delivery ID'] = '送货编号';
$lang['config_scale_7'] = '重量嵌入式条码 EAN 13 4 位价格数字';


$lang['config_use_main_image_as_default_image_in_e_commerce'] = '在电子商务中使用主图像作为默认图像';

$lang['config_disable_discounts_percentage_per_line_item'] = '禁用每个订单项的折扣百分比';
$lang['config_send_sms_via_whatsapp'] = '通过 Whatsapp 发送短信';
$lang['config_additional_appointment_note'] = '附加预约说明';
$lang['config_keywords_help_text'] = '在这里，您可以使用一些关键字。';

$lang['config_number_of_decimals_displayed_on_sales_interface'] = '销售界面显示的小数位数';

$lang['shopify_private_key'] = 'Shopify 私钥';
$lang['shopify_public_key'] = 'Shopify 公钥';
$lang['config_reconnect_to_shopify'] = '重新连接到 Shopify';

$lang['config_customer_allow_partial_match'] = '允许客户搜索的部分匹配';
?>