<?php

class Cupa_Model_DbTable_LeagueTeam extends Zend_Db_Table
{
    protected $_name = 'league_team';
    protected $_primary = 'id';
    
    public function fetchAllTeams($leagueId, $order = 'name')
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->order($order);
        
        return $this->fetchAll($select);
    }

    public function isUnique($leagueId, $name)
    {
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('name = ?', $name);

        $result = $this->fetchRow($select);

        if(isset($result->name)) {
            return false;
        }

        return true;
    }
    
    public function fetchAllTeamRanks($leagueId)
    {
        $data = array();
        $data['current'] = array();
        $data['final'] = array();
        $select = $this->select()
                       ->where('league_id = ?', $leagueId)
                       ->where('final_rank IS NOT NULL')
                       ->order('final_rank ASC');
        
        $result = $this->fetchAll($select);
        
        if($result) {
            $data['final'] = $result->toArray();
        }
        
        $result = $this->fetchAllTeams($leagueId);
        if($result) {
            $leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
            $result = $result->toArray();
            $i = 0;
            foreach($result as $team) {
                $result[$i]['record'] = $leagueGameTable->fetchRecord($leagueId, $team['id']);
                $i++;
            }
            usort($result, array('Cupa_Model_DbTable_LeagueTeam', 'rankTeams'));
            $data['current'] = $result;
        }
        
        return $data;
    }
    
    private function rankTeams($a, $b)
    {
        list($aWin, $aLoss, $aTie) = explode('-', $a['record']);
        list($bWin, $bLoss, $bTie) = explode('-', $b['record']);
        
        if($aWin == $bWin) {
            if($aLoss == $bLoss) {
                if($aTie == $bTie) {
                    // TODO check head to head matchup
                    $leagueGameTable = new Cupa_Model_DbTable_LeagueGame();
                    return $leagueGameTable->fetchHeadToHead($a['league_id'], $a, $b);
                } else {
                    return ($aTie > $bTie) ? -1 : 1;
                }
            } else {
                return ($aLoss < $bLoss) ? -1 : 1;
            }
        } else {
            return ($aWin > $bWin) ? -1 : 1;
            
        }
    }
    
}
