
$(document).ready(function () {
    //LOAD POPULATE PROVIDERS
    $.ajax({
        url: "api/list-providers",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $.each(data, function (i, option) {
                $('#providers').append($('<option/>').attr("value", option.providerName).text(option.providerName));
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
                $('#budget_line').append($('<option/>').attr("value", option.id).text(option.department_name));
            });
        }
    })
    // load cash category


    //LOAD Users  
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


    $(document).on('click', '.activate', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to approve this Proposal?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {

                var id = $(this).attr("id");
                const submitdata = {
                    'operation': 'approvecoo',
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

    // reject
    $(document).on('click', '.reject', function () {
        var id = $(this).attr("id");
        $('#this_form').removeClass();
        $.ajax({
            url: 'api/get-single-proposals-' + id,
            method: "GET",
            data: {
                id: id
            },
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    $('#actionModal').modal('show');
                    $('#view_refNo').html(res.data.refNo);
                    $('#subject').html(res.data.subject);
                    $('#xamount').html(res.data.price);
                    $('#fproposal_date').html(res.data.proposal_date);
                    $('#view_budget_line').html(res.data.budget_line);
                    $('#view_introduction').html(res.data.introduction);
                    $('#objective').html(res.data.objective);
                    $('#view_supporting_doc').html(res.data.supporting_doc);
                    $('#view_status').html(res.data.status);
                    $('#pcreated_by').html(res.data.created_by);
                    $('#view_created_at').html(res.data.created_at);
                    $('#view_onbehalf_of').html(res.data.onbehalf_of);
                    $('#view_updated_by').html(res.data.updated_by);
                    $('#view_updated_at').html(res.data.updated_at);

                    $('#action_title').html('Rejection Reason');
                    $("#action_body").empty();
                    var action_body =
                        '<div class="row dynamic_row" id="row">' +
                        '<div class="col-sm-12 col-md-12">' +
                        '<div class="mb-3 mt-2">' +
                        '<textarea class="form-control" rows="4" name="rejectReason" id="rejectReason" required data-error="Reason is required"></textarea>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    $("#action_body").append($(action_body).hide().delay(100).fadeIn(300));

                    $('.modal-title').text(" Proposal Rejection");
                    $('#id3').val(id);
                    $('#action3').show();
                    $('#action3').val("Reject Request");
                    $('#operation3').val("reject");
                    $('#this_form').addClass("reject_form");
                } else {
                    Swal.fire('Error.', res.message, 'error')
                }
            }
        })
    });

    $(document).on('click', '.suspend', function () {
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure you want to Suspend this Proposal?',
            showDenyButton: false,
            showCancelButton: true,
            confirmButtonText: 'Yes'
        }).then((result) => {
            if (result.isConfirmed) {
                var id = $(this).attr("id");
                const submitdata = {
                    'operation': 'suspend',
                    'id': id
                }
                $.ajax({
                    type: "POST",
                    url: "api/proposals/action",
                    data: JSON.stringify(submitdata),
                    ContentType: "application/json",
                    beforeSend: function () {
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
            'url': 'api/get-all-proposals-finance'
        },
        'columns': [
            { data: 'refNo' },
            { data: 'subject' },
            { data: 'FTotal' },
            { data: 'budget_line' },
            { data: 'status' },
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


    $('#user_form .select2').select2({
        dropdownParent: $('.modals')
    });


    $('#add_button').click(function () {
        $('#proposal_form')[0].reset();
        $('.modal-title').text("Register Proposals");
        $('#action').val("Register");
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
    // end

    // get single 

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

    $(document).on('click', '.fina_disburse', function () {
        var id = $(this).attr("id");
        $('#this_form').removeClass();
        $.ajax({
            url: 'api/get-single-proposals-' + id,
            method: "GET",
            // data: {
            //     id: id
            // },
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    $('#actionModal').modal('show');
                    $('#prop_refNo').html(res.data.refNo);
                    $('#prop_subject').html(res.data.subject);
                    $('#prop_totalAmount').html(res.data.FTotal);
                    $('#prop_proposeddate').html(res.data.proposal_date);
                    $('#prop_proposed_by').html(res.data.created_by);
                    $('#action_title').html('Disbursement Details');
                    $("#action_body").empty();
                    var action_body =
                        '<div class="row dynamic_row" id="row">' +
                        '<div class="col-sm-12 col-md-12">' +
                        '<ul class="nav nav-tabs" role="tablist">' +
                        '<li class="nav-item">' +
                        '<a class="nav-link active" data-bs-toggle="tab" href="#home">Cash/Mobile Money</a>' +
                        '</li>' +
                        '<li class="nav-item">' +
                        '<a class="nav-link" data-bs-toggle="tab" href="#menu1">Cheque</a>' +
                        '</li>' +
                        '</ul>' +

                        '<div class="tab-content">' +
                        '<div id="home" class="container tab-pane active"><br>' +

                        '<form method="post" id="disburse_form" enctype="multipart/form-data">' +
                        '<p>You are about to disburse money for this request.</p>' +
                        '<div class="form-group">' +
                        '<label for="charges">Transfer Charges (M-Pesa, Mobile Money, ...)</label>' +
                        '<input type="number" min="0" class="form-control" name="charges" id="charges" value="0" required data-error="This field is required">' +
                        '</div>' +


                        '<input type="hidden" name="id" id="id4" />' +
                        '<input type="hidden" name="operation" id="operation4"/>' +
                        '<div class="spinner-border text-primary m-1" role="status" id="loader4" style="display: none">' +
                        '<span class="sr-only">Loading...</span>' +
                        '</div>' +
                        '<br>' +
                        '<input type="submit" name="action" id="action4" class="btn btn-primary" value="Submit" />' +
                        '</form>' +

                        '</div>' +
                        '<div id="menu1" class="container tab-pane fade"><br>' +
                        '<form method="post" id="disburse_cheque_form" class="" data-toggle="validator" enctype="multipart/form-data">' +
                        '<p>Please fill in cheque details.</p>' +

                        '<div class="form-group">' +
                        '<label for="bank_name">Bank Name</label>' +
                        '<input type="text" class="form-control" name="bank_name" id="bank_name" required data-error="This field is required">' +
                        '<div class="help-block with-errors"></div>' +
                        '</div>' +

                        '<div class="form-group">' +
                        '<label for="cheque_number">Cheque Number</label>' +
                        '<input type="number" min="0" class="form-control" name="cheque_number" id="cheque_number" required data-error="This field is required">' +
                        '<div class="help-block with-errors"></div>' +
                        '</div>' +

                        '<input type="hidden" name="id" id="id5" />' +
                        '<input type="hidden" name="operation" id="operation5"/>' +
                        '<div class="spinner-border text-primary m-1" role="status" id="loader5" style="display: none">' +
                        '<span class="sr-only">Loading...</span>' +
                        '</div>' +
                        '<br>' +
                        '<input type="submit" name="action_cheque" id="action5" class="btn btn-primary" value="Submit" />' +
                        '</form>' +
                        '</div>' +
                        '</div>' +
                        '</div>' +
                        '</div>';
                    $("#action_body").append($(action_body).hide().delay(100).fadeIn(300));
                    $('.modal-title').text("Proposal Disbursement");
                    $('#action3').hide();
                    $('#id4').val(id);
                    $('#id5').val(id);
                    $("#action4").show();
                    $("#action5").show();
                    $('#action4').val("Disburse");
                    $('#action5').val("Disburse Cheque");
                    $('#operation4').val("fina_disburse");
                    $('#operation5').val("fina_disburse_cheque");
                    $('#disburse_form').addClass("disburse_form");
                    $('#disburse_cheque_form').addClass("disburse_cheque_form");
                } else {
                    Swal.fire('Error.', res.message, 'error')
                }
            }
        })
    });
    $(document).on('click', '#action5', function () {
        event.preventDefault();

        var bank_name = $('#bank_name').val();
        var cheque_number = $('#cheque_number').val();

        const submitdata = {
            'bank_name': bank_name,
            'cheque_number': cheque_number,
            'operation': 'fina_disburse_cheque',
            'id': $('#id5').val()
        }
        $.ajax({
            type: "POST",
            url: "api/proposals/action",
            data: JSON.stringify(submitdata),
            ContentType: "application/json",
            beforeSend: function () {
                $("#loader5").show();
            },
            success: function (res) {
                if (res.success) {
                    $('#actionModal').modal('hide');
                    Swal.fire('Success.', res.message, 'success')
                } else {
                    Swal.fire('Error.', res.message, 'error')
                }

                $("#loader5").hide();
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
                $("#loader5").hide();
            },
        });
    });
    // $(document).on('submit', '#disburse_form', function(event) {
    $(document).on('click', '#action4', function () {
        event.preventDefault();

        var charges = $('#charges').val();

        if (charges !== "" && !$.isNumeric(charges)) {
            Swal.fire('Error.', 'Please Input Required Fields', 'error')
        } else {
            const submitdata = {
                'operation': 'fina_disburse',
                'charges': charges,
                'id': $('#id4').val()
            }
            $.ajax({
                type: "POST",
                url: "api/proposals/action",
                data: JSON.stringify(submitdata),
                ContentType: "application/json",
                beforeSend: function () {
                    $("#loader4").show();
                },
                success: function (res) {

                    if (res.success) {
                        $('#actionModal').modal('hide');
                        Swal.fire('Success.', res.message, 'success')
                    } else {
                        Swal.fire('Error.', res.message, 'error')
                    }

                    $("#loader4").hide();
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
                    $("#loader4").hide();
                },
            });
        }
    });

});