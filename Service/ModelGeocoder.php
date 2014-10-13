<?php

namespace Tec4\GeocodeBundle\Service;

use Geocoder\Geocoder;
use Monolog\Logger;
use Tec4\GeocodeBundle\Model\GeocodeableInterface;

/**
 * Geocode model 
 */
class ModelGeocoder
{
    /** @var Logger $logger */
    private $logger;

    /**
     * @paam Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }
    
    /**
     * Set latitude & longitude coordinates on model
     *
     * @param  GeocodeableInterface $model
     * @param  Geocoder             $geocoder
     * @param  boolean              $removeGeocodingOnFail Remove previous geocoded values, if set
     * @return boolean
     */
    public function addCoordinates(
        GeocodeableInterface $model, 
        Geocoder $geocoder, 
        $removeGeocodingOnFail = false
    ) {
        $name = $model->getGeocodeableName();
        $result = $geocoder->geocode($name);
        if ($result) {
            $lat = $result->getLatitude();
            $lng = $result->getLongitude();

            // Set coordinates if found.
            if ($lat !== 0 && $lng !== 0) {
                $model->setCoordinates($lat, $lng);
                $this->logger->info('Geocoded: ' . $name);
                $this->logger->info('Latitude: ' . $lat);
                $this->logger->info('Longitude: ' . $lng);

                return true;
            } else {
                $this->logger->error(
                    $this->buildBaseErrorMessage($name) . 
                    'Zero given for both latitude/longitude from provider'
                );
            }
        } else {
            $this->logger->info(
                $this->buildBaseErrorMessage($name) . 'No result found from provider'
            );
        }

        if (true === $removeGeocodingOnFail) {
            $model->setCoordinates(null, null);
            $model->setGeocoded(false);
        }

        return false;
    }

    /** 
     * Build base error message from model's geocodeable name
     *
     * @param  string $geocodeableName
     * @return string
     */
    private function buildBaseErrorMessage($geocodeableName)
    {
        return 'Error retrieving coordinates for "' . $geocodeableName . '". ';
    }
}
