<?php declare(strict_types=1);

namespace App\Command;

use App\StationLoader\StationLoaderInterface;
use Caldera\LuftApiBundle\Api\StationApiInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StationLoadCommand extends Command
{
    protected static $defaultName = 'station:load';

    protected StationLoaderInterface $stationLoader;
    protected StationApiInterface $stationApi;

    public function __construct(StationLoaderInterface $stationLoader, StationApiInterface $stationApi)
    {
        $this->stationLoader = $stationLoader;
        $this->stationApi = $stationApi;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Load station list')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $result = $this->stationLoader->load();

        $io->success(sprintf('Found %d existing stations, %d new stations and %d changed stations.', count($result->getExistingStationList()), count($result->getNewStationList()), count($result->getChangedStationList())));

        $this->stationApi->putStations($result->getNewStationList());
        $this->stationApi->postStations($result->getChangedStationList());

        return Command::SUCCESS;
    }
}
