<?php
/**
 * Created by PhpStorm.
 * User: stas
 * Date: 09.07.18
 * Time: 0:30
 */

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class ParseFreelancingJobCommand extends Command
{
    private $availableArgs = [
        'fl' => TRUE,
        'freelansim' => TRUE,
    ];
    protected function configure()
    {
        $this
            ->setName('app:parse-freelancing-job')
            ->setDescription('Parse and send to telegram freelancing job.')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp('This command allows you to parse')
        ;
        $this->addArgument('site_name', InputArgument::REQUIRED, 'short name of site');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!array_key_exists($input->getArgument('site_name'), $this->availableArgs)) {
            throw new \RuntimeException('Available\'s site_name "fl", "freelansim"');
        }

    }
}