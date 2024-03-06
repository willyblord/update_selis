<div class="card">
    <ul class="list-group">

        <?php if ($row['can_be_super_user'] == 1 || $row['can_add_cash_requests'] == 1) { ?>
        <a href="pettycash-my-requests">
            <li class="list-group-item"> <i class="bx bx-money me-2"></i> My Requests</li>
        </a>
        <?php } ?>

        <?php if ($row['can_be_super_user'] == 1 || $row['can_be_cash_hod'] == 1) { ?>
        <a href="pettycash-hod">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> HOD
                Operations</li>
        </a>
        <?php } ?>

        <?php if ($row['can_be_super_user'] == 1 || $row['can_be_cash_coo'] == 1) { ?>
        <a href="pettycash-coo">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> COO / Country Manager</li>
        </a>
        <?php } ?>

        <?php if ($row['can_be_super_user'] == 1 || $row['can_be_cash_manager'] == 1) { ?>
        <a href="pettycash-gmd">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> GMD
                Operations</li>
        </a>
        <?php } ?>

        <?php if ($row['can_be_super_user'] == 1 || $row['can_be_cash_finance'] == 1) { ?>
        <a href="pettycash-finance">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> Finance
                Operations</li>
        </a>
        <a href="pettycash-budget">
            <li class="list-group-item"> <i class="bx bx-money me-2"></i> Funds Budgets</li>
        </a>
        <?php } ?>

        <?php if ($row['can_be_super_user'] == 1 || $row['can_view_cash_reports'] == 1) { ?>
        <a href="pettycash-report">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> Report</li>
        </a>
        <?php } ?>

    </ul>
</div>