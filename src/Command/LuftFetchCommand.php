<?php declare(strict_types=1);

namespace App\Command;

use App\SourceFetcher\Parser\ParserInterface;
use App\SourceFetcher\SourceFetcherInterface;
use Caldera\LuftApiBundle\Api\ValueApiInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LuftFetchCommand extends Command
{
    protected static $defaultName = 'luft:fetch';

    protected SourceFetcherInterface $sourceFetcher;
    protected ParserInterface $parser;
    protected ValueApiInterface $valueApi;

    public function __construct(string $name = null, SourceFetcherInterface $sourceFetcher, ParserInterface $parser, ValueApiInterface $valueApi)
    {
        $this->sourceFetcher = $sourceFetcher;
        $this->parser = $parser;
        $this->valueApi = $valueApi;

        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Fetch pollutants from uba api')
            ->addArgument('pollutants', InputArgument::IS_ARRAY, 'List pollutants to fetch')
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

            $dataString = $this->sourceFetcher->fetch();

            $valueList = $this->parser->parse($dataString, $pollutantIdentifier);

            $this->valueApi->putValues($valueList);

            $io->success(sprintf('Fetched %d values for pollutant "%s"', count($valueList), $pollutantIdentifier));
        }

        return Command::SUCCESS;
    }
}
