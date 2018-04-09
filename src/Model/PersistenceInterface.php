<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Model;

use Doctrine\DBAL\Schema\Schema;

interface PersistenceInterface
{
    public static function find($id): ?PersistenceInterface;

    public function save();

    public static function createSchema(Schema $schema);
}
