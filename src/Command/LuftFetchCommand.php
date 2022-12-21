<?php declare(strict_types=1);

namespace App\Command;

use App\SourceFetcher\Parser\ParserInterface;
use App\SourceFetcher\SourceFetcherInterface;
use Caldera\LuftApiBundle\Api\ValueApiInterface;
use Caldera\LuftModel\Model\Value;
use Carbon\Carbon;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'luft:fetch',
    description: 'Fetch pollutants from uba api'
)]
class LuftFetchCommand extends Command
{
    public function __construct(protected SourceFetcherInterface $sourceFetcher, protected ParserInterface $parser, protected ValueApiInterface $valueApi)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('pollutants', InputArgument::IS_ARRAY, 'List pollutants to fetch')
            ->addOption('from-date-time', null,InputOption::VALUE_REQUIRED, 'Only fetch values after this date time')
            ->addOption('until-date-time', null, InputOption::VALUE_REQUIRED, 'Only fetch values before this date time')
            ->addOption('tag', null, InputOption::VALUE_REQUIRED, 'Add a tag to fetched values')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $pollutantList = $input->getArgument('pollutants');

        if (empty($pollutantList)) {
            $io->error('Please specify at least one pollutant to fetch.');

            return Command::FAILURE;
        }

        foreach ($pollutantList as $pollutantIdentifier) {
            $pollutantFetchMethodName = sprintf('fetch%s', strtoupper($pollutantIdentifier));

            if (!method_exists($this->sourceFetcher, $pollutantFetchMethodName)) {
                $io->error(sprintf('Could not find a method to fetch pollutant "%s"', $pollutantIdentifier));

                continue;
            }

            if ($input->getOption('from-date-time')) {
                $fromDateTime = new Carbon($input->getOption('from-date-time'));
            } else {
                $fromDateTime = null;
            }

            if ($input->getOption('until-date-time')) {
                $untilDateTime = new Carbon($input->getOption('until-date-time'));
            } else {
                $untilDateTime = null;
            }

            $dataString = $this->sourceFetcher->fetch($pollutantIdentifier, $untilDateTime, $fromDateTime);

            $valueList = $this->parser->parse($dataString, $pollutantIdentifier);

            if ($tag = $input->getOption('tag')) {
                /** @var Value $value */
                foreach ($valueList as $value) {
                    $value->setTag($tag);
                }
            }

            $this->valueApi->putValues($valueList);

            if ($output->isVerbose()) {
                $io->table([
                    'Station Code',
                    'Date Time',
                    'Value',
                    'Tag',
                ], array_map(function (Value $value): array {
                    return [
                        $value->getStationCode(),
                        $value->getDateTime()->format('Y-m-d H:i:s'),
                        $value->getValue(),
                        $value->getTag(),
                    ];
                }, $valueList)
                );
            }

            $io->success(sprintf('Fetched %d values for pollutant "%s"', count($valueList), $pollutantIdentifier));
        }

        return Command::SUCCESS;
    }
}
