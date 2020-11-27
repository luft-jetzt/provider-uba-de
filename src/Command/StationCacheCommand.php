<?php declare(strict_types=1);

namespace App\Command;

use App\Model\Station;
use App\StationManager\StationManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class StationCacheCommand extends Command
{
    protected static $defaultName = 'station:cache';

    protected StationManagerInterface $stationManager;

    public function __construct(string $name = null, StationManagerInterface $stationManager)
    {
        $this->stationManager = $stationManager;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setDescription('Add a short description for your command')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $stationList = $this->stationManager->loadStationList();

        $io->table(['Station Code', 'UBA Station Id',], array_map(function (Station $station): array
        {
            return [$station->getStationCode(), $station->getUbaStationId(),];
        }, $stationList));

        return Command::SUCCESS;
    }
}
