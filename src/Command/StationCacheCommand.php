<?php declare(strict_types=1);

namespace App\Command;

use App\StationManager\StationManagerInterface;
use Caldera\LuftModel\Model\Station;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'station:cache',
    description: 'Cache stations for fetching luft values'
)]
class StationCacheCommand extends Command
{
    public function __construct(protected StationManagerInterface $stationManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $stationList = $this->stationManager->loadStationList();

        $this->stationManager->cacheStationList($stationList);

        if ($output->isVerbose()) {
            $io->table(['Station Code', 'UBA Station Id',], array_map(function (Station $station): array
            {
                return [$station->getStationCode(), $station->getUbaStationId(),];
            }, $stationList));
        }

        $io->success(sprintf('Cached %d stations from luft api', count($stationList)));

        return Command::SUCCESS;
    }
}
