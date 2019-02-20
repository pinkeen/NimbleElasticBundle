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
     * @var IndexManager
     */
    private $indexManager;

    /**
     * @var PopulatorManager
     */
    private $populatorManager;

    /**
     * PopulateCommand constructor.
     *
     * @param IndexManager $indexManager
     * @param PopulatorManager $populatorManager
     * @param null|string $name
     */
    public function __construct(
        IndexManager $indexManager,
        PopulatorManager $populatorManager,
        $name = null
    )
    {
        parent::__construct($name);
        $this->indexManager = $indexManager;
        $this->populatorManager = $populatorManager;
    }

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
     * @param Type $type
     * @param int $batchSize
     * @param OutputInterface $output
     */
    protected function populateType(Type $type, $batchSize, OutputInterface $output)
    {
        $progress = $this->createProgressBar($output);

        if ($type->getIndex()->isAliased()) {
            $output->writeln(sprintf('Index is aliased - using elasticsearch type <info>%s.%s</info>.',
                $type->getIndex()->getName(),
                $type->getName()
            ));
        }

        $output->writeln(sprintf('Populating type <info>%s.%s</info>.',
            $type->getIndex()->getId(),
            $type->getName()
        ));

        $count = $this->populatorManager->createPopulator($type)->populate($batchSize, $progress);

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
        $indexId = $input->getOption('index');
        $typeName = $input->getOption('type');
        $batchSize = $input->getOption('batch');

        /* TODO: Throw message if index/type not found or no fetcher for selected type defined. */

        foreach ($this->indexManager->getIndexes() as $index) {
            if (null !== $indexId && $index->getId() !== $indexId) {
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
