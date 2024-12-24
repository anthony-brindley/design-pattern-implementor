<?php

namespace AnthonyBrindley\DesignPatternImplementor\Commands;

use Illuminate\Support\Str;


class UnfinishedImplementCompositePatternCommand extends BaseImplementor
{
    protected $signature = 'implement-pattern:composite';

    protected static string $patternName = 'composite';

    public function handle(): void
    {
        // This pattern only makes sense if the core model of the domain can be represented as a tree - e.g products and boxes

        // 1. Component interface describes some basic operations common to all/most elements
        // 2. Leaf - basic element that doesnt have sub-elements
        // 3. Container (composite) - an element that DOES have sub-elements - either Leaves or other Containers
        // 4. Client - the requestor

        /**
         * GraphicInterface (component) declares some common operations
         * Dot (leaf class implementing the GraphicInterface)
         * CompoundGraphic (container/composite) may contain any number of sub-elements all enforcing the necessary interface
         * GUI - (client) - would work with both the Dot and CompoundGraphic objects the same way via their common interface
         */

         /**
          * CONCEPT - https://refactoring.guru/design-patterns/composite/php/example
          *
          */
          
    }

    
}
