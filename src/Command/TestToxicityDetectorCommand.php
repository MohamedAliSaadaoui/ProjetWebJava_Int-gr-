<?php

namespace App\Command;

use App\Service\ToxicityDetector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TestToxicityDetectorCommand extends Command
{
    protected static $defaultName = 'app:test-toxicity-detector';
    protected static $defaultDescription = 'Test the ToxicityDetector service with sample text';

    private ToxicityDetector $toxicityDetector;

    public function __construct(ToxicityDetector $toxicityDetector)
    {
        $this->toxicityDetector = $toxicityDetector;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('text', InputArgument::OPTIONAL, 'The text to analyze for toxicity', 'This is a test. This is a clean text.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $text = $input->getArgument('text');

        $io->title('Testing ToxicityDetector Service');
        
        // Test API connection first
        $io->section('Testing API Connection');
        $connectionTest = $this->toxicityDetector->testConnection();
        
        if ($connectionTest) {
            $io->success('✅ API Connection successful!');
            
            // Show available models if connection works
            $io->section('Available Models');
            $models = $this->toxicityDetector->getAvailableModels();
            
            if (!empty($models)) {
                $io->listing(array_slice($models, 0, 20)); // Show first 20 models to avoid overwhelming output
                $io->note('If your model isn\'t listed here, your API key may not have access to it.');
            } else {
                $io->warning('Could not retrieve model list.');
            }
        } else {
            $io->error('❌ API Connection failed!');
            $io->note('Check your API key in .env.local and make sure it has the right permissions.');
            return Command::FAILURE;
        }
        
        // Test toxicity analysis
        $io->section('Testing Toxicity Analysis');
        $io->text('Analyzing text: ' . $text);
        
        try {
            $result = $this->toxicityDetector->analyzeToxicity($text);
            
            $io->text('Results:');
            $io->table(
                ['Property', 'Value'],
                [
                    ['Is Toxic', $result['isToxic'] ? 'Yes' : 'No'],
                    ['Toxic Words', is_array($result['toxicWords']) ? implode(', ', $result['toxicWords']) : 'N/A'],
                    ['Reason', $result['reason'] ?? 'N/A'],
                ]
            );
            
            if ($result['isToxic']) {
                $io->warning('Text was identified as toxic');
            } else {
                $io->success('Text is clean');
            }
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Error during toxicity analysis: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 