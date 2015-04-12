<?php

namespace Nimble\ElasticBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractBaseCommand extends ContainerAwareCommand
{
    const TASKS_PAD_LENGTH = 88;

    /**
     * Configures common formatting.
     */
    protected function configureFormatter(OutputInterface $output)
    {
        $output->getFormatter()->setStyle('info', new OutputFormatterStyle(
            'blue', null
        ));

        $output->getFormatter()->setStyle('error', new OutputFormatterStyle(
            'red', null
        ));

        $output->getFormatter()->setStyle('success', new OutputFormatterStyle(
            'green', null
        ));

        $output->getFormatter()->setStyle('warning', new OutputFormatterStyle(
            'yellow', null
        ));

        $output->getFormatter()->setStyle('graphic', new OutputFormatterStyle(
            'cyan', null
        ));
    }

    /**
     * @param OutputInterface $output
     * @return ProgressBar
     */
    protected function createProgressBar(OutputInterface $output)
    {
        $progress = new ProgressBar($output);

        $progress->setBarWidth(50);
        $progress->setFormat('%current%/%max% (%percent:2s%%) [%bar%] <info>%elapsed:6s%</info> (EST %estimated:6s%) %memory:6s%');
        $progress->setBarCharacter('<success>=</success>');
        $progress->setProgressCharacter('<success>></success>');
        $progress->setEmptyBarCharacter('<error>=</error>');

        return $progress;
    }

    /**
     * @param OutputInterface $output
     * @param string $name
     */
    protected function writeTaskStart(OutputInterface $output, $name)
    {
        $output->write(str_pad(sprintf('<graphic>•</graphic> %s ', $name), self::TASKS_PAD_LENGTH, '.', STR_PAD_RIGHT));
    }

    /**
     * @param OutputInterface $output
     */
    protected function writeTaskSuccess(OutputInterface $output)
    {
        $output->writeln(' <success>✓</success>');
    }

    /**
     * @param OutputInterface $output
     */
    protected function writeTaskFailure(OutputInterface $output)
    {
        $output->writeln(' <error>✗</error>');
    }

    /**
     * @param OutputInterface $output
     * @param string $message
     */
    protected function writeSuccessMessage(OutputInterface $output, $message = '')
    {
        $output->writeln("\n" . '<success>✓</success> ' . $message);
    }

    /**
     * @param OutputInterface $output
     * @param string $message
     */
    protected function writeErrorMessage(OutputInterface $output, $message = '')
    {
        $output->writeln("\n" . '<error>✗</error> ' . $message);
    }
}
