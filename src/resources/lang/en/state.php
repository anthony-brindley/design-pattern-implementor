<?php

return [
    // Command
    'description' => 'Generate a state pattern implementation',
    'success' => 'State Pattern Implementation Completed',

    // Has State Interface Class
    'prompt.has-state-interface-class' => "What interface should the main :contextSinglular class implement?",

    // Is State Interface Class
    'prompt.state-interface-methods' => "Please provide any method names that your :stateClassSingular classes should implement",

    // State Folder
    'prompt.state-folder' => "What folder should the new states be created in?",
    'hint.state-folder' => "What should the folder that the various state classes of this functionality reside in?",

    // State Class
    'prompt.state-classes' => "Please provide any :stateClassSingular classes to create",

    // Context Class
    'prompt.context-class' => "What should the main Context class be called?",
    'hint.context-class' => "What is it that has :statesFolderName?",
];