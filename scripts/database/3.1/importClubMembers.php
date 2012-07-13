<?php
require_once realpath(__DIR__ . '/../../') . '/common.php';

// Database table links
$clubTable = new Model_DbTable_Club();
$userTable = new Model_DbTable_User();
$clubMemberTable = new Model_DbTable_ClubMember();

$clubMembers = array(
    'Steamboat' => array(
        2008 => array(
            'Nick Felicelli',
            'Maureen Felicelli',
            'Edward Mack',
            'Michael Rimler',
            'Josh Findley',
            'Jen Golan',
            'Chris Clements',
            'Brittany Winner',
            'Austin Winner',
            'Hajnal Salanki',
            'Issac Jeffries',
            'Sara Findley',
            'Roy Stephens',
            'Kelly Rimler',
            'Susan Conrad',
            'Justin Conrad',
            'Kyle Gerschutz',
            'Jonathan Cummings',
            'Steph Mack',
            'Lindsey Saum',
            'Ned Early',
            'Todd Grace',
            'Alex Bowers',
            'Sarah Zistler',
            'Dan Zistler',
            'Shannon Clear',
            'Kristin Riepenhoff',
            'Bryce Cannon',
        ),
        2009 => array(
            'Peter Ferrante',
            'Edward Mack',
            'Jonathan Cummings',
            'Steph Mack',
            'Michael Rimler',
            'Nick Felicelli',
            'Maureen Felicelli',
            'Hajnal Salanki',
            'Jen Golan',
            'Kelly Rimler',
            'Lindsey Kleiser',
            'Chris Clements',
            'Chris Kiessling',
            'Garrett Moulder',
            'Rachel Thaw',
            'Thomas Hein',
            'Andrew Uhling',
            'Alyssa McMahon',
            'Alex Bowers',
            'Jeff Haney',
            'Nicole Badie',
            'Matt Dudash',
            'David Weber',
            'Neil Narayan',
            'Kristin Riepenhoff',
            'Samantha Stewart',
            'Kristi Schmeling',
            'Carrie Kissman',
            'Bryce Cannon',
        ),
        2010 => array(
            'Nick Felicelli',
            'Ryan Gorman',
            'Aaron Armbruster',
            'Aaron Bacon',
            'Maureen Felicelli',
            'Edward Mack',
            'Steph Mack',
            'Brittany Winner',
            'Jen Golan',
            'Garrett Moulder',
            'Chance Hill',
            'David Weber',
            'Josh Findley',
            'Chris Clements',
            'Samantha Stewart',
            'Rachel Thaw',
            'Emily Wallace',
            'Kristi Schmeling',
            'Jeff Haney',
            'Hajnal Salanki',
            'Michael Rimler',
        ),
        2011 => array(
            'Tj Bartczack',
            'Edward Mack',
            'Steph Mack',
            'Ryan Gorman',
            'Jen Golan',
            'Erin Ritchie',
            'Chris Lidel',
            'Aaron Bacon',
            'Nathan Buddemeyer',
            'Brittany Winner',
            'Josh Kleymeyer',
            'Hajnal Salanki',
            'Pat Kraft',
            'Jeff Haney',
            'Matt Zenz',
            'Aaron Armbruster',
            'Joe Mozloom',
            'John Richey',
            'Lindsey Cencula',
            'Emily Wallace',
            'Elizabeth Bikun',
            'Kristi Schmeling',
        ),
    ),
    'Age Against the Machine' => array(
        2010 => array(
            'George White',
            'Bob Scheadler',
            'Dave Sweeny',
            'Arash Babaoff',
            'Tom Dutton',
            'Ken Hughes',
            'John Hull',
            'Ken Petren',
            'Dave Fry',
            'Chris Oldstone',
            'Tom Brewster',
            'Dave Schenck',
            'John Osterman',
            'Alan Frishman',
            'Phil Sawin',
            'Mike Kaylor',
            'Dave Mancino',
            'Steve Betts',
            'Tom Phillips',
        ),
        2011 => array(
            'George White',
            'Bob Scheadler',
            'Dave Sweeny',
            'Arash Babaoff',
            'Tom Dutton',
            'Ken Hughes',
            'John Hull',
            'Ken Petren',
            'Dave Fry',
            'Chris Oldstone',
            'Tom Brewster',
            'Dave Schenck',
            'John Osterman',
            'Alan Frishman',
            'Phil Sawin',
            'Mike Kaylor',
            'Dave Mancino',
            'Steve Betts',
            'Tom Phillips',
        ),
    ),
);

$progressBar = new Console_ProgressBar('    [%bar%] %percent%', '=>', '-', 50, 100);
$clubMemberTable->getAdapter()->beginTransaction();

foreach($clubMembers as $club => $data) {
    foreach($data as $year => $members) {
        $totalMembers = count($members);
        $i = 0;
        echo "    Importing $totalMembers $year $club Member:\n";
        $progressBar->reset('    [%bar%] %percent%', '=>', '-', 50, $totalMembers);
        foreach($members as $member) {
            $team = $clubTable->fetchByName($club);
            $user = $userTable->fetchByFullname($member);
            if(!empty($team) and !empty($user)) {
                $clubMemberTable->addMember($team->id, $user->id, $year, 'player');
            }
            $progressBar->update($i);
            $i++;
        }
        $progressBar->update($totalMembers);
        echo "\n";
    }
}
$clubMemberTable->getAdapter()->commit();
