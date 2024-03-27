<?= "<?php\n"; ?>

namespace <?= $namespace; ?>;

<?= $use_statements; ?>
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use PHPZlc\PHPZlc\Doctrine\ORM\Repository\AbstractServiceEntityRepository;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rule;
use PHPZlc\PHPZlc\Doctrine\ORM\Rule\Rules;

/**
* @extends AbstractServiceEntityRepository<<?= $entity_class_name; ?>>
*
* @method <?= $entity_class_name; ?>|null find($id, $lockMode = null, $lockVersion = null)
* @method <?= $entity_class_name; ?>|null findOneBy(array $criteria, array $orderBy = null)
* @method <?= $entity_class_name; ?>|null    findAssoc($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
* @method <?= $entity_class_name; ?>|null   findLastAssoc($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
* @method <?= $entity_class_name; ?>|null    findAssocById($id, $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
* @method <?= $entity_class_name; ?>[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
* @method <?= $entity_class_name; ?>[]    findAll($rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
* @method <?= $entity_class_name; ?>[]    findLimitAll($rows, $page = 1, $rules = null, ResultSetMappingBuilder $resultSetMappingBuilder = null, $aliasChain = '')
*/
class <?= $class_name; ?> extends AbstractServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, <?= $entity_class_name; ?>::class);
    }

    public function registerRules()
    {
        // TODO: Implement registerRules() method (<?= $entity_class_name; ?>规则注册).
    }

    public function ruleRewrite(Rule $currentRule, Rules $rules, ResultSetMappingBuilder $resultSetMappingBuilder)
    {
        // TODO: Implement ruleRewrite() method (<?= $entity_class_name; ?>规则实现).
    }
}
