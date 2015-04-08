<?php

namespace Nimble\ElasticBundle\Command;

use Nimble\ElasticBundle\Exception\TypeNotFoundException;
use Nimble\ElasticBundle\Index\IndexManager;
use Nimble\ElasticBundle\Populator\PopulatorManager;
use Nimble\ElasticBundle\Type\Type;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class PopulateCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('nimble:elastic:populate')
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
     * @param OutputInterface $output
     * @return ProgressBar
     */
    protected function createProgressBar(OutputInterface $output)
    {
        $progress = new ProgressBar($output);

        $progress->setBarWidth(50);
        $progress->setFormat("%current%/%max% (%percent:2s%%) [%bar%] %elapsed:6s% (EST %estimated:6s%) %memory:6s%");

        return $progress;
    }

    /**
     * @param Type $type
     * @param int $batchSize
     * @param OutputInterface $output
     */
    protected function populateType(Type $type, $batchSize, OutputInterface $output)
    {
        $progress = $this->createProgressBar($output);

        $output->writeln(sprintf('Populating type <info>%s.%s</info> ...',
            $type->getIndex()->getName(),
            $type->getName()
        ));

        $count = $this->getPopulatorManager()->createPopulator($type)->populate($batchSize, $progress);

        $output->writeln('');
        $output->writeln(sprintf('Successfully populated %d documents.', $count));
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
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
