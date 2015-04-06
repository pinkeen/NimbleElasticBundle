<?php

namespace Nimble\ElasticBundle\Command;

use Nimble\ElasticBundle\Index\IndexManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('nimble:elastic:reset')
            ->setDescription('Resets and index or all indexes.')
            ->addOption('index', 'i', InputOption::VALUE_OPTIONAL, 'Name of the index to reset. All are reset if null.')
        ;
    }

    /**
     * @return IndexManager
     */
    protected function getIndexManager()
    {
        return $this->getContainer()->get('nimble_elastic.index_manager');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indexManager = $this->getIndexManager();
        $indexesToReset = $indexManager->getIndexNames();

        $indexName = $input->getOption('index');

        if (null !== $indexName) {
            if (!$indexManager->hasIndex($indexName)) {
                throw new \InvalidArgumentException(sprintf('Index "%s" does not exist.', $indexName));
            }

            $indexesToReset = [$indexName];
        }

        foreach ($indexesToReset as $indexName) {
            $output->writeln(sprintf('Resetting index <info>%s</info>...', $indexName));

            $index = $indexManager->getIndex($indexName);
            $index->reset();
        }
    }
}
