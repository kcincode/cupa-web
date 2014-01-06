<?php

class Form_LeagueCoach extends Twitter_Bootstrap_Form_Horizontal
{
    protected $_coach;
    protected $_user;
    protected $_type;
    protected $_userProfile;

    protected $_checks = array(
        'director' => array(
            'background' => 'Background Check',
            //'bsa_safety' => 'BSA Safety',
            'concussion' => 'Concussion Training',
            //'chaperon' => 'Chaperon Form',
            'manual' => 'Read Coaching Manual',
            'rules' => 'Read Rules',
            //'usau' => 'USAU requirements',
        ),
        'coach' => array(
            //'bsa_safety' => 'BSA Safety',
            'concussion' => 'Concussion Training',
            //'chaperon' => 'Chaperon Form',
            'manual' => 'Read Coaching Manual',
            'rules' => 'Read Rules',
        ),
        'assistant_coach' => array(
            'manual' => 'Read Coaching Manual',
            'rules' => 'Read Rules',
        )
    );

    public function __construct($coach, $type)
    {
        $this->_coach = $coach;

        $userTable = new Model_DbTable_User();
        $this->_user = $userTable->find($coach['user_id'])->current();
        $this->_type = $type;
        $userProfileTable = new Model_DbTable_UserProfile();
        $this->_userProfile = $userProfileTable->find($coach['user_id'])->current();

        parent::__construct();
    }

    public function init()
    {
        $this->addElement('text', 'first_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid first name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Firstname:',
            'class' => 'span3',
            'value' => $this->_user->first_name,
        ));

        $this->addElement('text', 'last_name', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                array('StringLength', true, array('min' => 2, 'max' => 25, 'messages' => array('stringLengthInvalid' => 'Invalid last name, max of 25 characters.'))),
            ),
            'required' => true,
            'label' => 'Lastname:',
            'class' => 'span3',
            'value' => $this->_user->last_name,
        ));

        $this->addElement('text', 'email', array(
            'filters' => array('StringTrim'),
            'validators' => array(
                'EmailAddress',
                array('Db_NoRecordExists', false, array('table' => 'user', 'field' => 'email', 'exclude' => array('field' => 'id', 'value' => $this->_user->id), 'messages' => array('recordFound' => 'Email address is already used.'))),

            ),
            'class' => 'span5',
            'required' => true,
            'label' => 'Email Address:',
            'value' => $this->_user->email,
        ));

        $this->addElement('text', 'phone', array(
            'filters' => array('StringTrim'),
            'required' => true,
            'validators' => array(
                array('Regex', false, array('pattern' => '/^\d\d\d-\d\d\d-\d\d\d\d$/', 'messages' => array('regexNotMatch' => 'Invalid phone number ###-###-####'))),
            ),
            'description' => 'Format: ###-###-####',
            'label' => 'Phone:',
            'class' => 'span2',
            'style' => 'text-align: center',
            'value' => (empty($this->_userProfile->phone)) ? null : $this->_userProfile->phone,
        ));

        $userWaiverTable = new Model_DbTable_UserWaiver();
        $view = Zend_Layout::getMvcInstance()->getView();
        $user = (Zend_Auth::getInstance()->hasIdentity()) ? $view->user : null;
        $waiver = $userWaiverTable->hasWaiver($this->_user->id, date('Y'));
        if($waiver) {
            $text = '<span class="text-success">' . date('Y') . ' Waiver is signed</span>';
        } else {
            $text = ($user != null && $user->id == $this->_user->id) ? '<span class="text-error">' . date('Y') . ' Waiver <strong>NOT</strong> signed</span> (<a href="' . $view->baseUrl() . '/waiver/' . date('Y') . '">Sign Waiver Here</a>)' : '<span class="text-error">' . date('Y') . ' Waiver <strong>NOT</strong> signed</span>';
        }

        $this->addElement('html', 'waiver', array(
            'label' => 'Waiver',
            'value' => $text,
        ));

        foreach($this->_checks[$this->_type] as $type => $label) {
            $this->addElement('radio', $type, array(
                'label' => $label,
                'multiOptions' => array(0 => 'Incomplete', 1 => 'Complete'),
                'label_class' => 'inline',
                'value' => $this->_coach[$type],
            ));
        }

        $this->addElement('button', 'save', array(
            'type' => 'submit',
            'label' => 'Update',
            'buttonType' => Twitter_Bootstrap_Form_Element_Submit::BUTTON_PRIMARY,
            'escape' => false,
            'icon' => 'hdd',
            'whiteIcon' => true,
            'iconPosition' => Twitter_Bootstrap_Form_Element_Button::ICON_POSITION_LEFT,
        ));

        $this->addElement('button', 'cancel', array(
            'type' => 'submit',
            'label' => 'Cancel',
        ));

        $this->addDisplayGroup(
            array('first_name' ,'last_name', 'email', 'phone'),
            'coach_edit_form',
            array(
                'legend' => 'Youth Coach Information',
            )
        );

        $this->addDisplayGroup(
            array_merge(array('waiver'), array_keys($this->_checks[$this->_type])),
            'coach_edit_require',
            array(
                'legend' => 'Coaching Requirements',
            )
        );

        $this->addDisplayGroup(
            array('save', 'cancel'),
            'coach_edit_actions',
            array(
                'disableLoadDefaultDecorators' => true,
                'decorators' => array('Actions'),
            )
        );
    }
}
