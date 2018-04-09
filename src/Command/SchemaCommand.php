<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Command;

use App\Kernel;
use App\Model\Task;
use App\Model\User;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SchemaCommand extends Command
{
    protected $kernel;
    
    public function __construct(Kernel $kernel)
    {
        $this->kernel = $kernel;
        parent::__construct('app:schema:create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->kernel->createContainer();
        $db = $container->get(Connection::class);
        $sm = $db->getSchemaManager();

        $sm->dropAndCreateDatabase(basename($db->getDatabase()));
        $schema = new Schema();

        User::createSchema($schema);
        Task::createSchema($schema);

        $platform = $db->getDatabasePlatform();
        $queries = $schema->toSql($platform);

        foreach ($queries as $query) {
            $db->exec($query);
        }

        $output->writeln('');
    }
}
