<?php

return [
    // Command
    'description' => 'Generate a adaptor pattern implementation',
    'success' => 'Adaptor Pattern Implementation Completed',
    
    // Target Interface
    'error.target-interface-missing' => "That interface doesn't exist. Please review and try again.",
    'success.target-interface-verified' => "Using verified interface: :targetInterfaceNamespace.",
    'prompt.target-interface' => "Please provide the namespace to (and the name of) the interface class that you are adapting to",

    // Incompatible Class
    'error.incompatible-class-missing' => "That class doesn't exist. Please review and try again.",
    'success.incompatible-class-verified' => "Using verified class: :incompatibleClass",
    'prompt.incompatible-class' => "Please provide the namespace to (and the name of) the class that you are adapting",

    // Adaptor Class
    'prompt.adaptor-class' => "What should your adaptor class be called?",
];