<?php

class My_View_Helper_Leaguename extends Zend_View_Helper_Abstract
{
    /**
     * This helper will return true if the userId has the role
     * specified and false otherwise 
     * 
     * @param Zend_Db_Rowset $user
     * @param string $role 
     * @return boolean
     */
    public function leaguename($league, $showYear = false, $showDay = false, $showSeason = false, $showLeague = false)
    {
        if(is_numeric($league)) {
            $leagueTable = new Model_DbTable_League();
            $leagueObject = $leagueTable->find($league)->current();
            
        } else if(get_class($league) == 'Zend_Db_Table_Row') {
            $leagueObject = $league;
        }

        if($leagueObject) {
            $name = '';
            
            if($showYear === true) {
                $name .= $leagueObject->year;
            }
            
            if($showDay === true) {
                $name .= ' ' . $leagueObject->day . ' ';
            }

            if($showSeason === true) {
                $leagueSeasonTable = new Model_DbTable_LeagueSeason();
                $season = $leagueSeasonTable->find($leagueObject->season)->current();
                $name .= ' ' . ucwords($season->name);
            }

            if(!empty($leagueObject->name)) {
                $name .= ' ' . $leagueObject->name;
            }

            if($this->isLeague($name) and $showLeague === true) {
                $name .= ' League';
                
            }
            
            return $this->view->escape(trim($name));
        }
        
        return 'Unknown';
    }
    
    private function isLeague($name)
    {
        $exclusions = array(
            'hat',
            'tournament',
            'clinic',
        );
        
        foreach($exclusions as $exclusion) {
            if(strstr(strtolower($name), $exclusion)) {
                return false;
            }
        }
        
        return true;
    }
}
