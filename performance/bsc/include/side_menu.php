<div class="card">
    <ul class="list-group">

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="bsc-dashboard">
            <li class="list-group-item side-menu-list"> <i class="bx bx-money me-2"></i> Dashboard</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'USER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="annual-individual-bsc">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> Individual staff BSC</li>
        </a>
        <?php } ?>              

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'SECTION_LEADER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="annual-section-bsc">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> Section Level</li>
        </a>
        <?php } ?>        

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'UNIT_LEADER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="annual-unit-bsc">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> Unit Level</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'HOD_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="annual-departmental-bsc">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> Department Level</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'COUNTRY_MANAGER_ROLE', 'MAIN_FUNCTION_LEADER_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="annual-function-bsc">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> Country/Main Function Level</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="annual-gmd-bsc">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> GMD Level</li>
        </a>
        <?php } ?>

        <?php 
            $requiredRoles = ['SUPER_USER_ROLE', 'ADMIN_ROLE', 'GMD_ROLE', 'HR_ROLE'];
            $requiredPermissions = [];
            $requiredModules = 'Performance';
            
            if ($user->hasPermission($userDetails, $requiredRoles, $requiredPermissions, $requiredModules)) {
        ?>
        <a href="bsc-report">
            <li class="list-group-item side-menu-list"><i class="bx bx-money me-2"></i> Report</li>
        </a>
        <?php } ?>

    </ul>
</div>