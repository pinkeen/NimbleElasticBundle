<?php

namespace Nimble\ElasticBundle\Command;

use Nimble\ElasticBundle\Index\IndexManager;
use Nimble\ElasticBundle\Populator\PopulatorManager;
use Nimble\ElasticBundle\Type\Type;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateCommand extends AbstractBaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('elastic:populate')
            ->setDescription('Populates indexes and types.')
            ->addOption('index', 'i', InputOption::VALUE_OPTIONAL, 'Name of the index to populate.')
            ->addOption('type', 't', InputOption::VALUE_OPTIONAL, 'Name of the type to populate.')
            ->addOption('batch', 'b', InputOption::VALUE_OPTIONAL, 'Size of single batch.', 100)
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
     * @return PopulatorManager
     */
    protected function getPopulatorManager()
    {
        return $this->getContainer()->get('nimble_elastic.populator_manager');
    }

    /**
     * @param Type $type
     * @param int $batchSize
     * @param OutputInterface $output
     */
    protected function populateType(Type $type, $batchSize, OutputInterface $output)
    {
        $progress = $this->createProgressBar($output);

        $output->writeln(sprintf('Populating type <info>%s.%s</info>.',
            $type->getIndex()->getName(),
            $type->getName()
        ));

        $count = $this->getPopulatorManager()->createPopulator($type)->populate($batchSize, $progress);

        if ($count === 0) {
            $output->writeln('<warning>No data found to populate.</warning>');
            return;
        }

        $this->writeSuccessMessage($output, sprintf('Successfully populated <info>%d</info> documents.', $count));
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
        $batchSize = $input->getOption('batch');

        /* TODO: Throw message if index/type not found or no fetcher for selected type defined. */

        foreach ($indexManager->getIndexes() as $index) {
            if (null !== $indexName && $index->getName() !== $indexName) {
                continue;
            }

            foreach ($index->getTypes() as $type) {
                if (null !== $typeName && $type->getName() !== $typeName) {
                    continue;
                }

                $this->populateType($type, $batchSize, $output);
            }
        }
    }
}
