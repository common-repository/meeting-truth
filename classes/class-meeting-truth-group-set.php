<?php

if (  ! class_exists( 'Meeting_Truth_Group_Set' ) ) {
    class Meeting_Truth_Group_Set {
        public $Count = 0;
        public $Groups = array();
        
        public function add_group( $group ) {
            array_push( $this->Groups, $group );
        }
    }
}