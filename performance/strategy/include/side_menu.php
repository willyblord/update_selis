<div class="card">
    <ul class="list-group">

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE','ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="strategy-dashboard">
            <li class="list-group-item side-menu-list"> <i class="bx bx-money me-2"></i> Dashboard</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE','ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="strategy-3-year">
            <li class="list-group-item side-menu-list"> <i class="bx bx-money me-2"></i> 3-Year Strategies</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE','ADMIN_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="annual-business-plans">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> Annual business plans</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE','ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="annual-group-strategies">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> COO</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE','ADMIN_ROLE', 'GMD_ROLE', 'COO_ROLE'];
            $requiredPermissions = ['view_report_strategy'];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="strategy-report">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> Report</li>
        </a>
        <?php } ?>

    </ul>
</div>