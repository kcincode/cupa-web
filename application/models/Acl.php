<?php
class Model_Acl extends Zend_Acl
{

    /**
     * Sets up the Zend_Acl relationships
     */
    public function __construct()
    {
        // loop through all of the resources and create a resource in the ACL.
        $dir = realpath(APPLICATION_PATH . '/controllers');
        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if(is_file("$dir/$entry")) {
                    $resource = strtolower(str_replace('Controller.php', '', $entry));
                    $this->addResource(new Zend_Acl_Resource($resource));
                }
            }
        }

        $adminRole = null;

        $roleTable = new Model_DbTable_Role();
        // loops through all of the roles in the database.
        foreach($roleTable->fetchAllRoles('weight') as $role) {
            if($role->name == 'admin') {
                // get admin role
                $adminRole = $role;
            }

            // create a role object for each entry.
            if(isset($role->inherit) and $role->inherit) {
                $this->addRole(new Zend_Acl_Role($role->name), $role->inherit);
            } else {
                $this->addRole(new Zend_Acl_Role($role->name));
            }
        }

        $privilegesTable = new Model_DbTable_Privilege();

        // allow admins to view everything
        $this->allow($adminRole->name, null, null);

        // allow everyone to view error pages
        $this->allow(null, 'error', null);

        // loops through all of the privileges in the database for the rest of the permissions.
        foreach($privilegesTable->fetchAllPrivileges() as $privilege) {
            if($privilege['type'] == 'allow') {
                // allow the specific action to the resource for the role
                $this->allow($privilege['role'], $privilege['resource'], $privilege['action']);
            } else {
                // deny the specific action to the resource for the role
                $this->deny($privilege['role'], $privilege['resource'], $privilege['action']);
            }
        }
    }
}
