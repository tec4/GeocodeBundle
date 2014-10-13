Summary
=======
This bundle provides a wrapper around the 
[BazingaGeocoderBundle](https://github.com/geocoder-php/BazingaGeocoderBundle).
It provides a simple way to implement latitude/longitude coordinates in to your
doctrine models.

Installation
============

Step 1: Download the Bundle
---------------------------

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```bash
$ composer require tec4/geocode-bundle
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

Step 2: Enable the Bundle
-------------------------

Then, enable the bundle by adding the following line in the `app/AppKernel.php`
file of your project:

```php
<?php
// app/AppKernel.php

// ...
class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            // ...

            new Tec4\GeocodeBundle\Tec4GeocodeBundle(),
        );

        // ...
    }

    // ...
}
```

Also install [BazingaGeocoderBundle](https://github.com/geocoder-php/BazingaGeocoderBundle) per
their documentation.

Step 3: Implement interface on Model
------------------------------------

Add interface to your model (entity/document).

User helper traits if you desire to get most of the required properties and attriubtes for interface.

Must implement new method called getGeocodeableName in code

```php
<?php

namespace Acme\SomeBundle\Model;

use Tec4\GeocodeBundle\Model\GeocodeableInterface;
use Tec4\GeocodeBundle\Model\GeocodeableTraits;

class NewClass implements GeocodeableInterface
    /**
     * Include properties and accessors for geocoding
     */
    use GeocodeableTraits;
    
    // ...
    private $address;
        
    // ...
    
    /**
     * {@inheridoc}
     */
    public function getGeocodeableName()
    {
        // Return some geocodeable, perhaps something like: $this->getAddress();
        // Example location would be: 1234 Some Street #1, Some City, Some State
        // Can be any geocodeable location
        return $this->address;
    }
}
```

Usage
=====

Auto-update entities when certain fields change (ex: when an address changes)
-----------------------------------------------------------------------------

For more info read symfony's docs about [registering event listeners and subscribers](http://symfony.com/doc/current/cookbook/doctrine/event_listeners_subscribers.html)

```php
<?php

namespace Acme\SomeBundle\EventListener;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Bazinga\Bundle\GeocoderBundle\Geocoder\LoggableGeocoder;
use Tec4\GeocodeBundle\Model\GeocodeableInterface;
use Tec4\GeocodeBundle\Service\ModelGeocoder;

/**
 * Hook into Doctrine prePersist & preUpdate events
 * to update geocode specific fields if information
 * about an entity's location has changed.
 */
class GeocodeEntitySubscriber implements EventSubscriber
{ 
    /** @var LoggableGeocoder $geocoder */
    private $geocoder;

    /** @var ModelGeocoder $modelGeocoder */
    private $modelGeocoder;

    /**
     * @param LoggableGeocoder $geocoder
     * @param ModelGeocoder    $modelGeocoder
     */
    public function __construct(LoggableGeocoder $geocoder, ModelGeocoder $modelGeocoder)
    {
        $this->geocoder = $geocoder;
        $this->modelGeocoder = $modelGeocoder;
    }

    public function getSubscribedEvents()
    {
        return array(
            'prePersist',
            'preUpdate',
        );
    }

    /**
     * Geocode entity if it has not already been done
     *
     * @param LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        if (($entity = $args->getEntity()) instanceof GeocodeableInterface) {
            if (!$entity->isGeocoded()) {
                $this->geocode($entity);
            }
        }
    }

    /**
     * Geocode entity if has yet to be done or if location has changed
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        if (($entity = $args->getEntity()) instanceof GeocodeableInterface) {
            if (!$entity->isGeocoded() || $args->hasChangedField('address')) {
                $this->geocode($entity);

                // Need to recompute changeset to update correctly
                $em = $args->getEntityManager();
                $uow = $em->getUnitOfWork();
                $meta = $em->getClassMetadata(get_class($entity));
                $uow->recomputeSingleEntityChangeSet($meta, $entity);
            }
        }
    }

    /**
     * Handle geocoding of entity
     *
     * @param GeocodeableInterface $entity
     */
    public function geocode(GeocodeableInterface $entity)
    {
        try {
            $this->modelGeocoder->updateModel($entity, $this->geocoder, true);
        } catch (\Exception $e) {
            // Perhaps do something here
            // Add message to flash bag?
        }
    }
}
```

Configure service:

```yaml
# src/Acme/SomeBundle/Resources/config/services.yml
services:
    acme.listener.geocode_entities:
        class: Acme\SomeBundle\EventListener\GeocodeEntitySubscriber
        arguments:
            - @bazinga_geocoder.geocoder
            - @tec4_geocode.model_geocoder
        tags:
            - { name: doctrine.event_subscriber, connection: default }
```

Batch update via command
------------------------

All options, except the arguemnt are optional. The class name, however, is required. 

```
php app/console tec4:geocode "Acme\\SomeBundle\\Model\\ClasName" --limit=100 --geocode_provider="google_maps"
```

TODO
====
1. Add better logging (ability to extend logger and implement own solution/channel to log to, etc)
