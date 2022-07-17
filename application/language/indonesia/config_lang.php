<?php
$lang['config_info']='Informasi Konfigurasi Toko';

$lang['config_address']='Alamat Perusahaan';
$lang['config_phone']='Telepon Perusahaan';

$lang['config_fax']='Faks';
$lang['config_default_tax_rate']='Tarif Pajak Biasa%';


$lang['config_company_required']='Nama Perusahaan wajib diisi';

$lang['config_phone_required']='Telepon Perusahaan wajib diisi';
$lang['config_default_tax_rate_required']='Tarif Pajak Biasa wajib diisi';
$lang['config_default_tax_rate_number']='Tarif Pajak Biasa harus angka';
$lang['config_company_website_url']='Situs Perusahaan bukan URL yang benar(http://...)';

$lang['config_saved_unsuccessfully']='Gagal untuk menyimpan konfigurasi. Perubahan konfigurasi tidak diperbolehkan dalam modus demo atau pajak tidak disimpan dengan benar';
$lang['config_return_policy_required']='Kebiajak retur wajib diisi';
$lang['config_print_after_sale']='Cetak bon setelah penjualan';


$lang['config_currency_symbol'] = 'Mata Simbol';
$lang['config_backup_database'] = 'Pencadangan database';
$lang['config_restore_database'] = 'Restore database';

$lang['config_number_of_items_per_page'] = 'Jumlah Item Per Halaman';
$lang['config_date_format'] = 'Format Tanggal';
$lang['config_time_format'] = 'Format Waktu';



$lang['config_database_optimize_successfully'] = 'Optimized Basis Data Berhasil';
$lang['config_payment_types'] = 'Jenis Pembayaran';
$lang['select_sql_file'] = 'pilih. sql file yang';

$lang['restore_heading'] = 'Hal ini memungkinkan Anda untuk mengembalikan database Anda';
$lang['type_file'] = 'pilih. sql file dari komputer Anda';

$lang['restore'] = 'mengembalikan';

$lang['required_sql_file'] = 'Tidak ada file sql dipilih';

$lang['restore_db_success'] = 'DataBase dipulihkan berhasil';

$lang['db_first_alert'] = 'Anda yakin untuk mengembalikan database?';
$lang['db_second_alert'] = 'Menyajikan data akan hilang, terus?';
$lang['password_error'] = 'sandi yang salah';
$lang['password_required'] = 'Kolom Password tidak boleh kosong';
$lang['restore_database_title'] = 'Restore database';
$lang['config_use_scale_barcode'] = 'Gunakan Barcode Skala';

$lang['config_environment'] = 'lingkungan';


$lang['config_sandbox'] = 'bak pasir';
$lang['config_production'] = 'produksi';
$lang['disable_confirmation_sale']='Nonaktifkan konfirmasi untuk dijual lengkap';



$lang['config_default_payment_type'] = 'Bawaan Jenis Pembayaran';
$lang['config_speed_up_note'] = 'Hanya merekomendasikan jika Anda memiliki lebih dari 10.000 item atau pelanggan';
$lang['config_hide_signature'] = 'Sembunyikan Signature';
$lang['config_automatically_email_receipt']='Email otomatis penerimaan';
$lang['config_barcode_price_include_tax']='Termasuk pajak atas barcode';
$lang['config_round_cash_on_sales'] = 'Putaran ke .05 terdekat pada penerimaan ';
$lang['config_prefix'] = 'Sale ID Awalan';
$lang['config_sale_prefix_required'] = 'Sale ID awalan adalah bidang yang dibutuhkan';
$lang['config_customers_store_accounts'] = 'Pelanggan Toko Account';
$lang['config_change_sale_date_when_suspending'] = 'Ubah tanggal penjualan saat menangguhkan penjualan';
$lang['config_change_sale_date_when_completing_suspended_sale'] = 'Ubah tanggal penjualan saat menyelesaikan penjualan ditangguhkan';
$lang['config_price_tiers'] = 'Tingkatan harga';
$lang['config_add_tier'] = 'Tambahkan tingkat';
$lang['config_show_receipt_after_suspending_sale'] = 'Tampilkan penerimaan setelah menangguhkan penjualan';
$lang['config_backup_overview'] = 'Sekilas Backup';
$lang['config_backup_overview_desc'] = 'Back up data anda sangat penting, tetapi dapat mengganggu dengan jumlah data yang besar. Jika Anda memiliki banyak gambar, item, dan penjualan ini dapat meningkatkan ukuran database Anda.';
$lang['config_backup_options'] = 'Kami menawarkan banyak pilihan untuk cadangan untuk membantu Anda memutuskan bagaimana untuk melanjutkan';
$lang['config_backup_simple_option'] = 'Mengklik &quot;Backup database&quot;. Ini akan mencoba untuk men-download seluruh database ke sebuah file. Jika Anda mendapatkan layar kosong atau tidak dapat men-download file tersebut, coba salah satu pilihan lain.';
$lang['config_backup_phpmyadmin_1'] = 'PHPMyAdmin adalah alat populer untuk mengelola database Anda. Jika Anda menggunakan versi unduh dengan installer, dapat diakses dengan pergi ke';
$lang['config_backup_phpmyadmin_2'] = 'Nama pengguna Anda adalah root dan password adalah apa yang Anda gunakan selama instalasi awal PHP POS. Setelah login pilih database Anda dari panel di sebelah kiri. Kemudian pilih ekspor dan kemudian menyerahkan formulir.';
$lang['config_backup_control_panel'] = 'Jika Anda telah diinstal pada server Anda sendiri yang memiliki panel kontrol seperti cpanel, mencari modul cadangan yang akan sering membiarkan Anda men-download backup database Anda.';
$lang['config_backup_mysqldump'] = 'Jika Anda memiliki akses ke shell dan mysqldump pada server Anda, Anda dapat mencoba untuk menjalankan dengan mengklik link di bawah ini. Jika tidak, Anda akan perlu mencoba pilihan lain.';
$lang['config_mysqldump_failed'] = 'mysqldump backup telah gagal. Hal ini bisa disebabkan oleh batasan server atau perintah mungkin tidak tersedia. Silakan coba metode cadangan lain';



$lang['config_looking_for_location_settings'] = 'Mencari opsi konfigurasi lainnya? Pergi ke';
$lang['config_module'] = 'Modul';
$lang['config_automatically_calculate_average_cost_price_from_receivings'] = 'Hitung Biaya rata-rata Harga mulai Receivings';
$lang['config_averaging_method'] = 'Averaging Metode';
$lang['config_historical_average'] = 'Sejarah Rata-rata';
$lang['config_moving_average'] = 'Moving Average';

$lang['config_hide_dashboard_statistics'] = 'Sembunyikan Panel Statistik';
$lang['config_hide_store_account_payments_in_reports'] = 'Pembayaran Sembunyikan Toko Rekening Dalam Laporan';
$lang['config_id_to_show_on_sale_interface'] = 'Item ID untuk Tampilkan Penjualan Antarmuka';
$lang['config_auto_focus_on_item_after_sale_and_receiving'] = 'Auto Focus On Barang Bidang Bila menggunakan Penjualan / Receivings Antarmuka';
$lang['config_automatically_show_comments_on_receipt'] = 'Secara otomatis Tampilkan Comments on Penerimaan';
$lang['config_hide_customer_recent_sales'] = 'Sembunyikan Penjualan terbaru untuk Pelanggan';
$lang['config_spreadsheet_format'] = 'Spreadsheet Format';
$lang['config_csv'] = 'CSV';
$lang['config_xlsx'] = 'XLSX';
$lang['config_disable_giftcard_detection'] = 'Nonaktifkan Deteksi giftcard';
$lang['config_disable_subtraction_of_giftcard_amount_from_sales'] = 'Nonaktifkan giftcard pengurangan ketika menggunakan giftcard selama penjualan';
$lang['config_always_show_item_grid'] = 'Selalu Tampilkan Barang Grid';
$lang['config_legacy_detailed_report_export'] = 'Legacy Detil Laporan Excel Ekspor';
$lang['config_print_after_receiving'] = 'Penerimaan cetak setelah menerima';
$lang['config_company_info'] = 'Informasi Perusahaan';


$lang['config_suspended_sales_layaways_info'] = 'Penjualan Suspended / Layaways';
$lang['config_application_settings_info'] = 'Pengaturan aplikasi';
$lang['config_hide_barcode_on_sales_and_recv_receipt'] = 'Sembunyikan barcode pada penerimaan';
$lang['config_round_tier_prices_to_2_decimals'] = 'Putaran Harga tier 2 desimal';
$lang['config_group_all_taxes_on_receipt'] = 'Kelompok semua pajak pada penerimaan';
$lang['config_receipt_text_size'] = 'Ukuran teks Penerimaan';
$lang['config_small'] = 'Kecil';
$lang['config_medium'] = 'Sedang';
$lang['config_large'] = 'Besar';
$lang['config_extra_large'] = 'Ekstra besar';
$lang['config_select_sales_person_during_sale'] = 'Pilih orang penjualan selama penjualan';
$lang['config_default_sales_person'] = 'Orang penjualan default';
$lang['config_require_customer_for_sale'] = 'Memerlukan pelanggan untuk dijual';

$lang['config_hide_store_account_payments_from_report_totals'] = 'Pembayaran rekening toko Sembunyikan dari total laporan';
$lang['config_disable_sale_notifications'] = 'Menonaktifkan pemberitahuan penjualan';
$lang['config_id_to_show_on_barcode'] = 'ID untuk menunjukkan pada barcode';
$lang['config_currency_denoms'] = 'Denominasi mata uang';
$lang['config_currency_value'] = 'Nilai mata uang';
$lang['config_add_currency_denom'] = 'Tambah mata uang';
$lang['config_enable_timeclock'] = 'Aktifkan Time Clock';
$lang['config_change_sale_date_for_new_sale'] = 'Perubahan Sale Tanggal Untuk dijual Baru';
$lang['config_dont_average_use_current_recv_price'] = 'Jangan rata, gunakan harga yang diterima saat ini';
$lang['config_number_of_recent_sales'] = 'Jumlah penjualan terbaru oleh pelanggan untuk menunjukkan';
$lang['config_hide_suspended_recv_in_reports'] = 'Sembunyikan Receivings ditangguhkan dalam laporan';
$lang['config_calculate_profit_for_giftcard_when'] = 'Hitung Hadiah Profit Card Ketika';
$lang['config_selling_giftcard'] = 'Jual Gift Card';
$lang['config_redeeming_giftcard'] = 'Kartu Hadiah Menebus';
$lang['config_remove_customer_contact_info_from_receipt'] = 'Hapus info kontak pelanggan dari penerimaan';
$lang['config_speed_up_search_queries'] = 'Mempercepat permintaan pencarian?';




$lang['config_redirect_to_sale_or_recv_screen_after_printing_receipt'] = 'Redirect ke penjualan atau menerima layar setelah mencetak tanda terima';
$lang['config_enable_sounds'] = 'Aktifkan suara untuk pesan status';
$lang['config_charge_tax_on_recv'] = 'Mengisi pajak Receivings';
$lang['config_report_sort_order'] = 'Laporan Urutan Sortir';
$lang['config_asc'] = 'Terlama pertama';
$lang['config_desc'] = 'Terbaru pertama';
$lang['config_do_not_group_same_items'] = 'JANGAN item kelompok yang sama';
$lang['config_show_item_id_on_receipt'] = 'Tampilkan Item id pada penerimaan';
$lang['config_show_language_switcher'] = 'Tampilkan Bahasa Switcher';
$lang['config_do_not_allow_out_of_stock_items_to_be_sold'] = 'Jangan biarkan keluar dari stok barang yang akan dijual';
$lang['config_number_of_items_in_grid'] = 'Jumlah setiap halaman dalam grid';
$lang['config_edit_item_price_if_zero_after_adding'] = 'Mengedit harga barang jika 0 setelah menambah penjualan';
$lang['config_override_receipt_title'] = 'Judul penerimaan Override';
$lang['config_automatically_print_duplicate_receipt_for_cc_transactions'] = 'Secara otomatis mencetak duplikat tanda terima untuk transaksi kartu kredit';






$lang['config_default_type_for_grid'] = 'Jenis default untuk Grid';
$lang['config_billing_is_managed_through_paypal'] = 'Penagihan dikelola melalui <a target="_blank" href="http://paypal.com">Paypal</a>. Anda dapat membatalkan langganan Anda dengan mengklik <a target="_blank" href="https://www.paypal.com/cgi-bin/webscr?cmd=_subscr-find&alias=BNTRX72M8UZ2E">di sini</a>. <a href="http://phppointofsale.com/update_billing.php" target="_blank">Anda dapat memperbarui penagihan disini</a>';
$lang['config_cannot_change_language'] = 'Bahasa tidak dapat disimpan di tingkat aplikasi. Namun karyawan admin default dapat mengubah bahasa menggunakan pemilih di header program';
$lang['disable_quick_complete_sale'] = 'Nonaktifkan jual cepat selesai';
$lang['config_fast_user_switching'] = 'Aktifkan switching pengguna cepat (password tidak diperlukan)';
$lang['config_require_employee_login_before_each_sale'] = 'Memerlukan login karyawan sebelum setiap penjualan';
$lang['config_reset_location_when_switching_employee'] = 'Ulang lokasi ketika berpindah karyawan';
$lang['config_number_of_decimals'] = 'Jumlah desimal';
$lang['config_let_system_decide'] = 'Biarkan sistem memutuskan (Direkomendasikan)';
$lang['config_thousands_separator'] = 'Ribuan Separator';
$lang['config_enhanced_search_method'] = 'Metode Pencarian yang Disempurnakan';
$lang['config_hide_store_account_balance_on_receipt'] = 'Sembunyikan toko saldo rekening pada penerimaan';
$lang['config_decimal_point'] = 'Titik desimal';
$lang['config_hide_out_of_stock_grid'] = 'Menyembunyikan dari persediaan item dalam grid';
$lang['config_highlight_low_inventory_items_in_items_module'] = 'Sorot item persediaan rendah item modul';
$lang['config_sort'] = 'Urutkan';
$lang['config_enable_customer_loyalty_system'] = 'Mengaktifkan sistem Loyalitas Pelanggan';
$lang['config_spend_to_point_ratio'] = 'Menghabiskan jumlah untuk menunjukkan rasio';
$lang['config_point_value'] = 'Point Value';
$lang['config_hide_points_on_receipt'] = 'Sembunyikan Poin Pada Penerimaan';
$lang['config_show_clock_on_header'] = 'Tampilkan Jam di Header';
$lang['config_show_clock_on_header_help_text'] = 'Ini terlihat hanya pada layar lebar';
$lang['config_loyalty_explained_spend_amount'] = 'Masukkan jumlah yang menghabiskan';
$lang['config_loyalty_explained_points_to_earn'] = 'Masukkan poin yang akan diperoleh';
$lang['config_simple'] = 'Sederhana';
$lang['config_advanced'] = 'Maju';
$lang['config_loyalty_option'] = 'Opsi Program Loyalitas';
$lang['config_number_of_sales_for_discount'] = 'Jumlah penjualan untuk diskon';
$lang['config_discount_percent_earned'] = 'Diskon persen diperoleh ketika mencapai penjualan';
$lang['hide_sales_to_discount_on_receipt'] = 'Sembunyikan penjualan untuk diskon pada penerimaan';
$lang['config_hide_price_on_barcodes'] = 'Harga Sembunyikan barcode';
$lang['config_always_use_average_cost_method'] = 'Selalu Penggunaan Global Rata-rata Biaya Harga Untuk Sebuah Dijual Item Biaya Harga. (JANGAN memeriksa kecuali Anda tahu apa artinya)';

$lang['config_test_mode_help'] = 'Penjualan TIDAK disimpan';
$lang['config_require_customer_for_suspended_sale'] = 'Memerlukan pelanggan untuk dijual ditangguhkan';
$lang['config_default_new_items_to_service'] = 'Standar Produk Baru sebagai item layanan';






$lang['config_prompt_for_ccv_swipe'] = 'Meminta untuk CCV ketika menggesekkan kartu kredit';
$lang['config_disable_store_account_when_over_credit_limit'] = 'Akun toko menonaktifkan ketika melewati batas kredit';
$lang['config_mailing_labels_type'] = 'Mailing Label Format';
$lang['config_phppos_session_expiration'] = 'Sesi kedaluwarsa';
$lang['config_hours'] = 'Jam';
$lang['config_never'] = 'Tak pernah';
$lang['config_on_browser_close'] = 'Pada Browser Tutup';
$lang['config_do_not_allow_below_cost'] = 'Jangan biarkan barang yang akan dijual di bawah harga biaya';
$lang['config_store_account_statement_message'] = 'Toko Rekening Pesan';
$lang['config_enable_markup_calculator'] = 'Aktifkan Mark Up Kalkulator';
$lang['config_enable_quick_edit'] = 'Aktifkan cepat edit pada mengelola halaman';
$lang['config_show_orig_price_if_marked_down_on_receipt'] = 'Tampilkan harga asli pada penerimaan jika ditandai';
$lang['config_cancel_account'] = 'Batalkan Akun';
$lang['config_update_billing'] = 'Anda dapat memperbarui dan membatalkan informasi penagihan dengan mengklik tombol di bawah ini:';
$lang['config_include_child_categories_when_searching_or_reporting'] = 'Termasuk kategori anak ketika mencari atau pelaporan';
$lang['config_confirm_error_messages_modal'] = 'Konfirmasi pesan kesalahan menggunakan modal dialog';
$lang['config_remove_commission_from_profit_in_reports'] = 'Hapus komisi dari laba di laporan';
$lang['config_remove_points_from_profit'] = 'Hapus poin penebusan dari laba';
$lang['config_capture_sig_for_all_payments'] = 'Menangkap tanda tangan untuk semua penjualan';
$lang['config_suppliers_store_accounts'] = 'Pemasok Toko Akun';
$lang['config_currency_symbol_location'] = 'Mata uang Simbol Lokasi';
$lang['config_before_number'] = 'sebelum Nomor';
$lang['config_after_number'] = 'setelah Number';
$lang['config_hide_desc_on_receipt'] = 'Sembunyikan Deskripsi di Receipt';
$lang['config_default_percent_off'] = 'Bawaan Persen Off';
$lang['config_default_cost_plus_percent'] = 'Biaya standar Ditambah Persen';
$lang['config_default_tier_percent_type_for_excel_import'] = 'Standar Tier Persen Jenis untuk excel impor';
$lang['config_override_tier_name'] = 'Menimpa Tier Nama pada Penerimaan';
$lang['config_loyalty_points_without_tax'] = 'Loyalitas poin yang diterima tidak termasuk pajak';
$lang['config_lock_prices_suspended_sales'] = 'harga kunci bila unsuspending dijual bahkan jika mereka milik tier';
$lang['config_remove_customer_name_from_receipt'] = 'Hapus Nama Pelanggan Dari Receipt';
$lang['config_scale_1'] = 'UPC-12 4 digit harga';
$lang['config_scale_2'] = 'UPC-12 5 Digit Harga';
$lang['config_scale_3'] = 'EAN-13 5 digit harga';
$lang['config_scale_4'] = 'EAN-13 6 digit harga';
$lang['config_scale_format'] = 'Skala Barcode Format';
;
$lang['config_enable_scale'] = 'aktifkan Skala';
$lang['config_scale_divide_by'] = 'Skala Harga Divide By';
$lang['config_logout_on_clock_out'] = 'Log out secara otomatis ketika clocking keluar';
$lang['config_user_configured_layaway_name'] = 'Override Nama angsuran';
$lang['config_use_tax_value_at_all_locations'] = 'Gunakan Nilai Pajak di semua lokasi';
$lang['config_enable_ebt_payments'] = 'Aktifkan pembayaran EBT';
$lang['config_item_id_auto_increment'] = 'Item ID Auto Increment Mulai Nilai';
$lang['config_change_auto_increment_item_id_unsuccessful'] = 'Ada kesalahan saat mengubah auto_increment untuk item_id';
$lang['config_item_kit_id_auto_increment'] = 'Item Kit ID Auto Increment Mulai Nilai';
$lang['config_sale_id_auto_increment'] = 'Sale ID Auto Increment Mulai Nilai';
$lang['config_receiving_id_auto_increment'] = 'Menerima ID Auto Increment Mulai Nilai';
$lang['config_change_auto_increment_item_kit_id'] = 'Ada kesalahan saat mengubah auto_increment untuk Iitem_kit_id';
$lang['config_change_auto_increment_sale_id'] = 'Ada kesalahan saat mengubah auto_increment untuk sale_id';
$lang['config_change_auto_increment_receiving_id'] = 'Ada kesalahan saat mengubah auto_increment untuk receiving_id';
$lang['config_auto_increment_note'] = 'Anda hanya dapat meningkatkan nilai Auto Increment. Memperbarui mereka tidak akan mempengaruhi ID untuk produk, barang kit, penjualan atau Receivings yang sudah ada.';

$lang['config_online_price_tier'] = 'Online Harga Tier';
$lang['config_woo_api_key'] = 'Woocommerce API Key';
$lang['config_email_settings_info'] = 'Pengaturan email';

$lang['config_last_sync_date'] = 'Terakhir Sync Tanggal';
$lang['config_sync'] = 'Sinkronisasi';
$lang['config_smtp_crypto'] = 'SMTP Enkripsi';
$lang['config_email_protocol'] = 'Mail Mengirim Protocol';
$lang['config_smtp_host'] = 'SMTP Server Alamat';
$lang['config_smtp_user'] = 'Alamat email';
$lang['config_smtp_pass'] = 'email Sandi';
$lang['config_smtp_port'] = 'SMTP Pelabuhan';
$lang['config_email_charset'] = 'Set karakter';
$lang['config_email_newline'] = 'karakter baris baru';
$lang['config_email_crlf'] = 'CRLF';
$lang['config_smtp_timeout'] = 'SMTP Timeout';
$lang['config_send_test_email'] = 'Kirim Uji Email';
$lang['config_please_enter_email_to_send_test_to'] = 'Masukkan alamat email untuk mengirim email tes untuk';
$lang['config_email_succesfully_sent'] = 'Email telah berhasil dikirim';
$lang['config_taxes_info'] = 'Pajak';
$lang['config_currency_info'] = 'Mata uang';

$lang['config_receipt_info'] = 'Penerimaan';

$lang['config_barcodes_info'] = 'barcode';
$lang['config_customer_loyalty_info'] = 'Kesetiaan pelanggan';
$lang['config_price_tiers_info'] = 'Tiers Harga';
$lang['config_auto_increment_ids_info'] = 'Nomor ID';
$lang['config_items_info'] = 'item';
$lang['config_employee_info'] = 'Karyawan';
$lang['config_store_accounts_info'] = 'Akun toko';
$lang['config_sales_info'] = 'Penjualan';
$lang['config_payment_types_info'] = 'jenis pembayaran';
$lang['config_profit_info'] = 'laba Perhitungan';
$lang['reports_view_dashboard_stats'] = 'Lihat Statistik Dashboard';
$lang['config_keyword_email'] = 'pengaturan email';
$lang['config_keyword_company'] = 'perusahaan';
$lang['config_keyword_taxes'] = 'pajak';
$lang['config_keyword_currency'] = 'mata uang';
$lang['config_keyword_payment'] = 'pembayaran';
$lang['config_keyword_sales'] = 'penjualan';
$lang['config_keyword_suspended_layaways'] = 'layaways ditangguhkan';
$lang['config_keyword_receipt'] = 'penerimaan';
$lang['config_keyword_profit'] = 'keuntungan';
$lang['config_keyword_barcodes'] = 'barcode';
$lang['config_keyword_customer_loyalty'] = 'kesetiaan pelanggan';
$lang['config_keyword_price_tiers'] = 'tingkatan harga';
$lang['config_keyword_auto_increment'] = 'mulai kenaikan otomatis basis data nomor id';
$lang['config_keyword_items'] = 'item';
$lang['config_keyword_employees'] = 'para karyawan';
$lang['config_keyword_store_accounts'] = 'rekening toko';
$lang['config_keyword_application_settings'] = 'pengaturan aplikasi';
$lang['config_keyword_ecommerce'] = 'Platform e-commerce';
$lang['config_keyword_woocommerce'] = 'pengaturan woocommerce e-commerce';
$lang['config_billing_info'] = 'Informasi tagihan';
$lang['config_keyword_billing'] = 'penagihan membatalkan pembaruan';
$lang['config_woo_version'] = 'WooCommerce Versi';

$lang['sync_phppos_item_changes'] = 'perubahan item sinkronisasi';
$lang['config_sync_phppos_item_changes'] = 'perubahan item sinkronisasi';
$lang['config_import_ecommerce_items_into_phppos'] = 'Impor item ke phppos';
$lang['config_sync_inventory_changes'] = 'perubahan persediaan Sync';
$lang['config_export_phppos_tags_to_ecommerce'] = 'tag ekspor ke e-commerce';
$lang['config_export_phppos_categories_to_ecommerce'] = 'kategori ekspor ke e-commerce';
$lang['config_export_phppos_items_to_ecommerce'] = 'item ekspor ke e-commerce';
$lang['config_ecommerce_cron_sync_operations'] = 'E-commerce Sync Operasi';
$lang['config_ecommerce_progress'] = 'Sync Kemajuan';
$lang['config_woocommerce_settings_info'] = 'Pengaturan Woocommerce';
$lang['config_store_location'] = 'Lokasi toko';
$lang['config_woo_api_secret'] = 'Woocommerce API Rahasia';
$lang['config_woo_api_url'] = 'Woocommerce API Url';
$lang['config_ecommerce_settings_info'] = 'Landasan e-commerce';
$lang['config_ecommerce_platform'] = 'Pilih platform';
$lang['config_magento_settings_info'] = 'Pengaturan Magento';
$lang['confirmation_woocommerce_cron_cancel'] = 'Apakah Anda yakin ingin membatalkan sinkronisasi?';
$lang['config_force_https'] = 'Membutuhkan https untuk program';

$lang['config_keyword_price_rules'] = 'Aturan Harga';
$lang['config_disable_price_rules_dialog'] = 'Menonaktifkan dialog Harga Aturan';
$lang['config_price_rules_info'] = 'Aturan Harga';

$lang['config_prompt_to_use_points'] = 'Prompt untuk menggunakan poin bila tersedia';



$lang['config_always_print_duplicate_receipt_all'] = 'Selalu cetak tanda terima duplikat untuk semua transaksi';


$lang['config_orders_and_deliveries_info'] = 'Pesanan dan Pengiriman';
$lang['config_delivery_methods'] = 'Metode pengiriman';
$lang['config_shipping_providers'] = 'Penyedia Pengiriman';
$lang['config_expand'] = 'Memperluas';
$lang['config_add_delivery_rate'] = 'Tambahkan Tarif Pengiriman';
$lang['config_add_shipping_provider'] = 'Tambahkan Penyedia Pengiriman';
$lang['config_delivery_rates'] = 'Harga pengiriman';
$lang['config_delivery_fee'] = 'Biaya pengiriman';
$lang['config_keyword_orders_deliveries'] = 'Memesan pengiriman pengiriman';
$lang['config_delivery_fee_tax'] = 'Biaya Pengiriman Pajak';
$lang['config_add_rate'] = 'Tambahkan Rate';
$lang['config_delivery_time'] = 'Waktu Pengiriman Dalam Hari';
$lang['config_delivery_rate'] = 'Tarif pengiriman';
$lang['config_rate_name'] = 'Nama tarif';
$lang['config_rate_fee'] = 'Tarif biaya';
$lang['config_rate_tax'] = 'Tarif pajak';
$lang['config_tax_classes'] = 'Kelompok pajak';
$lang['config_add_tax_class'] = 'Tambahkan Grup Pajak';

$lang['config_wide_printer_receipt_format'] = 'Format Penerimaan Printer Lebar';

$lang['config_default_cost_plus_fixed_amount'] = 'Biaya Tetap Plus Plus';
$lang['config_default_tier_fixed_type_for_excel_import'] = 'Default Tier Fixed Amount untuk Excel Import';
$lang['config_default_reorder_level_when_creating_items'] = 'Default Reorder Level Saat Membuat Item';
$lang['config_remove_customer_company_from_receipt'] = 'Hapus nama perusahaan pelanggan dari penerimaan';

$lang['config_import_ecommerce_categories_into_phppos'] = 'Impor kategori ke phppos';
$lang['config_import_ecommerce_tags_into_phppos'] = 'Impor tag ke phppos';

$lang['config_shipping_zones'] = 'Zona Pengiriman';
$lang['config_add_shipping_zone'] = 'Tambahkan Zona Pengiriman';
$lang['config_no_results'] = 'Tidak ada hasil';
$lang['config_zip_search_term'] = 'Ketik kode pos';
$lang['config_searching'] = 'Mencari ...';
$lang['config_tax_class'] = 'Kelompok pajak';
$lang['config_zone'] = 'Daerah';

$lang['config_zip_codes'] = 'Kode pos';
$lang['config_add_zip_code'] = 'Tambahkan Kode Pos';
$lang['config_ecom_sync_logs'] = 'Log Sinkronisasi E-Commerce';
$lang['config_currency_code'] = 'Kode mata uang';

$lang['config_add_currency_exchange_rate'] = 'Tambahkan Kurs Mata Uang';
$lang['config_currency_exchange_rates'] = 'Nilai tukar';
$lang['config_exchange_rate'] = 'Kurs';
$lang['config_item_lookup_order'] = 'Item Lookup Order';
$lang['config_item_id'] = 'Id item';
$lang['config_reset_ecommerce'] = 'Setel ulang E-Commerce';
$lang['config_confirm_reset_ecom'] = 'Yakin ingin mereset e-commerce? Ini hanya akan mengatur ulang titik penjualan php jadi item tidak lagi terhubung';
$lang['config_reset_ecom_successfully'] = 'Anda telah mengatur ulang E-Commerce dengan sukses';
$lang['config_number_of_decimals_for_quantity_on_receipt'] = 'Jumlah Decimal Jumlah Kuantitas Penerimaan';
$lang['config_enable_wic'] = 'Aktifkan WIC';
$lang['config_store_opening_time'] = 'Waktu Pembukaan Toko';
$lang['config_store_closing_time'] = 'Waktu Penutupan Toko';
$lang['config_limit_manual_price_adj'] = 'Membatasi Penyesuaian Harga Dan Diskon Manual';
$lang['config_always_minimize_menu'] = 'Selalu Minimalkan Menu Bar Side Kiri';

$lang['config_emailed_receipt_subject'] = 'Subjek Penerimaan Email';

$lang['config_do_not_tax_service_items_for_deliveries'] = 'JANGAN item layanan pajak untuk pengiriman';


$lang['config_do_not_show_closing'] = 'Jangan tampilkan jumlah penutupan yang diharapkan saat menutup register';

$lang['config_paypal_me'] = 'Username PayPal.me';


$lang['config_show_barcode_company_name'] = 'Tunjukkan nama perusahaan pada kode batang';
$lang['config_import_ecommerce_attributes_into_phppos'] = 'Impor atribut ke phppos';
$lang['config_export_phppos_attributes_to_ecommerce'] = 'Atribut Ekspor ke e-niaga';

$lang['config_sku_sync_field'] = 'Bidang SKU untuk disinkronkan dengan';



$lang['config_overwrite_existing_items_on_excel_import'] = 'Timpa item yang ada pada excel import';

$lang['config_do_not_force_http'] = 'Jangan memaksakan HTTP saat dibutuhkan untuk Pengolahan Kartu Kredit EMV';
$lang['config_add_suspended_sale_type'] = 'Tambahkan Tipe Penjualan yang Ditangguhkan';
$lang['config_additional_suspend_types'] = 'Jenis Penjualan Suspended Tambahan';
$lang['config_remove_employee_from_receipt'] = 'Hapus Nama Karyawan Dari Tanda Terima';
$lang['config_import_ecommerce_orders_into_phppos'] = 'Impor pesanan ke phppos';
$lang['import_ecommerce_orders_into_phppos'] = 'Pesanan Impor ke pos php';
$lang['config_hide_name_on_barcodes'] = 'Sembunyikan nama pada barcode';


$lang['config_api_settings_info'] = 'Pengaturan API';
$lang['config_keyword_api'] = 'API';
$lang['config_api_keys'] = 'Tombol API';
$lang['config_api_key_ending_in'] = 'Kunci API Berakhir di';
$lang['config_permissions'] = 'Izin';
$lang['config_last_access'] = 'Akses terakhir';
$lang['config_add_key'] = 'Tambahkan Kunci API';
$lang['config_api_key'] = 'kunci API';
$lang['config_read'] = 'Baca baca';
$lang['config_read_write'] = 'Baca tulis';
$lang['config_submit_api_key'] = 'Yakin ingin menambahkan kunci ini? Pastikan Anda telah menyalin kunci ke lokasi yang aman karena tidak akan ditampilkan lagi.';
$lang['config_write'] = 'Menulis';
$lang['config_api_key_confirm_delete'] = 'Yakin ingin menghapus kunci api ini?';
$lang['config_key_copied_to_clipboard'] = 'Kunci disalin ke clipboard';

$lang['config_new_items_are_ecommerce_by_default'] = 'Item Baru adalah E-Commerce Secara Default';


$lang['config_new_items_are_ecommerce_by_default'] = 'Item Baru adalah E-Commerce Secara Default';

$lang['config_hide_description_on_sales_and_recv'] = 'Sembunyikan deskripsi tentang antarmuka penjualan dan penerima';





$lang['config_hide_item_descriptions_in_reports'] = 'menyembunyikan deskripsi item dalam laporan';





$lang['config_do_not_allow_item_with_variations_to_be_sold_without_selecting_variation'] = 'JANGAN biarkan variasi item terjual tanpa memilih variasi';



$lang['config_verify_age_for_products'] = 'Verifikasi usia untuk produk';
$lang['config_default_age_to_verify'] = 'Umur bawaan untuk memverifikasi';




$lang['config_remind_customer_facing_display'] = 'Ingatkan karyawan untuk membuka tampilan pelanggan';

$lang['config_import_tax_classes_into_phppos'] = 'Mengimpor Kelas Pajak ke dalam phppos';
$lang['config_export_tax_classes_into_phppos'] = 'Ekspor kelas pajak ke e-niaga';
$lang['config_import_shipping_classes_into_phppos'] = 'Impor Kelas Pengiriman ke phppos';
$lang['config_disable_confirm_recv'] = 'Nonaktifkan Konfirmasi untuk Menerima Lengkap';
$lang['config_minimum_points_to_redeem'] = 'Jumlah poin minimum untuk ditebus';
$lang['config_default_days_to_expire_when_creating_items'] = 'Hari default berakhir saat membuat item';


$lang['config_quickbooks_settings'] = 'Pengaturan Quickbooks';
$lang['config_qb_sync_operations'] = 'Operasi Sinkronisasi Quickbooks';
$lang['config_import_quickbooks_items_into_phppos'] = 'Impor item ke dalam phppos';
$lang['config_export_phppos_items_to_quickbooks'] = 'Ekspor barang ke buku cepat';
$lang['config_import_customers_into_phppos'] = 'Impor pelanggan ke phppos';
$lang['config_import_suppliers_into_phppos'] = 'Pemasok impor ke phppos';
$lang['config_import_employees_into_phppos'] = 'Impor karyawan ke phppos';
$lang['config_export_employees_to_quickbooks'] = 'Ekspor karyawan ke buku cepat';
$lang['config_export_sales_to_quickbooks'] = 'Ekspor penjualan ke buku pintas';
$lang['config_export_receivings_to_quickbooks'] = 'Mengekspor piutang ke buku saku';
$lang['config_export_customers_to_quickbooks'] = 'Ekspor pelanggan ke buku pintas';
$lang['config_export_suppliers_to_quickbooks'] = 'Mengekspor pemasok ke buku saku';
$lang['config_connect_to_qb_online'] = 'Hubungkan ke buku panduan online';
$lang['config_refresh_tokens'] = 'Segarkan Token';
$lang['config_reconnect_quickbooks'] = 'Hubungkan kembali ke buku kilat online';
$lang['config_reset_quickbooks'] = 'Reset Quickbooks';
$lang['config_qb_sync_logs'] = 'Log sinkronisasi quickbooks';
$lang['config_quickbooks_progress'] = 'Quickbooks menyinkronkan kemajuan';
$lang['config_last_qb_sync_date'] = 'Tanggal Sinkronisasi Terakhir';
$lang['config_confirmation_qb_cron_cancel'] = 'Anda yakin ingin membatalkan sinkronisasi buku cepat?';
$lang['config_confirmation_qb_cron'] = 'Apakah Anda yakin ingin menyinkronkan buku cepat?';
$lang['config_confirm_reset_qb'] = 'Apakah Anda yakin ingin mengatur ulang buku cepat? Ini akan membatalkan tautan Anda dari buku cepat.';
$lang['$platform=$this->Appconfig->get("ecommerce_platform");'] = 'if ($ platform == "woocommerce")';
$lang['config_reset_qb_successfully'] = 'Anda telah berhasil mereset buku ringkas';
$lang['config_export_phppos_categories_to_quickbooks'] = 'Mengekspor kategori dari phppos ke quickbooks';
$lang['config_create_payment_methods'] = 'Buat metode pembayaran di QB';


$lang['config_allow_scan_of_customer_into_item_field'] = 'Izinkan pemindaian pelanggan ke dalam bidang item';
$lang['config_cash_alert_high'] = 'Waspada saat uang tunai di atas';
$lang['config_cash_alert_low'] = 'Waspada saat uang tunai di bawah';


$lang['config_sync_inventory_changes_qb'] = 'Sinkronkan perubahan inventaris';

$lang['config_sort_receipt_column'] = 'Urutkan Kolom Tanda Terima';





$lang['config_show_tax_per_item_on_receipt'] = 'Tampilkan pajak per item saat diterima';





$lang['config_enable_timeclock_pto'] = 'Aktifkan waktu jam waktu berbayar';


$lang['config_enable_timeclock_pto'] = 'Aktifkan waktu jam waktu berbayar';

$lang['config_show_item_id_on_recv_receipt'] = 'Tampilkan id item saat menerima';





$lang['config_import_all_past_orders_for_woo_commerce'] = 'Impor SEMUA pesanan terakhir untuk WooCommerce';




$lang['config_enable_margin_calculator'] = 'Aktifkan Kalkulator Margin';










$lang['config_hide_barcode_on_barcode_labels'] = 'Sembunyikan Label Barcode Pada';



$lang['config_do_not_delete_saved_card_after_failure'] = 'JANGAN hapus kartu yang disimpan setelah kegagalan';





$lang['config_capture_internal_notes_during_sale'] = 'Tangkap Catatan Internal Saat Dijual';





$lang['config_hide_prices_on_fill_sheet'] = 'Sembunyikan Harga pada Lembar Pemenuhan';



$lang['$platform=$this->Appconfig->get("ecommerce_platform");'] = 'if ($ platform == "woocommerce")';
$lang['config_default_revenue_account_for_item'] = 'Akun Pendapatan Default Untuk Item';
$lang['config_default_asset_account_for_item'] = 'Akun Aset Default Untuk Item';
$lang['config_default_expense_account_for_item'] = 'Akun Pengeluaran Default untuk Item';
$lang['config_export_expenses_to_quickbooks'] = 'Pengeluaran ekspor ke buku saku';
$lang['config_chart_of_accounts'] = 'Quickbooks Chart of accounts';
$lang['config_keyword_chart_of_account'] = 'Quickbooks Chart of accounts';
$lang['config_default_refund_cash_account_name'] = 'Rekening Kas Pengembalian Dana';
$lang['config_default_refund_credit_account_name'] = 'Pengembalian Rekening Kredit';
$lang['config_default_refund_debit_card_account_name'] = 'Rekening Kartu Debit Pengembalian Dana';
$lang['config_default_refund_credit_card_account_name'] = 'Pengembalian Rekening Kartu Kredit';
$lang['config_default_refund_check_account_name'] = 'Periksa Rekening Kembalian';
$lang['config_default_refund_deposit_account_name'] = 'Rekening Deposit Pengembalian Dana';
$lang['config_default_expense_account_name'] = 'Rekening pengeluaran';
$lang['config_default_expense_bank_credit_account_name'] = 'Expense Bank / Rekening Kredit';
$lang['config_default_commission_credit_account_name'] = 'Rekening Kredit Komisi';
$lang['config_default_commission_debit_account_name'] = 'Akun Debit Komisi';
$lang['config_default_house_account_name'] = 'Simpan Nama Akun';
$lang['config_default_discount_item_name'] = 'Item Diskon';
$lang['config_default_house_item_name'] = 'Nama Item Rumah';
$lang['config_default_store_account_item_name'] = 'Simpan Item Akun';
$lang['config_default_house_account_category_name'] = 'Kategori Akun Rumah';
$lang['config_default_customer_id'] = 'Nama Pelanggan Default';
$lang['config_revenue_id'] = 'Gagal menyimpan konfigurasi. Akun Pendapatan Default Untuk Item hilang.';
$lang['config_asset_id'] = 'Gagal menyimpan konfigurasi. Akun Aset Default untuk Item hilang';
$lang['config_export_confirm_box_text'] = 'Apakah Anda ingin mengekspor barang ke buku pintas?';
$lang['config_discount_accounting_id'] = 'Id Akuntansi Item Diskon tidak ada untuk Dijual';
$lang['config_sync_for_discount_accounting_id'] = 'Harap sinkronkan item sebelum membuat faktur dengan diskon';


$lang['config_hide_desc_emailed_receipts'] = 'Sembunyikan Keterangan pada Tanda Terima E-Mail';


$lang['config_default_tax'] = 'Pajak Default';
$lang['config_default_store_account_tax'] = 'Pajak akun Store Default';
$lang['config_check_tax_name'] = 'Nama pajak yang diberikan tidak benar. Harap periksa id penjualan:';
$lang['config_qb_start_sync_date'] = 'Mulai Tanggal Sinkronisasi';
$lang['config_default_tax_id'] = 'Pajak Default';
$lang['config_markup_markdown'] = 'Markup / Penurunan harga';
$lang['config_show_total_discount_on_receipt'] = 'Tampilkan Diskon Total Saat Menerima';
$lang['config_enable_pdf_receipts'] = 'Aktifkan tanda terima PDF';
$lang['config_default_credit_limit'] = 'Batas Kredit Default';

$lang['config_hide_expire_date_on_barcodes'] = '';

$lang['config_auto_capture_signature'] = 'Tanda Tangkap Otomatis';


$lang['config_pdf_receipt_message'] = 'Pesan tanda terima PDF di badan email';

$lang['config_hide_merchant_id_from_receipt'] = 'Sembunyikan ID Pedagang dari tanda terima';


$lang['config_hide_all_prices_on_recv'] = 'Sembunyikan SEMUA harga saat menerima';
$lang['config_do_not_delete_serial_number_when_selling'] = 'JANGAN menghapus nomor seri saat menjual';
$lang['config_webhooks'] = 'Kait Web';
$lang['config_new_customer_web_hook'] = 'URL Hook Web Pelanggan Baru';
$lang['config_new_sale_web_hook'] = 'URL Hook Web Penjualan Baru';
$lang['config_new_receiving_web_hook'] = 'Hook Web Penerimaan Baru';

$lang['config_strict_age_format_check'] = 'Pemeriksaan format tanggal ketat verifikasi usia';

$lang['config_flat_discounts_discount_tax'] = 'Diskon Flat juga diskon pajak';
$lang['config_show_item_kit_items_on_receipt'] = 'Tampilkan Item Kit Item Pada Tanda Terima';
$lang['config_amount_of_cash_to_be_left_in_drawer_at_closing'] = 'Jumlah Uang yang tersisa di Laci saat Penutupan';
$lang['config_hide_tier_on_receipt'] = 'Sembunyikan Kwitansi Kwitansi';
$lang['config_second_language'] = 'Bahasa Kedua pada Penerimaan';
$lang['config_disable_gift_cards_sold_from_loyalty'] = 'Nonaktifkan Kartu Hadiah yang Dijual Dari Loyalitas Penghasilan';
$lang['config_track_shipping_cost_for_receivings'] = 'Lacak Biaya Pengiriman Untuk Penerimaan';
$lang['config_enable_points_for_giftcard_payments'] = 'Aktifkan poin untuk pembayaran kartu hadiah';




$lang['config_enable_tips'] = 'Aktifkan Tips';

$lang['config_support_regex'] = 'Mendukung ekspresi reguler. Contoh: 144. * cocok dengan apa pun yang dimulai dengan 144';

$lang['config_not_all_processors_support_tips'] = 'Tidak semua prosesor mendukung pemrosesan tip terintegrasi';
$lang['config_require_supplier_recv'] = 'Membutuhkan Pemasok untuk Menerima';
$lang['config_default_payment_type_recv'] = 'Jenis Pembayaran Default untuk Penerimaan';
$lang['config_taxjar_api_key'] = 'Kunci API TaxJar (Hanya AS)';

$lang['config_quick_variation_grid'] = 'Aktifkan Pilih Cepat untuk Varitions di kisi item';


$lang['config_quick_variation_grid'] = 'Pilih cepat untuk Variasi';


$lang['config_quick_variation_grid'] = 'Aktifkan Pilih Cepat Di Kisi Item Untuk Variasi';



$lang['config_show_full_category_path'] = 'Tampilkan Jalur Kategori Lengkap Saat Mencari';


$lang['config_do_not_upload_images_to_ecommerce'] = 'JANGAN unggah gambar ke E-Commerce';

$lang['config_woo_enable_html_desc'] = 'Aktifkan HTML untuk deskripsi';

$lang['config_use_rtl_barcode_library'] = 'Gunakan perpustakaan barcode RTL';
$lang['config_default_new_customer_to_current_location'] = 'Default pelanggan baru ke lokasi saat ini';
$lang['config_week_start_day'] = 'Hari Mulai Minggu';
$lang['config_scan_and_set_sales'] = 'Pilih Kuantitas Setelah Menambahkan Item dalam Penjualan';
$lang['config_scan_and_set_recv'] = 'Pilih Kuantitas Setelah Menambahkan Item dalam Penerimaan';
$lang['config_edit_sale_web_hook'] = 'Edit URL Hook Web Penjualan';
$lang['config_edit_recv_web_hook'] = 'Edit Menerima URL Web Hook';
$lang['config_hide_expire_dashboard'] = 'Sembunyikan Item yang Kedaluwarsa Di Dasbor';
$lang['config_hide_images_in_grid'] = 'Sembunyikan Gambar di Kotak';
$lang['config_taxes_summary_on_receipt'] = 'Tampilkan Ringkasan Kena Pajak dan Tidak Kena Pajak Pada Tanda Terima';
$lang['config_collapse_sales_ui_by_default'] = 'Tutup antarmuka Penjualan secara default';
$lang['config_collapse_recv_ui_by_default'] = 'Perkecil antarmuka penerima secara default';
$lang['config_enable_customer_quick_add'] = 'Aktifkan Tambah Cepat Pelanggan';
$lang['config_uppercase_receipts'] = 'Teks Penerimaan Huruf Besar';

$lang['config_edit_customer_web_hook'] = 'Edit URL Hook Web Pelanggan';
$lang['config_show_selling_price_on_recv'] = 'Tampilkan Harga Jual Saat Menerima Kwitansi';

$lang['config_hide_email_on_receipts'] = 'Sembunyikan E-Mail On Kwitansi';



$lang['config_hide_available_giftcards'] = 'Sembunyikan kartu hadiah yang tersedia di register penjualan';


$lang['config_enable_supplier_quick_add'] = 'Aktifkan Tambah Cepat Pemasok';
$lang['config_sync_inventory_from_location'] = 'Sinkronkan Inventaris Dari Lokasi';
$lang['config_taxes_summary_details_on_receipt'] = 'Tampilkan Rincian Pajak Saat Menerima';
$lang['config_disable_recv_number_on_barcode'] = 'Nonaktifkan Menerima Nomor Pada Barcode';
$lang['config_tax_jar_location'] = 'Gunakan API Lokasi TaxJar untuk menarik pajak';
$lang['config_disable_loyalty_by_default'] = 'Nonaktifkan Loyalitas Secara Default';

$lang['config_ecommerce_only_sync_completed_orders'] = 'Hanya Sinkronkan Pesanan E-Commerce yang Selesai';

$lang['config_damaged_reasons'] = 'Alasan Rusak';

$lang['config_display_item_name_first_for_variation_name'] = 'Tampilkan nama item terlebih dahulu untuk variasi pada barcode';


$lang['config_do_not_allow_sales_with_zero_value'] = 'JANGAN Bolehkan Penjualan Dengan Nilai Nol';

$lang['config_dont_recalculate_cost_price_when_unsuspending_estimates'] = 'Jangan menghitung ulang harga biaya saat perkiraan yang tidak digunakan';


$lang['config_show_signature_on_receiving_receipt'] = 'Tampilkan tanda tangan saat menerima tanda terima';

$lang['config_do_not_treat_service_items_as_virtual'] = 'JANGAN memperlakukan item layanan sebagai produk virtual dalam perdagangan woo';

$lang['config_hide_latest_updates_in_header'] = 'Sembunyikan Pembaruan Terbaru di Header';
$lang['config_prompt_amount_for_cash_sale'] = 'Jumlah Prompt Untuk Penjualan Tunai';
$lang['config_do_not_allow_items_to_go_out_of_stock_when_transfering'] = 'Jangan biarkan barang kehabisan stok saat mentransfer';
$lang['config_show_tags_on_fulfillment_sheet'] = 'Tampilkan Tag Item Pada Lembar Pemenuhan';
$lang['config_automatically_sms_receipt'] = 'Tanda terima SMS otomatis';
$lang['config_items_per_search_suggestions'] = 'Jumlah item untuk saran pencarian';

$lang['config_shopify_settings_info'] = 'Pengaturan Shopify';
$lang['config_shopify_shop'] = 'URL Toko Shopify';
$lang['config_connect_to_shopify'] = 'Hubungkan ke Shopify';
$lang['config_connect_to_shopify_reconnect'] = 'Hubungkan Kembali ke Shopify';
$lang['config_connected_to_shopify'] = 'Anda terhubung ke Shopify';
$lang['config_disconnect_to_shopify'] = 'Putuskan Hubungan Dari Shopify';

$lang['config_offline_mode'] = 'Aktifkan Mode Offline';
$lang['config_reset_offline_data'] = 'Setel Ulang Data Offline';



$lang['config_remove_quantity_suspending'] = 'Hapus Kuantitas Saat Menangguhkan';
$lang['config_auto_sync_offline_sales'] = 'Auto Sync Penjualan Offline Saat Kembali Online';

$lang['config_shopify_billing_terms'] = 'Aktifkan penagihan - uji coba 14 hari, lalu {SHOPIFY_PRICE} per bulan';
$lang['config_shopfiy_billing_failed'] = 'Penagihan Shopify Gagal';
$lang['config_cancel_shopify'] = 'Batalkan Penagihan Shopify';
$lang['config_confirm_cancel_shopify'] = 'Anda yakin ingin membatalkan shopify?';
$lang['config_step_1'] = 'Langkah 1';
$lang['config_step_2'] = 'Langkah 2';
$lang['config_step_3'] = 'LANGKAH 3';
$lang['config_step_4'] = 'LANGKAH 4';
$lang['config_install_shopify_app'] = 'Instal aplikasi Shopify';
$lang['config_connect_billing'] = 'Hubungkan Penagihan';
$lang['config_choose_sync_options'] = 'Pilih Opsi Sinkronisasi';
$lang['config_ecommerce_sync_running'] = 'Sinkronisasi E-Commerce sekarang berjalan di latar belakang. Anda dapat memeriksa statusnya di Store Config.';
$lang['config_show_total_on_fulfillment'] = 'Tunjukkan Total Pada Lembar Pemenuhan';
$lang['config_connect_shopify_in_app_store'] = 'Anda tidak terhubung ke Shopify. Anda dapat terhubung ke Shopify di App Store';
$lang['config_override_signature_text'] = 'Timpa Teks Tanda Tangan';

$lang['config_delivery_color_based_on'] = 'Warna Pengiriman Berdasarkan';
$lang['config_delivery_color_based_on_status'] = 'Status';
$lang['config_delivery_color_based_on_category'] = 'Kategori';


$lang['config_update_cost_price_on_transfer'] = 'Perbarui Biaya Harga Saat Transfer';



$lang['config_tip_preset_zero'] = 'Tip jumlah preset 0%';



$lang['config_layaway_statement_message'] = 'Pesan Pernyataan Layaway';


$lang['config_show_person_id_on_receipt'] = 'Tunjukkan ID Orang pada tanda terima';




$lang['config_import_ecommerce_orders_suspended'] = 'Impor Pesanan E-Niaga Ditangguhkan';

$lang['config_show_images_on_receipt'] = 'Tunjukkan Gambar di Tanda Terima';

$lang['config_disabled_fixed_discounts'] = 'Nonaktifkan Seluruh Diskon Tetap Pada Antarmuka Penjualan';



$lang['config_always_put_last_added_item_on_top_of_cart'] = 'Selalu letakkan item yang terakhir ditambahkan di atas troli';



$lang['config_show_giftcards_even_if_0_balance'] = 'Tunjukkan kartu Hadiah meskipun saldo nol';

$lang['config_scale_5'] = 'Kode Batang Tertanam Berat';

$lang['config_disable_modules'] = 'Nonaktifkan Modul';

$lang['config_hide_description_on_suspended_sales'] = 'Sembunyikan Deskripsi Item Pada Penjualan yang Ditangguhkan';
$lang['config_override_symbol_non_taxable'] = 'Ganti Simbol untuk Tidak Kena Pajak';



$lang['config_hide_categories_sales_grid'] = 'Sembunyikan Kategori di Kotak Penjualan';
$lang['config_hide_tags_sales_grid'] = 'Sembunyikan Tag di Kotak Penjualan';
$lang['config_hide_favorites_sales_grid'] = 'Sembunyikan Favorit di Kotak Penjualan';
$lang['config_hide_categories_receivings_grid'] = 'Sembunyikan Kategori di Kotak Penerimaan';
$lang['config_hide_tags_receivings_grid'] = 'Sembunyikan Tag di Kotak Penerimaan';
$lang['config_hide_suppliers_receivings_grid'] = 'Sembunyikan Pemasok di Kotak Penerimaan';
$lang['config_hide_favorites_receivings_grid'] = 'Sembunyikan Favorit di Kotak Penerimaan';
$lang['config_hide_suppliers_sales_grid'] = 'Sembunyikan Pemasok di Kotak Penjualan';

$lang['config_offline_mode_sync_period'] = 'Waktu Siklus Sinkronisasi Mode Offline (Jam)';



$lang['config_receipt_download_filename_prefix'] = 'Tanda Terima Unduhan Nama File Awalan';
$lang['config_remove_employee_lastname_from_receipt'] = 'Hapus Nama Belakang Karyawan Dari Tanda Terima';
$lang['config_override_symbol_taxes_summary_on_receipt'] = 'OVERRIDE SIMBOL UNTUK MENUNJUKKAN RINGKASAN KENA PAJAK DAN TIDAK PAJAK PADA PENERIMAAN';

$lang['config_show_images_on_receipt_width_percent'] = 'Lebar Maksimal Gambar Barang Pada Tanda Terima (Persentase)';

$lang['config_link_to_receipt'] = 'Tautan ke Tanda Terima';
$lang['config_sale_summary_info'] = 'Info Ringkasan Penjualan';
$lang['config_qr_code_format'] = 'Format Kode QR';
$lang['config_override_symbol_taxable_summary'] = 'Ganti Simbol untuk Ringkasan Kena Pajak Pada Tanda Terima';
$lang['config_override_symbol_non_taxable_summary'] = 'Ganti Simbol untuk Ringkasan Tidak Kena Pajak Pada Tanda Terima';
$lang['config_allow_drag_drop_recv'] = 'Izinkan Susun Ulang di layar penerima';
$lang['config_allow_drag_drop_sale'] = 'Izinkan Pesan Ulang di layar obral';
$lang['config_disable_signature_capture_on_terminal_for_phppos_credit_card_processing'] = 'Nonaktifkan Pengambilan Tanda Tangan untuk Pemrosesan Kartu Kredit POS PHP';


$lang['config_capture_internal_notes_during_receiving'] = 'Tangkap Catatan Internal Selama Menerima';


$lang['config_default_employee_for_deliveries'] = 'Karyawan Default untuk Pengiriman';

$lang['config_disable_verification_for_qr_codes'] = 'Nonaktifkan verifikasi untuk kode QR (Tidak memerlukan nomor telepon atau email)';


$lang['config_disable_variation_popup_in_receivings'] = 'Nonaktifkan Popup Variasi dalam Menerima';

$lang['config_saudi_arabia_digital_receipt'] = 'Tanda Terima Digital Arab Saudi';

$lang['config_scale_6'] = 'Kode Batang Tertanam Berat EAN 13';

$lang['config_hide_location_name_on_receipt'] = 'Sembunyikan Nama Lokasi Di Tanda Terima';
$lang['config_disable_discount_by_percentage'] = 'Nonaktifkan Seluruh Diskon Berdasarkan Persentase pada Antarmuka Penjualan';

$lang['Delivery ID'] = 'ID pengiriman';
$lang['config_scale_7'] = 'Berat Kode Batang Tertanam EAN 13 4 digit harga';


$lang['config_use_main_image_as_default_image_in_e_commerce'] = 'Gunakan Gambar Utama Sebagai Gambar Default Di E-Commerce';

$lang['config_disable_discounts_percentage_per_line_item'] = 'Nonaktifkan Persentase Diskon Per Item Baris';
$lang['config_send_sms_via_whatsapp'] = 'Kirim SMS Via Whatsapp';
$lang['config_additional_appointment_note'] = 'Catatan Janji Temu Tambahan';
$lang['config_keywords_help_text'] = 'Di sini, Anda dapat menggunakan beberapa kata kunci.';

$lang['config_number_of_decimals_displayed_on_sales_interface'] = 'Jumlah Desimal yang Ditampilkan pada Antarmuka Penjualan';

$lang['shopify_private_key'] = 'Kunci Pribadi Shopify';
$lang['shopify_public_key'] = 'Kunci Publik Shopify';
$lang['config_reconnect_to_shopify'] = 'Hubungkan kembali ke Shopify';

$lang['config_customer_allow_partial_match'] = 'Izinkan Pencocokan Sebagian Untuk Penelusuran Pelanggan';
?>