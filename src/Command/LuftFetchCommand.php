<?php declare(strict_types=1);

namespace App\Command;

use App\SourceFetcher\Parser\ParserInterface;
use App\SourceFetcher\Query\Pollutant;
use App\SourceFetcher\SourceFetcherInterface;
use Caldera\LuftApiBundle\Api\ValueApiInterface;
use Caldera\LuftModel\Model\Value;
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
    public function __construct(
        private readonly SourceFetcherInterface $sourceFetcher,
        private readonly ParserInterface $parser,
        private readonly ValueApiInterface $valueApi,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('pollutants', InputArgument::IS_ARRAY, 'List pollutants to fetch')
            ->addOption('from-date-time', null, InputOption::VALUE_REQUIRED, 'Only fetch values after this date time')
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
            $pollutant = Pollutant::tryFrom(strtolower($pollutantIdentifier));

            if (!$pollutant) {
                $io->error(sprintf('Unknown pollutant "%s". Valid values: %s', $pollutantIdentifier, implode(', ', array_column(Pollutant::cases(), 'value'))));

                continue;
            }

            $fromDateTime = $input->getOption('from-date-time')
                ? new \DateTimeImmutable($input->getOption('from-date-time'))
                : null;

            $untilDateTime = $input->getOption('until-date-time')
                ? new \DateTimeImmutable($input->getOption('until-date-time'))
                : null;

            $dataString = $this->sourceFetcher->fetch($pollutant, $untilDateTime, $fromDateTime);

            $valueList = $this->parser->parse($dataString, $pollutant->value);

            if ($tag = $input->getOption('tag')) {
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

            $io->success(sprintf('Fetched %d values for pollutant "%s"', count($valueList), $pollutant->value));
        }

        return Command::SUCCESS;
    }
}
