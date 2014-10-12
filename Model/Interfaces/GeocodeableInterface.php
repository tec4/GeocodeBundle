<?php

namespace Tec4\GeocodeBundle\Model\Interfaces;

/**
 * Implement if entities can be geocoded
 * and/or have coordinates
 **/
interface GeocodeableInterface
{
    /**
     * Set latitude
     *
     * @param float $latitude
     * @return self
     */
    public function setLatitude($latitude);

    /**
     * Get latitude
     *
     * @return float 
     */
    public function getLatitude();

    /**
     * Set longitude
     *
     * @param float $longitude
     * @return self
     */
    public function setLongitude($longitude);

    /**
     * Get longitude
     *
     * @return float 
     */
    public function getLongitude();

    /**
     * Set true if entity has been geocoded
     *
     * @param boolean
     * @return self
     */
    public function setGeocoded($geocoded);

    /**
     * Has entity been geocoded?
     *
     * @return boolean
     */
    public function isGeocoded();

    /**
     * Get string to geocode (ex: address, city/state name, etc)
     *
     * @return float 
     */
    public function getGeocodeableName();
}
