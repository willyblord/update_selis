<div class="card">
    <ul class="list-group">

        <?php
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = [];
        $requiredModules = 'Funds';
        if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="seris-proposals">
            <li class="list-group-item"> <i class="bx bx-money me-2"></i> My Proposal</li>
        </a>

        <?php } ?>
        <?php
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = [];
        $requiredModules = 'Funds';
        if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="proposal-hod">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> HOD
                Operations </li>
        </a>
        <?php
        }
        ?>
        <?php
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = [];
        $requiredModules = 'Funds';
        if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="proposals-country-manager">
            <li class="list-group-item"><i class="bx bx-money me-2"></i>Country Manager</li>
        </a>
        <?php
        }
        ?>
        <?php
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = [];
        $requiredModules = 'Funds';
        if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="proposal-coo">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> COO</li>
        </a>
        <?php
        }
        ?>
        <?php
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = [];
        $requiredModules = 'Funds';
        if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="proposal-cof">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> CFO</li>
        </a>
        <?php
        }
        ?>
        <?php
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = [];
        $requiredModules = 'Funds';
        if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="proposal-gmd">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> GMD
                Operations</li>
        </a>
        <?php
        }
        ?>
        <?php
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = [];
        $requiredModules = 'Funds';
        if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="proposal-finance">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> Finance
                Operations</li>
        </a>
        <?php
        }
        ?>
        <?php
        $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE'];
        $requiredPermissions = [];
        $requiredModules = 'Funds';
        if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="proposal-report">
            <li class="list-group-item"><i class="bx bx-money me-2"></i> Report</li>
        </a>
        <?php
        }
        ?>

    </ul>
</div>
<style>
.card-header {
    background-color: #a5aba3;
    color: #ffffff;
}
</style>