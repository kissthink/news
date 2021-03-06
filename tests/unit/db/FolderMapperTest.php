<?php

/**
* ownCloud - News
*
* @author Alessandro Cosentino
* @author Bernhard Posselt
* @copyright 2012 Alessandro Cosentino cosenal@gmail.com
* @copyright 2012 Bernhard Posselt dev@bernhard-posselt.com
*
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either
* version 3 of the License, or any later version.
*
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*
* You should have received a copy of the GNU Affero General Public
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
*
*/

namespace OCA\News\Db;

require_once(__DIR__ . "/../../classloader.php");


class FolderMapperTest extends \OCA\News\Utility\MapperTestUtility {

	private $folderMapper;
	private $folders;
	private $user;
	
	protected function setUp(){
		$this->beforeEach();

		$this->folderMapper = new FolderMapper($this->api);

		// create mock folders
		$folder1 = new Folder();
		$folder2 = new Folder();

		$this->folders = array(
			$folder1,
			$folder2
		);
		$this->user = 'hh';
	}


	public function testFind(){
		$userId = 'john';
		$id = 3;
		$rows = array(
		  array('id' => $this->folders[0]->getId()),
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
			
		$this->setMapperResult($sql, array($id, $userId), $rows);
		
		$result = $this->folderMapper->find($id, $userId);
		$this->assertEquals($this->folders[0], $result);
		
	}


	public function testFindNotFound(){
		$userId = 'john';
		$id = 3;
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
			
		$this->setMapperResult($sql, array($id, $userId));
		
		$this->setExpectedException('\OCA\News\Db\DoesNotExistException');
		$result = $this->folderMapper->find($id, $userId);	
	}
	

	public function testFindMoreThanOneResultFound(){
		$userId = 'john';
		$id = 3;
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `id` = ? ' .
			'AND `user_id` = ?';
		
		$this->setMapperResult($sql, array($id, $userId), $rows);
		
		$this->setExpectedException('\OCA\News\Db\MultipleObjectsReturnedException');
		$result = $this->folderMapper->find($id, $userId);
	}



	public function testFindAllFromUser(){
		$userId = 'john';
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `user_id` = ? ' .
			'AND `deleted_at` = 0';
		
		$this->setMapperResult($sql, array($userId), $rows);
		
		$result = $this->folderMapper->findAllFromUser($userId);
		$this->assertEquals($this->folders, $result);
	}


	public function testFindByName(){
		$folderName = 'heheh';
		$userId = 'john';
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `name` = ? ' .
			'AND `user_id` = ?';
		
		$this->setMapperResult($sql, array($folderName, $userId), $rows);
		
		$result = $this->folderMapper->findByName($folderName, $userId);
		$this->assertEquals($this->folders, $result);
	}


	public function testDelete(){
		$folder = new Folder();
		$folder->setId(3);

		$sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `id` = ?';
		$arguments = array($folder->getId());

		$sql2 = 'DELETE FROM `*PREFIX*news_feeds` WHERE `folder_id` = ?';

		$sql3 = 'DELETE FROM `*PREFIX*news_items` WHERE `feed_id` NOT IN '.
			'(SELECT `feeds`.`id` FROM `*PREFIX*news_feeds` `feeds`)';
		$arguments2 = array($folder->getId());

		$this->setMapperResult($sql, $arguments);
		$this->setMapperResult($sql2, $arguments2);
		$this->setMapperResult($sql3);

		$this->folderMapper->delete($folder);
	}


	public function testGetPurgeDeleted(){
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$deleteOlderThan = 110;
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `deleted_at` < ? ';
		$this->setMapperResult($sql, array($deleteOlderThan), $rows);
		$result = $this->folderMapper->getToDelete($deleteOlderThan);

		$this->assertEquals($this->folders, $result);
	}



	public function testGetPurgeDeletedUser(){
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$deleteOlderThan = 110;
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `deleted_at` < ? ' .
			'AND `user_id` = ?';
		$this->setMapperResult($sql, array($deleteOlderThan, $this->user), $rows);
		$result = $this->folderMapper->getToDelete($deleteOlderThan, $this->user);

		$this->assertEquals($this->folders, $result);
	}


	public function testGetAllPurgeDeletedUser(){
		$rows = array(
			array('id' => $this->folders[0]->getId()),
			array('id' => $this->folders[1]->getId())
		);
		$deleteOlderThan = 110;
		$sql = 'SELECT * FROM `*PREFIX*news_folders` ' .
			'WHERE `deleted_at` > 0 ' .
			'AND `user_id` = ?';
		$this->setMapperResult($sql, array($this->user), $rows);
		$result = $this->folderMapper->getToDelete(null, $this->user);

		$this->assertEquals($this->folders, $result);
	}


	public function testDeleteFromUser(){
		$userId = 'john';
		$sql = 'DELETE FROM `*PREFIX*news_folders` WHERE `user_id` = ?';

		$this->setMapperResult($sql, array($userId));

		$this->folderMapper->deleteUser($userId);
	}


}