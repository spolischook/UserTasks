<?php
/**
 * Created by Serhii Polishchuk
 * Yet another test job
 */

namespace App\Model;

use App\Kernel;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Type;
use GuzzleHttp\Psr7\UploadedFile;
use Respect\Validation\Validator as v;

class Task extends AbstractModel implements PersistenceInterface
{
    const MAX_WIDTH = 320;
    const MAX_HEIGHT = 240;
    const TABLE = 'tasks';
    const IMAGE_QUALITY = 80;
    const ALLOWED_MIME_TYPES = [
        'image/png',
        'image/jpeg',
        'image/gif',
    ];
    const IMAGE_DIR = 'images';

    protected $id;

    protected $username;

    protected $email;

    protected $text;

    /** @var int */
    protected $status;

    /**
     * @var UploadedFile|string
     */
    protected $image;

    public function __construct(array $data)
    {
        parent::__construct($data);

        if (
            isset($this->image) &&
            is_object($this->image) &&
            UploadedFile::class === get_class($this->image)
        ) {
            $file = tempnam(sys_get_temp_dir(), '');
            try {
                $this->image->moveTo($file);
                $this->image = $file;
            } catch (\Exception $e) {
                $this->image = '';
                unlink($file);
            }
        }

        $this->status = 0;
    }

    public static function count()
    {
        $qb = Kernel::$db->createQueryBuilder();
        return (int) $qb
            ->select('count(t.id) as taskCount')
            ->from(self::TABLE, 't')
            ->execute()
            ->fetchColumn(0);
    }

    /**
     * todo: implement search by criteria
     * @param array $criteria
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return array
     */
    public static function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = Kernel::$db->createQueryBuilder();
        $qb
            ->select('u.username, u.email, t.status, t.id')
            ->from(self::TABLE, 't')
            ->leftJoin('t', User::TABLE, 'u', 't.user = u.id');

        if ($orderBy) {
            $qb->orderBy(key($orderBy), $orderBy[key($orderBy)]);
        }

        if ($limit) {
            $qb->setMaxResults($limit);
        }

        if ($offset) {
            $qb->setFirstResult($offset);
        }

        return $qb->execute()
            ->fetchAll();
    }

    public function save()
    {
        $user = User::findByUsername($this->getUsername());

        if (!$user) {
            $user = new User([
                'username' => $this->getUsername(),
                'email' => $this->getEmail(),
            ]);
            $user->save();
        }

        $this->moveImage();

        $data = [
            'user' => $user->getId(),
            'text' => $this->getText(),
            'image' => $this->getImage(),
            'status' => $this->isStatus(),
        ];

        if ($this->getId()) {
            Kernel::$db->update(self::TABLE, $data, ['id' => $this->getId()]);
        } else {
            Kernel::$db->insert(self::TABLE, $data);
            $this->setId(Kernel::$db->lastInsertId());
        }
    }

    private function moveImage()
    {
        if (false === strpos($this->image, sys_get_temp_dir())) {
            return;
        }

        $this->resizeImage();

        $mime = mime_content_type($this->image);
        $mimeParts = explode('/', $mime);
        $ext = array_pop($mimeParts);

        $newPath = implode('', [
            self::IMAGE_DIR,
            DIRECTORY_SEPARATOR,
            uniqid(),
            '.',
            $ext
        ]);

        $newFile = implode('', [
            Kernel::getPublicDir(),
            DIRECTORY_SEPARATOR,
            $newPath
        ]);
        rename($this->image, $newFile);
        $this->image = $newPath;
    }

    private function resizeImage()
    {
        $source = imagecreatefromjpeg($this->image);
        list($width, $height) = getimagesize($this->image);

        if ($width < self::MAX_WIDTH && $height < self::MAX_HEIGHT) {
            return;
        }

        $wDivider = $width/self::MAX_WIDTH;
        $hDivider = $height/self::MAX_HEIGHT;

        $divider = max($wDivider, $hDivider);

        $newwidth = $width/$divider;
        $newheight = $height/$divider;

        $destination = imagecreatetruecolor($newwidth, $newheight);
        imagecopyresampled($destination, $source, 0, 0, 0, 0, $newwidth, $newheight, $width, $height);

        imagejpeg($destination, $this->image, self::IMAGE_QUALITY);
    }

    /**
     * @param $id
     * @return Task|PersistenceInterface|null
     */
    public static function find($id): ?PersistenceInterface
    {
        $qb = Kernel::$db->createQueryBuilder();
        $data = $qb
            ->select('t.*, u.username as username, u.email as email')
            ->from(self::TABLE, 't')
            ->leftJoin('t', User::TABLE, 'u', 't.user = u.id')
            ->where('t.id = ?')
            ->setParameter(0, $id)
            ->execute()
            ->fetch();

        if (empty($data)) {
            return null;
        }

        return new Task($data);
    }

    public function validate(): bool
    {
        if (!v::stringType()->notEmpty()->validate($this->username)) {
            $this->errors[] = 'Username is empty';
        }

        if (!v::email()->validate($this->email)) {
            $this->errors[] = 'Email is invalid';
        }

        if (!v::stringType()->length(10, 200)->validate($this->text)) {
            $this->errors[] = 'Text length should be between 10 and 200 chapter length';
        }

        if (!v::stringType()->notEmpty()->validate($this->image)) {
            $this->errors[] = 'Image should be provided';
        } elseif (!is_file($this->image)) {
            $this->errors[] = 'Image is broken, provide another one';
        } elseif (!in_array(mime_content_type($this->image), self::ALLOWED_MIME_TYPES)) {
            $this->errors[] = sprintf(
                'Image should have one of "%s" mime types',
                implode(', ', self::ALLOWED_MIME_TYPES)
            );
        }

        return empty($this->errors);
    }

    public static function createSchema(Schema $schema)
    {
        $taskTable = $schema->createTable(self::TABLE);
        $taskTable->addColumn("id", Type::INTEGER, ["unsigned" => true, "autoincrement" => true]);
        $taskTable->addColumn('user', Type::INTEGER);
        $taskTable->addColumn('image', Type::TEXT);
        $taskTable->addColumn('text', Type::TEXT);
        $taskTable->addColumn('status', Type::INTEGER, ["default" => 0]);
        $taskTable->setPrimaryKey(array("id"));
        $taskTable->addForeignKeyConstraint(
            User::TABLE,
            ['user'],
            ['id']
        );
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername($username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText($text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage($image): self
    {
        $this->image = $image;

        return $this;
    }

    public function isStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): Task
    {
        $this->status = $status;
        return $this;
    }
}
