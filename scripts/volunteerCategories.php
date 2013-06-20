<?php

echo "Tournament:\n";
$data = array(
    array(
        'type' => 'checkboxes',
        'name' => 'available',
        'title' => 'What day(s) are you available to help out with this event?',
        'answers' => array(
            'prior' => 'Prior to Tournament Weekend',
            'all' => 'All Tournament Weekend',
            'saturday' => 'Part of the weekend (Saturday)',
            'sunday' => 'Part of the weekend (Sunday)',
        ),
        'required' => true,
    ),
    array(
        'type' => 'checkboxes',
        'name' => 'area',
        'title' => 'What area(s) would you be most interested in helping during this opportunity?',
        'answers' => array(
            'field_prep' => 'Field Prep (Marking/Painting Thursday or Friday)',
            'game_day' => 'Game day Food/Catering (Helping Vendors)',
            'scorekeep' => 'Score Keeper (Assisting Head Score Keeper)',
            'statkeep' => 'Stat Keeper (Assisting Head Stat Keeper)',
            'social_event' => 'Social Event (Saturday Night)',
            'sat_prep' => 'Saturday Prep (Before 9:00 am)',
            'sat_help' => 'Saturday Day',
            'sun_prep' => 'Sunday Prep (Before 8:30 am)',
            'sun_help' => 'Sunday Day',
            'other' => 'Other (enter in description)',
        ),
        'required' => true,
    ),
    array(
        'type' => 'textarea',
        'name' => 'other',
        'title' => 'Please explain other',
        'required' => false,
    ),
);

echo json_encode($data);
echo "\n\n";


echo "Clinics/Camps:\n";
$data = array(
    array(
        'type' => 'checkboxes',
        'name' => 'area',
        'title' => 'In what area would you like to help?',
        'answers' => array(
            'boy_scouts' => 'Boy Scouts',
            'girl_scouts' => 'Girl Scouts',
            'schools' => 'Elementary/Middle Schools',
            'other' => 'Other',
        ),
        'required' => true,
    ),
    array(
        'type' => 'textarea',
        'name' => 'other',
        'title' => 'Please explain other',
        'required' => false,
    ),
    array(
        'type' => 'checkboxes',
        'name' => 'available',
        'title' => 'What time of day are you available?',
        'answers' => array(
            'morning' => 'Morning (Before 12:00pm)',
            'afternoon' => 'Afternoon (Post 12:00pm)',
            'evening' => 'Evening (Post 5:00pm)',
        ),
        'required' => true,
    ),
    array(
        'type' => 'checkboxes',
        'name' => 'area_town',
        'title' => 'What area of town would you be willing to help out in?',
        'answers' => array(
            'north' => 'North',
            'east' => 'East',
            'south' => 'South',
            'west' => 'West',
        ),
        'required' => true,
    ),
);

echo json_encode($data);
echo "\n\n";


echo "Leagues:\n";
$data = array(
    array(
        'type' => 'checkboxes',
        'name' => 'season',
        'title' => 'What season would you like to help out with?',
        'answers' => array(
            'spring' => 'Spring',
            'summer' => 'Summer',
            'fall' => 'Fall',
            'winter' => 'Winter',
        ),
        'required' => true,
    ),
    array(
        'type' => 'checkboxes',
        'name' => 'level',
        'title' => 'At what level would you like to help out with?',
        'answers' => array(
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'elite' => 'Elite/Advanced',
        ),
        'required' => true,
    ),
    array(
        'type' => 'radio',
        'name' => 'current_yn',
        'title' => 'Is there a current league that you would like to help with?',
        'answers' => array(
            'yes' => 'Yes',
            'no' => 'No',
        ),
        'required' => true,
    ),
    array(
        'type' => 'textarea',
        'name' => 'current_league',
        'title' => 'If yes, which one?',
        'required' => false,
    ),
    array(
        'type' => 'textarea',
        'name' => 'new_league',
        'title' => 'If no, what type of league would you like to start/help out with in the future?',
        'required' => false,
    ),
);

echo json_encode($data);
echo "\n\n";


echo "Youth:\n";
$data = array(
    array(
        'type' => 'checkboxes',
        'name' => 'level',
        'title' => 'What level of coaching would you be interested in learning more about?',
        'answers' => array(
            'head' => 'Head Coach',
            'assistant' => 'Assistant Coach',
            'part-time' => 'Part-time Coach',
            'other' => 'Other',
        ),
        'required' => true,
    ),
    array(
        'type' => 'textarea',
        'name' => 'other',
        'title' => 'If other, please explain:',
        'required' => false,
    ),
    array(
        'type' => 'textarea',
        'name' => 'program',
        'title' => 'What school/program would you be interested in helping out?',
        'required' => true,
    ),
);

echo json_encode($data);
echo "\n\n";


