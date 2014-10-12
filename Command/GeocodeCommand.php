<?php

namespace Tec4\GeocodeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get latitude/longitude coordinates for any entity 
 * implementing specified interface
 **/
class GeocodeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('tec4:geocode')
            ->setDescription(
                'Geocode any model implementing \\Tec4\\GeocodeBundle\\Model\\Interfaces\\GeocodeableInterface'
            )
            ->addArgument(
                'class', 
                InputArgument::REQUIRED, 
                'Full namespaced model class to geocode (ie: "Acme\\SomeBundle\\Entity\\ClasName")'
            )
            ->addOption(
                'em', 
                null,
                InputOption::VALUE_REQUIRED, 
                'Name of entity manager to use.',
                'default'
            )
            ->addOption(
                'geocoder', 
                null,
                InputOption::VALUE_REQUIRED, 
                'Name of willdurand/geocoder-bundle geocoder to use. See willdurand/geocoder-bundle docs for available types.',
                'google_maps'
            )
            ->addOption(
                'limit', 
                null,
                InputOption::VALUE_REQUIRED, 
                'Set number of entities to geocode.',
                5
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $class = $input->getArgument('class');
        $limit = $input->getOption('limit');
        $geocoder = $input->getOption('geocoder');
        $emName = $input->getOption('em');
        $em = $container->get('doctrine')->getManager($emName); 

        $refClass = new \ReflectionClass($class);
        $interface = '\\Tec4\\GeocodeBundle\\Model\\Interfaces\\GeocodeableInterface';

        // If not of interface, throw exception
        if (false === $refClass->implementsInterface($interface)) {
            throw new \Exception('Class must implement GeocodeableInterface');
        }

        $entities = $em->getRepository($class)->findBy(
            array('geocoded' => false),
            $order_by = null,
            $limit
        );

        $geocoder = $container->get('bazinga_geocoder.geocoder')
            ->using($geocoder)
        ;

        $output->writeln('Begining geocode');

        $i = 0;
        foreach ($entities as $entity) {
            $name = $entity->getGeocodeableName();
            $result = $geocoder->geocode($name);
            if ($result) {
                $lat = $result->getLatitude();
                $lng = $result->getLongitude();

                // Set coordinates if found.
                if ($lat !== 0 && $lng !== 0) {
                    $i++;
                    $entity->setCoordinates($lat, $lng);
                    $output->writeln('<info>Geocoded: ' . $name . '</info>');
                    $output->writeln('<comment>Latitude: ' . $lat . '</comment>');
                    $output->writeln('<comment>Longitude: ' . $lng . '</comment>');

                    $em->persist($entity);
                    if (($i % 30) == 0) {
                        $em->flush();
                    }
                }
            }
            // Hitting API too fast causes errors with many geocoding services
            sleep(1);
        }
        $em->flush();
        $em->clear();
        $output->writeln('Done');
    }
}
