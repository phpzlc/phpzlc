<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

use <?= $entity_full_class_name; ?>;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PHPZlc\PHPZlc\Doctrine\ORM\Repository\AbstractServiceEntityRepository;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rules;
use PHPZlc\Validate\Validate;
<?= $with_password_upgrade ? "use Symfony\Component\Security\Core\Exception\UnsupportedUserException;\n" : '' ?>
<?= $with_password_upgrade ? "use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;\n" : '' ?>
<?= $with_password_upgrade ? "use Symfony\Component\Security\Core\User\UserInterface;\n" : '' ?>

/**
 * @method <?= $entity_class_name; ?>|null find($id, $lockMode = null, $lockVersion = null)
 * @method <?= $entity_class_name; ?>|null findOneBy(array $criteria, array $orderBy = null)
 * @method <?= $entity_class_name; ?>[]    findAll()
 * @method <?= $entity_class_name; ?>[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class <?= $class_name; ?> extends AbstractServiceEntityRepository<?= $with_password_upgrade ? " implements PasswordUpgraderInterface\n" : "\n" ?>
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, <?= $entity_class_name; ?>::class);
    }

    public function registerRules()
    {
        // TODO: Implement registerRules() method.
    }

    public function ruleRewrite(Rule $currentRule, Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder)
    {
        // TODO: Implement ruleRewrite() method.
    }

    /**
    * @param <?= $entity_class_name; ?> $entity
    * @param array $params
    * @return array
    */
    protected function toArray($entity, array $params): array
    {
        return parent::toArray($entity, $params);
    }
}
