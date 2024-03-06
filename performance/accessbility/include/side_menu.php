<div class="card">
    <ul class="list-group">

        <a href="accessbility-dashboard">
            <li class="list-group-item"> <i class="bx bx-money me-2"></i> Dashboard</li>
        </a>

        <?php if ($row['can_be_super_user'] == 1 || $row['can_be_coo'] == 1) { ?>
        <a href="accessbility-downtimes">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> Downtimes </li>
        </a>
        <?php } ?>

    </ul>
</div>