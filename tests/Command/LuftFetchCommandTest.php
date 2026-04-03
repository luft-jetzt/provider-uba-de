<?php declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\LuftFetchCommand;
use App\SourceFetcher\Parser\ParserInterface;
use App\SourceFetcher\Query\Pollutant;
use App\SourceFetcher\SourceFetcherInterface;
use Caldera\LuftApiBundle\Api\ValueApiInterface;
use Caldera\LuftModel\Model\Value;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class LuftFetchCommandTest extends TestCase
{
    private function createCommandTester(
        ?SourceFetcherInterface $fetcher = null,
        ?ParserInterface $parser = null,
        ?ValueApiInterface $valueApi = null,
    ): CommandTester {
        $fetcher ??= $this->createMock(SourceFetcherInterface::class);
        $parser ??= $this->createMock(ParserInterface::class);
        $valueApi ??= $this->createMock(ValueApiInterface::class);

        $command = new LuftFetchCommand($fetcher, $parser, $valueApi);

        $application = new Application();
        $application->addCommand($command);

        return new CommandTester($application->find('luft:fetch'));
    }

    public function testExecuteWithoutPollutantsShowsError(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute([]);

        $this->assertSame(1, $tester->getStatusCode());
        $this->assertStringContainsString('Please specify at least one pollutant', $tester->getDisplay());
    }

    public function testExecuteWithInvalidPollutantShowsError(): void
    {
        $tester = $this->createCommandTester();
        $tester->execute(['pollutants' => ['invalid']]);

        $this->assertStringContainsString('Unknown pollutant "invalid"', $tester->getDisplay());
    }

    public function testExecuteWithValidPollutantFetchesData(): void
    {
        $fetcher = $this->createMock(SourceFetcherInterface::class);
        $fetcher->expects($this->once())
            ->method('fetch')
            ->with(Pollutant::PM10, $this->anything(), $this->anything())
            ->willReturn('{"data":{}}');

        $parser = $this->createMock(ParserInterface::class);
        $parser->expects($this->once())
            ->method('parse')
            ->with('{"data":{}}', 'pm10')
            ->willReturn([]);

        $valueApi = $this->createMock(ValueApiInterface::class);
        $valueApi->expects($this->once())
            ->method('putValues')
            ->with([]);

        $tester = $this->createCommandTester($fetcher, $parser, $valueApi);
        $tester->execute(['pollutants' => ['pm10']]);

        $this->assertSame(0, $tester->getStatusCode());
        $this->assertStringContainsString('Fetched 0 values for pollutant "pm10"', $tester->getDisplay());
    }

    public function testExecuteWithTagSetsTagOnValues(): void
    {
        $value = new Value();
        $value->setStationCode('DEBW001');
        $value->setDateTime(new \DateTime());
        $value->setPollutant('pm10');
        $value->setValue(42.0);

        $fetcher = $this->createMock(SourceFetcherInterface::class);
        $fetcher->method('fetch')->willReturn('{}');

        $parser = $this->createMock(ParserInterface::class);
        $parser->method('parse')->willReturn([$value]);

        $valueApi = $this->createMock(ValueApiInterface::class);

        $tester = $this->createCommandTester($fetcher, $parser, $valueApi);
        $tester->execute(['pollutants' => ['pm10'], '--tag' => 'test-tag']);

        $this->assertSame('test-tag', $value->getTag());
    }

    public function testExecuteWithFromDateTimeParsesDate(): void
    {
        $fetcher = $this->createMock(SourceFetcherInterface::class);
        $fetcher->expects($this->once())
            ->method('fetch')
            ->with(
                Pollutant::NO2,
                $this->isNull(),
                $this->callback(fn (\DateTimeImmutable $dt) => $dt->format('Y-m-d') === '2024-01-15'),
            )
            ->willReturn('{"data":{}}');

        $parser = $this->createMock(ParserInterface::class);
        $parser->method('parse')->willReturn([]);

        $tester = $this->createCommandTester($fetcher, $parser);
        $tester->execute([
            'pollutants' => ['no2'],
            '--from-date-time' => '2024-01-15',
        ]);

        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testExecuteIsCaseInsensitive(): void
    {
        $fetcher = $this->createMock(SourceFetcherInterface::class);
        $fetcher->expects($this->once())
            ->method('fetch')
            ->with(Pollutant::PM10)
            ->willReturn('{"data":{}}');

        $parser = $this->createMock(ParserInterface::class);
        $parser->method('parse')->willReturn([]);

        $tester = $this->createCommandTester($fetcher, $parser);
        $tester->execute(['pollutants' => ['PM10']]);

        $this->assertSame(0, $tester->getStatusCode());
    }

    public function testExecuteMultiplePollutants(): void
    {
        $fetcher = $this->createMock(SourceFetcherInterface::class);
        $fetcher->expects($this->exactly(2))
            ->method('fetch')
            ->willReturn('{"data":{}}');

        $parser = $this->createMock(ParserInterface::class);
        $parser->method('parse')->willReturn([]);

        $valueApi = $this->createMock(ValueApiInterface::class);
        $valueApi->expects($this->exactly(2))->method('putValues');

        $tester = $this->createCommandTester($fetcher, $parser, $valueApi);
        $tester->execute(['pollutants' => ['pm10', 'no2']]);

        $this->assertSame(0, $tester->getStatusCode());
    }
}
