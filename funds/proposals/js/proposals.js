
$(document).ready(function () {
    $('#proposal_form .select2').select2({
        dropdownParent: $('.modals')
    });
    //LOAD POPULATE PROVIDERS 
    $.ajax({
        url: "api/list-providers",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $.each(data, function (i, option) {
                $('#providers').append($('<option/>').attr("value", option.id).text(option.providerName));
            });
        }
    })
    //LOAD POPULATE CUSTOMERS 
    $.ajax({
        url: "api/list-departments",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $.each(data, function (i, option) {
                $('#budget_line2').append($('<option/>').attr("value", option.id).text(option.department_name));
            });
        }
    })
    //LOAD Users
    // load proposal budget
    $.ajax({
        url: "api/list-cash-categories",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $.each(data, function (i, option) {
                $('#budget_line_category').append($('<option/>').attr("value", option.id).text(option.category));
            });
        }
    })

    // get users list
    $.ajax({
        url: "api/list-all-users",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $.each(data, function (i, option) {
                $('#onbehalf_of').append($('<option/>').attr("value", option.userId).text(option.name));
            });
        }
    })
    $(document).on('click', '.complete', function () {

        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to complete this Proposal?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {

                var id = $(this).attr("id");

                const submitdata = {
                    'operation': 'complete_req',
                    'id': id
                }
                $.ajax({
                    type: "POST",
                    url: "api/proposals/action",
                    data: JSON.stringify(submitdata),
                    ContentType: "application/json",
                    beforeSend: function () {//We add this before send to disable the button once we submit it so that we prevent the multiple click
                        $('#cover-spin').show()
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire('Success.', res.message, 'success')
                        } else {
                            Swal.fire('Error.', res.message, 'error')
                        }
                        dataTable.ajax.reload();
                        $('#cover-spin').hide()
                    }
                });


            } else if (result.isDenied) {
                Swal.fire('Changes are not saved', '', 'info')
            }
        });

    });

    var dataTable = $('#table_data').DataTable({
        'processing': true,
        'serverSide': true,
        'stateSave': true,
        'serverMethod': 'Post',
        'ajax': {
            'url': 'api/get-all-proposals'
        },
        'columns': [
            { data: 'refNo' },
            { data: 'subject' },
            { data: 'FTotal' },
            { data: 'budget_line' },
            { data: 'status' },
            { data: 'location'},
            { data: 'created_by' },
            { data: 'onbehalf_of' },
            { data: 'created_at' },
            { data: 'actions' },
        ],
        'columnDefs': [
            {
                "targets": [8],
                "orderable": false
            },
        ],
        'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]]
    });
    setInterval(function () {
        dataTable.ajax.reload(null, false);
    }, 30000);
    //  end of all proposals

    $('#add_button').click(function () {
        $('#proposal_form')[0].reset();
        $('.modal-title').text("Submit Proposals");
        $('#action').val("Submit");
        $('#operation').val("Add");
        $("#country").val('').trigger('change');
        $("#department").val('').trigger('change');
    });

    // create
    $(document).on('submit', '#proposal_form', function (event) {
        event.preventDefault();
        //post with ajax
        $.ajax({
            url: "api/create-proposals",
            method: 'POST',
            data: new FormData(this),
            contentType: false,
            processData: false,
            beforeSend: function () {
                $("#loader").show();
            },
            success: function (data) {
                if (data.success) {
                    $('#proposal_form')[0].reset();
                    $('#addModal').modal('hide');
                    Swal.fire('Success.', data.message, 'success')
                } else {
                    Swal.fire('Error.', data.message, 'error')
                }

                $("#loader").hide();
                dataTable.ajax.reload();
            },
            error: function (jqXHR, exception) {
                var msg = '';
                if (jqXHR.status === 0) {
                    msg = 'Not connect.\n Verify Network.';
                } else if (jqXHR.status == 404) {
                    msg = 'Requested page not found. [404]';
                } else if (jqXHR.status == 500) {
                    msg = 'Internal Server Error [500].';
                } else if (exception === 'parsererror') {
                    msg = 'Requested JSON parse failed.\n' + jqXHR.responseText;
                } else if (exception === 'timeout') {
                    msg = 'Time out error.';
                } else if (exception === 'abort') {
                    msg = 'Ajax request aborted.';
                } else {
                    msg = 'Uncaught Error.\n' + jqXHR.responseText;
                }
                Swal.fire('Error.', msg, 'error')
                $("#loader").hide();
            },

        });
    });


    $(document).on('click', '.resend', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to Resend this Proposal?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {

                var id = $(this).attr("id");
                const submitdata = {
                    'operation': 'myproposalResend',
                    'id': id
                }
                $.ajax({
                    type: "POST",
                    url: "api/proposals/action",
                    data: JSON.stringify(submitdata),
                    ContentType: "application/json",
                    beforeSend: function () {//We add this before send to disable the button once we submit it so that we prevent the multiple click
                        $('#cover-spin').show()
                    },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire('Success.', res.message, 'success')
                        } else {
                            Swal.fire('Error.', res.message, 'error')
                        }
                        dataTable.ajax.reload();
                        $('#cover-spin').hide()
                    }
                });

            } else if (result.isDenied) {
                Swal.fire('Changes are not saved', '', 'info')
            }
        });

    });

    // view section
    $(document).on('click', '.view', function () {
        var id = $(this).attr("id");
        $.ajax({
            url: 'api/get-single-proposals-' + id,
            method: "GET",
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    $('#viewModal').modal('show');
                    $('#view_refNo').html(res.data.refNo);
                    $('#view_subject').html(res.data.subject);
                    // propo
                    $('#view_country').html(res.data.country);
                    $('#view_department').html(res.data.department);
                    $('#view_proposal_date').html(res.data.proposal_date);
                    $('#view_budget_line').html(res.data.budget_line);
                    $('#view_introduction').html(res.data.introduction);
                    $('#view_objective').html(res.data.objective);
                    $('#view_supporting_doc').html(res.data.supporting_doc);
                    $('#view_status').html(res.data.status);
                    $('#view_FTotal').html(res.data.FTotal);
                    $('#view_reasonRevert').html(res.data.returnReason);
                    res.data.returnReason !== null && res.data.returnReason !== '' ? $('#displayr').show() : $('#displayr').hide();
                    $('#view_created_by').html(res.data.created_by);
                    $('#view_created_at').html(res.data.created_at);
                    $('#view_onbehalf_of').html(res.data.onbehalf_of);
                    $('#view_updated_by').html(res.data.updated_by);
                    $('#view_updated_at').html(res.data.updated_at);
                    $('#view_upproved_by').html(res.data.upproved_by);

                    $("#view_proposal_items").empty();
                    res.data.proposalItem_array.forEach(item => {
                        $("#view_proposal_items").append(`
                            <tr id="${item.proposalItem_array}">
                                <td>${item.item}</td>
                                <td>${item.quantity}</td>
                                <td>${item.price}</td>
                                <td>${item.total}</td>
                                <td>${item.supplier}</td>
                            </tr>
                        `);
                    });
                    // disply comment
                    $('#view_commentArray').empty();
                    res.data.commentArray.forEach(comm => {
                        $("#view_commentArray").append(`
                          <ul class="comment-section" id="${comm.commentId}">
                                <li class="comment user-comment">
                                    <div class="info">
                                        <a href="#">${comm.commentBy}</a>
                                        <span>${comm.date}</span>
                                    </div>
                                    <a class="avatar" href="#">
                                        <img src="assets/images/users/avatar.png" width="35" />
                                    </a>
                                    <p>${comm.comment}</p>
                                </li>
                            </ul>
                            `);
                            
                    });
                    // end comment
                    $('#view_additional_doc').empty();
                    $('#view_additional_doc').append($('<a href="' + res.data.additional_doc + '" target="_blank">Click To View Support Document</a>'));
                    res.data.additional_doc !== null && res.data.additional_doc !== '' ? $('#display_document').show() : $('#display_document').hide();
                    $('.modal-title').text("View Proposal");
                    $('#id').html(id);
                } else {
                    Swal.fire('Error.', res.message, 'error')
                }
            }
        })
    });


    // del
    $(document).on('click', '.cancel', function () {
        var id = $(this).attr("id");
        $('#this_form').removeClass();
        $.ajax({
            url: 'api/get-single-proposals-' + id,
            method: "GET",
            data: {
                // id: id
            },
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    $('#actionModal').modal('show');
                    $('#prop_refNo').html(res.data.refNo);
                    $('#prop_subject').html(res.data.subject);
                    $('#prop_totalAmount2').html(res.data.total);
                    $('#prop_proposeddate').html(res.data.proposal_date);
                    $('#action_title').html('Cancellation Reason');
                    $("#action_body").empty();
                    var action_body =
                        '<div class="row dynamic_row" id="row">' +
                        '<div class="col-sm-12 col-md-12">' +
                        '<div class="mb-3 mt-2">' +
                        '<textarea class="form-control" rows="4" name="cancelReason" id="cancelReason" required data-error="Reason is required"></textarea>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    $("#action_body").append($(action_body).hide().delay(100).fadeIn(300));

                    $('.modal-title').text(" Request Cancellation");
                    $('#id3').val(id);
                    $('#action3').show();
                    $('#action3').val("Cancel Request");
                    $('#operation3').val("cancel_req");
                    $('#this_form').addClass("cancel_form");
                } else {
                    Swal.fire('Error.', res.message, 'error')
                }
            }
        })
    });

    $(document).on('submit', '.cancel_form', function (event) {
        event.preventDefault();
        var cancelReason = $('#cancelReason').val();
        if (cancelReason !== null && cancelReason.trim() !== '') {
            const submitdata = {
                'operation': 'cancel_req',
                'cancelReason': cancelReason,
                'id': $('#id3').val()
            }
            $.ajax({
                type: "POST",
                url: "api/proposals/action",
                data: JSON.stringify(submitdata),
                ContentType: "application/json",
                beforeSend: function () {
                    $("#loader3").show();
                },
                success: function (data) {
                    if (data.success) {
                        $('#this_form')[0].reset();
                        $('#this_form').removeClass();
                        $("#action3").hide();
                        $('#actionModal').modal('hide');
                        Swal.fire('Success.', data.message, 'success')
                    } else {
                        Swal.fire('Error.', data.message, 'error')
                    }
                    $("#loader3").hide();
                    dataTable.ajax.reload();
                }
            });
        } else {
            Swal.fire('Error.', 'Please Input Reason', 'error')
        }
    });

    $(document).on('click', '.update', function () {
        var id = $(this).attr("id");
        console.log(id);
        $.ajax({
            url: 'api/get-single-proposals-' + id,
            method: "GET",
            dataType: "json",
            success: function (res) {
                console.log(res);
                if (res.success) {
                    $('#addModal').modal('show');
                    $('#id').val(res.data.id);
                    $('#refNo').val(res.data.refNo);
                    $('#subject').val(res.data.subject);
                    $('#quantity').val(res.data.quantity);
                    $('#price').val(res.data.price);
                    $('#total').val(res.data.total);
                    $('#supplier').val(res.data.supplier);
                    $('#FTotal').val(res.data.FTotal);
                    $('#country').val(res.data.country).trigger('change');
                    $('#proposal_date').val(res.data.proposal_date).trigger('change');
                    $('#budget_line').val(res.data.budget_line);
                    $('#introduction').val(res.data.introduction);
                    $('#objective').val(res.data.objective);
                    $('#supporting_doc').val(res.data.supporting_doc);
                    $('#onbehalf_of').val(res.data.onbehalf_of).trigger('change');

                    // Change event for the row
                    $('#row').on('change', function () {
                        $("#dynamicAddRemove").empty(row);
                    });
                    $('.modal-title').text("Edit Proposals");
                    $('#id2').val(id);
                    $('#action').val("Save Changes");
                    $('#operation').val("Edit");
                    $('.updateD').empty();
                    var iaddT = 1;
                    res.data.proposalItem_array.forEach(function (items, index) {
                        if (index == 0) {
                            $('#item').val(items.item).trigger('change');
                            $('#quantity').val(items.quantity).trigger('change');
                            $('#price').val(items.price).trigger('change');
                            $('#total').val(items.total).trigger('change');
                            $('#supplier').val(items.supplier).trigger('change');
                        }
                        if (index > 0) {
                            iaddT++;
                            var repeater_fields =
                                '<div class="row updateD" id="row' + iaddT + '" >' +
                                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                                '<label for="Supplier"></label>' +
                                '<input type="text" class="form-control item" value="' + items.item + '" name="item[]">' +
                                '</div>' +
                                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                                '<label for="Supplier"></label>' +
                                '<input type="number" class="form-control quantity" value="' + items.quantity + '" name="quantity[]">' +
                                '</div>' +
                                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                                '<label for="Supplier"></label>' +
                                '<input type="number" class="form-control price" value="' + items.price + '"  name="price[]">' +
                                '</div>' +
                                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                                '<label for="Supplier"></label>' +
                                '<input type="number" class="form-control total" value="' + items.total + '" name="total[]" readonly>' +
                                '</div>' +
                                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                                '<label for="Supplier"></label>' +
                                '<input class="form-control supplier" type="text" value="' + items.supplier + '" name="supplier[]">' +
                                '</div>' +
                                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                                '<label for="Supplier"></label>' +
                                '<button type="button" class="btn btn-outline-danger remove-input-field circle"><i class="fa fa-trash" aria-hidden="true"></i></button>' +
                                '</div>' +
                                '</div>';
                            $("#dynamicAddRemove").append($(repeater_fields).hide().delay(100).fadeIn(300));
                        }
                    });
                } else {
                    Swal.fire('Error.', res.message, 'error');
                }
            },
            error: function (jqXHR, exception) {
                var msg = '';
                if (jqXHR.status === 0) {
                    msg = 'Not connect.\n Verify Network.';
                } else if (jqXHR.status == 404) {
                    msg = 'Requested page not found. [404]';
                } else if (jqXHR.status == 500) {
                    msg = 'Internal Server Error [500].';
                } else if (exception === 'parsererror') {
                    msg = 'Requested JSON parse failed.\n' + jqXHR.responseText;
                } else if (exception === 'timeout') {
                    msg = 'Time out error.';
                } else if (exception === 'abort') {
                    msg = 'Ajax request aborted.';
                } else {
                    msg = 'Uncaught Error.\n' + jqXHR.responseText;
                }
                Swal.fire('Error.', msg, 'error');
            }
        });
    });


    $('#addModalitem').click(function () {
        $('#proposal_form_item')[0].reset();
        $('.modal-title').text("Proposal Items");
        $('#action').val("Register");
        $('#operation').val("Add");
        $("#country").val('').trigger('change');
        $("#department").val('').trigger('change');
    });
    $(document).ready(function () {
        var iaddT = 0;
        $("#repeatForm").click(function () {
            ++iaddT;
            $("#dynamicAddRemove").append(
                '<div class="row">' +
                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                '<label for=""></label>' +
                '<input type="text" placeholder="Enter Item" name="item[]" class="form-control">' +
                '</div > ' +
                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                '<label for=""></label>' +
                '<input type="number"  value="0" name="quantity[]" class="form-control quantity">' +
                '</div>' +

                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                '<label for="price"></label>' +
                '<input type="number" value="0" name="price[]" class="form-control price">' +
                '</div>' +
                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                '<label for=""></label>' +
                '<input type="number" value="0" name="total[]" class="form-control total" readonly>' +
                '</div>' +
                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                '<label for=""></label>' +
                '<input class="form-control"  type="text" name="supplier[]">' +
                '</div>' +
                '<div class="col-sm-2 mb-3 mb-sm-0">' +
                '<label for=""></label>' +
                '<button type="button" class="btn btn-outline-danger remove-input-field circle"><i class="fa fa-trash" aria-hidden="true"></i></button>' +
                '</div>' +
                '</div > '
            );
        });

        $(document).on('click', '.remove-input-field', function () {
            $(this).parents('.row').remove();
            calculateTotal();
        });
        // Calculate and update total on quantity or price change
        $('#dynamicAddRemove').on('input', '.quantity, .price', function () {
            const $row = $(this).closest(".row");
            const price = $('.price', $row).val();
            const quantity = $('.quantity', $row).val();
            $('.total', $row).val(price * quantity);
            calculateTotal();
        });
        // grand total
        function calculateTotal() {
            let sum = 0;
            $('.total').each(function () {
                sum += parseFloat($(this).val());
            });
            $('.FTotal').val(sum);
        }
    });


});