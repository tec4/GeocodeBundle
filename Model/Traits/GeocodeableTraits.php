<?php

namespace Tec4\GeocodeBundle\Model\Traits;

use Doctrine\ORM\Mapping as ORM;

/**
 * Model properties and accessors needed 
 * for geocoding. This is a helper class to
 * included madatory items and does not have
 * to be used. 
 *
 * Usable with PHP >= 5.4
 **/
trait GeocodeableTraits
{
    /**
     * @var decimal $latitude
     *
     * @ORM\Column(type="decimal", precision=13, scale=9, nullable=true)
     **/
    protected $latitude;

    /**
     * @var decimal $longitude
     *
     * @ORM\Column(type="decimal", precision=13, scale=9, nullable=true)
     **/
    protected $longitude;

    /**
     * @var integer
     *
     * @ORM\Column(name="geocoded", type="boolean")
     */
    protected $geocoded = false;

    /**
     * Set latitude
     *
     * @param decimal $latitude
     * @return self
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    
        return $this;
    }

    /**
     * Get latitude
     *
     * @return decimal 
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * Set longitude
     *
     * @param decimal $longitude
     * @return self
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    
        return $this;
    }

    /**
     * Get longitude
     *
     * @return decimal 
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * Does property been geocoded?
     *
     * @return boolean
     **/
    public function isGeocoded()
    {
        return $this->geocoded;
    }

    /**
     * Flag property has having coordinates
     *
     * @param boolean $geocoded
     * @return self
     */
    public function setGeocoded($geocoded)
    {
        $this->geocoded = $geocoded;

        return $this;
    }

    /**
     * Set latitude/longitude and flag entity as geocoded
     *
     * @param decimal $latitude
     * @param decimal $longitude
     * @return self
     **/
    public function setCoordinates($latitude, $longitude)
    {
        $this->setLongitude($longitude);
        $this->setLatitude($latitude);
        $this->setGeocoded(true);

        return $this;
    }
}
