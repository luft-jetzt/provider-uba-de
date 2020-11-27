<?php declare(strict_types=1);

namespace App\Command;

use App\Api\ValueApiInterface;
use App\SourceFetcher\Parser\ParserInterface;
use App\SourceFetcher\SourceFetcherInterface;
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
            ->setDescription('Add a short description for your command')
            ->addArgument('arg1', InputArgument::OPTIONAL, 'Argument description')
            ->addOption('option1', null, InputOption::VALUE_NONE, 'Option description')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $dataString = $this->sourceFetcher->fetch();

        $valueList = $this->parser->parse($dataString, 3);

        $this->valueApi->putValues($valueList);

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return Command::SUCCESS;
    }
}
