<?php

    /**
     * Class for the main template
     *
     * Any information requested by the template will be provided by this class
     * as well as it's behaviour.
     */
    class main extends Template {

        // Set Meta Tags Information.
        public $_title = "Aegis Framework";

        // Set what page and template should be used to render this template.
        function __construct () {
            $this -> setPage ("home.html");
            $this -> setTemplate ("main.html");
        }
    }

?>