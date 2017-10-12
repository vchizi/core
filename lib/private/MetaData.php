<?php
/**
 * @author Sujith Haridasan <sharidasan@owncloud.com>
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

use OC\User\User;
use OCP\Files\Folder;
use OCP\IL10N;
use OCP\ILogger;
use OCP\IMetaData;


class MetaData implements IMetaData {
	/** @var Folder */
	private $folder;
	/** @var IL10N */
	private $l;
	/** @var User */
	private $user;
	/** @var ILogger  */
	private $logger;

	private $metadataInfo = [];

	private $metaPath;

	/**
	 * constructor
	 *
	 * @param Folder $folder the root folder
	 * @param IL10N $l
	 * @param User $user
	 * @param ILogger $logger
	 */
	public function __construct (Folder $folder, IL10N $l, $user, ILogger $logger) {
		$this->folder = $folder;
		$this->l = $l;
		$this->user = $user;
		$this->logger = $logger;
		$this->metadataInfo = [
			'user' => $user,
			'fileId' => '',
			'uuid_fileid' => ''
		];
		$this->metaPath = $this->user->getHome() . '/meta/';
	}

	protected function generate_uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}

	public function getMetaData() {
		return $this->metadataInfo;
	}

	public function getFileId() {
		return $this->metadataInfo['fileId'];
	}

	public function setFileId() {
		if (!$this->folder->nodeExists($this->metadataInfo['fileId'])) {
			$this->metadataInfo['uuid_fileid'] = $this->generate_uuid();
			$this->metadataInfo['fileId'] = $this->folder->getId();
			$this->folder->newFolder($this->metaPath . $this->metadataInfo['uuid_fileid']);
		}
	}

	public function newMetaFolder() {
		if (!$this->folder->nodeExists($this->metaPath)) {
			$this->folder->newFolder($this->metaPath);
		}
	}

	public function newMetaSubFolders($folderName) {
		if (($folderName === 'p') || ($folderName === 'v')) {
			if (!$this->folder->nodeExists($folderName)) {
				$this->folder->newFolder($this->metaPath . $folderName);
			}
		}
	}
}
