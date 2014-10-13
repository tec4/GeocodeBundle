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

Usage
=====
// @TODO
// Give example of auto-updating via doctrine event subscriber
// Give example of leveraging interface in class

TODO
====
1. Add better logging (ability to extend logger and implement own solution/channel to log to, etc)
