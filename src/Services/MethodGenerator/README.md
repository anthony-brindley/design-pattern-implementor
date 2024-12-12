# Class Generator API

This set of services allows a user to generate a full Class file including imports, methods, properties, extends, implements and PHP8 attributes via a simple and intuitive API interface. 

## Roadmap

This API will probably get extracted into it's own Laravel package and then be able to include things such as a CI interface. As it stands though, I've included it (as I developed it) as part of the Design Pattern Implementor package. 

## Summary

The basic building blocks of this functionality are: 

SERVICES
- ClassGeneratorService
- MethodGeneratorService
- InterfaceGeneratorService

DTOs
- PropertyDTO
- MethodDTO
- MethodParameterDTO

The purpose of this API is to allow logical definition of the components necessary to create a functional Class within the application, and get the application itself to create the file. 

## Usage

Let's take an example that we can use to demonstrate as much functionality as possible. Let's create a Class that has some properties (both static and instantiated), some static and non-static methods (some that take parameters and some that dont) and then see how we would create a Class.

First of all, we need to define the Methods array. This should contain an array of MethodDTO objects. In order to create these objects, I have provided static constructors that relate to the intended 'visibility' of the method ('public','protected','private') to make things simpler. In each of these constructors, you are required to provide a name for the method which should be unique.

```php

use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\MethodDTO;
use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\MethodParameterDTO;
use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\PropertyDTO;

$methods = [
    MethodDTO::public('__construct'),
    MethodDTO::public('new')->static()->returnType(newClassName::class)
];

```

In this example, you can see that we have added 2 methods, one of which is static and also has a return type specified (the name of the new class). We are using a fluent language formate where we can 'tag-on' additional details to the MethodDTO object. For more details of the API footprint, see the [MethodDTO](#methoddto) section below.

In the following example, we will also define some parameters:

```php
use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\MethodDTO;
use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\MethodParameterDTO;
use AnthonyBrindley\DesignPatternImplementor\Services\MethodGenerator\DTO\PropertyDTO;
use Some\Service\Class;
use Illuminate\Http\Request;


$methods = [
    MethodDTO::private('__construct'),
    MethodDTO::public('new')
        ->static()
        ->parameter(
            MethodParameterDTO::make('service')
                ->type(Class::class)
                ->setDefault(null),
            MethodParameterDTO::make('request')
                ->type(Request::class),
            MethodParameterDTO::make('isPublic')
                ->type('bool')
                ->setDefault(true)
        )
];

```

As you can see, we have essentially defined a class where instantiation can only be acieved via a static constructor, into which you must provide a 'Class' service, a 'Request' and an 'isPublic' boolean. These are arbitrary variables used just for demonstrating how you would use the API. 

You can also define the Return types, type-hint the parameters and more via this fluent interface. For more details, see the relevant specific DTO documentation below.

With the addition of PHP attributes in PHP8, an extra level of valuable functionality was added to the software and I've tried to encompass that within this API too. Again, there's more details in the specific documentation. 

### Class body/functionality

In order to allow users to add some actual functionality to their class methods, I've added a `->body()` method that allows you to provide a string to be used within the relevant method. There's a whole extra level involved in ensuring that this string is parsed and validated before it's written to the file itself, so for now I would recommend just adding simple functionality via this method and populating the more complex ones directly after the class file has been created. 

### Generating Classes
Once you've defined all the methods along with the parameters you would like on your class, you can grab an instance of the `ClassGeneratorService` which - as you would expect - is responsible for generating the actual class file. 

In order to get an instance of this service, you are required to use either the `new` or `abstract` constructors (which naturally reflect the type of class being generated). You'll need to provide a name for the class too. 

Once you have the instance, you are able to pass in your `$methods` array via the `->addMethods()` method. Alternatively you are able to add individual methods via the `->addMethod()` method - which accepts a MethodDTO object. 

Since we're creating a Class here, we are able to define a lot of the other elements:

#### Class Properties

Using the `->addProperty()` method, you are able to pass in a PropertyDTO object. See details below in the [PropertyDTO](#propertydto) section.

#### Extending another class

You can specify that the class should extend another class via the `->extends()` method. This method accepts a FQN referencing an existing class. Your class can only extend 1 class. Attempts to add other classes to extend will throw an error. When successfully adding a Class to extend, the reference class will also be added as an import automatically. 

#### Class implementing interfaces

You can specify that your class should implement any number of interfaces using the `->implements()` method and providing a FQN reference to an existing interface. Your implemented interfaces will be de-duped and added to the list of imports automatically. 

### Generate the class file

When you've defined everything you want to define, you can trigger the service to generate the class file using the `->generateClass()` method. This method accepts a required namespace for the folder where the new class file should reside. If a class already exists there, an error will be thrown. 


## ClassGeneratorService API

### Constructors that define abstraction

| Method | Description | Required | Example |
|--------|-------------|----------|---------|
| `::new()` | Creates a standard class. This is one of the required constructors. | One constructor is required. | `$service = ClassGeneratorService::new('className');` |
|`::abstract()`| Creates an abstract class. This is one of the required constructors. | One constructor is required | `$service = ClassGeneratorService::abstract('className')` |

### Class functionality inheritance/constuction

| Method | Description | Required | Example |
|--------|-------------|----------|---------|
| `->extends()` | Defines a class that this new class should extend. Ensure a FQN is passed in. Only allowed once per class definition. | No | `$service = ClassGeneratorService::new('className')->extends(Another::class);` |
|`->implements(AnInterface::class)`| Allows you to define any interfaces that should be implemented by the class. This method can be used repeatedly in order to import as many interfaces as necessary. | No | `$service = ClassGeneratorService::abstract('className')->implements(Interface1::class)->implements(Interface2::class)` |

### Class Finality

Using the `->final()` method, you can denote whether a class should be marked as final and therefore unable to be extended or overwritten by other classes.

### Class Methods

| Method | Description | Required | Example |
|--------|-------------|----------|---------|
| `->addMethods()` | Allows users to pass in an array of MethodDTO objects for inclusion into the generated class | No | `$service = ClassGeneratorService::new('className')->addMethods(['array-of-methods]);` |
| `->addMethod()` | Allows users to pass in a MethodDTO object for inclusion into the generated class | No | `$service = ClassGeneratorService::new('className')->addMethod(MethodDTOObject);` |

### Class Properties

| Method | Description | Required | Example |
|--------|-------------|----------|---------|
| `->addProperty()` | Allows users to pass in a PropertyDTO object for inclusion into the generated class | No | `$service = ClassGeneratorService::new('className')->addProperty(PropertyDTOObject);` |


### Executable

The `->generateClass()` method is the trigger method that generates the file. This method requires a namespace to be passed in (to the containing folder of the new class file). The class file will be generated and populated based on the specifics defined before calling this method. 

## MethodDTO

The method DTO acts as a data structure that allows users to easily define details of a proposed method for their new class. 

### Method Finality

Using the `->final()` method, you can denote whether a method should be marked as final and therefore unable to be extended or overwritten by other classes.

### Method Visibility

| Method | Description | Required | Example |
|--------|-------------|----------|---------|
| `::public` | Generates a public method | A visibility definition is required - either `public`, `protected` or `private` | `MethodDTO::public('name')` |

### Method Parameters

Using the `->parameter()` method, a user is able to provide a MethodParameterDTO object into the method which will define it's specifics. This method can be called as many times as necessary in the fluent interface in order to define all method parameters. See details on the MethodParameterDTO documentation with regards to defining defaults, type-hinting etc. 

### Method ReturnType

Using the `->returnType()` method you are able to define the returnType for the method. This includes union types and the method will also attempt to ensure any class or interface-based types are imported into the class too. This is NOT a required value though.

### Method Body

Using the `->body()` method, you are able to provide a stringified version of the method's body. For now, it's recommended to keep this as simple as possible and fill in any complex functionality once the class has been generated. 

> Note: the body string is ignored if you are creating a method string for an interface. 

### Static 

Using the `->static()` method, you can define whether the method should be a static one or not.

### Method Attributes

Using the `->addAttribute()` method, you are able to add PHP8 attributes to your method.

### Executable - Generate the Method String

The `->toString()` method is the main 'trigger' method for the class and it's the one that generates the actual method in string form (ready for insertion into the class file). This will attempt to understand the specifics you have provided to the service and generate a string based on those details. 

### Executable - Generate the Interface Method String 

The `->toInterfaceString()` method is an alternate 'trigger' method and it's used to generate the string that represents the method for use within an interface. This means that no body will be included in the string and if the method is not a public one, only an empty string will be returned (as interfaces can only enforce public methods);

### Get Method Dependencies

The `->getDependencies()` method will return an array of any dependencies that are required for the method to work. This is based on the method parameter types as well as the return type if these have been defined. Currently, the body of the method is not considered as this will require parsing etc - but it is on the roadmap. The method will return an array which can be used by other services to generate the list of imports.  

## MethodParameterDTO

The method parameter DTO acts as a data structure that allows users to easily define details of a proposed parameters required for a method that's part of  their new class. 

### Method Parameter Construction

Users are required to use the static `::make()` method in order to construct a MethodParameterDTO object. This is so that validation checks can be added to the class AND it's a quicker/simpler syntax that's used in the $methods array - which can get quite confusing.

### Method Parameter Type

Used for type-hinting the parameters, using the `->type()` method will require the user to provide either a 'basic' type (one of: 'int', 'string', 'float', 'bool', 'array', 'callable', 'iterable', 'object', 'mixed') or a class/interface based one - which will require a FQN reference to be passed in. In this instance, the relevant file will be added to the import list that can be obtained via the `->getDependencies()` method when needed.

### Method Parameter Default value

Using the `->setDefault()` method allows the user to define a default value for the parameter. Using this method helps us overcome the situation where you may want to set the default value of a parameter to null. This method triggers a flag within the DTO that specifies that a default value was set (even if the default was set to null) and ensures that this is added to the stringified output.

### Method Parameter Attributes

Users can use the `->addAttribute()` method in order to add PHP8 attributes to the method parameters list.

### Executable - Generate the Method Parameter String 

The `->toString()` method is the main trigger method of this class and will pull together all the specifics of the parameter that have been provided and convert this detail into a representative string for inclusion in the new class file. 

### Get Method Parameter Dependencies

Since you are able to type-hint the parameter type, if the parameter is interface or class-based, then the associated import will be added to the imports list which can be obtained using the `->getDependencies()` method. This will return a de-duped array of any necessary imports relating to the parameter.


## PropertyDTO 

The property DTO acts as a data structure that allows users to easily define details of a proposed property required as part of their new class. 

### Property DTO Construction

Users are required to use one of the 'visibility'-related static constructors in order to intantiate a PropertyDTO object. This provides a simple way to declare whether a property should be `public`,`protected` or `private`.

These static constructors will also require a 'name' string and an optional default value. Please note that this default value can be overwritten at a later point using the `->setDefault()` method (see below for details).

### Property DTO static

Using the `->static()` method, you can define whether a property should be declared static or not. 

### Property DTO Default value

Using the `->setDefault()` method, you can define a default value for the property. Using this method will toggle a flag that indicates that a default value should be set - even if that value is 'null'. This will then be reflected within the generated string. 

### Property DTO Type

Using the `->type()` method, you can type-hint the property's type. You can use standard 'basic' types ('int', 'string', 'float', 'bool', 'array', 'callable', 'iterable', 'object', 'mixed') or an interface/class based type - which will then be added to the list of imports assuming a valid FQN reference is passed in. 

If a default value is set on the Property DTO object and it defines the property as 'nullable', then the type will be adjusted to reflect this.

### Executable - generate Property string

Using the `->toString()` method on the object will return a generated string that attempts to harness all the specifics passed into the object. 

### Get Property Dependencies

Since you are able to type-hint the property type, if the property is interface or class-based, then the associated import will be added to the imports list which can be obtained using the `->getDependencies()` method. This will return a de-duped array of any necessary imports relating to the property.