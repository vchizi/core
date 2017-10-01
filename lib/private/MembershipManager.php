<?php
/**
 * @author Piotr Mrowczynski <piotr@owncloud.com>
 *
 * @copyright Copyright (c) 2017, ownCloud GmbH
 * @license AGPL-3.0
 *
 * This code is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License, version 3,
 * along with this program.  If not, see <http://www.gnu.org/licenses/>
 *
 */

namespace OC;

use OC\Group\BackendGroup;
use OC\User\Account;
use OCP\AppFramework\Db\Access;
use OCP\AppFramework\Db\Entity;
use OCP\IConfig;
use OCP\IDBConnection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use OCP\DB\QueryBuilder\IQueryBuilder;

class MembershipManager extends Access {

	/**
	 * types of memberships in the group
	 */
	const MEMBERSHIP_TYPE_GROUP_USER = 0;
	const MEMBERSHIP_TYPE_GROUP_ADMIN = 1;

	/* @var IConfig */
	protected $config;

	/** @var \OC\Group\GroupMapper */
	private $groupMapper;

	/** @var \OC\Group\GroupMapper */
	private $accountMapper;

	/** @var \OC\User\AccountTermMapper */
	private $termMapper;

	/** @var \OC\User\Manager */
	private $userManager;

	/** @var \OC\Group\Manager */
	private $groupManager;

	public function __construct(IDBConnection $db, IConfig $config,
								\OC\User\AccountMapper $accountMapper, \OC\Group\GroupMapper $groupMapper, \OC\User\AccountTermMapper $termMapper,
								\OC\User\Manager $userManager, \OC\Group\Manager $groupManager) {
		parent::__construct($db, 'memberships');
		$this->config = $config;
		$this->accountMapper = $accountMapper;
		$this->groupMapper = $groupMapper;
		$this->termMapper = $termMapper;
		$this->userManager = $userManager;
		$this->groupManager = $groupManager;
	}

	private function getTableAlias() {
		return 'm';
	}

	/**
	 * Return backend group entities for given account (identified by user's uid)
	 *
	 * @param int $userId
	 *
	 * @return BackendGroup[]
	 */
	public function getUserBackendGroups($userId) {
		return $this->getBackendGroupsSqlQuery($userId, self::MEMBERSHIP_TYPE_GROUP_USER);
	}

	/**
	 * Return backend group entities for given account (identified by user's uid) of which
	 * the user is admin.
	 *
	 * @param int $userId
	 *
	 * @return BackendGroup[]
	 */
	public function getAdminBackendGroups($userId) {
		return $this->getBackendGroupsSqlQuery($userId, self::MEMBERSHIP_TYPE_GROUP_ADMIN);
	}

	/**
	 * Return user account entities for given group (identified with gid)
	 *
	 * @param string $gid
	 *
	 * @return Account[]
	 */
	public function getGroupUserAccounts($gid) {
		return $this->getAccountsSqlQuery($gid, self::MEMBERSHIP_TYPE_GROUP_USER);
	}

	/**
	 * Return admin account entities for given group (identified with gid)
	 *
	 * @param string $gid
	 *
	 * @return Account[]
	 */
	public function getGroupAdminAccounts($gid) {
		return $this->getAccountsSqlQuery($gid, self::MEMBERSHIP_TYPE_GROUP_ADMIN);

	}

	/**
	 * Return admin account entities for all backend groups
	 *	 *
	 * @return Account[]
	 */
	public function getAdminAccounts() {
		return $this->getAccountsSqlQuery(null, self::MEMBERSHIP_TYPE_GROUP_ADMIN);
	}

	/**
	 * Check whether given account (identified by user's uid) is user of
	 * the group (identified with gid)
	 *
	 * @param int $userId
	 * @param string $gid
	 *
	 * @return boolean
	 */
	public function isGroupUser($userId, $gid) {
		return $this->isGroupMember($userId, $gid, self::MEMBERSHIP_TYPE_GROUP_USER);
	}

	/**
	 * Check whether given account (identified by user's uid) is admin of
	 * the group (identified with gid)
	 *
	 * @param int $userId
	 * @param string $gid
	 *
	 * @return boolean
	 */
	public function isGroupAdmin($userId, $gid) {
		return $this->isGroupMember($userId, $gid, self::MEMBERSHIP_TYPE_GROUP_ADMIN);

	}

	/**
	 * Search for members which match the pattern and
	 * are users in the group (identified with gid)
	 *
	 * @param string $gid
	 * @param string $pattern
	 * @param integer $limit
	 * @param integer $offset
	 * @return Entity[]
	 */
	public function find($gid, $pattern, $limit, $offset) {
		return $this->searchQuery($gid, $pattern, $limit, $offset);
	}

	/**
	 * Count members which match the pattern and
	 * are users in the group (identified with gid)
	 *
	 * @param string $gid
	 * @param string $pattern
	 * @param integer $limit
	 * @param integer $offset
	 * @return int
	 */
	public function count($gid, $pattern, $limit, $offset) {
		return count($this->searchQuery($gid, $pattern, $limit, $offset));
	}

	/**
	 * Add a group account (identified by user's uid) to group.
	 *
	 * @param int $userId
	 * @param string $gid group user becomes member of
	 * @return bool
	 */
	public function addGroupUser($userId, $gid) {
		return $this->addGroupMember($userId, $gid, self::MEMBERSHIP_TYPE_GROUP_USER);
	}

	/**
	 * Add a group admin account (identified by user's uid) to the group.
	 *
	 * @param int $userId
	 * @param string $gid group user becomes admin of
	 * @return bool
	 */
	public function addGroupAdmin($userId, $gid) {
		return $this->addGroupMember($userId, $gid, self::MEMBERSHIP_TYPE_GROUP_ADMIN);
	}

	/**
	 * Delete a group user (identified by user's uid)
	 * from group.
	 *
	 * @param int $userId
	 * @param string $gid group user is member of
	 * @return bool
	 */
	public function removeGroupUser($userId, $gid) {
		return $this->removeGroupMemberships($gid, $userId, [self::MEMBERSHIP_TYPE_GROUP_USER]);
	}

	/**
	 * Delete a group admin (identified by user's uid)
	 * from group.
	 *
	 * @param int $userId
	 * @param string $gid group user is member of
	 * @return bool
	 */
	public function removeGroupAdmin($userId, $gid) {
		return $this->removeGroupMemberships($gid, $userId, [self::MEMBERSHIP_TYPE_GROUP_ADMIN]);
	}

	/**
	 * Removes members from group (identified by group's gid),
	 * regardless of the role in the group.
	 *
	 * @param string $gid group user is member of
	 * @return bool
	 */
	public function removeGroupMembers($gid) {
		return $this->removeGroupMemberships($gid, null, [self::MEMBERSHIP_TYPE_GROUP_USER, self::MEMBERSHIP_TYPE_GROUP_ADMIN]);
	}

	/**
	 * Delete the memberships of user (identified by user's uid),
	 * regardless of the role in the group.
	 *
	 * @param int $userId
	 * @return bool
	 */
	public function removeMemberships($userId) {
		return $this->removeGroupMemberships(null, $userId, [self::MEMBERSHIP_TYPE_GROUP_USER, self::MEMBERSHIP_TYPE_GROUP_ADMIN]);
	}

	/**
	 * Check if the given user is member of the group with specific membership type
	 *
	 * @param int $userId
	 * @param string $gid
	 *
	 * @return boolean
	 */
	private function isGroupMember($userId, $gid, $membershipType) {
		$qb = $this->db->getQueryBuilder();
		$alias = $this->getTableAlias();
		$qb->select($qb->expr()->literal('1'))
			->from($this->getTableName(), $alias);

		$qb = $this->applyPredicates($qb, $userId, $gid);

		// Place predicate on membership_type
		$qb->andWhere($qb->expr()->eq($alias.'.membership_type', $qb->createNamedParameter($membershipType)));
		$resultArray = $qb->execute()->fetchAll();

		return empty($resultArray);
	}


	/**
	 * Add user to the group with specific membership type $membershipType
	 *
	 * //FIXME: Can we use INSERT INTO ... SELECT .. FROM .. ?
	 *
	 * @param string $gid
	 * @param int $userId
	 * @param string $membershipType
	 *
	 * @return boolean
	 */
	private function addGroupMember($userId, $gid, $membershipType) {
		$qb = $this->db->getQueryBuilder();
		$user = $this->userManager->get($userId);
		$group = $this->groupManager->get($gid);

		if (!is_null($user) && !is_null($user)) {
			$qb->insert($this->getTableName())
				->values([
					'backend_group_id' => $qb->createNamedParameter($group->getBackendGroupId()),
					'account_id' => $qb->createNamedParameter($user->getAccountId()),
					'membership_type' => $qb->createNamedParameter($membershipType),
				]);

			try {
				$qb->execute();
				return true;
			} catch (UniqueConstraintViolationException $e) {
				// TODO: hmmm raise some warning?
			}
		}

		return false;
	}


	/*
	 * Removes users from the groups. If the predicate on a user or group is null, then it will apply
	 * removal to all the entries of that type.
	 *
	 * @param string|null $gid
	 * @param int|null $userId
	 * @param int[] $membershipTypeArray
	 *
	 * @return boolean
	 */
	private function removeGroupMemberships($gid, $userId, $membershipTypeArray) {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName(), $this->getTableAlias());

		if (!is_null($gid) && !is_null($userId)) {
			// Both $gid and $userId predicates are specified
			$qb = $this->applyPredicates($qb, $userId, $gid);
		} else if (!is_null($gid)) {
			// Group predicate $gid specified
			$qb = $this->applyGroupPredicates($qb, $gid);
		} else if (!is_null($userId)) {
			// User predicate $userId specified
			$qb = $this->applyUserPredicates($qb, $userId);
		} else {
			return false;
		}

		$qb->andWhere($qb->expr()->in('membership_type',
				$qb->createNamedParameter($membershipTypeArray, IQueryBuilder::PARAM_INT_ARRAY)));
		$qb->execute();

		return true;
	}

	/*
	 * Return backend group entities for given user uid $userId of which
	 * the user has specific membership type
	 *
	 * @param int $userId
	 * @param int $membershipType
	 *
	 * @return BackendGroup[]
	 */
	private function getBackendGroupsSqlQuery($userId, $membershipType) {
		$qb = $this->db->getQueryBuilder();
		$alias = $this->getTableAlias();
		$qb->select(['g.id', 'g.group_id', 'g.display_name', 'g.backend'])
			->from($this->getTableName(), $alias)
			->innerJoin($alias, $this->groupMapper->getTableName(), 'g', $qb->expr()->eq('g.id', $alias.'.backend_group_id'));

		$qb = $this->applyUserPredicates($qb, $userId);

		// Place predicate on membership_type
		$qb->andWhere($qb->expr()->eq($alias.'membership_type', $qb->createNamedParameter($membershipType)));

		$stmt = $qb->execute();
		$groups = [];
		while($attributes = $stmt->fetch()){
			// Map attributes in array to BackendGroup
			$groups[] = $this->groupMapper->mapRowToEntity($attributes);
		}

		$stmt->closeCursor();

		return $groups;
	}

	/**
	 * Return account entities for given group gid $gid of which
	 * the accounts have specific membership type. If gid is not specified, it will
	 * return result for all groups.
	 *
	 * @param string|null $gid
	 * @param int $membershipType
	 * @return Account[]
	 */
	private function getAccountsSqlQuery($gid, $membershipType) {
		$qb = $this->db->getQueryBuilder();
		$alias = $this->getTableAlias();
		$qb->select(['a.id','a.user_id', 'a.lower_user_id', 'a.display_name', 'a.email', 'a.last_login', 'a.backend', 'a.state', 'a.quota', 'a.home'])
			->from($this->getTableName(), 'm')
			->innerJoin('m', $this->accountMapper->getTableName(), 'a', $qb->expr()->eq('a.id', 'm.account_id'));

		if (!is_null($gid)) {
			$qb = $this->applyGroupPredicates($qb, $gid);
		}

		// Place predicate on membership_type
		$qb->andWhere($qb->expr()->eq($alias.'membership_type', $qb->createNamedParameter($membershipType)));

		return $this->getAccountsQuery($qb);
	}

	/**
	 * @param IQueryBuilder $qb
	 * @return Account[]
	 */
	private function getAccountsQuery($qb) {
		$stmt = $qb->execute();
		$accounts = [];
		while($attributes = $stmt->fetch()){
			// Map attributes in array to Account
			$accounts[] = $this->accountMapper->mapRowToEntity($attributes);
		}

		$stmt->closeCursor();
		return $accounts;
	}

	/**
	 * Search for members which match the pattern and
	 * are users in the group (identified with gid)
	 *
	 * @param string $gid
	 * @param string $pattern
	 * @param integer $limit
	 * @param integer $offset
	 * @return Account[]
	 */
	private function searchQuery($gid, $pattern, $limit = null, $offset = null) {
		$alias = $this->getTableAlias();
		$allowMedialSearches = $this->config->getSystemValue('accounts.enable_medial_search', true);
		if ($allowMedialSearches) {
			$parameter = '%' . $this->db->escapeLikeParameter($pattern) . '%';
			$loweredParameter = '%' . $this->db->escapeLikeParameter(strtolower($pattern)) . '%';
		} else {
			$parameter = $this->db->escapeLikeParameter($pattern) . '%';
			$loweredParameter = $this->db->escapeLikeParameter(strtolower($pattern)) . '%';
		}

		// Optimize query if patter is an empty string, and we can retrieve information with faster query
		$emptyPattern = empty($pattern) ? true : false;

		$qb = $this->db->getQueryBuilder();
		$qb->selectAlias('DISTINCT a.id', 'id')
			->addSelect(['a.user_id', 'a.lower_user_id', 'a.display_name', 'a.email', 'a.last_login', 'a.backend', 'a.state', 'a.quota', 'a.home'])
			->from($this->getTableName(), 'a')
			->innerJoin('a', $this->getTableName(), $alias, $qb->expr()->eq('a.id', $alias.'.account_id'));

		if ($emptyPattern) {
			$qb->leftJoin('a', $this->termMapper->getTableName(), 't', $qb->expr()->eq('a.id', 't.account_id'));
		}

		$qb = $this->applyGroupPredicates($qb, $gid);


		if (!$emptyPattern) {
			// Non empty pattern means that we need to set predicates on parameters
			// and just fetch all users
			$qb->andwhere($qb->expr()->like('a.lower_user_id', $qb->createNamedParameter($loweredParameter)))
				->orWhere($qb->expr()->iLike('a.display_name', $qb->createNamedParameter($parameter)))
				->orWhere($qb->expr()->iLike('a.email', $qb->createNamedParameter($parameter)))
				->orWhere($qb->expr()->like('t.term', $qb->createNamedParameter($loweredParameter)));
		}

		// Place predicate on membership_type
		$qb->andWhere($qb->expr()->eq($alias.'membership_type', $qb->createNamedParameter(self::MEMBERSHIP_TYPE_GROUP_USER)));

		// Order by display_name so we can use limit and offset
		$qb->orderBy('display_name');

		/** @var Account[] $accounts */
		$accounts = [];
		$stmt = $this->execute($qb->getSQL(), $qb->getParameters(), $limit, $offset);
		while($row = $stmt->fetch()){
			$accounts[] = $this->accountMapper->mapRowToEntity($row);
		}

		return $accounts;
	}

	/**
	 * @param IQueryBuilder $qb
	 * @param int $userId
	 * @param string $gid
	 * @return IQueryBuilder
	 */
	private function applyPredicates($qb, $userId, $gid) {
		// Adjust the query depending on availability of accountId and $backendGroupId
		// to have optimized access
		$alias = $this->getTableAlias();
		if ($this->userManager->isCached($userId) && $this->groupManager->isCached($gid)) {
			// No need to JOIN any tables, we already have all information required
			// Apply predicate on backend_group_id and account_id in memberships table
			$qb->where($qb->expr()->eq($alias.'.backend_group_id',
				$qb->createNamedParameter($this->groupManager->get($gid)->getBackendGroupId())));
			$qb->andWhere($qb->expr()->eq($alias.'.account_id',
				$qb->createNamedParameter($this->userManager->get($userId)->getAccountId())));
		} else if ($this->groupManager->isCached($gid)) {
			// Information of backendGroupId is cached and we can fetch it from group manager
			// We need to join with accounts table, since we miss information on accountId
			$qb->innerJoin($alias, $this->accountMapper->getTableName(),
				'a', $qb->expr()->eq('a.id', $alias.'.account_id'));

			// Apply predicate on user_id in accounts table
			$qb->where($qb->expr()->eq('a.user_id', $qb->createNamedParameter($userId)));

			// Apply predicate on backend_group_id in memberships table
			$qb->andWhere($qb->expr()->eq($alias.'.backend_group_id',
				$qb->createNamedParameter($this->groupManager->get($gid)->getBackendGroupId())));
		} else if ($this->userManager->isCached($userId)) {
			// Information of accountId is cached and we can fetch it from user manager
			// We need to join with backend group table, since we miss information on backendGroupId
			$qb->innerJoin($alias, $this->groupMapper->getTableName(),
				'g', $qb->expr()->eq('g.id', $alias.'.backend_group_id'));

			// Apply predicate on group_id in backend groups table
			$qb->where($qb->expr()->eq('g.group_id', $qb->createNamedParameter($gid)));

			// Apply predicate on account_id in memberships table
			$qb->andWhere($qb->expr()->eq($alias.'.account_id',
				$qb->createNamedParameter($this->userManager->get($userId)->getAccountId())));
		}
		return $qb;
	}

	/**
	 * @param IQueryBuilder $qb
	 * @param int $userId
	 * @return IQueryBuilder
	 */
	private function applyUserPredicates($qb, $userId) {
		// Adjust the query depending on availability of accountId
		// to have optimized access
		$alias = $this->getTableAlias();
		if ($this->userManager->isCached($userId)) {
			// Apply predicate on account_id in memberships table
			$accountId = $this->userManager->get($userId)->getAccountId();
			$qb->where($qb->expr()->eq($alias.'account_id', $qb->createNamedParameter($accountId)));
		} else {
			// We need to join with accounts table, since we miss information on accountId
			$qb->innerJoin($alias, $this->accountMapper->getTableName(), 'a', $qb->expr()->eq('a.id', $alias.'.account_id'));

			// Apply predicate on user_id in accounts table
			$qb->where($qb->expr()->eq('a.user_id', $qb->createNamedParameter($userId)));
		}
		return $qb;
	}

	/**
	 * @param IQueryBuilder $qb
	 * @param string $gid
	 * @return IQueryBuilder
	 */
	private function applyGroupPredicates($qb, $gid) {
		// Adjust the query depending on availability of accountId
		// to have optimized access
		$alias = $this->getTableAlias();
		if ($this->groupManager->isCached($gid)) {
			// Information of backendGroupId is cached and we can fetch it from group manager
			$backendGroupId = $this->groupManager->get($gid)->getBackendGroupId();
			$qb->where($qb->expr()->eq($alias.'backend_group_id', $qb->createNamedParameter($backendGroupId)));
		} else {
			// We need to join with backend group table, since we miss information on backendGroupId
			$qb->innerJoin($alias, $this->groupMapper->getTableName(), 'g', $qb->expr()->eq('g.id', $alias.'.backend_group_id'));

			// Apply predicate on group_id in backend groups table
			$qb->where($qb->expr()->eq('a.group_id', $qb->createNamedParameter($gid)));
		}
		return $qb;
	}
}
