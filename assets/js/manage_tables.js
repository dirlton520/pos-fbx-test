function checkbox_click(event) {
    do_email(enable_email.url);
    if ($(event.target).prop('checked')) {
        $(event.target).parent().parent().find("td").addClass('selected').css("backgroundColor", "");
    } else {
        $(event.target).parent().parent().find("td").removeClass('selected');
    }

    determine_checkbox_status();
}

function remove_all_paren_data(text) {
    return text.replace(/ *\([^)]*\)*/g, "");
}


function enable_search(suggest_url, confirm_search_message) {
    //Keep track of enable_email has been called
    if (!enable_search.enabled)
        enable_search.enabled = true;

    $("#search").focus();
    $('#search').click(function() {
        $(this).attr('value', '');
    });

    $("#search").autocomplete({
        source: suggest_url + "/suggest",
        delay: 500,
        autoFocus: false,
        minLength: 0,
        select: function(event, ui) {
            if (!ENABLE_QUICK_EDIT) {
                event.preventDefault();

                var people_pages = [SITE_URL + '/customers', SITE_URL + '/employees', SITE_URL + '/suppliers'];
                if ($.inArray(suggest_url, people_pages) != -1) {
                    $(this).val(decodeHtml(remove_all_paren_data(ui.item.label)));
                } else {
                    $(this).val(decodeHtml(ui.item.label));
                }
                do_search(true);
            } else {
                window.location.href = suggest_url + '/view/' + decodeHtml(ui.item.value) + '/2';
            }
        },
    }).data("ui-autocomplete")._renderItem = function(ul, item) {
        return $("<li class='customer-badge suggestions'></li>")
            .data("item.autocomplete", item)
            .append('<a href="' + suggest_url + '/view/' + item.value + '/2"  class="suggest-item"><div class="avatar">' +
                '<img src="' + item.avatar + '" alt="">' +
                '</div>' +
                '<div class="details">' +
                '<div class="name">' +
                item.label +
                '</div>' +
                '<span class="email">' +
                item.subtitle +
                '</span>' +
                '</div></a>')
            .appendTo(ul);
    };

    $('#search').bind('keypress', function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
            $('#search').autocomplete('close');
            $('#search').val($(this).val());
            $('#search_form').submit();
        }
    });

    $('#search_form').submit(function(event) {
        event.preventDefault();

        if (get_selected_values().length > 0) {
            bootbox.confirm(confirm_search_message, function(result) {
                if (result) {
                    do_search(true);
                }
            });
        } else {
            do_search(true);
        }
    });
}
enable_search.enabled = false;

function do_search(show_feedback, on_complete) {
    //If search is not enabled, don't do anything
    if (!enable_search.enabled)
        return;

    if (show_feedback)
        $('#spinner').show();
    $("#ajax-loader").show();

    $('#search_form').ajaxSubmit({
        success: function(response) {
            if (typeof on_complete == 'function')
                on_complete();
            $('#sortable_table tbody').html(response.manage_table);
            $("#manage_total_items").html(response.total_rows);
            $('.clear-block').removeClass('hidden');
            if (response.pagination == "") {
                $('.pagination').addClass('hidden');
            } else {
                $('.pagination').removeClass('hidden');
                $('.pagination').html(response.pagination);
            }
            $('#spinner').hide();
            $("#ajax-loader").hide();
            $("#select_all").prop('checked', false);
        },
        dataType: 'json'
    });

}

function enable_email(email_url) {
    //Keep track of enable_email has been called
    if (!enable_email.enabled)
        enable_email.enabled = true;

    //store url in function cache
    if (!enable_email.url) {
        enable_email.url = email_url;
    }

    $('#select_all, #sortable_table tbody :checkbox').click(checkbox_click);
}
enable_email.enabled = false;
enable_email.url = false;

function do_email(url) {
    //If email is not enabled, don't do anything
    if (!enable_email.enabled)
        return;

    $.post(url, { 'ids[]': get_selected_values() }, function(response) {
        $('#email').attr('href', response);
    });

}

function enable_checkboxes() {
    $(document).on('click', "#select_all, #sortable_table tbody :checkbox", checkbox_click);
}

function enable_delete(confirm_message, none_selected_message) {
    //Keep track of enable_delete has been called
    if (!enable_delete.enabled)
        enable_delete.enabled = true;

    $('#delete').click(function(event) {
        event.preventDefault();
        if ($("#sortable_table tbody :checkbox:checked").length > 0) {
            bootbox.confirm(confirm_message, function(result) {
                if (result) {
                    do_delete($("#delete").attr('href'));
                }
            });
        }
    });
}
enable_delete.enabled = false;

function do_delete(url) {
    //If delete is not enabled, don't do anything
    if (!enable_delete.enabled)
        return;

    var row_ids = get_selected_values();
    var selected_rows = get_selected_rows();
    $.post(url, { 'ids[]': row_ids }, function(response) {
        //delete was successful, remove checkbox rows
        if (response.success) {
            show_feedback('success', response.message, COMMON_SUCCESS);
            $(".manage-row-options").addClass("hidden");
            $(selected_rows).each(function(index, dom) {
                $(this).find("td").addClass({ backgroundColor: "#FF0000" }, 1200, "linear")
                    .end().animate({ opacity: 0 }, 1200, "linear", function() {
                        $(this).remove();

                    });
            });

            //update count
            $("#manage_total_items").html(response.total_rows);
        } else {
            show_feedback('error', response.message, COMMON_ERROR);
        }


    }, "json");
}

function enable_select_all() {
    //Keep track of enable_select_all has been called
    if (!enable_select_all.enabled)
        enable_select_all.enabled = true;


    $(document).on('click', ".btn-select-all", function(e) {
        e.preventDefault();

        if ($(this).hasClass('active')) {
            $("#select_all").prop('checked', false).trigger('change');
            $(this).toggleClass('active', false);
        } else {
            $("#select_all").prop('checked', true).trigger('change');
            $(this).toggleClass('active', true);
        }



        return false;
    });

    $(document).on('change', "#select_all", function(e) {
        if ($(this).prop('checked')) {
            $('.btn-select-all').toggleClass('active', true);
            $('#selectall').show('medium');
            $("#sortable_table tbody :checkbox").each(function() {
                $(this).prop('checked', true);
                $(this).parent().parent().find("td").addClass('selected').css("backgroundColor", "");

            });
        } else {
            $('.btn-select-all').toggleClass('active', false);
            $('#selectall').hide('medium');
            $('#selectnone').hide('medium');
            $("#sortable_table tbody :checkbox").each(function() {
                $(this).prop('checked', false);
                $(this).parent().parent().find("td").removeClass('selected');
            });
        }

        determine_checkbox_status();

    });

}
enable_select_all.enabled = false;

function enable_row_selection() {
    //Keep track of enable_row_selection has been called
    if (!enable_row_selection.enabled)
        enable_row_selection.enabled = true;

    $("#sortable_table tbody").on({
        mouseenter: function() {
            $(this).css("cursor", "pointer");
        },
        mouseleave: function() {
            if (!$(this).find("td").hasClass("selected")) {
                $(this).find("td").removeClass('over');
            }
        }
    }, "tr"); // descendant selector

    var $chkboxes_container = $('#sortable_table tbody tr');
    var lastChecked = null;

    $(document).on('click', "#sortable_table tbody tr", function row_click(event) {
        var checkbox = $(this).find(":checkbox");
        checkbox.prop('checked', !checkbox.prop('checked'));

        //event.preventDefault();
        if (!lastChecked) {
            lastChecked = this;

            do_email(enable_email.url);

            if (checkbox.prop('checked')) {
                $(this).find("td").addClass('selected').css("backgroundColor", "");
            } else {
                $(this).find("td").removeClass('selected').css("backgroundColor", "");
            }

            determine_checkbox_status();

            return;
        }

        if (event.shiftKey) {
            var start = $chkboxes_container.index(this);
            var end = $chkboxes_container.index(lastChecked);

            var $chkboxes_containers = $chkboxes_container.slice(Math.min(start, end), Math.max(start, end) + 1);
            $($chkboxes_containers).each(function() {
                $(this).find('input[type="checkbox"]').prop('checked', true);
                $(this).find("td").addClass('selected');
            });

            //$chkboxes_container.slice(Math.min(start, end), Math.max(start, end) + 1).find('input[type="checkbox"]').prop('checked', true);
        }

        lastChecked = this;

        do_email(enable_email.url);

        if (checkbox.prop('checked')) {
            $(this).find("td").addClass('selected').css("backgroundColor", "");
        } else {
            $(this).find("td").removeClass('selected').css("backgroundColor", "");
        }

        determine_checkbox_status();
    });
}
enable_row_selection.enabled = false;


function update_row(row_id, url) {
    $.post(url, { 'row_id': row_id }, function(response) {
        //Replace previous row
        var row_to_update = $("#sortable_table tbody tr :checkbox[value=" + row_id + "]").parent().parent();
        row_to_update.replaceWith(response);
        highlight_row(row_id);
    });
}


function highlight_row(checkbox_id) {
    var new_checkbox = $("#sortable_table tbody tr :checkbox[value=" + checkbox_id + "]");
    var new_row = new_checkbox.parent().parent();

    new_row.find("td").animate({ backgroundColor: "#e1ffdd" }, "slow", "linear")
        .animate({ backgroundColor: "#e1ffdd" }, 5000)
        .animate({ backgroundColor: "#e9e9e9" }, "slow", "linear");
}

function get_selected_values() {
    var selected_values = new Array();
    $("#sortable_table tbody :checkbox:checked").each(function() {
        selected_values.push($(this).val());
    });
    return selected_values;
}

function get_selected_rows() {
    var selected_rows = new Array();
    $("#sortable_table tbody :checkbox:checked").each(function() {
        selected_rows.push($(this).parent().parent());
    });
    return selected_rows;
}

function get_visible_checkbox_ids() {
    var row_ids = new Array();
    $("#sortable_table tbody :checkbox").each(function() {
        row_ids.push($(this).val());
    });
    return row_ids;
}

function determine_checkbox_status() {
    if ($("#sortable_table tbody :checkbox:checked").length > 0) {
        $(".manage-row-options").removeClass("hidden");
        $("#email").removeClass("disabled");
        $("#delete").removeClass("disabled");
        $("#generate_barcodes").removeClass("disabled");
        $("#generate_barcode_labels").removeClass("disabled");
        $("#bulk_edit").removeClass("disabled");
        $("#merge").removeClass("disabled");
    } else {
        $(".manage-row-options").addClass("hidden");
        $("#email").addClass("disabled");
        $("#delete").addClass("disabled");
        $("#generate_barcodes").addClass("disabled");
        $("#generate_barcode_labels").addClass("disabled");
        $("#bulk_edit").addClass("disabled");
        $("#merge").addClass("disabled");
    }
}

function enable_cleanup(confirm_message) {
    if (!enable_cleanup.enabled)
        enable_cleanup.enabled = true;

    $('#cleanup').click(function(event) {
        do_cleanup(event, confirm_message);
    });
}
enable_cleanup.enabled = false;

function do_cleanup(event, confirm_message) {
    event.preventDefault();

    if (!enable_cleanup.enabled)
        return;

    bootbox.confirm(confirm_message, function(result) {
        if (result) {
            $.post($('#cleanup').attr('href'), {}, function(response) {
                show_feedback('success', response.message, COMMON_SUCCESS);

            }, 'json');
        }
    });
}


function enable_sorting(sort_url) {
    if (!enable_sorting.enabled) {
        enable_sorting.enabled = true;
    }

    $(document).on('click', "#sortable_table tr th", function(event) {
        if ($(this).data('sort-column')) {
            $('#sortable_table tbody').html('<img src="assets/img/ajax-loader.gif"  width="16" height="16" />');

            if ($(this).hasClass('headerSortUp')) {
                do_sorting(sort_url, 0, $(this).data('sort-column'), "desc");
                $('#sortable_table tr th').removeClass('header headerSortUp ion-arrow-up-b').removeClass('header headerSortDown ion-arrow-down-b');
                $(this).removeClass('header headerSortUp ion-arrow-up-b').addClass('header headerSortDown ion-arrow-down-b');
            } else {
                do_sorting(sort_url, 0, $(this).data('sort-column'), "asc");
                $('#sortable_table tr th').removeClass('header headerSortUp ion-arrow-up-b').removeClass('header headerSortDown ion-arrow-down-b');
                $(this).removeClass('header headerSortDown ion-arrow-down-b').addClass('header headerSortUp ion-arrow-up-b');
            }
        }
    });

    $(document).on('click', ".pagination a", function(event) {
        event.preventDefault();
        $(".manage-row-options").addClass("hidden");
        var offset = !is_int($(this).attr('href').substring($(this).attr('href').lastIndexOf('/') + 1)) ? 0 : $(this).attr('href').substring($(this).attr('href').lastIndexOf('/') + 1);
        do_sorting($(this).attr('href'), offset);
    });

}

enable_sorting.enabled = false;

function do_sorting(sort_url, offset, order_col, order_dir) {
    var params = { "search": $("#search").val(), "offset": offset };


    if (typeof order_col !== 'undefined') {
        params['order_col'] = order_col;
    }

    if (typeof order_dir !== 'undefined') {
        params['order_dir'] = order_dir;
    }

    if ($("#category_id").length == 1 && !$("#category_id").prop('disabled')) {
        params['category_id'] = $("#category_id").val();
    }

    if ($("#fields").length == 1 && !$("#fields").prop('disabled')) {
        params['fields'] = $("#fields").val();
    }

    $.post(sort_url, params, function(response) {
        $('#sortable_table tbody').html(response.manage_table);
        $('.pagination').html(response.pagination);
    }, "json");
}

$(document).on('click', ".btn-clear-selection", function(event) {
    event.preventDefault();
    clearSelections();
});

function clearSelections() {
    $('#selectall').hide('medium');
    $('#selectnone').hide('medium');
    $("#sortable_table tbody :checkbox").each(function() {
        $(this).prop('checked', false);
        $(this).parent().parent().find("td").removeClass('selected');
    });

    $("#select_all").prop('checked', false);
    $(".manage-row-options").addClass("hidden");

}

$(document).ready(function() {
    $('.toggle_deleted').click(function(e) {
        e.preventDefault();
        $.get($(this).attr('href'), function() {
            window.location.reload();
        });
    });

    $(".clone_manage_table").click(function(e) {
        var $that = $(this);
        e.preventDefault();
        bootbox.confirm(CONFIRM_CLONE, function(result) {
            if (result) {
                window.location = $that.attr('href');
            }
        });
    });

});