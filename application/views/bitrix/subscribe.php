<?php $this->load->view("partial/header"); ?>

<?php 
	foreach(get_bitrix_css_files() as $css_file) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo base_url().$css_file['path'].'?'.ASSET_TIMESTAMP;?>" />
	<?php } ?>

	<?php foreach(get_bitrix_js_files() as $js_file) { ?>
		<script src="<?php echo base_url().$js_file['path'].'?'.ASSET_TIMESTAMP;?>" type="text/javascript" charset="UTF-8"></script>
	<?php } ?>	


<div class="row" id="form">
    <div class="bitrix-loader" id="grid-loader" style="display:none">
        <img src="<?= base_url('assets/assets/images/loading-screen.gif'); ?>" />
    </div>
    <div class="col-md-12">
        <?php
			echo form_open_multipart('bitrix/save/', array('id' => 'subscription_form', 'class' => 'form-horizontal'));
		?>
        <div class="panel panel-piluku">
            <div class="panel-heading">
                <h3 class="panel-title">
                    <i class="ion-edit"></i>
                    <?= lang('module_bitrix'); ?>
                </h3>
            </div>
            <div class="panel-body">
                <input type="hidden" name="parent_id" value="<?= ($parentSectionId > 0)?$parentSectionId:'' ?>" />
                <!-- ======Start====== -->
                <div class="form-group">
                    <div class="col-md-12">
                        <div class="col-md-5 col-sm-5 col-xs-5">
                            <input type="text" class="form-control ui-autocomplete-input serach_icon " name="search" id="search" value="" placeholder="Search Product" autocomplete="off" />
                        </div>
                        <div class="col-md-2 col-sm-2 col-xs-2 filter">
                            <div class="select_filter">
                                <select name="filters" class="select2-offscreen" id="filters" tabindex="-1">
                                    <option>Filter</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-4 col-xs-4 savBtn">
                            <div class="buttons-list items-buttons " style="float: right; margin:0;">
                                <div class="pull-right-btn">
                                    <button type="submit" class="btn btn-primary btn-lg hidden-sm hidden-xs sbtn "
                                        title="Sync">Sync</button>
                                    <a href="javascript:void(0)" id="cancel"
                                        class="btn btn-default btn-lg hidden-sm hidden-xs sbtn cancel_btn "
                                        title="Cancel">Cancel</a>
                                    <a href="javascript:void(0)" id="cache-clean"
                                        class="btn btn-default btn-lg hidden-sm hidden-xs sbtn cancel_btn"
                                        title="Purg Cache">Purge Cache</a>    
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <!-- =======END======= -->
                <div class="form-group">
                    <div class="col-sm-12 col-md-12 col-lg-12">
                        <div class="col-sm-3 col-md-3 col-lg-3">
                            <div id="page-wrap">
                                <?= $sectiontree ?>
                            </div>
                        </div>
                        <div class="col-sm-9 col-md-9 col-lg-9 border">
                            <div class="panel-body nopadding">
                                <div class="heading">
                                    <b style='color:#489ee7'><?= lang('bitrix_breadgrub_products'); ?></b> / <b
                                        id="grid_breadcrub_section"><?= lang('bitrix_category_default') ?></b>
                                    <span style="float:right">
                                        <b style="color: green;" id="bitrix_items_synced"><?= $itemssynced ?></b>
                                        <b><?= lang('bitrix_items_synced'); ?></b>
                                    </span>
                                </div>
                                <div class="row product">
                                    <div class="row trable">
                                        <div class="col-sm-1 col-md-1" data-sort-column="" class="leftmost">
                                            <input type="checkbox" id="select_all">
                                            <label class="labelbitrix" for="select_all"><span></span></label>
                                        </div>
                                        <div class="col-sm-4 col-md-4 accoridan_topmargin">
                                            <b><?= lang('category_product_name_label'); ?></b>
                                        </div>
                                        <div class="col-sm-2 col-md-2 accoridan_topmargin product_center">
                                            <?= lang('bitrix_product_id'); ?>
                                        </div>
                                        <div class="col-sm-2 col-md-2 accoridan_topmargin product_center">
                                            <?= lang('bitrix_unit_price'); ?>
                                        </div>
                                        <div class="col-sm-3 col-md-3 accoridan_topmargin product_center">
                                            <span>
                                                <b id="category_count"
                                                    style="color:skyblue">0</b><?= lang('bitrix_category_label'); ?> |
                                            </span>
                                            <span><b id="products_count"
                                                    style="color:skyblue">0</b><?= lang('bitrix_products_label'); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row product body-grid"></div>
                                <div class="row text-center">
                                    <div class="col-sm-12 col-md-12 col-lg-12">
                                        <div class="text-center grid-pagination">
                                            <div class="pagination pagination-top hidden-print text-center"
                                                id="bitrix-main-grid-pagination">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
</div>

<script>
var filter = {
    'SECTION_ID': 0
};
var currPage = 1;
var pageSize = 50;
var sectionsPagination = {
    'next': 0
};
var productsPagination = {
    'next': 0
};
var filters = {};
var childFilters = {};
var sectionNextRecord = {};

var mainGridLimit = 50;
var mainGridCpage = 1;
var levelcount = 1;

$(".toggle-child-sections").click(function() {
    $(this).parent().children('ul').toggleClass('hidden');
    $(this).parent().children('ul').toggleClass('child-show');
    $(this).find('i').toggleClass('ti-angle-up');
    $(this).find('i').toggleClass('ti-angle-right');
});

$(".items_checkbox").change(function() {
    var deleteSection = $("#deletesections").val();
    var deleteProducts = $("#deleteproducts").val();
    var checkDelete = $(this).data('delete') + "";
    if (!(typeof $(this).data('deletesections') === "undefined")) {
        if (!$(this).prop("checked")) {
            if (deleteSection != '') {
                deleteSection += "," + $(this).data('deletesections');
            } else {
                deleteSection += $(this).data('deletesections');
            }
            $("#deletesections").val(deleteSection);
        } else {
            deleteSection = $("#deletesections").val();
            y = deleteSection.split(',');
            yData = $.grep(y, function(value) {
                return value != checkDelete;
            });
            $("#deletesections").val(yData.join(","));
        }
    } else if (!(typeof $(this).data('deleteproducts') === "undefined")) {
        if (!$(this).prop("checked")) {
            if (deleteProducts != '') {
                deleteProducts += "," + $(this).data('deleteproducts');
            } else {
                deleteProducts += $(this).data('deleteproducts');
            }
            $("#deleteproducts").val(deleteProducts);
        } else {
            deleteProducts = $("#deleteproducts").val();
            y = deleteProducts.split(',');
            yData = $.grep(y, function(value) {
                return value != checkDelete;
            });
            $("#deleteproducts").val(yData.join(","));
        }
    }
});
$("#cache-clean").click(function() {
    forceClearCache();
});
$("#cancel").click(function() {
    $('#search').val('').trigger("change");
    $("select[name=filters]").val('0');
    $("#rootCategoryLabel").trigger("click");
});

$("#select_all").change(function() {
    var checkAll = $("#select_all").prop("checked");
    $('.bitrix_grid_checkbox').prop('checked', checkAll);
    if (checkAll == true) {
        $('.isaccordion').removeClass('unchecked_accordion');
        $('.isaccordion').addClass('checked_accordion');
    } else {
        $('.isaccordion').removeClass('checked_accordion');
        $('.isaccordion').addClass('unchecked_accordion');

    }
    updateFilterOption();
});

$(document).on('change', '.bitrix_grid_checkbox', function() {

    var checkAll = $(this).prop("checked");
    if (checkAll == true) {
        $(this).parent().parent('.isaccordion').removeClass('unchecked_accordion');
        $(this).parent().parent('.isaccordion').addClass('checked_accordion');
    } else {
        $(this).parent().parent('.isaccordion').removeClass('checked_accordion');
        $(this).parent().parent('.isaccordion').addClass('unchecked_accordion');
    }
    updateFilterOption();
});

$(".reload-grid").click(function() {
    childFilters = {};
    sectionNextRecord = {};
    $("select[name=filters]").val('');
    $("#search").val('');
    mainGridCpage = 1;
    $(".reload-grid").parent().removeClass('section-tree-active');
    if (!$(this).hasClass('dark-heading')) {
        $(this).parent().addClass('section-tree-active');
    }
    filter['SECTION_ID'] = $(this).data('sectionid');
    levelcount = $(this).data('levelcount');
    filters = {
        'cpage': mainGridCpage,
        'mlimit': mainGridLimit,
        'filters': filter
    };
    $("#grid_breadcrub_section").html($(this).data('scetionname'));
    renderGridItems(filters, levelcount);
});

$("#search").keyup(function(e) {
    e.preventDefault();

    $("select[name=filters]").val('');
    
    mainGridCpage = 1;
    var code = e.key;
    
    filters = {
        'cpage': mainGridCpage,
        'mlimit': mainGridLimit,
        'filters': {
            'PRODUCT_ID': $(this).val()
        }
    };
    renderGridItems(filters, 0);
});

$('#subscription_form').on('keyup keypress', function(e) {
    var keyCode = e.keyCode || e.which;
    if (keyCode === 13) {
        e.preventDefault();
        return false;
    }
});

function renderGridItems(filtersData, levelcount = 0, appendNextProducts = false) {
    $("#grid-loader").show();
    filtersData['levelcount'] = levelcount;
    $.ajax({
        type: 'POST',
        url: '<?= site_url('bitrix/bitrixgridjson/') ?>',
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        data: JSON.stringify(filtersData),
        success: function(result) {
            $("#grid-loader").hide();
            var filterSyncedItem = (filtersData.filters.all_synced_items == "all_synced_items") ? '1':0;
            var childSectionId = (filtersData.section_child > 0) ? filtersData.section_child : 0;
            processBitrixResponseHtml(result, childSectionId, levelcount + 1, appendNextProducts,filterSyncedItem);
            updateFilterOption(childSectionId);
        }
    });
}

$('.unsubscribe_checkbox_div').click(function() {
    updateFilterOption();
});

function updateFilterOption(resetSelected = '0') {
    var total_checked_category = $('.checkboxCheckedCategory:checked').length;
    var total_checked_product = $('.checkboxCheckedProduct:checked').length;

    var selectedOpt0 = ($('#filters').val() == '0' && resetSelected == '0') ? 'selected="selected"' : '';
    var selectedOpt1 = ($('#filters').val() == '1' && resetSelected == '0') ? 'selected="selected"' : '';
    var selectedOpt2 = ($('#filters').val() == '2' && resetSelected == '0') ? 'selected="selected"' : '';
    var selectedOpt3 = ($('#filters').val() == '3' && resetSelected == '0') ? 'selected="selected"' : '';

    var selectFilter = '<option ' + selectedOpt0 + ' value="0">Filter</option><option ' + selectedOpt1 +
        ' class="border-bottom" value="1"><?= lang('all_synced_items') ?>('+$('#bitrix_items_synced').text()+')<option ' + selectedOpt2 +
        ' class="border-bottom"value="2">Checked Products (' +
        total_checked_product + ')</option><option ' + selectedOpt3 + ' value="3">Checked Categories (' +
        total_checked_category +
        ')</option>';
    $('#filters').select2('destroy');
    $('#filters').html(selectFilter);
    $("#filters").select2({
        // dropdownAutoWidth: true,
        minimumResultsForSearch: -1
    });

    var html = $(".select2-chosen").text();
    var filter = 'Filter By:';
    if (html != 'Filter') {
        $(".select2-chosen").text(filter + html);
    }
}

$('.select_filter').change(function() {
    var html = $(".select2-chosen").text();
    var filter = 'Filter By:';
    if (html != 'Filter') {
        $(".select2-chosen").text(filter + html);
    }

});

function processBitrixResponseHtml(items = '', childSectionId = 0, levelcount,  appendNextProducts = false, filterSyncedItem = 0) {
    var gridHtml = '';
    var noRecordMessage = "No Category/ Products to display.";

    if (childSectionId > 0) {
        noRecordMessage = "No Products to display.";
    }

    if (items.data.length > 0) {
        $.each(items.data, function(index, item) {
            if (item.is_section == 1) {
                gridHtml += renderSectionHtml(item, levelcount, childSectionId, filterSyncedItem);
            } else {
                gridHtml += renderProductHtml(item, levelcount, childSectionId, filterSyncedItem );
            }
        });
    } else {
        gridHtml += '<div class="row product"><div class="col-md-12 norecord"><p>' + noRecordMessage +
            '</p></div></div>';
    }

    if (childSectionId == 0) {
        $("#category_count").html(items.filters.totalSections);
        $("#products_count").html(items.filters.totalProducts);
        $('.body-grid').html(gridHtml);
        $('#bitrix-main-grid-pagination').html(generatePagination(items));

    } else {
        if (appendNextProducts) {
            var lastElement = $(document).find("#card_body_" + childSectionId).find('.row.product').last().attr('id');
            $("#collapsesection_" + childSectionId).find('.card-body').append(gridHtml);

            $("#collapsesection_" + childSectionId).animate({
                scrollTop: $(document).find("#" + lastElement).offset().top
            }, 2000);
        } else {
            $("#collapsesection_" + childSectionId).find('.card-body').html(gridHtml);
        }
        $(document).find('#card_body_' + childSectionId).attr('data-cpage', parseInt(items.filters.cpage) + 1);
        var totalPages = items.filters.total / items.filters.mlimit;
        totalPages = (totalPages > Math.ceil(totalPages)) ? totalPages += 1 : Math.ceil(totalPages);
        if (parseInt(items.filters.cpage) == parseInt(totalPages)) {
            sectionNextRecord['key_' + childSectionId] = 'noscroll';
        }
    }
}

function generatePagination(items) {
    var totalPages = items.filters.total / items.filters.mlimit;

    totalPages = (totalPages > Math.ceil(totalPages)) ? totalPages += 1 : Math.ceil(totalPages);
    var pgHtml = '';
    var nextLink = '';
    var nextPage = 0;
    var lastLink = '';
    for (var i = 1; i <= totalPages; i++) {
        if (i <= 3) {
            pgHtml += (items.filters.cpage == i) ? '<strong>' + i + '</strong>' :
                '<a onclick="mainGridPagination(this, ' + totalPages +
                ')" href="javascript:void(0);" data-cpage="' + i + '">' + i + '</a>';;
        } else if (i <= totalPages - 1) {
            if (nextPage == 1) {
                continue;
            }

            if (items.filters.cpage == i) {
                nextPage = 1;
                nextLink = '<strong id="pagination-next" onclick="mainGridPagination(this, ' + totalPages +
                    ')" href="javascript:void(0);" rel="next">&gt;</strong>';
            } else {
                nextLink = '<a id="pagination-next" onclick="mainGridPagination(this, ' + totalPages +
                    ')" href="javascript:void(0);" rel="next">&gt;</a>';
            }
        } else if (i == totalPages) {
            lastLink += (items.filters.cpage == totalPages) ? '<strong>Last ›</strong>' :
                '<a onclick="mainGridPagination(this, ' + totalPages +
                ')" href="javascript:void(0);" data-cpage="' + i + '">Last ›</a>';
        }
    }
    return pgHtml + nextLink + lastLink;
}

function mainGridPagination(element, totalPage) {
    if ($(element).attr('id') != 'pagination-next') {
        mainGridCpage = parseInt($(element).attr('data-cpage'));
    } else {
        mainGridCpage = mainGridCpage + 1;
    }
    if (mainGridCpage > totalPage) {
        mainGridCpage = totalPage;
    }
    if (parseInt($("select[name=filters]").val()) == 1) {
        filters = {
            'cpage': mainGridCpage,
            'mlimit': mainGridLimit,
            'filters': {
                'all_synced_items': 'all_synced_items'
            }
        };
    } else if ($("#search").val() != '') {
        filters = {
            'cpage': mainGridCpage,
            'mlimit': mainGridLimit,
            'filters': {
                'PRODUCT_ID': $("#search").val()
            }
        };
    } else {
        filters = {
            'cpage': mainGridCpage,
            'mlimit': mainGridLimit,
            'filters': filter
        };
    }
    renderGridItems(filters, levelcount);
}

function renderSectionHtml(item, levelcount = 0, childSectionId = 0, filterSyncedItem = 0) {
    var checkCheckboxCatergory = (childSectionId == 0) ? "checkboxCheckedCategory" : "";
    var checkedClass = (item.is_checked == 1) ? "checked_accordion" : "unchecked_accordion";
    var checkedCheckedAttr = (item.is_checked == 1) ? 'checked="checked"' : '';
    var checkboxId = "section_" + item.id;
    var itemName = "'" + item.name + "'";
    var subscribeClass = (item.is_checked == '1') ? 'subscribe' : ''
    var checkedInputCategory = (item.is_checked == '0') ? '<input ' + checkedCheckedAttr + ' value="' + item.id +
        '" name="section[]" type="checkbox" class="bitrix_grid_checkbox ' + checkCheckboxCatergory + '" id="checkbox_' +
        checkboxId + '" /><label class="labelbitrix" for="checkbox_' + checkboxId + '"><span></span></label>' : '';
    var unsubscribeLinkCategory = (item.is_checked == 1) ?
        '<div class="col-sm-2 col-md-2 top_margin product_center"><a href="javascript:void(0)" style="color:#d4d4d4" onclick="unSubscribeCategory(this,' +
        item.id + ',' + itemName + ')" ><b><?= lang('unsubscribe_category'); ?></b></a></div>' : '';
    if (typeof(childFilters['key_' + item.id]) == 'undefined') {
        childFilters['key_' + item.id] = {
            'cpage': mainGridCpage,
            'mlimit': mainGridLimit,
            'filter': {
                'SECTION_ID': item.id
            }
        };
    }
    var checkFilterSyncedItem = (filterSyncedItem == 0) ? '<i id="acco_icon_id_'+item.id+'" data-levelcount="' +
        levelcount + '" class="icon ti-angle-right" onclick="getSectionChildProducts(this, ' + item.id +
        ')" data-toggle="collapse" href="#collapse' + checkboxId +
        '" role="button" aria-expanded="false" aria-controls="collapse' + checkboxId +
        '"></i>' : "";
    var sectionTemplate = '<div class="row product ' + subscribeClass + ' ' + checkedClass +
        ' isaccordion total_checked_category category_row"><div class="col-sm-1 col-md-1 add_Checkbox" data-sort-column="" class="leftmost">' +
        checkedInputCategory + '</div><div class="col-sm-8 col-md-8 top_margin"><span><b>' + item.name +
        '</b></span></div>' + unsubscribeLinkCategory + '<p id="ptag">' + checkFilterSyncedItem + '</p><div class="collapse" id="collapse' + checkboxId +
        '"><div id="card_body_' + item.id +
        '" data-cpage="1" class="card card-body" onscroll="appendProductsOnScrollEnd(this,' + item.id +
        ')" style="margin-top: 33px;"></div></div></div>';
    return sectionTemplate;
}

function unSubscribeCategory(el, categoryId, name) {
    swal({
            title: "Are you sure?",
            text: "You want to Unsubscribe " + name,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Yes, I am sure!',
            cancelButtonText: "No, cancel it!",
            closeOnConfirm: false,
            closeOnCancel: true
        },
        function(isConfirm) {
            if (isConfirm) {
                $("#grid-loader").show();
                $.ajax({
                    type: 'POST',
                    url: '<?= site_url('bitrix/categoryDelete/') ?>',
                    dataType: "json",
                    data: JSON.stringify(categoryId),
                    success: function(result) {
                        if (result.status == 1) {
                            if ($('#acco_icon_id_'+categoryId).hasClass('.ti-angle-down')) {
                                $('#acco_icon_id_'+categoryId).trigger('click');
                            }                            
                            $('#card_body_'+categoryId).html('');                            
                            var checkboxId = "section_" + categoryId;
                            var categoryUnsubscribehtml = '<input  value="' + categoryId +
                                '" name="section[]" type="checkbox" class="bitrix_grid_checkbox checkboxCheckedCategory" id="checkbox_' +
                                checkboxId + '" /><label class="labelbitrix" for="checkbox_' +
                                checkboxId +
                                '"><span></span></label>';
                            $(el).parents('.category_row').find('.add_Checkbox').html(
                                categoryUnsubscribehtml);

                            $(el).parents('.isaccordion').removeClass('checked_accordion').addClass(
                                'unchecked_accordion').find('input[type="checkbox"]').removeAttr(
                                'checked');
                            $(el).remove();
                            $(el).parents('.isaccordion').find('.card.card-body').find('.unsubscribe')
                                .removeClass(
                                    'unsubscribe');
                            $(el).parents('.isaccordion').find('.card.card-body').find('a').parent()
                                .remove();
                            $('#bitrix_items_synced').text(result.syncedItem);
                            updateFilterOption();
                            swal("Unsubscribed!", "You are successfully Unsubscribe", "success");
                        }
                        $("#grid-loader").hide();
                    }
                });
            }
        });
        
}

function renderProductHtml(item, levelcount = 0, childSectionId = 0) {
    var checkboxId = "product_" + item.id;
    var checkedClass = (item.is_checked == 1) ? "unsubscribe" : "";
    var productValue = item.parent_id + "_" + item.id;
    var itemName = "'" + item.name + "'"
    var unSubscribePermission = "<?php echo $unsubscribe; ?>";
    var unsubscribeLink = (item.is_checked == 1 && unSubscribePermission == '1') ?
        '<div class="col-sm-2 col-md-2 top_margin product_center"><a href="javascript:void(0)" style="color:#d4d4d4" onclick="unSubscribe(this,' +
        item.id + ',' + item.parent_id + ',' + itemName + ')" ><b><?= lang('unsubscribe_category'); ?></b></a></div>' :
        '';
    var checkCheckboxproduct = (childSectionId == 0) ? "checkboxCheckedProduct" : "";

    var checkedInput = (item.is_checked == '0') ? '<input value="' + productValue +
        '" name="product[]" type="checkbox" class="bitrix_grid_checkbox ' + checkCheckboxproduct + ' " id="checkbox_' +
        checkboxId + '"/><label class="labelbitrix" for="checkbox_' + checkboxId + '"><span></span></label>' : '';
    var productImage = item.product_image;
    var productTemplate = '<div id="product_row_' + item.id +
        '" class="row product checked_product ' + checkedClass +
        '"><div class="col-sm-1 col-md-1 unsubscribe_checkbox_div" data-sort-column="" class="leftmost">' +
        checkedInput + '</div><div class="col-sm-4 col-md-4 top_margin"><span class="product_label">' + item.name +
        '</span></div><div class="col-sm-2 col-md-2 top_margin product_center"><span class="productId">' + item.id +
        '</span></div><div class="col-sm-2 col-md-2 top_margin product_center"><span>' + item.price +
        '</span></div><div class="col-sm-1 col-md-1 top_margin product_center"><img src="' + productImage +
        '" width="25" /></div>' + unsubscribeLink + '</div>';
    return productTemplate;
}

function unSubscribe(el, unsubscribeId, unsubscribePid, name) {
    swal({
            title: "Are you sure?",
            text: "You want to Unsubscribe " + name,
            type: "warning",
            showCancelButton: true,
            confirmButtonColor: '#DD6B55',
            confirmButtonText: 'Yes, I am sure!',
            cancelButtonText: "No, cancel it!",
            closeOnConfirm: false,
            closeOnCancel: true
        },
        function(isConfirm) {
            if (isConfirm) {
                var productValue = unsubscribePid + "_" + unsubscribeId;
                $("#grid-loader").show();
                $.ajax({
                    type: 'POST',
                    url: '<?= site_url('bitrix/delete/') ?>',
                    dataType: "json",
                    data: JSON.stringify(unsubscribeId),
                    success: function(result) {
                        if (result.status == 1) {
                            $('#product_row_' + unsubscribeId).removeClass('unsubscribe');

                            var unsubscribeHtml = '<input value="' + productValue +
                                '" name="product[]" type="checkbox" class="bitrix_grid_checkbox" id="checkbox_' +
                                'product_' + unsubscribeId +
                                '" /><label class="labelbitrix" for="checkbox_' +
                                'product_' + unsubscribeId + '"><span></span></label>';

                            $('#product_row_' + unsubscribeId).find('.unsubscribe_checkbox_div').html(
                                unsubscribeHtml);

                            $(el).remove();
                            $('#bitrix_items_synced').text($('#bitrix_items_synced').text() - 1);
                            updateFilterOption();
                            $("#grid-loader").hide();
                            swal("Unsubscribed!", "You are successfully Unsubscribe", "success");
                        }
                    }
                });
            }
        });
}

function getSectionChildProducts(el, sectionId = 0, appendNextProducts = false, cpage = 1) {
    if (!appendNextProducts) {
        $(el).toggleClass('ti-angle-down');
    }
    var callAjax = false;
    var applyFilter = {};
    if (appendNextProducts && cpage > 1) {
        childFilters['key_' + sectionId]['cpage'] = cpage;
        applyFilter = {
            'cpage': childFilters['key_' + sectionId]['cpage'],
            'mlimit': childFilters['key_' + sectionId]['mlimit'],
            'filters': childFilters['key_' + sectionId]['filter'],
            'section_child': sectionId
        };
        callAjax = true;
    } else if ($(el).parents('.isaccordion').find('.card-body').find('.row').length == 0) {
        if (typeof(childFilters['key_' + sectionId]) == 'undefined') {
            childFilters['key_' + sectionId] = {
                'cpage': $('#card_body_' + sectionId).data('cpage'),
                'mlimit': 50,
                'filter': {
                    'SECTION_ID': sectionId
                }
            };
        }
        applyFilter = {
            'cpage': childFilters['key_' + sectionId]['cpage'],
            'mlimit': childFilters['key_' + sectionId]['mlimit'],
            'filters': childFilters['key_' + sectionId]['filter'],
            'section_child': sectionId
        };
        callAjax = true;
    }

    if (callAjax == true) {
        renderGridItems(applyFilter, $(el).data('levelcount'), appendNextProducts);
    }
}

function checkCacheStatus() {
    $.ajax({
        type: 'GET',
        url: '<?= site_url('bitrix/checkCacheStatus/') ?>',
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        success: function(result) {
            if (result['reload_page'] == 1 || result['reload_page'] == '1') {
                location.reload(true);
                // window.location.href = '<?#= site_url('bitrix/index/') ?>';
            } 
        }
    });
}

function forceClearCache() {
    $.ajax({
        type: 'GET',
        url: '<?= site_url('bitrix/forceClearCache/') ?>',
        dataType: "json",
        contentType: "application/json; charset=utf-8",
        success: function(result) {
            if (result['reload_page'] == 1 || result['reload_page'] == '1') {
                location.reload(true);
            } 
        }
    });
}

$(document).ready(function() {
    filters = {
        'cpage': mainGridCpage,
        'mlimit': mainGridLimit,
        'filters': filter
    };
    checkCacheStatus();
    renderGridItems(filters, 0);
    $('.active-section').closest('.list-group-item').find('ul').removeClass('hidden');
    $('.active-section').closest('.list-group-item').find('ul').addClass('child-show');
    $('.active-section').closest('.list-group-item').find('ul').find('i').removeClass('ti-angle-up');
    $('.active-section').closest('.list-group-item').find('ul').find('i').addClass('ti-angle-right');

    $('.active-section').parent().find('ul').addClass('hidden');
    $('.active-section').parent().find('ul').removeClass('child-show');
    $('.active-section').parent().find('ul').find('i').addClass('ti-angle-up');
    $('.active-section').parent().find('ul').find('i').find('i').removeClass('ti-angle-right');
});

$(document).ready(function() {
    $("#filters").select2({
        dropdownAutoWidth: true,
        minimumResultsForSearch: -1
    });
});

var lastSelectedFilter = '0';
$("select[name=filters]").change(function() {
    $('.checkboxCheckedProduct:checked').parent().parent().show();
    $('.unsubscribe').show();
    $('.subscribe').show();
    $('.checkboxCheckedCategory:not(:checked)').parent().parent().show();
    $('.checkboxCheckedCategory:checked').parent().parent().show();
    if (parseInt($(this).val()) == 0 && parseInt(lastSelectedFilter) == 1) {
        lastSelectedFilter = '0';
        $('#cancel').trigger('click');
    }
    if ($(this).val() == 0) {
        $('.checkboxCheckedProduct:not(:checked)').parent().parent().show();
        $('.checkboxCheckedCategory:not(:checked)').parent().parent().show();
        $('.unsubscribe').show();
        $('.subscribe').show();
    } else if ($(this).val() == 1) {
        $("#search").val('');
        mainGridCpage = 1;
        filters = {
            'cpage': mainGridCpage,
            'mlimit': mainGridLimit,
            'filters': {
                'all_synced_items': 'all_synced_items'
            }
        };
        renderGridItems(filters, levelcount);
    } else if ($(this).val() == 2) {
        $('.checkboxCheckedProduct:not(:checked)').parent().parent().hide();
        $('.unsubscribe').hide();
        $('.checkboxCheckedCategory:not(:checked)').parent().parent().hide();
        $('.checkboxCheckedCategory:checked').parent().parent().hide();
        $('.subscribe').hide();
    } else if ($(this).val() == 3) {    
        $('.checkboxCheckedProduct:not(:checked)').parent().parent().hide();
        $('.checkboxCheckedCategory:not(:checked)').parent().parent().hide();
        $('.checkboxCheckedProduct:checked').parent().parent().hide();
        $('.unsubscribe').hide();
        $('.subscribe').hide();
    }
    lastSelectedFilter = $("select[name=filters]").val();
});

function appendProductsOnScrollEnd(ele, sectionId = 0) {
    if (sectionNextRecord['key_' + sectionId] != 'noscroll') {
        if ($(ele).scrollTop() + $(ele).innerHeight() == $(ele)[0].scrollHeight) {
            cpage = (typeof($(ele).data('cpage')) == 'undefined') ? 1 : $(ele).attr('data-cpage');
            if (typeof(sectionNextRecord['key_' + sectionId]) == 'undefined') {
                sectionNextRecord['key_' + sectionId] = 1;
                getSectionChildProducts(ele, sectionId, true, cpage);
            } else if (sectionNextRecord['key_' + sectionId] != cpage) {
                sectionNextRecord['key_' + sectionId] = cpage;
                getSectionChildProducts(ele, sectionId, true, cpage);
            }
        }
    }
}

$("#subscription_form").submit(function(){
    $("#grid-loader").show();
});
</script>
<?php $this->load->view("partial/footer"); ?>