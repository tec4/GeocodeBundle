<?php

namespace Tec4\GeocodeBundle\Service;

use Geocoder\Geocoder;
use Geocoder\Result\ResultInterface;
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
     * @throws \Exception           Re-throws parent exception as defined by geocoding service
     */
    public function updateModel(
        GeocodeableInterface $model, 
        Geocoder $geocoder, 
        $removeGeocodingOnFail = false
    ) {
        $name = $model->getGeocodeableName();
        $result = null;

        // Attempt to get results. If exception, log and
        // rethrow it so it can be caught down the chain,
        // in the calling code
        try {
            $result = $geocoder->geocode($name);
        } catch (\Exception $e) {
            $this->logger->error(
                $this->buildBaseErrorMessage($name) .
                'Exception thrown: ' . $e->getMessage()
            );
            throw $e;
        }
        $success = $this->addCoordinates($model, $result);

        // Un-geocode if unsuccessful and indicated to remove
        // previous geocoded values
        if (!$success && true === $removeGeocodingOnFail) {
            $model->setCoordinates(null, null);
            $model->setGeocoded(false);
        }

        return $success;
    }

    /**
     * Apply geocoded coordinates (latitude/longitude) to model
     *
     * @param  GeocodeableInterface $model
     * @param  ResultInterface|null $result
     * @return boolean
     */
    private function addCoordinates(GeocodeableInterface $model, ResultInterface $result = null)
    {
        $name = $model->getGeocodeableName();
        if ($result instanceof ResultInterface) {
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
