<?php

if (  ! class_exists( 'Meeting_Truth_Session_Set' ) ) {
    class Meeting_Truth_Session_Set {
        public $currency = '';
        public $allfree = false;
        public $fee = 0;
        public $sessions = array();
        public $count = 0;
        
        public function add_session( $session ) {
            array_push( $this->sessions, $session );
        }
    }
}