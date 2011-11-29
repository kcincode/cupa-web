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
    public function leaguename($league, $showYear = false)
    {
        if(is_numeric($league)) {
            $leagueTable = new Cupa_Model_DbTable_League();
            $leagueObject = $leagueTable->find($league)->current();
            
        } else if(get_class($league) == 'Zend_Db_Table_Row') {
            $leagueObject = $league;
        }

        if($leagueObject) {
            $name = '';
            
            if($showYear) {
                $name .= $leagueObject->year;
            }
            
            $name .= ' ' . $leagueObject->day . ' ';

            if($leagueObject->season != 'Other') {
                $name .= ' ' . $leagueObject->season;
            }

            if(!empty($leagueObject->name)) {
                $name .= ' ' . $leagueObject->name;
            }

            if($this->isLeague($name)) {
                $name .= ' League';
                
            }
            
            return trim($name);
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