<?php
/*
** Zabbix
** Copyright (C) 2001-2023 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/

require_once dirname(__FILE__).'/../../include/CLegacyWebTest.php';
require_once dirname(__FILE__).'/../../../include/items.inc.php';
require_once dirname(__FILE__).'/../behaviors/CMessageBehavior.php';

/**
 * Test the creation of inheritance of new objects on a previously linked template.
 *
 * @backup items
 */
class testInheritanceItem extends CLegacyWebTest {
	private $templateid = 15000;	// 'Inheritance test template'
	private $template  = 'Inheritance test template';

	private $hostid = 15001;		// 'Template inheritance test host'
	private $host = 'Template inheritance test host';

	/**
	 * Attach MessageBehavior to the test.
	 */
	public function getBehaviors() {
		return [CMessageBehavior::class];
	}

	// returns list of items from a template
	public static function update() {
		return CDBHelper::getDataProvider(
			'SELECT itemid'.
			' FROM items'.
			' WHERE hostid=15000'.	//	$this->templateid.
				' AND flags=0'.
				' AND type<>'.ITEM_TYPE_HTTPTEST
		);
	}

	/**
	 * @dataProvider update
	 */
	public function testInheritanceItem_SimpleUpdate($data) {
		$sqlItems = 'SELECT * FROM items ORDER BY itemid';
		$oldHashItems = CDBHelper::getHash($sqlItems);

		$this->page->login()->open('zabbix.php?action=item.list&filter_set=1&filter_hostids[0]=15001&context=host');
		$this->query('link:'.CDBHelper::getValue('SELECT name from items WHERE itemid='.$data['itemid']))->one()->click();
		COverlayDialogElement::find()->one()->waitUntilready()->getFooter()->query('button:Update')->one()->click();
		COverlayDialogElement::ensureNotPresent();
		$this->zbxTestCheckTitle('Configuration of items');
		$this->zbxTestTextPresent('Item updated');

		$this->assertEquals($oldHashItems, CDBHelper::getHash($sqlItems));
	}

	// Returns create data
	public static function create() {
		return [
			[
				[
					'expected' => TEST_GOOD,
					'name' => 'testInheritanceItem5',
					'key' => 'test-inheritance-item5'
				]
			],
			[
				[
					'expected' => TEST_BAD,
					'name' => 'itemInheritance',
					'key' => 'key-item-inheritance',
					'errors' => [
						'Cannot inherit item with key "key-item-inheritance" of template "Inheritance test template" to host '.
							'"Template inheritance test host", because an item with the same key is already inherited '.
							'from template "Inheritance test template 2".'
					]
				]
			]
		];
	}

	/**
	 * @dataProvider create
	 */
	public function testInheritanceItem_SimpleCreate($data) {
		$this->page->login()->open('zabbix.php?action=item.list&filter_set=1&filter_hostids[0]='.$this->templateid.'&context=template');
		$this->query('button:Create item')->one()->click();
		$dialog = COverlayDialogElement::find()->one()->waitUntilReady();
		$form = $dialog->asForm();
		$form->getField('Name')->fill($data['name']);
		$form->getField('Key')->fill($data['key']);
		$dialog->getFooter()->query('button:Add')->one()->click();

		switch ($data['expected']) {
			case TEST_GOOD:
				COverlayDialogElement::ensureNotPresent();
				$this->zbxTestCheckTitle('Configuration of items');
				$this->zbxTestCheckHeader('Items');
				$this->zbxTestTextPresent('Item added');

				$itemId = 0;

				// template
				$dbResult = DBselect(
					'SELECT itemid,name,templateid'.
					' FROM items'.
					' WHERE hostid='.$this->templateid.
						' AND key_='.zbx_dbstr($data['key']).
						' AND flags=0'
				);
				if ($dbRow = DBfetch($dbResult)) {
					$itemId = $dbRow['itemid'];
					$this->assertEquals($dbRow['name'], $data['name']);
					$this->assertEquals($dbRow['templateid'], 0);
				}

				$this->assertNotEquals($itemId, 0);

				// host
				$dbResult = DBselect(
					'SELECT key_,name'.
					' FROM items'.
					' WHERE hostid='.$this->hostid.
						' AND templateid='.$itemId.
						' AND flags=0'
				);
				if ($dbRow = DBfetch($dbResult)) {
					$this->assertEquals($dbRow['key_'], $data['key']);
					$this->assertEquals($dbRow['name'], $data['name']);
				}
				break;

			case TEST_BAD:
				$this->zbxTestCheckTitle('Configuration of items');
				$this->zbxTestCheckHeader('Items');
				$this->zbxTestTextNotPresent('Item added');
				$this->assertMessage(TEST_BAD, 'Cannot add item', $data['errors']);
				break;
		}
	}
}
