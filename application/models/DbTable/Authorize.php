<?php

class Model_DbTable_Authorize extends Zend_Db_Table
{
    protected $_name = 'authorize';
    protected $_primary = 'id';
    protected $_roles = array();
    protected $_userId = null;

    public function __construct($userId)
    {
        $userRoleTable = new Model_DbTable_UserRole();
        $this->_userId = $userId;

        $roles = array();
        foreach($userRoleTable->fetchRolesData($userId) as $role) {
            if(empty($role->page_id)) {
                $roles[] = $role->role;
            }
        }

        $this->_roles = $roles;

        parent::__construct();
    }

    public function isAuthorized($routeName, $data)
    {
        $select = $this->select()
                       ->where('route_name = ?', $routeName);

        $result = $this->fetchRow($select);
        if($result) {
            // if auth function and args are null then no auth required
            if(is_null($result->function) && is_null($result->args)) {
                return true;
            }

            if(empty($this->_userId)) {
                return false;
            }

            if(is_null($result->args)) {
                return $this->{$result->function}($this->_userId);
            } else {
                $args = array();
                $args[] = $this->_userId;
                foreach(explode(',', $result->args) as $arg) {
                    $args[] = $data[$arg];
                }

                return call_user_func_array(array($this, $result->function), $args);
            }
        }

        return false;
    }

    private function manage($userId)
    {
        if(in_array('admin', $this->_roles) || in_array('manager', $this->_roles)) {
            return true;
        }

        return false;
    }

    private function canEditPage($userId, $pageId = null)
    {
        if($this->manage($userId)) {
            return true;
        }

        $userRoleTable = new Model_DbTable_UserRole();
        if($pageId) {
            return $userRoleTable->hasRole($userId, 'editor', $pageId);
        } else {
            return $userRoleTable->hasRole($userId, 'editor');
        }

        return false;
    }

    private function adminOnly($userId)
    {
        if(in_array('admin', $this->_roles)) {
            return true;
        }

        return false;
    }

    private function leagueDirector($userId, $leagueId = null)
    {
        if($this->manage($userId)) {
            return true;
        }

        $leagueMemberTable = new Model_DbTable_LeagueMember();
        if($leagueId) {
            foreach($leagueMemberTable->fetchAllByType($leagueId, 'director') as $member) {
                if($userId == $member->user_id) {
                    return true;
                }
            }
        } else {
            return $leagueMemberTable->isALeagueDirector($userId);
        }

        return false;
    }

    private function reporter($userId)
    {
        if($this->manage($userId)) {
            return true;
        }

        if(in_array('reporter', $this->_roles)) {
            return true;
        }

        return false;
    }

    private function clubCaptain($userId, $clubId = null)
    {
        if($this->manage($userId)) {
            return true;
        }

        $clubCaptainTable = new Model_DbTable_ClubCaptain();
        foreach($clubCaptainTable->fetchAllByClub($clubId) as $captain) {
            if(isset($captain['user_id']) and $userId == $captain['user_id']) {
                return true;
            }
        }
    }

    private function leagueCaptain($userId, $leagueId = null, $teamId = null)
    {
        if($this->manage($userId)) {
            return true;
        }

        if($this->leagueDirector($userId, $leagueId)) {
            return true;
        }

        if($userId) {
            $leagueMemberTable = new Model_DbTable_LeagueMember();
            foreach($leagueMemberTable->fetchAllByType($leagueId, 'captain', $teamId) as $member) {
                if($member->user_id == $userId) {
                    return true;
                }
            }
        }

        return false;
    }

    private function tournamentDirector($userId, $name, $year = null)
    {
        if($this->manage($userId)) {
            return true;
        }

        $tournamentTable = new Model_DbTable_Tournament();
        if(is_null($year)) {
            $year = $tournamentTable->fetchMostCurrentYear($name, true);
        }

        $tournament = $tournamentTable->fetchTournament($year, $name, true);

        $tournamentMemberTable = new Model_DbTable_TournamentMember();
        if($userId) {
            foreach($tournamentMemberTable->fetchAllDirectors($tournament->id) as $member) {
                if($member->user_id == $userId) {
                    return true;
                }
            }
        }

        return false;
    }

    private function volunteer($userId)
    {
        if($this->adminOnly($userId)) {
            return true;
        }

        if(in_array('volunteer', $this->_roles)) {
            return true;
        }

        return false;
    }
}
