<?php

return [
    // Command
    'description' => 'Generate a decorator pattern implementation',
    'success' => 'Decorator Pattern Implementation Completed',
    
    // Target Interface
    'error.target-interface-missing' => "That interface doesn't exist. Please review and try again.",
    'success.target-interface-verified' => "Using verified interface: :targetInterfaceNamespace.",
    'prompt.target-interface' => "Please provide the namespace to (and the name of) the interface class that you are decorating to",

    // Incompatible Class
    'error.incompatible-class-missing' => "That class doesn't exist. Please review and try again.",
    'success.incompatible-class-verified' => "Using verified class: :incompatibleClass",
    'prompt.incompatible-class' => "Please provide the namespace to (and the name of) the class that you are decorating",

    // Adaptor Class
    'prompt.decorator-class' => "What should your decorator class be called?",

];