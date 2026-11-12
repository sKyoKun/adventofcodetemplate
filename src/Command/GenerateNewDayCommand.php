<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class GenerateNewDayCommand extends Command
{
    private const CONTROLLER_PATH = './src/Controller/';
    private const SERVICE_PATH = './src/Services/';
    private const CONTROLLER_TEST_PATH = './features/';
    private const SERVICE_TEST_PATH = './tests/Services/';
    private const EXERCISES_FILE_PATH = './public/Files/';

    private string $name = 'generate:day';

    public function __construct()
    {
        parent::__construct($this->name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!is_numeric($input->getArgument('dayNumber'))) {
            return Command::FAILURE;
        }

        $day = $input->getArgument('dayNumber');
        $finder = new Finder();
        $filesystem = new Filesystem();

        $finder->files()->in(__DIR__.'/../../templates');

        if (false === $finder->hasResults()) {
            throw new \Exception('No templates found');
        }

        foreach ($finder as $file) {
            $content = $file->getContents();
            $content = str_replace('[calendarDay]', $day, $content);
            $path = null;
            if (str_contains($content, 'namespace App\Controller')) {
                $fileName = 'Day'.$day.'Controller.php';
                $path = self::CONTROLLER_PATH.$fileName;
            } elseif (str_contains($content, 'namespace App\Services')) {
                $fileName = 'Day'.$day.'Services.php';
                $path = self::SERVICE_PATH.$fileName;
            } elseif (str_contains($content, 'Feature:')) {
                $fileName = 'day'.$day.'.feature';
                $path = self::CONTROLLER_TEST_PATH.$fileName;
            } elseif (str_contains($content, 'namespace App\Tests\Services')) {
                $fileName = 'Day'.$day.'ServicesTest.php';
                $path = self::SERVICE_TEST_PATH.$fileName;
            }
            if (false === $filesystem->exists($path)) {
                $filesystem->appendToFile($path, $content);
            }

            $inputPath = self::EXERCISES_FILE_PATH.'day'.$day.'.txt';
            $inputTestPath = self::EXERCISES_FILE_PATH.'day'.$day.'test.txt';
            if (false === $filesystem->exists($inputPath)) {
                $filesystem->appendToFile($inputPath, '');
            }
            if (false === $filesystem->exists($inputTestPath)) {
                $filesystem->appendToFile($inputTestPath, '');
            }
        }

        return Command::SUCCESS;
    }

    protected function configure(): void
    {
        $this->setDescription('Adds new day files in the project.')
            ->addArgument('dayNumber', InputArgument::REQUIRED, 'Advent day')
        ;
    }
}
