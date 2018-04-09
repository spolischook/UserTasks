<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Model;

use App\Kernel;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;

class User extends AbstractModel implements PersistenceInterface
{
    const TABLE = 'users';

    /** @var int */
    protected $id;

    /** @var string */
    protected $username;

    /** @var string */
    protected $email;

    public function validate(): bool
    {
        return true;
    }

    public static function find($id): ?PersistenceInterface
    {
        $qb = Kernel::$db->createQueryBuilder();

        $data = $qb
            ->select('*')
            ->from(self::TABLE)
            ->where('id = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();

        if (empty($data)) {
            return null;
        }

        return new User($data);
    }

    public static function findByUsername($username)
    {
        $qb = Kernel::$db->createQueryBuilder();
        $data = $qb
            ->select('*')
            ->from(self::TABLE)
            ->where('username = ?')
            ->setParameter(0, $username)
            ->execute()
            ->fetch();

        if (empty($data)) {
            return null;
        }

        return new User($data);
    }

    public function save()
    {
        $data = [
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
        ];

        if ($this->getId()) {
            Kernel::$db->update(self::TABLE, $data, ['id' => $this->getId()]);
        } else {
            Kernel::$db->insert(self::TABLE, $data);
            $this->setId(Kernel::$db->lastInsertId());
        }
    }

    public static function createSchema(Schema $schema)
    {
        $usersTable = $schema->createTable(self::TABLE);
        $usersTable->addColumn("id", Type::INTEGER, ["unsigned" => true, "autoincrement" => true]);
        $usersTable->addColumn("username", Type::STRING, ["length" => 256]);
        $usersTable->addColumn("email", Type::STRING, ["length" => 256]);

        $usersTable->setPrimaryKey(array("id"));
        $usersTable->addUniqueIndex(['username'], 'uniq_username');
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): User
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }
}
