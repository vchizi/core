<?php
/**
 * @author Thomas Müller <thomas.mueller@tmit.eu>
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


namespace OC\Files\Meta;


use OCP\Files\Folder;
use OCP\Files\InvalidPathException;
use OCP\Files\NotFoundException;
use OCP\Files\NotPermittedException;

class MetaFileIdNode implements Folder {

	private $fileId;

	public function __construct($fileId) {
		$this->fileId = $fileId;

	}

	/**
	 * Get the full mimetype of the file or folder i.e. 'image/png'
	 *
	 * @return string
	 * @since 7.0.0
	 */
	public function getMimetype() {
		// TODO: Implement getMimetype() method.
	}

	/**
	 * Get the first part of the mimetype of the file or folder i.e. 'image'
	 *
	 * @return string
	 * @since 7.0.0
	 */
	public function getMimePart() {
		// TODO: Implement getMimePart() method.
	}

	/**
	 * Check whether the file is encrypted
	 *
	 * @return bool
	 * @since 7.0.0
	 */
	public function isEncrypted() {
		// TODO: Implement isEncrypted() method.
	}

	/**
	 * Check whether this is a file or a folder
	 *
	 * @return \OCP\Files\FileInfo::TYPE_FILE|\OCP\Files\FileInfo::TYPE_FOLDER
	 * @since 7.0.0
	 */
	public function getType() {
		// TODO: Implement getType() method.
	}

	/**
	 * Check if a file or folder is shared
	 *
	 * @return bool
	 * @since 7.0.0
	 */
	public function isShared() {
		// TODO: Implement isShared() method.
	}

	/**
	 * Check if a file or folder is mounted
	 *
	 * @return bool
	 * @since 7.0.0
	 */
	public function isMounted() {
		// TODO: Implement isMounted() method.
	}

	/**
	 * Get the mountpoint the file belongs to
	 *
	 * @return \OCP\Files\Mount\IMountPoint
	 * @since 8.0.0
	 */
	public function getMountPoint() {
		// TODO: Implement getMountPoint() method.
	}

	/**
	 * Get the owner of the file
	 *
	 * @return \OCP\IUser
	 * @since 9.0.0
	 */
	public function getOwner() {
		// TODO: Implement getOwner() method.
	}

	/**
	 * Get the stored checksum for this file
	 *
	 * @return string
	 * @since 9.0.0
	 */
	public function getChecksum() {
		// TODO: Implement getChecksum() method.
	}

	/**
	 * Get the full path of an item in the folder within owncloud's filesystem
	 *
	 * @param string $path relative path of an item in the folder
	 * @return string
	 * @throws \OCP\Files\NotPermittedException
	 * @since 6.0.0
	 */
	public function getFullPath($path) {
		// TODO: Implement getFullPath() method.
	}

	/**
	 * Get the path of an item in the folder relative to the folder
	 *
	 * @param string $path absolute path of an item in the folder
	 * @throws \OCP\Files\NotFoundException
	 * @return string
	 * @since 6.0.0
	 */
	public function getRelativePath($path) {
		// TODO: Implement getRelativePath() method.
	}

	/**
	 * check if a node is a (grand-)child of the folder
	 *
	 * @param \OCP\Files\Node $node
	 * @return bool
	 * @since 6.0.0
	 */
	public function isSubNode($node) {
		// TODO: Implement isSubNode() method.
	}

	/**
	 * get the content of this directory
	 *
	 * @throws \OCP\Files\NotFoundException
	 * @return \OCP\Files\Node[]
	 * @since 6.0.0
	 */
	public function getDirectoryListing() {
		yield new MetaVersionCollection($this->fileId);
	}

	/**
	 * Get the node at $path
	 *
	 * @param string $path relative path of the file or folder
	 * @return \OCP\Files\Node
	 * @throws \OCP\Files\NotFoundException
	 * @since 6.0.0
	 */
	public function get($path) {
		$pieces = explode('/', $path);
		if($pieces[0] === 'v') {
			array_shift($pieces);
			$node = new MetaVersionCollection($this->fileId);
			if (empty($pieces)) {
				return $node;
			}
			return $node->get(implode('/', $pieces));
		}
		throw new \InvalidArgumentException();

	}

	/**
	 * Check if a file or folder exists in the folder
	 *
	 * @param string $path relative path of the file or folder
	 * @return bool
	 * @since 6.0.0
	 */
	public function nodeExists($path) {
		// TODO: Implement nodeExists() method.
	}

	/**
	 * Create a new folder
	 *
	 * @param string $path relative path of the new folder
	 * @return \OCP\Files\Folder
	 * @throws \OCP\Files\NotPermittedException
	 * @since 6.0.0
	 */
	public function newFolder($path) {
		// TODO: Implement newFolder() method.
	}

	/**
	 * Create a new file
	 *
	 * @param string $path relative path of the new file
	 * @return \OCP\Files\File
	 * @throws \OCP\Files\NotPermittedException
	 * @since 6.0.0
	 */
	public function newFile($path) {
		// TODO: Implement newFile() method.
	}

	/**
	 * search for files with the name matching $query
	 *
	 * @param string $query
	 * @return \OCP\Files\Node[]
	 * @since 6.0.0
	 */
	public function search($query) {
		// TODO: Implement search() method.
	}

	/**
	 * search for files by mimetype
	 * $mimetype can either be a full mimetype (image/png) or a wildcard mimetype (image)
	 *
	 * @param string $mimetype
	 * @return \OCP\Files\Node[]
	 * @since 6.0.0
	 */
	public function searchByMime($mimetype) {
		// TODO: Implement searchByMime() method.
	}

	/**
	 * search for files by tag
	 *
	 * @param string|int $tag tag name or tag id
	 * @param string $userId owner of the tags
	 * @return \OCP\Files\Node[]
	 * @since 8.0.0
	 */
	public function searchByTag($tag, $userId) {
		// TODO: Implement searchByTag() method.
	}

	/**
	 * get a file or folder inside the folder by it's internal id
	 *
	 * @param int $id
	 * @return \OCP\Files\Node[]
	 * @since 6.0.0
	 */
	public function getById($id) {
		// TODO: Implement getById() method.
	}

	/**
	 * Get the amount of free space inside the folder
	 *
	 * @return int
	 * @since 6.0.0
	 */
	public function getFreeSpace() {
		// TODO: Implement getFreeSpace() method.
	}

	/**
	 * Check if new files or folders can be created within the folder
	 *
	 * @return bool
	 * @since 6.0.0
	 */
	public function isCreatable() {
		// TODO: Implement isCreatable() method.
	}

	/**
	 * Add a suffix to the name in case the file exists
	 *
	 * @param string $name
	 * @return string
	 * @throws NotPermittedException
	 * @since 8.1.0
	 */
	public function getNonExistingName($name) {
		// TODO: Implement getNonExistingName() method.
	}

	/**
	 * Move the file or folder to a new location
	 *
	 * @param string $targetPath the absolute target path
	 * @throws \OCP\Files\NotPermittedException
	 * @return \OCP\Files\Node
	 * @since 6.0.0
	 */
	public function move($targetPath) {
		// TODO: Implement move() method.
	}

	/**
	 * Delete the file or folder
	 *
	 * @return void
	 * @since 6.0.0
	 */
	public function delete() {
		// TODO: Implement delete() method.
	}

	/**
	 * Cope the file or folder to a new location
	 *
	 * @param string $targetPath the absolute target path
	 * @return \OCP\Files\Node
	 * @since 6.0.0
	 */
	public function copy($targetPath) {
		// TODO: Implement copy() method.
	}

	/**
	 * Change the modified date of the file or folder
	 * If $mtime is omitted the current time will be used
	 *
	 * @param int $mtime (optional) modified date as unix timestamp
	 * @throws \OCP\Files\NotPermittedException
	 * @return void
	 * @since 6.0.0
	 */
	public function touch($mtime = null) {
		// TODO: Implement touch() method.
	}

	/**
	 * Get the storage backend the file or folder is stored on
	 *
	 * @return \OCP\Files\Storage
	 * @throws \OCP\Files\NotFoundException
	 * @since 6.0.0
	 */
	public function getStorage() {
		// TODO: Implement getStorage() method.
	}

	/**
	 * Get the full path of the file or folder
	 *
	 * @return string
	 * @since 6.0.0
	 */
	public function getPath() {
		// TODO: Implement getPath() method.
	}

	/**
	 * Get the path of the file or folder relative to the mountpoint of it's storage
	 *
	 * @return string
	 * @since 6.0.0
	 */
	public function getInternalPath() {
		// TODO: Implement getInternalPath() method.
	}

	/**
	 * Get the internal file id for the file or folder
	 *
	 * @return int
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0
	 */
	public function getId() {
		// TODO: Implement getId() method.
	}

	/**
	 * Get metadata of the file or folder
	 * The returned array contains the following values:
	 *  - mtime
	 *  - size
	 *
	 * @return array
	 * @since 6.0.0
	 */
	public function stat() {
		// TODO: Implement stat() method.
	}

	/**
	 * Get the modified date of the file or folder as unix timestamp
	 *
	 * @return int
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0
	 */
	public function getMTime() {
		// TODO: Implement getMTime() method.
	}

	/**
	 * Get the size of the file or folder in bytes
	 *
	 * @return int
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0
	 */
	public function getSize() {
		// TODO: Implement getSize() method.
	}

	/**
	 * Get the Etag of the file or folder
	 * The Etag is an string id used to detect changes to a file or folder,
	 * every time the file or folder is changed the Etag will change to
	 *
	 * @return string
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0
	 */
	public function getEtag() {
		// TODO: Implement getEtag() method.
	}

	/**
	 * Get the permissions of the file or folder as a combination of one or more of the following constants:
	 *  - \OCP\Constants::PERMISSION_READ
	 *  - \OCP\Constants::PERMISSION_UPDATE
	 *  - \OCP\Constants::PERMISSION_CREATE
	 *  - \OCP\Constants::PERMISSION_DELETE
	 *  - \OCP\Constants::PERMISSION_SHARE
	 *
	 * @return int
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0 - namespace of constants has changed in 8.0.0
	 */
	public function getPermissions() {
		// TODO: Implement getPermissions() method.
	}

	/**
	 * Check if the file or folder is readable
	 *
	 * @return bool
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0
	 */
	public function isReadable() {
		// TODO: Implement isReadable() method.
	}

	/**
	 * Check if the file or folder is writable
	 *
	 * @return bool
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0
	 */
	public function isUpdateable() {
		// TODO: Implement isUpdateable() method.
	}

	/**
	 * Check if the file or folder is deletable
	 *
	 * @return bool
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0
	 */
	public function isDeletable() {
		// TODO: Implement isDeletable() method.
	}

	/**
	 * Check if the file or folder is shareable
	 *
	 * @return bool
	 * @throws InvalidPathException
	 * @throws NotFoundException
	 * @since 6.0.0
	 */
	public function isShareable() {
		// TODO: Implement isShareable() method.
	}

	/**
	 * Get the parent folder of the file or folder
	 *
	 * @return Folder
	 * @since 6.0.0
	 */
	public function getParent() {
		// TODO: Implement getParent() method.
	}

	/**
	 * Get the filename of the file or folder
	 *
	 * @return string
	 * @since 6.0.0
	 */
	public function getName() {
		// TODO: Implement getName() method.
	}

	/**
	 * Acquire a lock on this file or folder.
	 *
	 * A shared (read) lock will prevent any exclusive (write) locks from being created but any number of shared locks
	 * can be active at the same time.
	 * An exclusive lock will prevent any other lock from being created (both shared and exclusive).
	 *
	 * A locked exception will be thrown if any conflicting lock already exists
	 *
	 * Note that this uses mandatory locking, if you acquire an exclusive lock on a file it will block *all*
	 * other operations for that file, even within the same php process.
	 *
	 * Acquiring any lock on a file will also create a shared lock on all parent folders of that file.
	 *
	 * Note that in most cases you won't need to manually manage the locks for any files you're working with,
	 * any filesystem operation will automatically acquire the relevant locks for that operation.
	 *
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @throws \OCP\Lock\LockedException
	 * @since 9.1.0
	 */
	public function lock($type) {
		// TODO: Implement lock() method.
	}

	/**
	 * Check the type of an existing lock.
	 *
	 * A shared lock can be changed to an exclusive lock is there is exactly one shared lock on the file,
	 * an exclusive lock can always be changed to a shared lock since there can only be one exclusive lock int he first place.
	 *
	 * A locked exception will be thrown when these preconditions are not met.
	 * Note that this is also the case if no existing lock exists for the file.
	 *
	 * @param int $targetType \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @throws \OCP\Lock\LockedException
	 * @since 9.1.0
	 */
	public function changeLock($targetType) {
		// TODO: Implement changeLock() method.
	}

	/**
	 * Release an existing lock.
	 *
	 * This will also free up the shared locks on any parent folder that were automatically acquired when locking the file.
	 *
	 * Note that this method will not give any sort of error when trying to free a lock that doesn't exist.
	 *
	 * @param int $type \OCP\Lock\ILockingProvider::LOCK_SHARED or \OCP\Lock\ILockingProvider::LOCK_EXCLUSIVE
	 * @throws \OCP\Lock\LockedException
	 * @since 9.1.0
	 */
	public function unlock($type) {
		// TODO: Implement unlock() method.
}}
