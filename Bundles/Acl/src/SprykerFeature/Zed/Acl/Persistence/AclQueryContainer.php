<?php
namespace SprykerFeature\Zed\Acl\Persistence;

use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\Propel;
use SprykerEngine\Zed\Kernel\Persistence\AbstractQueryContainer;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use SprykerFeature\Zed\Acl\AclConfig;
use SprykerFeature\Shared\Acl\Transfer\RoleCollection;
use SprykerFeature\Zed\Acl\Persistence\Propel\Base\SpyAclUserHasGroupQuery;
use SprykerFeature\Zed\Acl\Persistence\Propel\Map\SpyAclGroupsHasRolesTableMap;
use SprykerFeature\Zed\Acl\Persistence\Propel\Map\SpyAclGroupTableMap;
use SprykerFeature\Zed\Acl\Persistence\Propel\Map\SpyAclRoleTableMap;
use SprykerFeature\Zed\Acl\Persistence\Propel\Map\SpyAclRuleTableMap;
use SprykerFeature\Zed\Acl\Persistence\Propel\Map\SpyAclUserHasGroupTableMap;
use SprykerFeature\Zed\Acl\Persistence\Propel\SpyAclGroupQuery;
use SprykerFeature\Zed\Acl\Persistence\Propel\SpyAclGroupsHasRolesQuery;
use SprykerFeature\Zed\Acl\Persistence\Propel\SpyAclRuleQuery;
use SprykerFeature\Zed\Acl\Persistence\Propel\SpyAclRoleQuery;
use SprykerFeature\Zed\Library\Propel\Formatter\PropelArraySetFormatter;
use SprykerFeature\Zed\User\Persistence\Propel\Map\SpyUserUserTableMap;
use SprykerFeature\Zed\User\Persistence\Propel\SpyUserUserQuery;

/**
 * @method AclDependencyContainer getDependencyContainer()
 */
class AclQueryContainer extends AbstractQueryContainer
{
    const ROLE_NAME = 'role_name';
    const TYPE = 'type';
    const BUNDLE = 'bundle';
    const CONTROLLER = 'controller';
    const ACTION = 'action';
    const HAS_ROLE = 'has_role';
    const SPY_ACL_GROUPS_HAS_ROLES = 'SpyAclGroupsHasRoles';
    const GROUP_NAME = 'group_name';
    const ID_ACL_GROUP = 'id_acl_group';
    const GROUP_JOIN = 'groupJoin';

    /**
     * @param string $name
     *
     * @return SpyAclGroupQuery
     */
    public function queryGroupByName($name)
    {
        $query = $this->queryGroup();

        $query->filterByName($name);

        return $query;
    }

    /**
     * @param int $id
     *
     * @return SpyAclGroupQuery
     */
    public function queryGroupById($id)
    {
        $query = $this->queryGroup();

        $query->filterByIdAclGroup($id);

        return $query;
    }

    /**
     * @return SpyAclGroupQuery
     */
    public function queryGroup()
    {
        return $this->getDependencyContainer()->createGroupQuery();
    }

    /**
     * @param int $id
     *
     * @return SpyAclGroupQuery
     */
    public function queryRoleById($id)
    {
        $query = $this->getDependencyContainer()->createRoleQuery();

        $query->filterByIdAclRole($id);

        return $query;
    }

    /**
     * @param string $name
     *
     * @return SpyAclRoleQuery
     */
    public function queryRoleByName($name)
    {
        $query = $this->getDependencyContainer()->createRoleQuery();

        $query->filterByName($name);

        return $query;
    }

    /**
     * @param int $idGroup
     * @param int $idRole
     *
     * @return SpyAclGroupsHasRolesQuery
     */
    public function queryGroupHasRoleById($idGroup, $idRole)
    {
        $query = $this->getDependencyContainer()->createGroupHasRoleQuery();

        $query->filterByFkAclGroup($idGroup)
            ->filterByFkAclRole($idRole);

        return $query;
    }

    /**
     * @param int $idGroup
     * @param int $idUser
     *
     * @return SpyAclUserHasGroupQuery
     */
    public function queryUserHasGroupById($idGroup, $idUser)
    {
        $query = $this->getDependencyContainer()->createUserHasRoleQuery();

        $query->filterByFkAclGroup($idGroup)
              ->filterByFkUserUser($idUser);

        return $query;
    }

    /**
     * @param int $idGroup
     *
     * @return SpyUserUserQuery
     */
    public function queryGroupUsers($idGroup)
    {
        $query = $this->getDependencyContainer()->createUserQuery();

        $join = new Join();

        $join->addCondition(
            SpyUserUserTableMap::COL_ID_USER_USER,
            SpyAclUserHasGroupTableMap::COL_FK_USER_USER
        );

        $query->addJoinObject($join, self::GROUP_JOIN);

        $condition = sprintf('%s = %s', SpyAclUserHasGroupTableMap::COL_FK_ACL_GROUP, $idGroup);
        $query->addJoinCondition(
            self::GROUP_JOIN,
            $condition
        );

        return $query;
    }

    /**
     * @param int $idGroup
     *
     * @return SpyAclRoleQuery
     */
    public function queryGroupRoles($idGroup)
    {
        $query = $this->getDependencyContainer()->createRoleQuery();

        $query->useSpyAclGroupsHasRolesQuery()
            ->filterByFkAclGroup($idGroup)
            ->endUse();

        return $query;
    }

    /**
     * @param int $id
     *
     * @return SpyAclRuleQuery
     */
    public function queryRuleById($id)
    {
        $query = $this->getDependencyContainer()->createRuleQuery();

        $query->filterByIdAclRule($id);

        return $query;
    }

    /**
     * @param ObjectCollection $relationshipCollection
     *
     * @return SpyAclRuleQuery
     */
    public function queryGroupRules(ObjectCollection $relationshipCollection)
    {
        $query = $this->getDependencyContainer()->createRuleQuery();
        $query->useAclRoleQuery()->filterBySpyAclGroupsHasRoles($relationshipCollection)->endUse();

        return $query;
    }

    /**
     * @param int $idGroup
     *
     * @return SpyAclGroupsHasRolesQuery
     */
    public function queryGroupHasRole($idGroup)
    {
        $query = $this->getDependencyContainer()->createGroupHasRoleQuery();
        $query->filterByFkAclGroup($idGroup);

        return $query;
    }

    /**
     * @param RoleCollection $roles
     * @param string $bundle
     * @param string $controller
     * @param string $action
     *
     * @return SpyAclRuleQuery
     */
    public function queryRuleByPathAndRoles(
        RoleCollection $roles,
        $bundle = AclConfig::VALIDATOR_WILDCARD,
        $controller = AclConfig::VALIDATOR_WILDCARD,
        $action = AclConfig::VALIDATOR_WILDCARD
    ) {
        $query = $this->getDependencyContainer()->createRuleQuery();

        if ($bundle !== AclConfig::VALIDATOR_WILDCARD) {
            $query->filterByBundle($bundle);
        }

        if ($controller !== AclConfig::VALIDATOR_WILDCARD) {
            $query->filterByController($controller);
        }

        if ($action !== AclConfig::VALIDATOR_WILDCARD) {
            $query->filterByAction($action);
        }

        $inRoles = [];
        foreach ($roles as $role) {
            $inRoles[] = $role->getIdAclRole();
        }

        $query->filterByFkAclRole($inRoles, Criteria::IN);

        return $query;
    }

    /**
     * @param int $idUser
     *
     * @return SpyAclGroupQuery
     */
    public function queryUserGroupByIdUser($idUser)
    {
        $query = $this->getDependencyContainer()->createGroupQuery();
        $query->useSpyAclUserHasGroupQuery()
            ->filterByFkUserUser($idUser)
            ->endUse();

        return $query;
    }

    /**
     * @return SpyUserUserQuery
     */
    public function queryUsersWithGroup()
    {
        $query = $this->getDependencyContainer()->createUserQuery();

        $query->addJoin(
            SpyUserUserTableMap::COL_ID_USER_USER,
            SpyAclUserHasGroupTableMap::COL_FK_USER_USER,
            Criteria::LEFT_JOIN
        );

        $query->addJoin(
            SpyAclUserHasGroupTableMap::COL_FK_ACL_GROUP,
            SpyAclGroupTableMap::COL_ID_ACL_GROUP,
            Criteria::LEFT_JOIN
        );

        $query->withColumn(SpyAclGroupTableMap::COL_NAME, self::GROUP_NAME);
        $query->withColumn(SpyAclGroupTableMap::COL_ID_ACL_GROUP, self::ID_ACL_GROUP);

        return $query;
    }

    /**
     * @param int $idGroup
     *
     * @return SpyUserUserQuery
     */
    public function queryUsersWithGroupByGroupId($idGroup)
    {
        $query = $this->queryUsersWithGroup();
        $query->filterBy(SpyAclUserHasGroupTableMap::COL_FK_ACL_GROUP, $idGroup);

        return $query;
    }

    /**
     * @param int $idGroup
     *
     * @return SpyAclRoleQuery
     */
    public function queryRulesFromGroup($idGroup)
    {
        $query = $this->getDependencyContainer()->createRoleQuery();
        $query->joinAclRule();
        $query->leftJoinSpyAclGroupsHasRoles();

        $condition = sprintf('%s = %s', SpyAclGroupsHasRolesTableMap::COL_FK_ACL_GROUP, $idGroup);
        $query->addJoinCondition(
            self::SPY_ACL_GROUPS_HAS_ROLES,
            $condition
        );

        $hasRole = sprintf("COUNT(%s)", SpyAclGroupsHasRolesTableMap::COL_FK_ACL_ROLE);

        $query->withColumn(SpyAclRoleTableMap::COL_NAME, self::ROLE_NAME);
        $query->withColumn(SpyAclRuleTableMap::COL_TYPE, self::TYPE);
        $query->withColumn(SpyAclRuleTableMap::COL_BUNDLE, self::BUNDLE);
        $query->withColumn(SpyAclRuleTableMap::COL_CONTROLLER, self::CONTROLLER);
        $query->withColumn(SpyAclRuleTableMap::COL_ACTION, self::ACTION);
        $query->withColumn($hasRole, self::HAS_ROLE);

        return $query;
    }
}