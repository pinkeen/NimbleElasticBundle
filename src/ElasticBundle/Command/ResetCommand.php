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
     * @param array $indexIds
     * @param OutputInterface $output
     */
    protected function resetIndexes(array $indexIds, OutputInterface $output)
    {
        foreach ($indexIds as $indexId) {

            $index = $this->getIndexManager()->getIndex($indexId);

            if ($index->isAliased()) {
                $output->writeln(sprintf('Index is aliased - using elasticsearch index <info>%s</info>.',
                    $index->getName()
                ));
            }

            $this->writeTaskStart($output, sprintf('Resetting index <info>%s</info>', $indexId));

            $index->reset();

            $this->writeTaskSuccess($output);
        }
    }

    /**
     * @param array $indexIds
     * @param string $typeName
     * @param OutputInterface $output
     */
    protected function resetType(array $indexIds, $typeName, OutputInterface $output)
    {
        $typeFound = false;

        foreach ($indexIds as $indexId) {
            $index = $this->getIndexManager()->getIndex($indexId);

            if (!$index->hasType($typeName)) {
                continue;
            }

            if ($index->isAliased()) {
                $output->writeln(sprintf('Index is aliased - using elasticsearch type <info>%s.%s</info>.',
                    $index->getName(),
                    $typeName
                ));
            }

            $this->writeTaskStart($output, sprintf('Resetting type <info>%s.%s</info> ... ', $indexId, $typeName));

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

        $indexId = $input->getOption('index');
        $typeName = $input->getOption('type');

        if (null !== $indexId) {
            $indexIds = [$indexId];
        } else {
            $indexIds = $indexManager->getIndexIds();
        }

        if (empty($indexIds)) {
            throw new \RuntimeException('No indexes found.');
        }

        if (null !== $typeName) {
            $this->resetType($indexIds, $typeName, $output);
        } else {
            $this->resetIndexes($indexIds, $output);
        }

        $this->writeSuccessMessage($output, 'Reset finished successfully.');
    }
}
