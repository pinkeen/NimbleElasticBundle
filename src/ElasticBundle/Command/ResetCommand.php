<?php

namespace Nimble\ElasticBundle\Command;

use Nimble\ElasticBundle\Exception\TypeNotFoundException;
use Nimble\ElasticBundle\Index\IndexManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ResetCommand extends AbstractBaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('elastic:reset')
            ->setDescription('Resets and index or all indexes.')
            ->addOption('index', 'i', InputOption::VALUE_OPTIONAL, 'Name of the index to reset. All are reset if null.')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Name of the type to reset.')
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
     * @param array $indexNames
     * @param OutputInterface $output
     */
    protected function resetIndexes(array $indexNames, OutputInterface $output)
    {
        foreach ($indexNames as $indexName) {
            $this->writeTaskStart($output, sprintf('Resetting index <info>%s</info>', $indexName));

            $this->getIndexManager()->getIndex($indexName)->reset();

            $this->writeTaskSuccess($output);
        }
    }

    /**
     * @param array $indexNames
     * @param string $typeName
     * @param OutputInterface $output
     */
    protected function resetType(array $indexNames, $typeName, OutputInterface $output)
    {
        $typeFound = false;

        foreach ($indexNames as $indexName) {
            $index = $this->getIndexManager()->getIndex($indexName);

            if (!$index->hasType($typeName)) {
                continue;
            }

            $this->writeTaskStart($output, sprintf('Resetting type <info>%s.%s</info> ... ', $indexName, $typeName));

            $index->getType($typeName)->reset();
            $typeFound = true;

            $this->writeTaskSuccess($output);
        }

        if (!$typeFound) {
            throw new TypeNotFoundException($typeName);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->configureFormatter($output);

        $indexManager = $this->getIndexManager();

        $indexName = $input->getOption('index');
        $typeName = $input->getOption('type');

        if (null !== $indexName) {
            $indexNames = [$indexName];
        } else {
            $indexNames = $indexManager->getIndexNames();
        }

        if (empty($indexNames)) {
            throw new \RuntimeException('No indexes found.');
        }

        if (null !== $typeName) {
            $this->resetType($indexNames, $typeName, $output);
        } else {
            $this->resetIndexes($indexNames, $output);
        }

        $this->writeSuccessMessage($output, 'Reset finished successfully.');
    }
}
