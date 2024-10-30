<?php

if (  ! class_exists( 'Meeting_Truth_Event_Set' ) ) {
    class Meeting_Truth_Event_Set {
        public $Pages = 1;
        public $PageNo = 1;
        public $Count = 0;
        public $Events = array();
        
        public function AddEvent($event) {
            array_push($this->Events, $event);
        }
    }
}