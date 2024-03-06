
$(document).ready(function () {

    $('#reset').click(function () {
        $("#country").val('').trigger('change');
        $("#department").val('').trigger('change');
        $("#budget_line").val('').trigger('change');
        $("#statusCheck").val('').trigger('change');
        $("#status").val('').trigger('change');
        $("#created_by").val('').trigger('change');
        $("#DateFrom").val('').trigger('change');
        $("#DateTo").val('').trigger('change');
        $('#extract_div').empty();

        var table = $('#data_table').DataTable();
        table.clear().destroy();
    });

    //LOAD POPULATE PROPERTIES
    $.ajax({
        url: "api/list-cash-report-country",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res.data;
            $('#country option').remove();
            if (res.all) {
                $('#country').append($('<option/>').attr("value", "").text("ALL"));
            }
            $.each(data, function (i, option) {
                $('#country').append($('<option/>').attr("value", option.id).text(option.country_name));
            });
        }
    })

    $.ajax({
        url: "api/list-cash-report-department",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res.data;
            $('#department option').remove();
            if (res.all) {
                $('#department').append($('<option/>').attr("value", "").text("ALL"));
            }
            $.each(data, function (i, option) {
                $('#department').append($('<option/>').attr("value", option.id).text(option.department_name));
            });
        }
    })

    $.ajax({
        url: "api/list-proposal-report-budget",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $('#budget_line option').remove();
            $('#budget_line').append($('<option/>').attr("value", "").text("ALL"));
            $.each(data, function (i, option) {
                $('#budget_line').append($('<option/>').attr("value", option.id).text(option.budget_line));
            });
        }
    })


    $.ajax({
        url: "api/list-proposal-status",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $('#status option').remove();
            $('#status').append($('<option/>').attr("value", "").text("ALL"));
            $.each(data, function (i, option) {
                $('#status').append($('<option/>').attr("value", option.status).text(option.status));
            });
        }
    })

    $.ajax({
        url: "api/list-proposal-report-proposedtby",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $('#created_by option').remove();
            $('#created_by').append($('<option/>').attr("value", "").text("ALL"));
            $.each(data, function (i, option) {
                $('#created_by').append($('<option/>').attr("value", option.id).text(option.created_by));
            });
        }
    })

    $.ajax({
        url: "api/list-cash-report-disbused",
        method: "GET",
        dataType: "json",
        success: function (res) {
            var data = res;
            $('#cashDisbBy option').remove();
            $('#cashDisbBy').append($('<option/>').attr("value", "").text("ALL"));
            $.each(data, function (i, option) {
                $('#cashDisbBy').append($('<option/>').attr("value", option.id).text(option.disbursedBy));
            });
        }
    })

    $(document).on('submit', '#filter_form', function (event) {
        event.preventDefault();

        // Get form values
        let country = $('#country').val();
        let department = $('#department').val();
        let budget_line = $('#budget_line').val();
        let statusCheck = $('#statusCheck').val();
        let status = $('#status').val();
        let created_by = $('#created_by').val();
        let DateFrom = $('#DateFrom').val();
        let DateTo = $('#DateTo').val();

        $('#extract_div').empty();
        $("<div class='row'>" +
            "<div class='col-sm-6 col-md-3'>" +
            "<div class='form-holder'>" +
            "<form method='post' id='myform' action='extract-pdf-proposal-reports'>" +
            "<input type='hidden' name='country' value='" + country + "' />" +
            "<input type='hidden' name='department' value='" + department + "' />" +
            "<input type='hidden' name='statusCheck' value='" + statusCheck + "' />" +
            "<input type='hidden' name='status' value='" + status + "' />" +
            "<input type='hidden' name='created_by' value='" + created_by + "' />" +
            "<input type='hidden' name='DateFrom' value='" + DateFrom + "' />" +
            "<input type='hidden' name='DateTo' value='" + DateTo + "' />" +
            "<button type='submit' name='viewPdf' class='btn btn-primary mb-3' id='extract'><i class='fas fa-download'></i> Extract PDF Report</button>" +
            "</form>" +
            "</div>" +
            "</div>" +

            "<div class='col-sm-6 col-md-3'>" +
            "<div class='form-holder'>" +
            "<form method='post' id='myform_2' action='extract-excel-proposal-reports'>" +
            "<input type='hidden' name='country' value='" + country + "' />" +
            "<input type='hidden' name='department' value='" + department + "' />" +
            "<input type='hidden' name='statusCheck' value='" + statusCheck + "' />" +
            "<input type='hidden' name='status' value='" + status + "' />" +
            "<input type='hidden' name='created_by' value='" + created_by + "' />" +
            "<input type='hidden' name='DateFrom' value='" + DateFrom + "' />" +
            "<input type='hidden' name='DateTo' value='" + DateTo + "' />" +
            "<button type='submit' name='exportExcel' class='btn btn-primary mb-3' id='extract'><i class='fas fa-download'></i> Extract Excel Report</button>" +
            "</form>" +
            "</div>" +
            "</div>" +

            "</div>").appendTo('#extract_div');
        // Initialize DataTable
        var dataTable = $('#data_table').DataTable({
            'oLanguage': {
                sProcessing: $("#loader").show(),
            },
            'processing': true,
            'serverSide': true,
            'stateSave': true,
            'serverMethod': 'post',
            'destroy': true,
            'ajax': {
                'url': 'api/get-proposal-report',
                'data': {
                    country: country,
                    department: department,
                    budget_line: budget_line,
                    statusCheck: statusCheck,
                    status: status,
                    created_by: created_by,
                    DateFrom: DateFrom,
                    DateTo: DateTo
                }
            },
            'columns': [
                // {
                //     data: 'id',
                //     render: function (data, type, full, meta) {
                //         return '<button type="button" id="' + data + '" class="btn btn-primary btn-sm view"><i class="fa fa-eye"></i></button>';
                //     }
                // },
                { data: 'refNo' },
                { data: 'subject' },
                { data: 'FTotal' },
                { data: 'budget_line' },
                { data: 'status' },
                { data: 'created_by' },
                { data: 'onbehalf_of' },
                { data: 'created_at' },
            ],
            'columnDefs': [
                {
                    targets: [0, 1, 2, 3, 4],
                    orderable: false
                },
            ],
            'lengthMenu': [[5, 10, 25, 500, -1], [5, 10, 25, 50, "All"]],
        });
    });



    // end
    $(document).on('click', '.view', function () {
        var id = $(this).attr("id");
        $.ajax({
            url: 'api/get-single-pettycash-request-' + id,
            method: "GET",
            dataType: "json",
            success: function (res) {
                if (res.success) {
                    $('#viewModal').modal('show');

                    $('.modal-title').text("View Request");
                    $('#view_refNo').html(res.data.refNo);
                    $('#view_visitDate').html(res.data.visitDate);
                    $('#view_caregory').html(res.data.category);
                    $('#view_budget_category').html(res.data.budget_category);

                    $('#view_providers').html(res.data.providers);
                    res.data.providers !== null && res.data.providers !== '' ? $('#display_providers').show() : $('#display_providers').hide();

                    $('#view_customers').html(res.data.customers);
                    res.data.customers !== null && res.data.customers !== '' ? $('#display_customers').show() : $('#display_customers').hide();

                    $('#view_transport').html(res.data.transport);
                    res.data.transport_Val !== null && res.data.transport_Val !== '0' ? $('#display_transport').show() : $('#display_transport').hide();


                    $('#view_accomodation').html(res.data.accomodation);
                    res.data.accomodation_Val !== null && res.data.accomodation_Val !== '0' ? $('#display_accomodation').show() : $('#display_accomodation').hide();

                    $('#view_meals').html(res.data.meals);
                    res.data.meals_Val !== null && res.data.meals_Val !== '0' ? $('#display_meals').show() : $('#display_meals').hide();

                    $('#view_otherExpenses').html(res.data.otherExpenses);

                    ((res.data.transport_Val !== null && res.data.transport_Val !== '0') ||
                        (res.data.accomodation_Val !== null && res.data.accomodation_Val !== '0') ||
                        (res.data.meals_Val !== null && res.data.meals_Val !== '0'))
                        ? $('#other_expenses_to_amount').html('Other Expenses')
                        : $('#other_expenses_to_amount').html('Amount');

                    $('#view_afterClearance').html(res.data.afterClearance);
                    $('#view_requester_charges').html(res.data.requester_charges);
                    $('#view_charges').html(res.data.charges);
                    $('#view_totalAmount').html(res.data.totalAmount);

                    $('#view_disbursed').html(res.data.partiallyDisbursed);
                    res.data.partiallyDisbursed_Val !== null && res.data.partiallyDisbursed_Val !== '0' ? $('#display_disbursed').show() : $('#display_disbursed').hide();
                    $('#view_remaining').html(res.data.partiallyRemaining);
                    res.data.partiallyRemaining_Val !== null && res.data.partiallyRemaining_Val !== '0' ? $('#display_remaining').show() : $('#display_remaining').hide();
                    $('#view_air_departure_date').html(res.data.air_departure_date);
                    res.data.air_departure_date !== null && res.data.air_departure_date !== '' ? $('#display_departure').show() : $('#display_departure').hide();

                    $('#view_air_return_date').html(res.data.air_return_date);
                    res.data.air_return_date !== null && res.data.air_return_date !== '' ? $('#display_return').show() : $('#display_return').hide();

                    $('#view_checkin').html(res.data.checkin_date);
                    res.data.checkin_date !== null && res.data.checkin_date !== '' ? $('#display_checkin').show() : $('#display_checkin').hide();

                    $('#view_checkout').html(res.data.checkout_date);
                    res.data.checkout_date !== null && res.data.checkout_date !== '' ? $('#display_checkout').show() : $('#display_checkout').hide();

                    $('#view_phone').html(res.data.phone);
                    res.data.phone !== null && res.data.phone !== '' ? $('#display_phone').show() : $('#display_phone').hide();

                    $('#view_cheque').html(res.data.cheque_number);
                    res.data.cheque_number !== null && res.data.cheque_number !== '' ? $('#display_cheque').show() : $('#display_cheque').hide();

                    $('#view_bank_name').html(res.data.bank_name);
                    res.data.bank_name !== null && res.data.bank_name !== '' ? $('#display_bank').show() : $('#display_bank').hide();

                    $('#view_status').html(res.data.status);
                    $('#view_description').html(res.data.description);
                    $('#view_requestBy').html(res.data.requestBy);

                    $('#view_requestDate').html(res.data.requestDate);
                    $('#view_hodDate').html(res.data.hodDate);
                    $('#view_hodApprove').html(res.data.hodApprove);
                    $('#view_financeDate').html(res.data.financeDate);
                    $('#view_financeApprove').html(res.data.financeApprove);
                    $('#view_disburseDate').html(res.data.financeReleaseDate);
                    $('#view_disbursedBy').html(res.data.financeRelease);
                    $('#view_managerDate').html(res.data.managerDate);
                    $('#view_managerApprove').html(res.data.managerApprove);
                    $('#view_gmdDate').html(res.data.gmdDate);
                    $('#view_gmdApprove').html(res.data.gmdApprove);
                    $('#view_gmdComment').html(res.data.gmdComment);
                    $('#view_clearanceDate').html(res.data.clearanceDate);
                    $('#view_clearedBy').html(res.data.clearedBy);
                    $('#view_clearenceReason').html(res.data.clearanceDescription);
                    $('#view_clearSupervisorComment').html(res.data.clearSupervisorComment);
                    $('#view_suspendDate').html(res.data.suspendDate);
                    $('#view_suspendedBy').html(res.data.suspendedBy);
                    $('#view_ReturnDate').html(res.data.ReturnDate);
                    $('#view_ReturnedBy').html(res.data.ReturnedBy);
                    $('#view_returnReason').html(res.data.returnReason);
                    $('#view_rejectDate').html(res.data.rejectDate);
                    $('#view_rejectedBy').html(res.data.rejectedBy);
                    $('#view_rejectReason').html(res.data.rejectReason);

                    $('#view_receiptImage').empty();

                    var receiptName = res.data.receiptImage;

                    if (receiptName != null) {
                        var ext = receiptName.split('.').pop();

                        if (ext === 'pdf' || ext === 'PDF') {
                            $('#view_receiptImage').append($('<a href="funds/pettycash/uploads/receipts/' + receiptName + '" target="_blank">Click To View Document</a>'));
                        } else {
                            $('#view_receiptImage').append($('<img src="funds/pettycash/uploads/receipts/' + receiptName + '"  width="500">'));
                        }
                        receiptName !== null && receiptName !== '' ? $('#display_receipt').show() : $('#display_receipt').hide();
                    }

                    $('#view_additional_doc').empty();
                    $('#view_additional_doc').append($('<a href="funds/pettycash/uploads/' + res.data.additional_doc + '" target="_blank">Click To View Document</a>'));
                    res.data.additional_doc !== null && res.data.additional_doc !== '' ? $('#display_document').show() : $('#display_document').hide();

                    $('#single_extract_div').empty();

                    $("<div class='form-holder'>" +
                        "<form method='post' id='singleform' action='extract-single-valuation-report'>" +
                        "<input type='hidden' name='request_id' value='" + id + "' />" +
                        "<button type='submit' name='viewPdf' class='btn btn-primary mb-3' id='extract'><i class='fas fa-download'></i> Extract Report</button>" +
                        "</form>" +
                        "</div>").appendTo('#single_extract_div');
                    $('.modal-title').text("View Request");
                } else {
                    Swal.fire('Error.', res.message, 'error')
                }
            }
        })
    });

    $(document).on('submit', '#myform', function (event) {

        var wind = window.open('about:blank', '__foo', 'width=900,height=500,status=yes,resizable=yes,scrollbars=yes');
        $("#myform").attr("target", "__foo");

    });

    $(document).on('submit', '#singleform', function (event) {

        var wind = window.open('about:blank', '__foo', 'width=900,height=500,status=yes,resizable=yes,scrollbars=yes');
        $("#singleform").attr("target", "__foo");

    });

});