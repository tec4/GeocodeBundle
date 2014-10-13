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
                'Geocode any model implementing \\Tec4\\GeocodeBundle\\Model\\GeocodeableInterface'
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
                'limit', 
                null,
                InputOption::VALUE_REQUIRED, 
                'Set number of entities to geocode.',
                5
            )
            ->addOption(
                'geocode_provider', 
                null,
                InputOption::VALUE_OPTIONAL, 
                'Name of willdurand/geocoder-bundle geocoder to use. See willdurand/geocoder-bundle docs for available types.'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $class = $input->getArgument('class');
        $limit = $input->getOption('limit');
        $emName = $input->getOption('em');
        $em = $container->get('doctrine')->getManager($emName); 

        $refClass = new \ReflectionClass($class);
        $interface = '\\Tec4\\GeocodeBundle\\Model\\GeocodeableInterface';

        // If not of interface, throw exception
        if (false === $refClass->implementsInterface($interface)) {
            throw new \Exception('Class must implement GeocodeableInterface');
        }

        $entities = $em->getRepository($class)->findBy(
            array('geocoded' => false),
            $orderBy = null,
            $limit
        );

        $geocoder = $container->get('bazinga_geocoder.geocoder');
        if ($input->getOption('geocode_provider')) {
            $geocoder->using($input->getOption('geocode_provider'));
        }

        $output->writeln('Begining geocode');
        $modelGeocoder = $container->get('tec4_geocode.model_geocoder');

        $i = 0;
        foreach ($entities as $entity) {
            if ($modelGeocoder->updateModel($entity, $geocoder, true)) {
                $i++;
                $em->persist($entity);
                if (($i % 30) == 0) {
                    $em->flush();
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
